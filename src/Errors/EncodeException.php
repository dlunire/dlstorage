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

namespace DLStorage\Errors;

use RuntimeException;
use Throwable;

/**
 * Excepción para errores de codificación y decodificación con entropía.
 *
 * Se lanza cuando la decodificación produce un resultado inválido, por ejemplo
 * cuando la longitud del mensaje recuperado no es par (entropía incorrecta o
 * datos corruptos).
 *
 * @package     DLStorage\Errors
 * @version     v0.2.0
 * @author      David E. Luna M. <info@dlunire.dev>
 * @copyright   Copyright (c) 2026 David E. Luna M.
 * @license     AGPL-3.0-or-later
 *
 * @see \DLStorage\Storage\Data::get_decode()
 */
final class EncodeException extends RuntimeException {
    /**
     * @param string         $message  Mensaje descriptivo del error.
     * @param int            $code     Código de error HTTP semántico (por defecto 500).
     * @param Throwable|null $previous Excepción anterior encadenada, si existe.
     */
    public function __construct(string $message, int $code = 500, ?Throwable $previous = null) {
        parent::__construct($message, $code, $previous);
    }
}
