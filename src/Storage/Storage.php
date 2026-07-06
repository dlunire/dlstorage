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

/**
 * Interfaz pública para lectura y escritura de archivos en formato binario DLStorage.
 *
 * Punto de entrada recomendado de la biblioteca. Extiende {@see SaveData} y encapsula
 * la ruta del archivo y la entropía en el constructor, delegando la persistencia en
 * `generate()` y la recuperación en `readfile()`.
 *
 * Responsabilidades:
 *
 * - Normalizar separadores de ruta (`/` → `DIRECTORY_SEPARATOR`) durante la construcción.
 * - Generar archivos `.dlstorage` mediante {@see generate()}.
 * - Leer y decodificar archivos mediante {@see readfile()}.
 * - Exponer la firma y versión binaria del formato mediante
 *   {@see get_current_signature()} y {@see get_current_version()}.
 *
 * Jerarquía de herencia: `Storage` → {@see SaveData} → {@see DataStorage} → {@see Data}.
 *
 * @package    DLStorage\Storage
 * @version    v0.2.0
 * @license    AGPL-3.0-or-later
 * @author     David E. Luna M. <info@dlunire.dev>
 * @copyright  Copyright (c) 2026 David E. Luna M.
 *
 * @see SaveData    Implementación base de persistencia binaria.
 * @see DataStorage Abstracción de almacenamiento persistente.
 * @see Data        Codificación y decodificación con entropía.
 *
 * @example Uso básico
 * ```php
 * use DLStorage\Storage\Storage;
 *
 * $storage = new Storage("usuarios", "MiClaveDeEntropia");
 * $storage->generate(json_encode(["id" => 1, "nombre" => "David"]));
 * echo $storage->readfile();
 * ```
 */
final class Storage extends SaveData {
    /**
     * Separador de directorios utilizado durante el análisis inicial de la ruta.
     */
    private const SEPARATOR = "/";

    /**
     * Ruta del archivo una vez normalizada para el sistema operativo actual.
     */
    private readonly string $filename;

    /**
     * Llave de entropía pasada al constructor para `generate()` y `readfile()`.
     *
     * `null` implica suma base 0 en {@see Data::encode()} y {@see Data::get_decode()}.
     */
    private readonly ?string $entropy;

    /**
     * Índice del recorrido durante {@see normalize_separator()}. Solo se usa en el constructor.
     */
    private int $offset = 0;

    /**
     * Longitud en bytes de la ruta antes de normalizar separadores.
     */
    private int $size = 0;

    /**
     * Inicializa una instancia con la ruta del archivo y la entropía opcional.
     *
     * La ruta se normaliza inmediatamente sustituyendo `/` por el separador nativo
     * del sistema operativo. No es necesario incluir la extensión `.dlstorage`.
     *
     * @param string      $filename Ruta relativa del archivo (sin extensión `.dlstorage`).
     * @param string|null $entropy  Llave de entropía. `null` usa suma base 0 al codificar/decodificar.
     */
    public function __construct(string $filename, ?string $entropy = null) {
        $this->load($filename, $entropy);
        $this->normalize_separator();
    }

    /**
     * Inicializa los datos internos necesarios para la operación.
     *
     * Calcula la longitud de la ruta, almacena el nombre del archivo y
     * registra la entropía asociada.
     *
     * @param string      $filename Ruta del archivo.
     * @param string|null $entropy  Entropía utilizada durante la operación.
     *
     * @return void
     */
    private function load(string $filename, ?string $entropy = null): void {
        $this->size = \strlen($filename);
        $this->filename = $filename;
        $this->entropy = $entropy;
    }

    /**
     * Sustituye cada `/` de `$this->filename` por `DIRECTORY_SEPARATOR`.
     *
     * Recorrido lineal O(n) sobre los bytes de la ruta. No transforma `\`;
     * la resolución final de rutas en disco la realiza {@see StorageTrait::get_file_path()}.
     *
     * @return void
     */
    private function normalize_separator(): void {
        while ($this->offset < $this->size) {
            $byte = $this->filename[$this->offset];

            if ($byte === self::SEPARATOR) {
                $this->filename[$this->offset] = DIRECTORY_SEPARATOR;
            }

            $this->offset++;
        }
    }

    /**
     * Lee y decodifica el archivo configurado en el constructor.
     *
     * Delega en {@see SaveData::read_storage_data()}. La extensión `.dlstorage` se añade
     * internamente; no debe incluirse en el nombre pasado al constructor.
     *
     * @param bool $storage `true` (predeterminado): `{raíz}/storage/{filename}.dlstorage`.
     *                      `false`: `{raíz}/{filename}.dlstorage` (véase {@see StorageTrait::get_file_path()}).
     *
     * @return string Contenido original decodificado (bytes de un solo carácter).
     *
     * @throws \DLStorage\Errors\StorageException Si el archivo no existe (404), la firma no
     *                                            coincide o la lectura/decodificación falla (500).
     */
    public function readfile(bool $storage = true): string {
        return $this->read_storage_data(
            filename: $this->filename,
            entropy: $this->entropy,
            storage: $storage
        );
    }

    /**
     * Genera un archivo `.dlstorage` con el contenido indicado.
     *
     * Delega en {@see SaveData::save_data()}. Codifica `$content` con la entropía del
     * constructor y escribe `{ruta}.dlstorage`.
     *
     * @param string $content Datos crudos (texto o binario) a codificar y persistir.
     * @param bool   $storage `true` (predeterminado): bajo `storage/`. `false`: bajo la raíz del proyecto.
     *
     * @throws \DLStorage\Errors\StorageException Si `file_put_contents` falla o el archivo no existe tras escribir (500).
     */
    public function generate(string $content, bool $storage = true): void {
        $this->save_data(
            filename: $this->filename,
            data: $content,
            entropy: $this->entropy,
            storage: $storage
        );
    }

    /**
     * Devuelve la firma del formato como bytes ASCII.
     *
     * `hex2bin($this->get_signature())` — por defecto los 9 bytes de `"DLStorage"`.
     *
     * @return string Firma binaria (9 bytes).
     */
    public function get_current_signature(): string {
        return hex2bin($this->get_signature());
    }

    /**
     * Devuelve la versión del formato almacenada en la cabecera como bytes ASCII.
     *
     * `hex2bin($this->get_version())` — por defecto los bytes de `"v0.1.0"` definidos en
     * {@see StorageTrait::$version}. No confundir con la versión de la biblioteca (`v0.2.0`).
     *
     * @return string Versión binaria del formato.
     */
    public function get_current_version(): string {
        return hex2bin($this->get_version());
    }
}
