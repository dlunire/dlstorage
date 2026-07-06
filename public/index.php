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

/**
 * IMPORTANTE:
 * 
 * Cuando corras esta aplicación como prueba, asegúrate que los archivos a los que apuntes
 * existan en la ruta seleccionada.
 * 
 * En esta prueba se asume que el archivo seleccionado existe en el directorio `storage`. Para esta
 * prueba (porque no aplica en producción) te recomiendo crear el directorio `storage` y copiar los archivos 
 * allí o simplemente, modifique la clase Storage para apuntar a archivos fuera de `storage`.
 * 
 * Este archivo no se debe utilizar para implementarlo en tu proyecto. Debe ser utilizado solo para ejecución de
 * pruebas de codificación.
 */

declare(strict_types=1);

use DLStorage\Storage\Storage as ST;

include dirname(__DIR__)
    . DIRECTORY_SEPARATOR
    . "vendor"
    . DIRECTORY_SEPARATOR
    . "autoload.php";

header("Content-type: text/plain; charset=utf-8", true, 200);

/** @var ST $st*/
$st = new ST(
    filename: "test-file",
    entropy: "Entropia de prueba"
);

$st->generate("Ciencias de la computación", true);


echo $st->readfile(true);
