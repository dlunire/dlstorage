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

use DLStorage\Traits\StorageTrait;

/**
 * Capa intermedia que combina codificación ({@see Data}) con rutas de archivo ({@see StorageTrait}).
 *
 * Hereda `encode()`, `get_decode()` y métodos de entropía de {@see Data}, y añade
 * resolución de rutas, firma, versión y lectura binaria parcial de {@see StorageTrait}.
 *
 * @package    DLStorage\Storage
 * @version    v0.2.0
 * @license    AGPL-3.0-or-later
 * @author     David E. Luna M. <info@dlunire.dev>
 * @copyright  Copyright (c) 2026 David E. Luna M.
 *
 * @abstract
 * @extends Data
 */
abstract class DataStorage extends Data {
    use StorageTrait;
}