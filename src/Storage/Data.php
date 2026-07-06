<?php

/**
 * DLUnire
 * Copyright (C) 2026 David E Luna M
 *
 * Operando bajo el establecimiento de comercio "DLUnire",
 * NIT 700551569-1, matrícula mercantil Nº 10007069
 * (matrícula mercantil personal Nº 10007068).
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as
 * published by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public
 * License along with this program. If not, see
 * <https://www.gnu.org/licenses/>.
 */

declare(strict_types=1);

namespace DLStorage\Storage;

use DLStorage\Errors\EncodeException;
use DLStorage\Traits\BinaryLengthTrait;
use DLStorage\Traits\ForTrait;

/**
 * Codificación y decodificación de datos mediante transformación con entropía.
 *
 * Cada byte de entrada se convierte en un bloque hexadecimal de 10 caracteres
 * ({@see BinaryLengthTrait::to_hex40()}), opcionalmente alterado por una llave
 * de entropía. Los bloques se concatenan y forman el payload de un archivo `.dlstorage`.
 *
 * @package    DLStorage\Storage
 * @version    v0.2.0
 * @license    AGPL-3.0-or-later
 * @author     David E. Luna M. <info@dlunire.dev>
 * @copyright  Copyright (c) 2026 David E. Luna M.
 *
 * @abstract
 */
abstract class Data {

    use ForTrait;
    use BinaryLengthTrait;

    /**
     * Reservado para extensiones futuras. No utilizado en v0.2.0.
     *
     * @var int
     */
    private int $last_offset = 0;

    /**
     * Codifica una cadena de bytes en bloques hexadecimales concatenados.
     *
     * Recorre `$input` byte a byte con {@see ForTrait::foreach_string()}, aplica
     * desplazamiento por entropía (`to_hex40`), sustituye `01` por `ffff` y compacta
     * ceros iniciales con {@see compact_zero()}.
     *
     * @param string      $input   Datos crudos (texto o binario).
     * @param string|null $entropy Llave opcional. Si es `null`, la suma base es 0.
     *
     * @return string Cadena hexadecimal concatenada (múltiplos de 10 caracteres por bloque).
     */
    public function encode(string $input, ?string $entropy = null): string {
        $sum = 0;
        $string_data = "";
        $this->set_entropy_value($sum, $entropy);
        $this->set_entropy($input, $string_data, $sum);

        return $string_data;
    }

    /**
     * Decodifica una cadena previamente codificada con {@see encode()}.
     *
     * 1. Restaura ceros compactados con {@see expand_zero()}.
     * 2. Divide en bloques de 10 caracteres hex.
     * 3. Revierte la entropía con {@see get_reverse_entropy()}.
     * 4. Valida que la longitud UTF-8 del resultado sea par; si no, lanza excepción.
     *
     * @param string      $encoded Cadena hexadecimal producida por `encode()`.
     * @param string|null $entropy Misma llave usada al codificar.
     *
     * @return string Cadena de bytes originales (caracteres de un solo byte).
     *
     * @throws EncodeException Si la longitud resultante es impar (entropía incorrecta o datos corruptos, código 403).
     */
    public function get_decode(string $encoded, ?string $entropy = null): string {
        $this->expand_zero($encoded);
        /** @var string[] $blocks */
        $blocks = str_split($encoded, 10);

        $value = $this->get_reverse_entropy($blocks, $entropy);

        $length = mb_strlen($value, 'UTF-8');
        $is_pair = ($length & 1) == 0;

        if (!$is_pair) {
            throw new EncodeException("Es posible que la llave de la entropía sea inválida o los datos se hayan corrompidos.", 403);
        }

        return $value;
    }

    /**
     * Decodifica hexadecimal a binario.
     *
     * Equivalente a `hex2bin(get_decode($encode, $entropy))`.
     *
     * @param string      $encode  Payload hexadecimal del archivo.
     * @param string|null $entropy Llave de entropía usada al codificar.
     *
     * @return string Contenido binario original.
     *
     * @throws EncodeException Si la decodificación falla (propagada desde {@see get_decode()}).
     */
    public function get_content(string $encode, ?string $entropy = null): string {
        return hex2bin($this->get_decode($encode, $entropy));
    }

