<?php

declare(strict_types=1);

namespace DLStorage\Storage;

use ValueError;

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

    public function save_binary_data(string $data_string, string $dir) {

        /** @var string */
        $root = $this->get_document_root();
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
}
