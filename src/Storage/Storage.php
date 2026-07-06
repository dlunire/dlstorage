<?php

/**
 * Copyright (c) 2026 David E Luna M
 * Licensed under the AGPL-3.0-or-later License.
 * See LICENSE file for details.
 */

declare(strict_types=1);

namespace DLStorage\Storage;

/**
 * Clase principal para la lectura y escritura de archivos mediante el
 * formato binario de DLStorage.
 *
 * Esta clase actúa como la interfaz pública del sistema de almacenamiento,
 * encargándose de:
 *
 * - Normalizar las rutas entre diferentes sistemas operativos.
 * - Leer archivos generados por DLStorage.
 * - Generar nuevos archivos utilizando el formato binario del proyecto.
 * - Gestionar la entropía utilizada durante el proceso de codificación y
 *   decodificación.
 * - Exponer información de la versión y firma del formato almacenado.
 *
 * La normalización de rutas se realiza mediante un recorrido secuencial
 * sobre cada byte de la cadena, sustituyendo el separador UNIX (`/`) por el
 * separador nativo del sistema (`DIRECTORY_SEPARATOR`). Este procedimiento
 * evita operaciones adicionales de reemplazo sobre la cadena completa.
 *
 * @package DLStorage\Storage
 * @version v0.2.0
 * @license AGPL-3.0-or-later
 * @author David E Luna M
 * @copyright Copyright (c) 2026 David E Luna M
 *
 * @property-read string      $filename Ruta del archivo normalizada.
 * @property-read string|null $entropy  Entropía utilizada durante la operación.
 * @property int              $offset   Posición actual del autómata durante la normalización.
 * @property int              $size     Longitud, en bytes, de la ruta suministrada.
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
     * Inicializa una nueva instancia del sistema de almacenamiento.
     *
     * Carga los parámetros recibidos y normaliza automáticamente la ruta para
     * adaptarla al sistema operativo donde se ejecuta la aplicación.
     *
     * @param string      $filename Ruta del archivo.
     * @param string|null $entropy  Entropía utilizada durante la operación.
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
     * Lee y decodifica un archivo generado mediante DLStorage.
     *
     * @param bool $storage Indica si el archivo se encuentra dentro del
     *                      directorio `storage/`.
     *
     * @return string Contenido recuperado tras el proceso de decodificación.
     */
    public function readfile(bool $storage = true): string {
        return $this->read_storage_data(
            filename: $this->filename,
            entropy: $this->entropy,
            storage: $storage
        );
    }

    /**
     * Genera un nuevo archivo utilizando el formato binario de DLStorage.
     *
     * El contenido es procesado y almacenado empleando la entropía indicada
     * durante la creación de la instancia.
     *
     * @param string $content Contenido que será almacenado.
     * @param bool   $storage Indica si el archivo debe generarse dentro del
     *                        directorio `storage/`.
     *
     * @return void
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
     * Obtiene la firma binaria del formato actualmente soportado.
     *
     * @return string Firma del formato en representación binaria.
     */
    public function get_current_signature(): string {
        return hex2bin($this->get_signature());
    }

    /**
     * Obtiene la versión binaria del formato actualmente soportado.
     *
     * @return string Versión del formato en representación binaria.
     */
    public function get_current_version(): string {
        return hex2bin($this->get_version());
    }
}
