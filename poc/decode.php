#!/usr/bin/env php
<?php

/**
 * PoC de decodificación DLStorage — solo para análisis de seguridad autorizado.
 *
 * Demuestra que el formato .dlstorage es ofuscación reversible, no cifrado.
 * Uso:
 *   php poc/decode.php --hex <cadena_hex>
 *   php poc/decode.php --file <ruta.dlstorage> --key "mi llave"
 *   php poc/decode.php --file <ruta.dlstorage> --dict
 *
 * @internal Herramienta de auditoría del propietario. No usar contra sistemas ajenos.
 */

declare(strict_types=1);

const SEED = 530000;
const SIGNATURE = 'DLStorage';

// ---------------------------------------------------------------------------
// Parser del formato binario
// ---------------------------------------------------------------------------

/**
 * @return array{
 *     signature: string,
 *     header_size: int,
 *     version: string,
 *     payload_size: int,
 *     payload_hex: string,
 *     total_bytes: int
 * }
 */
function parse_dlstorage(string $binary): array
{
    $total = strlen($binary);

    if ($total < 9 + 4) {
        throw new RuntimeException('Archivo demasiado corto para ser DLStorage.');
    }

    $signature = substr($binary, 0, 9);

    if ($signature !== SIGNATURE) {
        throw new RuntimeException(
            sprintf('Firma inválida: "%s" (se esperaba "%s").', $signature, SIGNATURE)
        );
    }

    $header_size = unpack('N', substr($binary, 9, 4))[1];
    $offset = 13;

    if ($offset + $header_size + 4 > $total) {
        throw new RuntimeException('Cabecera corrupta: tamaño de versión fuera de rango.');
    }

    $version = substr($binary, $offset, $header_size);
    $offset += $header_size;

    $payload_size = unpack('N', substr($binary, $offset, 4))[1];
    $offset += 4;

    if ($offset + $payload_size > $total) {
        throw new RuntimeException(
            sprintf('Payload truncado: declara %d bytes, hay %d disponibles.', $payload_size, $total - $offset)
        );
    }

    $payload_binary = substr($binary, $offset, $payload_size);
    $payload_hex = bin2hex($payload_binary);

    // Misma normalización que SaveData::delete_padding()
    $payload_hex = (string) preg_replace('/^0+/', '0', $payload_hex);

    return [
        'signature' => $signature,
        'header_size' => $header_size,
        'version' => $version,
        'payload_size' => $payload_size,
        'payload_hex' => $payload_hex,
        'total_bytes' => $total,
    ];
}

// ---------------------------------------------------------------------------
// Motor de decodificación (réplica de Data + BinaryLengthTrait)
// ---------------------------------------------------------------------------

function calculate_coefficient(int|float $seed): int
{
    $max = 0xffffffffffff;
    return max(1, abs((int) ((37 * $seed + 113) % $max)));
}

function get_entropy_value(string $input): array
{
    /** @var array<int, int> $bytes */
    $bytes = unpack('C*', $input);
    $byte_sum = array_sum($bytes);

    // calculate_coefficient() usa solo la suma de bytes; el valor retornado suma strlen().
    $coefficient = calculate_coefficient($byte_sum);

    return ['sum' => $byte_sum + strlen($input), 'coefficient' => $coefficient];
}

function get_circular_value(int $coefficient, int|float $value): int
{
    return abs((int) (($coefficient * $value + 17) % 100 + 17));
}

function get_entropy(int $index, int $sum, int $coefficient): int
{
    return 2 * get_circular_value($coefficient, $index) + $sum;
}

function expand_zero(string $input): string
{
    $blocks = explode('01', $input);
    $buffer = [];

    foreach ($blocks as $block) {
        if (!is_string($block) || trim($block) === '') {
            continue;
        }
        $block = str_replace('ffff', '01', $block);
        $buffer[] = str_pad($block, 10, '0', STR_PAD_LEFT);
    }

    return implode('', $buffer);
}

function from_hex40(string $block, int $key, int $sum, int $coefficient): string
{
    $entropy = get_entropy($key, $sum, $coefficient);
    $value = hexdec($block) - (SEED + $entropy);

    return str_pad(dechex($value), 2, '0', STR_PAD_LEFT);
}

