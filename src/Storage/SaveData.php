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

use DLStorage\Errors\StorageException;

/**
 * Persistencia de archivos en formato binario `.dlstorage`.
 *
 * Construye y lee la estructura física del archivo:
 *
 * ```
 * [firma 9B][tamaño_cabecera 4B][versión NB][tamaño_payload 4B][payload NB]
 * ```
 *
 * El payload es la salida hexadecimal de {@see Data::encode()}, convertida a binario
 * mediante `hex2bin()` al escribir el archivo.
 *
 * @package    DLStorage\Storage
 * @version    v0.2.0
 * @license    AGPL-3.0-or-later
 * @author     David E. Luna M. <info@dlunire.dev>
 * @copyright  Copyright (c) 2026 David E. Luna M.
 *
 * @see Storage Implementación concreta recomendada.
 * @see Data    Codificación del payload.
 *
 * @abstract
 */
abstract class SaveData extends DataStorage {

    /**
     * Codifica y persiste datos en un archivo `.dlstorage`.
     *
     * Flujo:
     * 1. {@see Data::encode()} transforma `$data` en una cadena hexadecimal.
     * 2. Se arma una cadena hex con: firma + tamaño(versión) + versión + tamaño(payload) + payload.
     * 3. `hex2bin()` convierte la cadena completa a binario y se escribe en disco.
     * 4. Si `$storage` es `true`, la ruta se resuelve bajo `{raíz}/storage/`.
     *
     * @param string      $filename Nombre relativo sin extensión `.dlstorage`.
     * @param string      $data     Contenido crudo (texto o binario) a codificar.
     * @param string|null $entropy  Llave de entropía para la codificación. `null` usa suma base 0.
     * @param bool        $storage  `true`: guarda en `storage/`. `false`: guarda en la raíz del proyecto.
     *
     * @throws StorageException Si `file_put_contents` falla o el archivo no existe tras la escritura (código 500).
     */
    public function save_data(string $filename, string $data, ?string $entropy = NULL, bool $storage = true): void {
        /** @var string $encode */
        $encode = $this->encode($data, $entropy);

        /** @var string $file */
        $file = $this->get_file_path(filename: $filename, create_dir: true, storage: $storage) . ".dlstorage";

        /** @var string $signature */
        $signature = $this->get_signature();

        /** @var string $version */
        $version = $this->get_version();

        /** @var string $header_size */
        $header_size = $this->get_section_size($version);

        /** @var string $payload_size */
        $payload_size = $this->get_section_size($encode);

        $this->normalize_hex_payload($payload_size, $encode);

        /** @var string $new_data */
        $new_data = $signature . $header_size . $version . $payload_size . $encode;

        file_put_contents($file, hex2bin($new_data));

        if (!file_exists($file)) {
            throw new StorageException("Error al crear el archivo. Asegúrese de establecer los permisos de escritura", 500);
        }
    }

    /**
     * Lee un archivo `.dlstorage` y devuelve el contenido original decodificado.
     *
     * Flujo de lectura (offsets 1-based):
     * - Bytes 1–9: firma (`DLStorage`).
     * - Bytes 10–13: tamaño de la sección de versión (entero, 4 bytes).
     * - Bytes 14..(14+header_size-1): versión.
     * - 4 bytes siguientes: tamaño del payload.
     * - Bytes restantes: payload hexadecimal, decodificado con {@see Data::get_content()}.
     *
     * @param string      $filename Nombre base; se añade `.dlstorage` automáticamente.
     * @param string|null $entropy  Llave usada al codificar. Debe coincidir con la de escritura.
     * @param bool        $storage  `true`: busca en `storage/`. `false`: busca en la raíz del proyecto.
     *
     * @return string Contenido original tras decodificación.
     *
     * @throws StorageException Archivo inexistente (404), firma inválida (500) o error de lectura.
     */
    public function read_storage_data(string $filename, ?string $entropy = NULL, bool $storage = true): string {

        $filename = "{$filename}.dlstorage";

        /** @var string $file */
        $file = $this->get_file_path(filename: $filename, storage: $storage);

        /** @var string $filename_only */
        $filename_only = basename($filename);

        if (!file_exists($file)) {
            throw new StorageException("El archivo «{$filename_only}» no existe en la ruta indicada.", 404);
        }

        /** @var string $signature */
        $signature = bin2hex($this->read_filename($file, 1, 9));

        if ($signature != $this->get_signature()) {
            throw new StorageException("El archivo «{$filename_only}» no es un archivo DLStorage.", 500);
        }

        /** @var int $header_size */
        $header_size = hexdec(bin2hex($this->read_filename($file, 10, 13)));

        $from = 14 + $header_size;
        $to = $from + 3;

        $payload_size = hexdec(bin2hex($this->read_filename($file, $from, $to)));

        /** @var string $content */
        $content = bin2hex($this->read_filename($file, $to + 1, $to + $payload_size));
        $content = $this->delete_padding($content);

        return $this->get_content($content, $entropy);
    }

