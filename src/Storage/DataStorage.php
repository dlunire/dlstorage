<?php

declare(strict_types=1);

namespace DLStorage\Storage;

use DLStorage\Errors\StorageException;

/**
 * Define una base para almacenar datos en archivos binarios u otros medios persistentes,
 * sin utilizar una base de datos.
 * 
 * En su lugar, puede generarse un token de referencia que pueda ser almacenado en una
 * base de datos si es necesario.
 *
 * @package    DLStorage
 * @version    v0.1.0
 * @license    MIT
 * @author     David E Luna M <dlunireframework@gmail.com>
 * @copyright  2025 Códigos del Futuro, DLUnire Framework
 * 
 * @abstract
 */
abstract class DataStorage extends Data {


    /**
     * Versión del archivo de almacenamiento de bytes transformados.
     * 
     * Esta propiedad almacena la versión actual del formato de archivo utilizado para
     * la persistencia de los datos transformados mediante el sistema de transformación
     * de bytes. El valor está representado en formato de cadena y puede ser utilizado 
     * para identificar cambios en la estructura o el esquema de los datos, asegurando
     * la compatibilidad entre versiones del sistema.
     *
     * **Versión en formato hexadecimal:**  
     * La versión "v0.1.0" está representada en hexadecimal como:
     * 
     * ```bash
     * 76 30 2e 31 2e 30
     * ```
     *  
     * Esta representación hexadecimal permite realizar comparaciones y verificaciones 
     * a nivel de bytes, útil para tareas como la validación o la compatibilidad 
     * entre diferentes versiones de archivos transformados.
     * 
     * **Ejemplo de uso:**
     * ```php
     * echo $this->version;  // Muestra la versión del archivo
     * ```
     * 
     * @var string $version
     * @since v0.1.0 Introducción del campo de versión en el archivo de almacenamiento.
     */
    private string $version = "v0.1.0";

    /**
     * Firma de la cabecera del archivo.
     * 
     * Esta propiedad almacena la firma que identifica de manera única el formato
     * del archivo de almacenamiento de datos transformados. La firma es una secuencia
     * de caracteres que se coloca al inicio del archivo, sirviendo como una "marca" 
     * para indicar que el archivo es reconocido por el sistema y sigue el formato 
     * adecuado.
     * 
     * **Representación en formato hexadecimal:**
     * La firma "DLStorage" se representa en hexadecimal como:
     * 
     * ```bash
     * 44 4c 53 74 6f 72 61 67 65
     * ```
     * 
     * Este valor permite verificar la integridad del archivo y validar que el contenido
     * corresponde a un archivo del sistema, facilitando la detección de archivos
     * corruptos o de un formato incorrecto.
     * 
     * **Ejemplo de uso:**
     * ```php
     * echo $this->signature;  // Muestra la firma de la cabecera del archivo
     * ```
     * 
     * @var string $signature
     * @since v0.1.0 Introducción de la firma de cabecera para validación de formato de archivo.
     */
    private string $signature = "DLStorage";

    /**
     * Devuelve la firma del archivo en formato hexadecimal.
     *
     * Convierte los bytes binarios de la propiedad `$signature`
     * a una representación legible como cadena hexadecimal.
     *
     * @return string Firma binaria representada en hexadecimal.
     */
    protected function get_signature(): string {
        return bin2hex($this->signature);
    }

    /**
     * Devuelve la versión del archivo en formato hexadecimal.
     *
     * Convierte los bytes binarios de la propiedad `$version`
     * a una representación legible como cadena hexadecimal.
     *
     * @return string Versión binaria representada en hexadecimal.
     */
    protected function get_version(): string {
        return bin2hex($this->version);
    }