function decode_payload(string $payload_hex, ?string $entropy_key): string
{
    $sum = 0;
    $coefficient = 0;

    if (is_string($entropy_key)) {
        ['sum' => $sum, 'coefficient' => $coefficient] = get_entropy_value($entropy_key);
    }

    $expanded = expand_zero($payload_hex);
    $blocks = str_split($expanded, 10);

    if ($blocks === [] || ($blocks[count($blocks) - 1] === '' && count($blocks) > 1)) {
        array_pop($blocks);
    }

    $hex_out = '';

    foreach ($blocks as $index => $block) {
        if (strlen($block) !== 10) {
            throw new RuntimeException(sprintf('Bloque %d tiene longitud %d (se esperaban 10).', $index, strlen($block)));
        }
        $hex_out .= from_hex40($block, $index, $sum, $coefficient);
    }

    $decoded = hex2bin($hex_out);
    if ($decoded === false) {
        throw new RuntimeException('hex2bin falló tras la reversión de bloques.');
    }

    return $decoded;
}

// ---------------------------------------------------------------------------
// Ataque por fuerza bruta de sum/coefficient (SIN la llave de entropía)
// ---------------------------------------------------------------------------

/**
 * Intenta decodificar variando sum y coefficient derivado de byte_sum.
 *
 * La llave de entropía NO se usa directamente: solo aporta dos enteros:
 *   sum         = array_sum(bytes_de_la_llave) + strlen(llave)
 *   coefficient = f(array_sum(bytes_de_la_llave))
 *
 * Espacio de búsqueda: ~sum_max × strlen_max (~1M combinaciones, segundos en CPU).
 *
 * @return list<array{sum: int, strlen: int, byte_sum: int, coefficient: int, score: float, content: string}>
 */
function brute_force_sum_attack(
    string $payload_hex,
    int $sum_max = 10000,
    int $strlen_max = 100
): array {
    $hits = [];

    for ($sum = 0; $sum <= $sum_max; $sum++) {
        for ($strlen = 1; $strlen <= $strlen_max; $strlen++) {
            $byte_sum = $sum - $strlen;
            if ($byte_sum < 0) {
                continue;
            }

            $coefficient = calculate_coefficient($byte_sum);

            try {
                $content = decode_payload_with_params($payload_hex, $sum, $coefficient);
            } catch (Throwable) {
                continue;
            }

            if (!is_mostly_printable($content)) {
                continue;
            }

            $hits[] = [
                'sum' => $sum,
                'strlen' => $strlen,
                'byte_sum' => $byte_sum,
                'coefficient' => $coefficient,
                'score' => score_plaintext($content),
                'content' => $content,
            ];
        }
    }

    usort($hits, static fn(array $a, array $b): int => $b['score'] <=> $a['score']);

    return $hits;
}

function decode_payload_with_params(string $payload_hex, int $sum, int $coefficient): string
{
    $expanded = expand_zero($payload_hex);
    $blocks = str_split($expanded, 10);
    $hex_out = '';

    foreach ($blocks as $index => $block) {
        if (strlen($block) !== 10) {
            throw new RuntimeException('Bloque inválido.');
        }
        $hex_out .= from_hex40_with_params($block, $index, $sum, $coefficient);
    }

    if ((mb_strlen($hex_out, 'UTF-8') & 1) !== 0) {
        throw new RuntimeException('Longitud impar tras decodificación.');
    }

    $decoded = hex2bin($hex_out);
    if ($decoded === false) {
        throw new RuntimeException('hex2bin falló.');
    }

    return $decoded;
}

function from_hex40_with_params(string $block, int $key, int $sum, int $coefficient): string
{
    $entropy = get_entropy($key, $sum, $coefficient);
    $value = hexdec($block) - (SEED + $entropy);

    return str_pad(dechex($value), 2, '0', STR_PAD_LEFT);
}

function score_plaintext(string $text): float
{
    $len = strlen($text);
    if ($len === 0) {
        return 0.0;
    }

    $spaces = substr_count($text, ' ');
    $words = str_word_count($text, 0, 'áéíóúñÁÉÍÓÚÑ');
    $alpha = (int) preg_match_all('/[a-zA-Záéíóúñ]/u', $text);

    return $spaces * 10.0 + $words * 5.0 + ($alpha / $len) * 20.0;
}

// ---------------------------------------------------------------------------
// Ataque por diccionario (demostración)
// ---------------------------------------------------------------------------

/** @return list<string> */
function default_dictionary(): array
{
    return [
        'Entorno de programación',
        'Entropia de prueba',
        'MiClaveDeEntropia',
        'dlstorage',
        'DLStorage',
        'password',
        'secret',
        'app_key',
        'APP_KEY',
    ];
}

/**
 * @return list<array{key: string, content: string}>
 */
