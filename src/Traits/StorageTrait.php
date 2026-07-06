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

use DLStorage\Errors\StorageException;

/**
 * Rutas de proyecto, metadatos del formato `.dlstorage` y lectura binaria parcial.
 *
 * @package   DLStorage\Traits
 * @version   v0.2.0
 * @license   AGPL-3.0-or-later
 * @author    David E. Luna M. <info@dlunire.dev>
 * @copyright Copyright (c) 2026 David E. Luna M.
 */
trait StorageTrait {

    /**
     * Versión del formato almacenado en la cabecera del archivo.
     *
     * @var string Valor por defecto: `"v0.1.0"`.
     */
    protected string $version = "v0.1.0";

    /**
     * Firma ASCII que identifica un archivo DLStorage válido.
     *
     * @var string Valor por defecto: `"DLStorage"` (9 bytes).
     */
    protected string $signature = "DLStorage";

    /**
     * Resuelve la ruta absoluta de un archivo relativo al proyecto.
     *
     * Base: {@see get_document_root()}. Si `$storage` es `true`, antepone `storage/`.
     * Normaliza separadores `\` y `/` al separador del SO.
     *
     * Con `$create_dir = true`, crea el directorio padre con `mkdir(..., 0755, true)`.
     *
     * @param string $filename   Ruta relativa (puede incluir subdirectorios).
     * @param bool   $create_dir Crea el directorio contenedor si no existe.
     * @param bool   $storage    `true`: bajo `storage/`. `false`: bajo la raíz del proyecto.
     *
     * @return string Ruta absoluta del archivo.
     *
     * @throws StorageException Si `$create_dir` es `true` y un archivo ocupa el nombre del directorio (500).
     */
    public function get_file_path(string $filename, bool $create_dir = false, bool $storage = true): string {
        /** @var string $root */
        $root = $this->get_document_root();

        /** @var string $separator */
        $separator = DIRECTORY_SEPARATOR;

        $filename = preg_replace("/[\\\\\/]+/", $separator, $filename);
        $filename = trim($filename, "\{$separator}");

        /** @var string $file */
        $file = $storage
            ? "{$root}{$separator}storage{$separator}{$filename}"
            : "{$root}{$separator}{$separator}{$filename}";

        if (!$create_dir) {
            return $file;
        }

        /** @var string $file_pattern */
        $file_pattern = "/[\\\\\/][^\\\\\/]+$/i";

        /** @var string $dir */
        $dir = preg_replace($file_pattern, "", $file);

        /** @var string $dirname_only */
        $dirname_only = basename($dir);

        if (file_exists($dir) && is_file($dir)) {
            throw new StorageException("No se puede crear el directorio con el nombre «{$dirname_only}», porque ya existe un archivo con ese nombre", 500);
        }

        if (!file_exists($dir)) {
            mkdir($dir, 0755, true);
        }

        return $file;
    }

    /**
     * Comprueba si los primeros 9 bytes del archivo coinciden con la firma DLStorage.
     *
     * @param string $file Ruta relativa resuelta con {@see get_file_path()} (sin extensión `.dlstorage`).
     *
     * @return bool `true` si la firma hex de los bytes 1–9 coincide con {@see get_signature()}.
     *
     * @throws StorageException Si el archivo no existe o la lectura falla (propagada desde {@see read_filename()}).
     */
    public function validate_saved_data(string $file): bool {
        /** @var string $filepath */
        $filepath = $this->get_file_path($file);

        /** @var string $signature */
        $signature = bin2hex($this->read_filename($filepath, 1, 9));

        return $signature == $this->get_signature();
    }