    /**
     * Obtiene el directorio raíz del sistema.
     *
     * Devuelve la ruta absoluta del directorio raíz de la aplicación.
     * 
     * Para lograrlo, se obtiene el directorio de trabajo actual (`getcwd()`), se retrocede 
     * un nivel hacia el directorio padre (`dirname()`), y luego se resuelve la ruta absoluta 
     * mediante `realpath()`. Finalmente, se elimina cualquier espacio innecesario con `trim()`.
     *
     * Esto es útil para establecer rutas base dentro de la aplicación, evitando 
     * problemas de rutas relativas al trabajar con diferentes entornos de desarrollo o despliegue.
     *
     * @return string Ruta absoluta del directorio raíz de la aplicación.
     *
     * @example Example
     * 
     * ```
     * // Ejemplo de uso
     * $root_path = $this->get_document_root();
     * echo $root_path; 
     * // Resultado esperado: /var/www/html/my-app
     *```
     *
     * @note
     * Asegúrate de tener los permisos adecuados para acceder al directorio raíz de la aplicación.
     * Este método asume que la estructura de carpetas sigue un patrón estándar donde 
     * el directorio raíz se encuentra un nivel por encima del directorio de ejecución actual.
     */
    public function get_document_root(): string {
        /**
         * Directorio raíz de la aplicación.
         *
         * @var string
         */
        $dir = getcwd();       // Obtiene el directorio de trabajo actual.
        $dir = dirname($dir);  // Retrocede un nivel al directorio padre.
        $dir = realpath($dir); // Resuelve la ruta absoluta.

        return trim($dir);     // Elimina posibles espacios en blanco.
    }

    /**
     * Valida si se trata de un archivo estructura binaria válida
     *
     * @param string $file Archivo a ser analizado
     * @return boolean
     */
    public function validate_saved_data(string $file): bool {
        /** @var string $filepath */
        $filepath = $this->get_file_path($file);

        /** @var string $signature */
        $signature = bin2hex($this->read_filename($filepath, 1, 9));

        return $signature == $this->get_signature();
    }

    /**
     * Lee un rango de bytes de un archivo binario.
     *
     * Este método permite leer una porción de un archivo binario, especificando los índices de inicio y fin del rango a leer.
     * Si el rango es inválido o excede el tamaño del archivo, se lanzan excepciones apropiadas.
     * El archivo se abre en modo binario, asegurando que la lectura no se vea afectada por la codificación de caracteres.
     *
     * @param string $filename Ruta del archivo a leer.
     * @param int $from Offset de inicio de la lectura (1 basado).
     * @param int $to Offset final de la lectura (1 basado).
     * @return string Los bytes leídos del archivo.
     * 
     * @throws StorageException Si el rango es inválido, el archivo no se puede acceder, o no se puede leer correctamente.
     * @throws StorageException Si el rango de lectura excede el tamaño del archivo.
     * @throws StorageException Si hay un error de entrada/salida al acceder a los metadatos del archivo.
     * @throws StorageException Si no se puede posicionar el puntero al byte indicado en el archivo.
     */
    protected function read_filename(string $filename, int $from = 1, int $to = 1): string {

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
     * Valida si el archivo existe y no es un directorio, aparte de ser legible.
     *
     * @param string $filename Archivo a ser analizado
     * @return void
     * 
     * @throws StorageException
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
     * Devuelve la ruta absoluta completa donde se almacenará un archivo, dentro del
     * sistema de almacenamiento gestionado por la clase. Puede opcionalmente crear
     * el directorio contenedor si no existe.
     *
     * Si se establece `$create_dir` en `true`, la función asegura que el directorio
     * padre del archivo exista, creándolo si es necesario. En caso de que exista un
     * archivo con el mismo nombre que el directorio, se lanza una excepción.
     *
     * @param string $filename Nombre relativo del archivo (puede contener subdirectorios).
     * @param bool $create_dir Si se debe crear el directorio contenedor si no existe. Por defecto es `false`.
     *
     * @return string Ruta absoluta del archivo dentro del almacenamiento gestionado.
     *
     * @throws StorageException Si `$create_dir` es `true` y existe un archivo con el mismo nombre que el directorio contenedor.
     *
     * @example Ejemplo de uso
     * ```php
     * $ruta = $storage->get_file_path("documentos/ejemplo.txt", true);
     * // Resultado: /ruta/absoluta/al/proyecto/storage/documentos/ejemplo.txt
     * ```
     *
     * @note Los separadores de directorio son normalizados automáticamente al formato del sistema operativo.
     *
     * @warning Si el nombre proporcionado en `$filename` genera una colisión con un archivo
     * en lugar de un directorio, y `$create_dir` es `true`, la operación fallará.
     */

    public function get_file_path(string $filename, bool $create_dir = false): string {
        /** @var string $root */
        $root = $this->get_document_root();

        /** @var string $separator */
        $separator = DIRECTORY_SEPARATOR;

        $filename = preg_replace("/[\\\\\/]+/", $separator, $filename);
        $filename = trim($filename, "\{$separator}");

        /** @var string $file */
        $file = "{$root}{$separator}storage{$separator}{$filename}";

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
}
