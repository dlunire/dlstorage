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

// include dirname(__DIR__) . "/vendor/autoload.php";
include dirname(__DIR__) . DIRECTORY_SEPARATOR . "vendor" . DIRECTORY_SEPARATOR . "autoload.php";

use DLStorage\Storage\SaveData;

/**
 * Clase utilizada para probar la codificación y decodificación
 */
final class Storage extends SaveData {

    /**
     * Contenido transformado
     *
     * @var string
     */
    private readonly string $encoded;

    /**
     * Contenido original transformao
     *
     * @var string|false
     */
    private readonly string|false $original;

    /**
     * Llave de entropía cargada en el constructor
     *
     * @var string|null
     */
    private readonly string|null $entropy;

    /**
     * Permite ejecutar pruebas de alteración de bytes
     *
     * @param string $filename Archivo a ser leído
     * @param string|null $entropy Llave de entropía
     */
    public function __construct(string $filename, ?string $entropy = null) {
        $this->entropy = $entropy;
        $this->encode_test($filename);
    }

    /**
     * Transforma los bytes del contenido original durante el instanciamiento
     *
     * @param string $filename Contenido de archivo a ser transformado
     * @return void
     */
    private function encode_test(string $filename): void {
        if (!\file_exists($filename)) {
            throw new Exception("El archivo «{$filename}» no existe. Revisa que esté bien escrito o que exista");
        }

        if (\is_dir($filename)) {
            throw new Exception("El archivo «{$filename}» que intentas leer es un directorio");
        }

        /** @var string|false */
        $this->original = \file_get_contents($filename);

        if ($this->original === false) {
            throw new Exception("Error en Input/Ouput al intentar leer el archivo «{$filename}»");
        }
        
        $this->encoded = $this->encode($this->original, $this->entropy);
    }

    /**
     * Devuelve el contneido codificado o transformado
     *
     * @return string
     */
    public function get_encoded(): string {
        return $this->encoded;
    }

    /**
     * Devuelve el contenido original
     *
     * @return string
     */
    public function get_original(): string {
        return $this->original;
    }

    /**
     * Devuelve la llave de entropía
     *
     * @return string|null
     */
    public function get_entropy_test(): ?string {
        return $this->entropy;
    }
}

$entropy_prefix = "Ciencias de la computació";

$data_test = [];

for ($byte = 0x00; $byte <= 0xFF; $byte++) {

    $entropy = $entropy_prefix . chr($byte);

    /** @var Storage $storage */
    $storage = new Storage("../test-file", $entropy);

    /** @var non-empty-string $encoded */
    $encoded = $storage->get_encoded();

    /** @var non-empty-string[] $chunks */
    $chunks = str_split($encoded, 8);

    $data_test[] = [
        "byte"         => sprintf("0x%02X", $byte),
        "entropy_hex"  => strtoupper(bin2hex(chr($byte))),
        "sha256"       => hash('sha256', $encoded),
        "unique_blocks"=> count(array_unique($chunks)),
        "first_blocks" => array_slice($chunks, 0, 12),
    ];
}


header("Content-type: application/json; charset=utf-8", true, 200);
echo json_encode($data_test, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);