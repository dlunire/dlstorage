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
 * Abstracción base para sistemas de almacenamiento persistente.
 *
 * Esta clase define la interfaz común para implementar mecanismos de
 * almacenamiento de datos utilizando medios persistentes distintos a una
 * base de datos relacional, como archivos binarios, estructuras
 * serializadas u otros formatos personalizados.
 *
 * Los datos almacenados pueden identificarse mediante un token o
 * identificador único, permitiendo su integración con sistemas externos,
 * incluidos motores de bases de datos, sin que éstos sean responsables del
 * almacenamiento físico del contenido.
 *
 * Como extensión de {@see Data}, esta clase reutiliza las capacidades de
 * codificación, transformación mediante entropía y manipulación binaria
 * proporcionadas por DLStorage, sirviendo como base para adaptadores de
 * almacenamiento especializados.
 *
 * Forma parte de **DLUnire Runtime**, proporcionando la infraestructura
 * necesaria para construir sistemas de persistencia desacoplados, portables
 * y de alto rendimiento dentro del ecosistema DLUnire.
 *
 * Características principales:
 *
 * - Persistencia de datos fuera de motores de bases de datos.
 * - Integración mediante identificadores o tokens de referencia.
 * - Base para adaptadores de almacenamiento personalizados.
 * - Reutilización del sistema de codificación binaria de DLStorage.
 *
 * @package    DLStorage\Storage
 * @version    v0.2.0
 * @license    AGPL-3.0-or-later
 * @author     David E. Luna M. <info@dlunire.dev>
 * @copyright  Copyright (c) 2026 David E. Luna M.
 *
 * @see        https://dlunire.dev DLUnire Runtime
 * @see        https://github.com/dlunire Ecosistema DLUnire
 *
 * @abstract
 * @extends Data
 */
abstract class DataStorage extends Data {
    use StorageTrait;
}