    /**
     * Lee el contenido crudo de un archivo del proyecto sin decodificarlo.
     *
     * A diferencia de {@see read_storage_data()}, no valida firma ni aplica decodificación.
     * Normaliza separadores `/` y `\` al separador del sistema operativo.
     *
     * @param string $filename Ruta relativa al proyecto. Puede incluir subdirectorios y extensión.
     * @param bool   $storage  `true`: resuelve bajo `{raíz}/storage/`. `false`: bajo la raíz.
     *
     * @return string Contenido binario del archivo tal como está en disco.
     *
     * @throws StorageException Si el archivo no existe (código 404).
     */
    public function get_file_content(string $filename, bool $storage = true): string {
        $filename = trim($filename, "\/");

        /** @var string $root */
        $root = $this->get_document_root();

        /** @var string $separator */
        $separator = DIRECTORY_SEPARATOR;

        /** @var string $filename */
        $filename = preg_replace("/[\\\\\/]+/", $separator, $filename);

        /** @var string $file */
        $file = $storage
            ? "{$root}{$separator}storage{$separator}{$filename}"
            : "{$root}{$separator}{$filename}";

        /** @var string $only_name_file */
        $only_name_file = basename($filename);

        if (!file_exists($file)) {
            throw new StorageException("El archivo «{$only_name_file}» no existe", 404);
        }

        return file_get_contents($file);
    }

    /**
     * Colapsa ceros iniciales de relleno en una cadena hexadecimal.
     *
     * Aplica `preg_replace('/^0+/', '0', $content)`: si hay uno o más ceros al inicio,
     * los sustituye por un único `0`. Complemento de {@see normalize_hex_payload()}.
     *
     * @param string $content Cadena hexadecimal leída del payload.
     *
     * @return string Cadena con como máximo un `0` inicial.
     */
    private function delete_padding(string $content): string {
        return preg_replace('/^0+/', '0', $content);
    }

    /**
     * Garantiza longitud par del payload hexadecimal antes de `hex2bin()`.
     *
     * Si `strlen($content)` es impar, antepone `0` al contenido e incrementa en 1
     * el valor byte del campo `$size` (representado como 8 caracteres hex).
     *
     * @param string $size    Referencia al tamaño del payload en hex de 8 caracteres.
     * @param string $content Referencia al payload hexadecimal.
     */
    private function normalize_hex_payload(string &$size, string &$content): void {
        /** @var int $payload_int */
        $payload_int = hexdec($size);

        /** @var bool $is_residue */
        $is_residue = \strlen($content) % 2 != 0;

        if ($is_residue) {
            $content = "0{$content}";
            $size = str_pad(dechex($payload_int + 1), 8, '0', STR_PAD_LEFT);
        }
    }

    /**
     * Calcula el tamaño en bytes de una cadena hexadecimal y lo devuelve como campo de 4 bytes.
     *
     * `intdiv(strlen($hex_content), 2)` obtiene la cantidad de bytes; el resultado se formatea
     * como cadena hexadecimal de 8 caracteres (relleno izquierdo con ceros).
     *
     * @param string $hex_content Cadena hexadecimal cuyo tamaño en bytes se medirá.
     *
     * @return string Representación de 8 caracteres hex (4 bytes big-endian).
     */
    private function get_section_size(string $hex_content): string {

        /** @var int $length_int */
        $length_int = intdiv(\strlen($hex_content), 2);

        return str_pad(dechex($length_int), 8, '0', STR_PAD_LEFT);
    }
}