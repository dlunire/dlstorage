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

/**
 * Iteración secuencial sobre cadenas y arreglos mediante callbacks.
 *
 * @package     DLStorage\Traits
 * @version     v0.2.0
 * @license     AGPL-3.0-or-later
 * @author      David E. Luna M. <info@dlunire.dev>
 * @copyright   Copyright (c) 2026 David E. Luna M.
 */
trait ForTrait {

    /**
     * Itera sobre una cadena o arreglo invocando `$callback` por cada posición.
     *
     * El callback recibe: `($data[$index], $index, $data)`.
     * Para cadenas usa `strlen()` (no seguro para UTF-8 multibyte).
     *
     * @param string|array $data     Cadena o arreglo indexado numéricamente.
     * @param callable     $callback `function(mixed $value, int $index, string|array $original): void`
     */
    public function foreach(string|array $data, callable $callback): void {
        $length = is_array($data) ? count($data) : strlen($data);

        for ($index = 0; $index < $length; ++$index) {
            $callback($data[$index], (int) $index, $data);
        }
    }

    /**
     * Itera sobre cada byte de una cadena binaria.
     *
     * Descompone `$input` con `unpack("C*", $input)` y ejecuta el callback
     * por cada byte: `($byte, $index, $bytes)`.
     *
     * @param string   $input    Cadena binaria.
     * @param callable $callback `function(int $byte, int $index, int[] $bytes): void`
     */
    protected function foreach_string(string $input, callable $callback): void {
        /** @var array<int,int> $bytes */
        $bytes = array_values(unpack("C*", $input));

        foreach ($bytes as $key => $byte) {
            $callback($byte, $key, $bytes);
        }
    }
}