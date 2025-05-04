<?php

declare(strict_types=1);

namespace DLStorage\Storage;

use DLStorage\Errors\StorageException;

/**
 * Permite guardar y recuperar datos binarios utilizando el sistema de almacenamiento gestionado,
 * sin necesidad de una implementación personalizada.
 *
 * Internamente aplica validaciones de integridad, control de versión, firma de datos
 * y manejo de directorios. Ideal para escenarios donde se necesita una solución lista
 * para uso directo en producción.
 *
 * Compatible con el sistema de transformación de bytes y validación automática del archivo.
 * Soporta escritura protegida, lectura estructurada y verificación de la huella binaria.
 *
 * @version v0.1.0
 * @package DLStorage\Storage
 * @license MIT
 * @author David E Luna M
 * @copyright 2025 David E Luna
 *
 * @see DataStorage Define los métodos y estructuras comunes de bajo nivel.
 * @see StorageException Maneja los errores específicos del almacenamiento binario.
 *
 * @example Guardar datos binarios
 * ```php
 * use DLStorage\Storage\SaveData;
 *
 * $storage = new SaveData();
 * $storage->save_binary_data("respaldo/config.bin", $contenido);
 * ```
 *
 * @example Leer datos previamente guardados
 * ```php
 * $contenido = $storage->read_filename("respaldo/config.bin");
 * ```
 *
 * @note Recomendado para entornos donde no se desea utilizar bases de datos,
 * pero se requiere persistencia confiable con control de integridad.
 */
final class SaveData extends DataStorage {


    /**
     * Guarda la información transformada en un archivo binario con encabezado estructurado.
     *
     * Este método:
     * 1. Codifica los datos crudos y los convierte en una cadena hexadecimal segura.
     * 2. Calcula y concatena la firma, tamaños de sección y versión.
     * 3. Convierte todo el contenido hexadecimal resultante a binario.
     * 4. Escribe el archivo con la extensión `.dlstorage`.
     * 5. Verifica que el archivo se haya creado correctamente.
     *
     * @param string      $filename Nombre del archivo (sin extensión) donde se guardará la información.
     * @param string      $data     Datos crudos que serán transformados byte por byte.
     * @param string|null $entropy  Su uso se recmienda. Llave de entropía opcional para modificar el patrón de transformación.
     * @return void
     *
     * @throws StorageException Si ocurre un error al crear el archivo o faltan permisos de escritura.
     *
     * @see encode()         Transforma los datos de entrada en una representación segura.
     */
    public function save_data(string $filename, string $data, ?string $entropy = NULL): void {
        /** @var string $encode */
        $encode = $this->encode($data, $entropy);

        /** @var string $file */
        $file = $this->get_file_path($filename, true) . ".dlstorage";

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
     * Lee un archivo binario .dlstorage y recupera su contenido original por medio de una llave de entropía.
     * 
     * @internal Este método debe ser invocado únicamente por clases hijas o el framework principal.
     *
     * @param string $filename Nombre del archivo sin extensión (.dlstorage será añadido automáticamente).
     * @param string|null $entropy Llave de entropía usada para revertir la transformación de bytes.
     *
     * @throws StorageException Si el archivo no existe o el contenido es inválido.
     * @return string Retorna el contenido original recuperado tras aplicar la decodificación.
     *
     * @example Example
     * ```php
     * $contenido = $this->read_file_storage("reporte-secreto", "clave🔐");
     * echo $contenido;
     * ```
     */
    public function read_storage_data(string $filename, ?string $entropy = NULL): string {

        $filename = "{$filename}.dlstorage";

        /** @var string $file */
        $file = $this->get_file_path($filename);

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
     * Normaliza el relleno de ceros en una cadena hexadecimal.
     *
     * Este método reemplaza cualquier cantidad de ceros iniciales en una cadena hexadecimal por un único `'0'`,
     * cuando estos ceros fueron agregados como parte del relleno para asegurar una longitud par.
     *
     * ⚠️ Advertencia: Este método no valida si los ceros fueron parte del contenido original o añadidos como relleno.
     * Debe usarse solo en contextos donde se controle el proceso de normalización y se conozca su origen.
     *
     * @version v0.0.1
     * @package DLStorage
     * @license MIT
     * @author David E Luna M
     * @copyright 2025 David E Luna
     *
     * @see encode() Método que puede generar longitud impar en hexadecimal.
     * @see normalize_hex_payload() Método que antepone ceros si la longitud es impar.
     *
     * @param string $content Cadena hexadecimal posiblemente con ceros iniciales.
     * @return string Cadena con un único '0' al inicio si existían múltiples ceros.
     */
    private function delete_padding(string $content): string {
        return preg_replace('/^0+/', '0', $content);
    }


    /**
     * Normaliza el contenido hexadecimal codificado para asegurar compatibilidad binaria.
     *
     * Este método verifica si la longitud del contenido hexadecimal es impar. En tal caso,
     * antepone un "0" al contenido para garantizar que la longitud final sea par, condición
     * requerida por funciones como `hex2bin()` para evitar errores durante la conversión a binario.
     *
     * Dado que esta operación modifica el contenido del payload, también actualiza el valor
     * de `$size`, el cual representa la longitud del payload en formato hexadecimal, para que
     * refleje con precisión la nueva longitud real tras la normalización.
     *
     * Esta operación es reversible mediante el método `delete_padding()`, que elimina el
     * relleno agregado y restaura el tamaño original.
     *
     * @param string &$size    Referencia al tamaño hexadecimal del payload (en longitud de cadena).
     * @param string &$content Referencia al contenido hexadecimal codificado a normalizar.
     *
     * @return void
     *
     * @see delete_padding() Método complementario para revertir la normalización.
     * @see encode() Método responsable de producir la salida hexadecimal original.
     */

    private function normalize_hex_payload(string &$size, string &$content): void {
        /** @var int $payload_int */
        $payload_int = hexdec($size);

        /** @var bool $is_residue */
        $is_residue = strlen($content) % 2 != 0;

        if ($is_residue) {
            $content = "0{$content}";
            $size = str_pad(dechex($payload_int + 1), 8, '0', STR_PAD_LEFT);
        }
    }

    /**
     * Calcula la longitud de la sección a partir del contenido en hexadecimal
     * y devuelve su representación como una cadena de 8 caracteres hexadecimales
     * (32 bits, big-endian), rellenada con ceros a la izquierda.
     *
     * @param string $hex_content Contenido en formato hexadecimal cuyo tamaño
     *                            en bytes se determinará al convertirlo a binario.
     * @return string Cadena de 8 caracteres hexadecimales que representa el
     *                tamaño en bytes del contenido original.
     *
     * @since v0.1.0
     */
    private function get_section_size(string $hex_content): string {

        /** @var int $length_int */
        $length_int = intdiv(strlen($hex_content), 2);

        return str_pad(dechex($length_int), 8, '0', STR_PAD_LEFT);
    }
}
