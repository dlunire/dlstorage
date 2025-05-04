<?php

declare(strict_types=1);

namespace DLStorage\Traits;

use DLStorage\Errors\StorageException;

/**
 * Copyright (c) 2025 David E Luna M  
 * Licensed under the MIT License. See LICENSE file for details.
 *
 * Trait DataSizeTrait
 *
 * Calcula la longitud real en bytes de una cadena binaria, 
 * garantizando que no exceda el límite máximo permitido de 4 GB (2^32 bytes).
 *
 * Este trait es esencial para validar entradas binarias dentro del sistema DLStorage,
 * asegurando integridad en operaciones de transformación y almacenamiento.
 *
 * @version v0.0.1
 * @package DLStorage\Traits
 * @author David E Luna M
 * @license MIT
 * @copyright 2025 David E Luna M
 */
trait BinaryLengthTrait {
    /**
     * Obtiene la longitud real en bytes de un string binario y valida su límite máximo.
     *
     * Convierte la cadena binaria a su representación hexadecimal para obtener 
     * una medida precisa, independientemente de su codificación interna.
     * Si la longitud calculada supera los 4 GB, lanza una excepción.
     *
     * @param string $input Cadena binaria de entrada.
     * @return int Longitud en bytes del contenido binario.
     *
     * @throws StorageException Si la longitud supera el límite permitido de 4 GB (2^32 bytes).
     *
     * @example
     * ```php
     * $length = $this->get_binary_length($entropy);
     * ```
     *
     * @note El método es útil para verificar datos sensibles antes de aplicarlos
     * a procesos criptográficos o de almacenamiento.
     */
    public function get_binary_length(string $input): int {
        $value = intdiv(strlen(bin2hex($input)), 2);

        if ($value > (2 ** 32)) {
            throw new StorageException("La longitud de la entropía excede el límite permitido de 4 GB", 500);
        }

        return $value;
    }
}
