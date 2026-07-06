<?php

/**
 * Copyright (c) 2026 David E Luna M
 * Licensed under the AGPL-3.0-or-later License.
 * See LICENSE file for details.
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
     * Entropía empleada para los procesos de codificación y decodificación.
     *
     * Un valor nulo indica que se utilizará el comportamiento predeterminado
     * implementado por la biblioteca.
     */
    private readonly ?string $entropy;

    /**
     * Posición actual del recorrido del autómata durante la normalización.
     */
    private int $offset = 0;

    /**
     * Longitud de la ruta expresada en bytes.
     */
    private int $size = 0;

    /**
     * Inicializa una instancia con la ruta del archivo y la entropía opcional.
     *
     * La ruta se normaliza inmediatamente sustituyendo `/` por el separador nativo
     * del sistema operativo. No es necesario incluir la extensión `.dlstorage`.
     *
     * @param string      $filename Ruta relativa del archivo (sin extensión `.dlstorage`).
     * @param string|null $entropy  Llave de entropía para codificación y decodificación.
     *                              Si es `null`, se aplica el comportamiento predeterminado.
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
     * Normaliza los separadores de directorio.
     *
     * Recorre la ruta byte a byte sustituyendo el carácter `/` por el
     * separador nativo del sistema operativo definido mediante
     * `DIRECTORY_SEPARATOR`.
     *
     * El algoritmo implementa un recorrido lineal O(n), donde `n` corresponde
     * al número de bytes de la ruta.
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
     * Delega en {@see SaveData::read_storage_data()} utilizando la ruta y entropía
     * almacenadas en la instancia.
     *
     * @param bool $storage Si es `true` (predeterminado), busca en `storage/` relativo
     *                      al directorio raíz del proyecto. Si es `false`, resuelve la
     *                      ruta desde la raíz del proyecto.
     *
     * @return string Contenido original decodificado.
     *
     * @throws \DLStorage\Errors\StorageException Si el archivo no existe, la firma no
     *                                            coincide o la decodificación falla.
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
     * Delega en {@see SaveData::save_data()} utilizando la ruta y entropía
     * configuradas en el constructor.
     *
     * @param string $content Datos en texto plano o binario que serán codificados y persistidos.
     * @param bool   $storage Si es `true` (predeterminado), guarda en `storage/`. Si es `false`,
     *                        guarda en la ruta relativa indicada en el constructor.
     *
     * @return void
     *
     * @throws \DLStorage\Errors\StorageException Si no se puede crear el archivo o faltan permisos.
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
     * Obtiene la firma de cabecera del formato en representación binaria.
     *
     * Equivalente a `hex2bin(get_signature())`. El valor predeterminado es la cadena
     * `"DLStorage"` codificada en bytes.
     *
     * @return string Firma binaria del formato (9 bytes).
     */
    public function get_current_signature(): string {
        return hex2bin($this->get_signature());
    }

    /**
     * Obtiene la versión del formato en representación binaria.
     *
     * Equivalente a `hex2bin(get_version())`. El valor predeterminado es `"v0.1.0"`.
     *
     * @return string Versión binaria del formato.
     */
    public function get_current_version(): string {
        return hex2bin($this->get_version());
    }
}
