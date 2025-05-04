<?php

declare(strict_types=1);

namespace DLStorage\Traits;

/**
 * Copyright (c) 2025 David E Luna M
 * Licensed under the MIT License. See LICENSE file for details.
 *
 * Trait DataSizeTrait
 *
 * Calcula de forma precisa la longitud de datos binarios reales,
 * independiente de la codificación del contenido (UTF-8, ASCII, binario crudo, etc.).
 *
 * Este método se utiliza especialmente para garantizar la estabilidad del sistema
 * al operar con secuencias binarias ofuscadas, comprimidas o transformadas,
 * donde `strlen()` y `mb_strlen()` pueden generar resultados inconsistentes.
 *
 * @version v0.0.1
 * @package DLStorage\Traits
 * @author David E Luna M
 * @license MIT
 * @copyright 2025 David E Luna M
 */
trait BinaryLengthTrait {
    /**
     * Obtiene la longitud en bytes reales de una cadena binaria.
     *
     * Este método convierte el contenido binario a su forma hexadecimal y divide
     * el total de caracteres entre 2, ya que cada byte representa dos dígitos hexadecimales.
     * Esto permite una medición agnóstica al encoding de origen, útil en flujos binarios.
     *
     * @param string $input Cadena binaria o con contenido de bytes arbitrarios.
     * @return int Longitud exacta en bytes del contenido binario.
     *
     * @example
     * ```php
     * $length = $this->get_binary_length($binary_data);
     * ```
     *
     * @note No utilizar este método para contenido en texto legible si se espera una longitud de caracteres.
     */
    public function get_binary_length(string $input): int {
        return intdiv(strlen(bin2hex($input)), 2);
    }
}
