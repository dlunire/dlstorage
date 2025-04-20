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

        /** @var string $binary */
        $binary = "";

        /**
         * Longitud de caracteres
         * 
         * @var integer $length
         */
        $length = strlen($data);

        /** @var int $sum */
        $sum = $this->get_entropy($entropy);

        for ($index = 0; $index < $length; ++$index) {
            /**
             * Carácter actual
             * 
             * @var string $char
             */
            $char = $data[$index];

            $binary .= $this->get_binary_char($char, $sum);
        }

        $binary .= $this->get_signature();
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
     * @return string Firma generada en formato binario (sin codificar).
     * 
     * @access private
     */
    public function get_signature(): string {
        /** @var string $library */
        $library = "DLStorage";

        /** @var string $signature */
        $signature = "";

        for ($index = 0; $index < strlen($library); ++$index) {
            /** @var string $char */
            $char = $library[$index];

            /** @var string $binary_char */
            $binary_char = $this->get_binary_char($char);

            $library .= $binary_char;
        }

        return $library;
    }

    /**
     * Devuelve cada carácter a formato finario
     *
     * @param string $char Carácter a ser analizado
     * @param int $entropy Valor sumatoria de la entropía
     * @return string
     */
    private function get_binary_char(string $char, int $entropy = 0): string {
        /** @var int $char_code */
        $char_code = ord($char) + $entropy;

        /** @var string $char_hex_code */
        $char_hex_code = dechex($char_code);

        return hex2bin($char_hex_code);
    }

    /**
     * Devuelve la entropía numérica a partir de una frase.
     *
     * @param string|null $entropy Frase a ser analizada para crear entropía.
     * @return integer
     */
    private function get_entropy(?string $entropy = null): int {

        if (!is_string($entropy) || empty(trim($entropy))) {
            return 0;
        }

        /** @var int $length */
        $length = strlen($entropy);

        /** @var int $sum */
        $sum = 0;

        for ($index = 0; $index < $length; ++$index) {
            $sum += ord($entropy[$index]);
        }

        return $sum;
    }
}