    /**
     * Compacta ceros iniciales reemplazándolos por el marcador `01`.
     *
     * Aplica `preg_replace("/^0+/", '01', $input)` sobre la cadena pasada por referencia.
     * Si `preg_replace` falla, la cadena no se modifica.
     *
     * @param string $input Cadena hexadecimal a compactar (por referencia).
     */
    protected function compact_zero(string &$input): void {
        $input = preg_replace("/^0+/", '01', $input);

        if (!is_string($input)) {
            return;
        }
    }

    /**
     * Expande el marcador `01` restaurando bloques de ceros compactados.
     *
     * Divide `$input` por `01`, descarta segmentos vacíos, reemplaza `ffff` por `01`
     * en cada bloque y rellena cada uno a 10 caracteres con {@see get_padding_zero()}.
     * El resultado reemplaza `$input` por referencia.
     *
     * @param string $input Cadena compactada (por referencia).
     */
    public function expand_zero(string &$input): void {
        /** @var string[] $blocks */
        $blocks = explode("01", $input);

        /** @var string[] $buffer */
        $buffer = [];

        foreach ($blocks as $block) {
            if (!is_string($block) || empty(trim($block))) {
                continue;
            }
            $block = str_replace("ffff", "01", $block);
            $buffer[] = $this->get_padding_zero($block);
        }

        $input = implode("", $buffer);
    }

    /**
     * Rellena una cadena hexadecimal hasta 10 caracteres.
     *
     * @param string $input Secuencia hexadecimal.
     * @param bool   $right `false` (defecto): relleno izquierdo. `true`: relleno derecho.
     *
     * @return string Cadena de exactamente 10 caracteres hex.
     */
    private function get_padding_zero(string $input, bool $right = false): string {
        return str_pad(
            string: $input,
            length: 10,
            pad_string: '0',
            pad_type: $right ? STR_PAD_RIGHT : STR_PAD_LEFT
        );
    }

    /**
     * Construye la cadena codificada procesando cada byte de `$input`.
     *
     * Por cada byte: calcula entropía con {@see BinaryLengthTrait::get_entropy()},
     * convierte con `to_hex40()`, reemplaza `01` → `ffff`, compacta ceros y acumula
     * el bloque en `$string_data`.
     *
     * @param string       $input       Datos de entrada en bytes crudos.
     * @param string       $string_data Acumulador de salida (por referencia).
     * @param int|float    $sum         Valor base de entropía (0 si no se proporcionó llave).
     */
    private function set_entropy(string $input, string &$string_data, int|float $sum = 0): void {

        /** @var string[] $buffer */
        $buffer = [];

        $this->foreach_string($input, function (int $byte, int $index) use ($sum, &$buffer) {

            $entropy = $this->get_entropy($index, $sum);

            $current_data = $this->to_hex40($byte, $entropy);

            $current_data = str_replace("01", "ffff", $current_data);

            $this->compact_zero($current_data);

            $buffer[] = $current_data;
        });

        $string_data = implode("", $buffer);
    }

    /**
     * Revierte la entropía de bloques hex de 10 caracteres.
     *
     * Por cada bloque llama a {@see BinaryLengthTrait::from_hex40()} con su índice
     * y la suma de entropía, concatenando los bytes resultantes.
     *
     * @param string[]    $blocks  Bloques de 10 caracteres hex.
     * @param string|null $entropy Llave usada al codificar; `null` deja suma en 0.
     *
     * @return string Cadena de caracteres de un byte reconstruida.
     */
    private function get_reverse_entropy(array &$blocks, ?string $entropy = null): string {

        $sum = 0;

        $this->set_entropy_value($sum, $entropy);

        /** @var string[] $buffer */
        $buffer = [];

        foreach ($blocks as $key => $block) {
            $buffer[] = $this->from_hex40($block, $key, $sum);
        }

        return implode("", $buffer);
    }

    /**
     * Calcula `hexdec(bin2hex($char)) + $index` para un carácter y su posición.
     *
     * @internal No invocado por la biblioteca en v0.2.0. Reservado para uso futuro.
     *
     * @param string $char  Carácter de un byte.
     * @param int    $index Índice en la cadena.
     *
     * @return int Valor numérico ajustado.
     */
    private function get_char_code(string $char, int $index): int {
        return hexdec(bin2hex($char)) + $index;
    }
}