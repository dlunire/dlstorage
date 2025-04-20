<?php

declare(strict_types=1);

namespace DLStorage\Storage;

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
abstract class DataStorage {
    /**
     * Convierte el contenido a su representación binaria con dígitos alterados
     *
     * @param string $data Datos a ser convertir en su representación binaria
     * @param string $filename Nombre de archivo
     * @param string $entropy Permite agregar algo de entropía al cifrado no estándar
     * @return bool
     */
    public function set_binary_data(string $data, string $filename, ?string $entropy = null): bool {

        $data = base64_encode($data);

        /**
         * Representación numérica de cada carácter
         * 
         * @var integer|null $number
         */
        $number = null;

        /**
         * Almacena una representación hexadecimal de los datos
         * 
         * @var string $hexadecimal
         */
        $hexadecimal = "";

        /**
         * Longitud de caracteres
         * 
         * @var integer $length
         */
        $length = strlen($data);

        for ($index = 0; $index < $length; ++$index) {
            /**
             * Carácter actual
             * 
             * @var string $char
             */
            $char = $data[$index];

            $number = ord($char) + 100;
            $hexadecimal .= dechex($number);
        }

        /**
         * Representación binaria de los datos
         * 
         * @var string $binary
         */
        $binary = hex2bin($hexadecimal);

        return (bool) file_put_contents($filename, $binary);
    }

    /**
     * Genera la firma binaria personalizada para los archivos DLStorage.
     *
     * Prepara la estructura que representa la firma del archivo binario,
     * incluyendo la cabecera con metadatos como autor, copyright, versión, etc.,
     * en un formato binario reconocible por programas personalizados.
     * 
     * El contenido real de la firma se agregará posteriormente en la lógica interna.
     *
     * @param string $data Datos base o plantilla sobre la cual se generará la firma.
     * @return string Firma generada en formato binario (sin codificar).
     * 
     * @access private
     */
    private function get_signature(string $data): string {
        /** @var string $library */
        $library = "DLStorage";

        /** @var string $signature */
        $signature = "";

        for ($index = 0; $index < strlen($library); ++$index) {
            /** @var string $char */
            $char = $library[$index];

            /** @var string $binary_char */
            $binary_char = $this->get_binary_char($char);
        }

        return $data;
    }

    /**
     * Devuelve cada carácter a formato finario
     *
     * @param string $char Carácter a ser analizado
     * @return string
     */
    private function get_binary_char(string $char, ?string $entropy = null): string {
        /** @var int $char_code */
        $char_code = ord($char);

        /** @var string $char_hex_code */
        $char_hex_code = dechex($char_code);

        return hex2bin($char_hex_code);
    }
}