    /**
     * Lee un rango inclusivo de bytes de un archivo (índices 1-based).
     *
     * Abre el archivo en modo binario (`rb`), posiciona con `fseek` y lee `$to - $from + 1` bytes.
     * No valida previamente la existencia del archivo.
     *
     * @param string $filename Ruta absoluta o relativa al archivo.
     * @param int    $from     Byte inicial (≥ 1).
     * @param int    $to       Byte final (≥ `$from`).
     *
     * @return string Bytes leídos.
     *
     * @throws StorageException Rango inválido (500), archivo inaccesible (500), rango fuera de tamaño (416),
     *                          error de seek (500) o lectura vacía/fallida (500).
     */
    public function read_filename(string $filename, int $from = 1, int $to = 1): string {

        /** @var string $filename_only */
        $filename_only = basename($filename);

        if ($from < 1 || !($from <= $to)) {
            throw new StorageException(
                "Rango inválido: el offset inicial debe ser mayor o igual a 1 y menor o igual que el offset final.",
                500
            );
        }

        $from -= 1;
        $to -= 1;

        /** @var int|false $size */
        $size = filesize($filename);

        if (is_bool($size)) {
            throw new StorageException(
                sprintf(
                    'No se pudo determinar el tamaño de «%s»: error de entrada/salida al acceder a los metadatos del archivo.',
                    $filename_only
                ),
                500
            );
        }

        /** @var int $length */
        $length = $to - $from + 1;

        /** @var resource $file */
        $file = fopen($filename, 'rb');

        /** @var bool $pointer */
        $pointer = fseek($file, $from, SEEK_SET) !== 0;

        if ($from > $size || $to > $size) {
            throw new StorageException("El rango de lectura excede el tamaño del archivo «{$filename_only}».", 416);
        }

        if ($pointer) {
            fclose($file);

            throw new StorageException(
                "No se pudo posicionar el puntero al byte {$from} del archivo «{$filename_only}».",
                500
            );
        }

        /** @var string|false $bytes */
        $bytes = fread($file, $length);

        fclose($file);

        if (!$bytes) {
            throw new StorageException(
                "Error al leer {$length} bytes desde el offset {$from}.",
                500
            );
        }

        return $bytes;
    }

    /**
     * Verifica que la ruta apunte a un archivo existente y legible.
     *
     * @param string $filename Ruta absoluta del archivo.
     *
     * @throws StorageException Archivo inexistente (404), es un directorio (500) o sin permiso de lectura (500).
     */
    protected function validate_filename(string $filename): void {

        /** @var string $filename_only */
        $filename_only = basename($filename);

        if (!file_exists($filename)) {
            throw new StorageException(
                sprintf(
                    'No se encontró el archivo «%s» en la ruta especificada. Verifica que el nombre y la ruta sean correctos.',
                    $filename_only
                ),
                404
            );
        }

        if (is_dir($filename)) {
            throw new StorageException("«{$filename}» debe ser un archivo, no un directorio.", 500);
        }

        if (!is_readable($filename)) {
            throw new StorageException("No se puede leer el archivo «{$filename_only}»: verifique los permisos de lectura.", 500);
        }
    }

    /**
     * Obtiene el directorio raíz del proyecto.
     *
     * Calcula: `realpath(dirname(getcwd()))`. Asume que el CWD está un nivel
     * por debajo de la raíz (p. ej. `public/`).
     *
     * @return string Ruta absoluta sin espacios laterales. Puede ser cadena vacía si `realpath` falla.
     */
    public function get_document_root(): string {
        $dir = getcwd();
        $dir = dirname($dir);
        $dir = realpath($dir);

        return trim((string) $dir);
    }

    /**
     * Devuelve la firma como cadena hexadecimal.
     *
     * @return string `bin2hex($this->signature)` — p. ej. `444c53746f72616765` para `"DLStorage"`.
     */
    protected function get_signature(): string {
        return bin2hex($this->signature);
    }

    /**
     * Devuelve la versión como cadena hexadecimal.
     *
     * @return string `bin2hex($this->version)` — p. ej. bytes ASCII de `"v0.1.0"` en hex.
     */
    protected function get_version(): string {
        return bin2hex($this->version);
    }
}