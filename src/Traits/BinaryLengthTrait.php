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

namespace DLStorage\Traits;

use DLStorage\Errors\StorageException;

/**
 * Transformación numérica de bytes y cálculo heurístico de entropía.
 *
 * @package   DLStorage\Traits
 * @version   v0.2.0
 * @license   AGPL-3.0-or-later
 * @author    David E. Luna M. <info@dlunire.dev>
 * @copyright Copyright (c) 2026 David E. Luna M.
 *
 * @see \DLStorage\Storage\Data
 */
trait BinaryLengthTrait {

    use StorageTrait;

    /**
     * Coeficiente derivado de la entropía de entrada, usado en {@see get_circular_value()}.
     */
    public int $coefficient = 0;

    /**
     * @internal Reservado. No utilizado en v0.2.0.
     */
    protected int $entropy_value = 0;

    /**
     * Constante sumada en {@see to_hex40()} y restada en {@see from_hex40()}.
     */
    protected int $seed = 530000;

    /**
     * Suma aritmética de todos los bytes de una cadena.
     *
     * @param string $input Cadena binaria.
     *
     * @return int `array_sum(unpack("C*", $input))`. No es `strlen()`.
     */
    public function get_binary_length(string $input): int {
        /** @var array<int,int> $bytes */
        $bytes = unpack("C*", $input);

        return array_sum($bytes);
    }

    /**
     * Convierte un byte desplazado a bloque hex de 10 caracteres.
     *
     * Fórmula: `dechex($byte + $entropy + $this->seed)`, rellenado a 10 chars con ceros a la izquierda.
     *
     * @param int $byte    Valor del byte (0–255). No se valida el rango.
     * @param int $entropy Desplazamiento adicional. Por defecto 0.
     *
     * @return string Bloque hexadecimal de exactamente 10 caracteres.
     */
    protected function to_hex40(int $byte, int $entropy = 0): string {
        $value = $byte + $entropy + $this->seed;

        return str_pad(
            string: dechex($value),
            length: 10,
            pad_string: '0',
            pad_type: STR_PAD_LEFT
        );
    }

    /**
     * Revierte un bloque de 10 caracteres hex a un carácter de un byte.
     *
     * Fórmula: `dechex(hexdec($block) - ($this->seed + get_entropy($key, $sum)))`, rellenado a 2 chars.
     *
     * @param string $block Bloque hex de 10 caracteres.
     * @param int    $key   Índice del bloque en la secuencia.
     * @param int    $sum   Suma base de entropía.
     *
     * @return string Carácter como cadena hex de 2 caracteres (un byte).
     */
    protected function from_hex40(string $block, int $key, int $sum): string {
        $entropy = $this->get_entropy($key, $sum);

        $block_value = hexdec($block);

        $value = dechex($block_value - ($this->seed + $entropy));

        return str_pad(
            string: $value,
            length: 2,
            pad_string: '0',
            pad_type: STR_PAD_LEFT
        );
    }

    /**
     * Calcula el desplazamiento de entropía para un índice dado.
     *
     * Fórmula: `2 * get_circular_value($index) + $sum`.
     *
     * @param int $index Posición del byte o bloque.
     * @param int $sum   Valor base (proveniente de la llave de entropía o 0).
     *
     * @return int Desplazamiento aplicado en `to_hex40` / `from_hex40`.
     */
    protected function get_entropy(int $index, int $sum): int {
        return 2 * ($this->get_circular_value($index)) + $sum;
    }

    /**
     * Asigna la suma de entropía a partir de una llave.
     *
     * Si `$entropy` no es `string`, no modifica `$sum`. En caso contrario,
     * reemplaza `$sum` con el resultado de {@see get_entropy_value()}.
     *
     * @param int         $sum     Referencia al acumulador de entropía.
     * @param string|null $entropy Llave de entropía.
     */
    protected function set_entropy_value(int &$sum, ?string $entropy = null): void {
        if (!is_string($entropy)) {
            return;
        }
        $sum = $this->get_entropy_value($entropy);
    }

    /**
     * Transformación circular acotada para dispersión numérica.
     *
     * Fórmula: `abs(($this->coefficient * $value + 17) % 100 + 17)`.
     * Rango resultante: [17, 116].
     *
     * @param int|float $value Índice o valor de entrada.
     *
     * @return int Entero en el rango [17, 116].
     *
     * @throws StorageException Si `$value` supera `0xffffffffffffff` (500).
     */
    private function get_circular_value(int|float $value): int {
        $max_value = 0xffffffffffffff;

        if ($value > $max_value) {
            throw new StorageException("El valor proporcionado excede el límite máximo permitido ({$max_value}) y representa un riesgo para la integridad del sistema.", 500);
        }

        return abs(($this->coefficient * $value + 17) % 100 + 17);
    }

    /**
     * Métrica heurística del contenido de un archivo.
     *
     * Lee hasta `0xFFFFFF` bytes (16 777 215). Si `$filename` no existe, intenta
     * resolverlo con {@see get_file_path()}. Retorna 0 si el archivo no se encuentra.
     *
     * @param string $filename Ruta al archivo.
     *
     * @return int Resultado de {@see get_entropy_value()} sobre el contenido leído, o `0`.
     *
     * @throws StorageException Si la lectura parcial falla.
     */
    public function get_entropy_file(string $filename): int {
        $value = 0xffffff;

        $file = $filename;

        if (!file_exists($filename)) {
            $file = $this->get_file_path($filename);
        }

        if (!file_exists($file)) {
            return 0;
        }

        $size = filesize($file);

        $content = $this->read_filename($file, 1, $size > $value ? $value : $size);

        return $this->get_entropy_value($content);
    }

    /**
     * Calcula un valor entero determinista a partir de bytes de entrada.
     *
     * Suma todos los bytes con `array_sum(unpack("C*", ...))`, deriva `$this->coefficient`
     * mediante {@see calculate_coefficient()} y retorna `suma + strlen($input)`.
     *
     * No es entropía de Shannon; es una heurística interna del MTB.
     *
     * @param string $input Cadena binaria.
     *
     * @return int Valor entero usado como suma base de entropía.
     */
    public function get_entropy_value(string $input): int {
        /** @var array<int,int> $bytes */
        $bytes = unpack("C*", $input);

        $sum = array_sum($bytes);

        $this->calculate_coefficient($sum);

        return $sum + strlen($input);
    }

    /**
     * Deriva y almacena `$this->coefficient` a partir de una semilla.
     *
     * Fórmula: `max(1, abs(intval((37 * $seed + 113) % 0xffffffffffff)))`.
     *
     * @param int|float $seed Valor semilla (típicamente la suma de bytes).
     */
    private function calculate_coefficient(int|float $seed): void {
        $min_coefficient = 1;
        $max_coefficient = 0xffffffffffff;

        $coefficient = abs(intval((37 * $seed + 113) % $max_coefficient));

        $this->coefficient = max($coefficient, $min_coefficient);
    }
}