function dictionary_attack(string $payload_hex, array $candidates): array
{
    $hits = [];

    foreach ($candidates as $key) {
        try {
            $content = decode_payload($payload_hex, $key);
            if (is_mostly_printable($content)) {
                $hits[] = ['key' => $key, 'content' => $content];
            }
        } catch (Throwable) {
            continue;
        }
    }

    return $hits;
}

function is_mostly_printable(string $text): bool
{
    if ($text === '') {
        return false;
    }

    $printable = 0;
    $len = strlen($text);

    for ($i = 0; $i < $len; $i++) {
        $ord = ord($text[$i]);
        if ($ord >= 32 && $ord <= 126 || in_array($ord, [9, 10, 13], true)) {
            $printable++;
        }
    }

    return ($printable / $len) >= 0.85;
}

// ---------------------------------------------------------------------------
// CLI
// ---------------------------------------------------------------------------

function usage(): void
{
    $self = basename(__FILE__);
    fwrite(STDERR, <<<TXT
PoC de decodificación DLStorage (auditoría de seguridad)

Estructura del archivo:
  [9 bytes]  Firma        → "DLStorage" (ASCII)
  [4 bytes]  Header size  → uint32 big-endian (longitud de la versión)
  [N bytes]  Versión      → ASCII (ej. "v0.1.0")
  [4 bytes]  Payload size → uint32 big-endian
  [M bytes]  Payload      → contenido transformado (hex empaquetado en binario)

Uso:
  php poc/{$self} --hex <cadena_hex_completa>
  php poc/{$self} --file <archivo.dlstorage> [--key "llave de entropía"]
  php poc/{$self} --file <archivo.dlstorage> --dict [--wordlist archivo.txt]
  php poc/{$self} --hex <cadena_hex> --bruteforce

Opciones:
  --hex       Volcado hexadecimal del archivo completo
  --file      Ruta a un .dlstorage
  --key       Llave de entropía usada al codificar
  --dict      Probar diccionario de llaves comunes
  --bruteforce  Ataque sin llave: fuerza bruta de sum/coefficient
  --wordlist  Una llave por línea (opcional, con --dict)
  --json      Salida en JSON

TXT);
}

/** @return array<string, mixed> */
function parse_args(array $argv): array
{
    $args = [
        'hex' => null,
        'file' => null,
        'key' => null,
        'dict' => false,
        'bruteforce' => false,
        'wordlist' => null,
        'json' => false,
    ];

    for ($i = 1; $i < count($argv); $i++) {
        match ($argv[$i]) {
            '--hex' => $args['hex'] = $argv[++$i] ?? null,
            '--file' => $args['file'] = $argv[++$i] ?? null,
            '--key' => $args['key'] = $argv[++$i] ?? null,
            '--wordlist' => $args['wordlist'] = $argv[++$i] ?? null,
            '--dict' => $args['dict'] = true,
            '--bruteforce' => $args['bruteforce'] = true,
            '--json' => $args['json'] = true,
            '--help', '-h' => usage() || exit(0),
            default => throw new RuntimeException("Opción desconocida: {$argv[$i]}"),
        };
    }

    if ($args['hex'] === null && $args['file'] === null) {
        usage();
        exit(1);
    }

    return $args;
}

