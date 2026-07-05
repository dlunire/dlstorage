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

/**
 * Clase personalizada de excepción para errores de valor.
 *
 * Se lanza cuando un valor proporcionado no cumple con las condiciones requeridas
 * por una operación específica dentro del sistema DLStorage.
 *
 * Forma parte del núcleo de manejo de errores del framework DLUnire.
 *
 * @author David E Luna M <info@dlunire.dev>
 * @license AGPL-3.0 license
 * @copyright 2025 David E Luna M
 * @package DLStorage
 * @project DLUnire Runtime
 * @organization DLUnire
 */
final class ValueError extends RuntimeException {
}