function print_report(array $parsed, ?string $decoded, ?string $key, bool $json): void
{
    $entropy_info = null;
    if ($key !== null) {
        $entropy_info = get_entropy_value($key);
    }

    $report = [
        'format' => [
            'signature' => $parsed['signature'],
            'header_size' => $parsed['header_size'],
            'version' => $parsed['version'],
            'payload_size' => $parsed['payload_size'],
            'total_bytes' => $parsed['total_bytes'],
            'payload_hex_length' => strlen($parsed['payload_hex']),
        ],
        'entropy_key' => $key,
        'entropy_sum' => $entropy_info['sum'] ?? 0,
        'entropy_coefficient' => $entropy_info['coefficient'] ?? 0,
        'decoded' => $decoded,
        'decoded_hex' => $decoded !== null ? bin2hex($decoded) : null,
        'decoded_length' => $decoded !== null ? strlen($decoded) : 0,
    ];

    if ($json) {
        echo json_encode($report, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n";
        return;
    }

    echo "=== Estructura DLStorage ===\n";
    echo sprintf("  Firma         : %s\n", $parsed['signature']);
    echo sprintf("  Header size   : %d bytes\n", $parsed['header_size']);
    echo sprintf("  Versión       : %s\n", $parsed['version']);
    echo sprintf("  Payload size  : %d bytes\n", $parsed['payload_size']);
    echo sprintf("  Total archivo : %d bytes\n", $parsed['total_bytes']);
    echo sprintf("  Payload (hex) : %d caracteres\n", strlen($parsed['payload_hex']));
    echo "\n";

    if ($key !== null) {
        echo "=== Entropía ===\n";
        echo sprintf("  Llave         : %s\n", $key);
        echo sprintf("  Suma (sum)    : %d\n", $entropy_info['sum']);
        echo sprintf("  Coeficiente   : %d\n", $entropy_info['coefficient']);
        echo sprintf("  Seed fijo     : %d\n", SEED);
        echo "\n";
    }

    if ($decoded !== null) {
        echo "=== Contenido decodificado ===\n";
        echo $decoded . "\n";
        echo "\n";
        echo sprintf("  Longitud : %d bytes\n", strlen($decoded));
        echo sprintf("  Hex      : %s\n", bin2hex($decoded));
    }
}

function main(array $argv): int
{
    try {
        $args = parse_args($argv);

        if ($args['file'] !== null) {
            if (!is_readable($args['file'])) {
                throw new RuntimeException("No se puede leer: {$args['file']}");
            }
            $binary = file_get_contents($args['file']);
            if ($binary === false) {
                throw new RuntimeException('Error al leer el archivo.');
            }
        } else {
            $hex = preg_replace('/\s+/', '', (string) $args['hex']);
            $binary = hex2bin((string) $hex);
            if ($binary === false) {
                throw new RuntimeException('Cadena hexadecimal inválida.');
            }
        }

        $parsed = parse_dlstorage($binary);

        if ($args['bruteforce']) {
            $hits = brute_force_sum_attack($parsed['payload_hex']);

            if ($args['json']) {
                echo json_encode(['format' => $parsed, 'bruteforce_hits' => array_slice($hits, 0, 10)], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n";
                return 0;
            }

            print_report($parsed, null, null, false);
            echo "=== Ataque por fuerza bruta (SIN llave de entropía) ===\n";
            echo "  Parámetros buscados: sum (0..10000) × strlen (1..100)\n";
            echo "  Candidatos legibles : " . count($hits) . "\n\n";

            foreach (array_slice($hits, 0, 5) as $rank => $hit) {
                echo sprintf(
                    "  #%d sum=%d strlen=%d byte_sum=%d coeff=%d score=%.1f\n     %s\n\n",
                    $rank + 1,
                    $hit['sum'],
                    $hit['strlen'],
                    $hit['byte_sum'],
                    $hit['coefficient'],
                    $hit['score'],
                    $hit['content']
                );
            }

            return 0;
        }

        if ($args['dict']) {
            $candidates = default_dictionary();
            if ($args['wordlist'] !== null) {
                $extra = file($args['wordlist'], FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
                if ($extra !== false) {
                    $candidates = array_merge($candidates, $extra);
                }
            }
            $hits = dictionary_attack($parsed['payload_hex'], array_unique($candidates));

            if ($args['json']) {
                echo json_encode(['format' => $parsed, 'hits' => $hits], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n";
                return 0;
            }

            print_report($parsed, null, null, false);
            echo "=== Ataque por diccionario ===\n";
            if ($hits === []) {
                echo "  Sin coincidencias legibles.\n";
            } else {
                foreach ($hits as $hit) {
                    echo sprintf("  Llave: \"%s\"\n", $hit['key']);
                    echo sprintf("  Texto: %s\n\n", $hit['content']);
                }
            }
            return 0;
        }

        $decoded = null;
        if ($args['key'] !== null) {
            $decoded = decode_payload($parsed['payload_hex'], $args['key']);
        } elseif ($args['hex'] !== null) {
            // Con --hex sin --key, probar sin llave y avisar
            try {
                $decoded = decode_payload($parsed['payload_hex'], null);
                fwrite(STDERR, "⚠ Decodificado sin llave (sum=0). Probablemente incorrecto.\n\n");
            } catch (Throwable $e) {
                fwrite(STDERR, "⚠ Sin llave no se pudo decodificar: {$e->getMessage()}\n");
                fwrite(STDERR, "  Usa --key o --dict.\n\n");
            }
        }

        print_report($parsed, $decoded, $args['key'], (bool) $args['json']);

        if ($decoded === null && $args['key'] === null) {
            fwrite(STDERR, "Sugerencia: php poc/decode.php --file ... --dict\n");
        }

        return 0;
    } catch (Throwable $e) {
        fwrite(STDERR, "Error: {$e->getMessage()}\n");
        return 1;
    }
}

if (realpath($argv[0] ?? '') === realpath(__FILE__)) {
    exit(main($argv));
}