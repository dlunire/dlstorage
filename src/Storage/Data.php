<?php

namespace DLStorage\Storage;

use DLStorage\Errors\StorageException;
use DLStorage\Errors\ValueError as ErrorsValueError;
use DLStorage\Traits\ForTrait;
use ValueError;

/**
 * Generación y manipulación de datos binarios con firmas en la cabecera o principio del archivo.
 *
 * Define una interfaz para el manejo de datos en formato binario personalizado, permitiendo generar firmas,
 * convertir caracteres a binario y calcular entropía. Esta clase sirve como base para adaptadores concretos
 * de almacenamiento en sistemas que requieren estructuras binarizadas para identificar y validar datos.
 *
 * @package    DLStorage\Storage
 * @version    v0.1.0
 * @license    MIT
 * @author     David E. Luna M. <dlunireframework@gmail.com>
 * @copyright  Copyright (c) 2025 David E. Luna M.
 */
abstract class Data {

    use ForTrait;

    private int $last_offset = 0;

    private int $value = 220000;

    /**
     * Convierte un carácter a su representación hexadecimal de 40 bits con la posibilidad de agregar entropía.
     *
     * Este método toma un carácter y lo convierte a una representación hexadecimal de 40 bits (con 10 dígitos hexadecimales).
     * Además, permite ajustar el valor resultante mediante la adición de entropía (un valor entero).
     * La función verifica que solo se pase un único carácter. Si no es así, lanza una excepción `ValueError`.
     *
     * @param string $char El carácter a convertir. Debe ser un solo carácter.
     * @param int $entropy (opcional) Valor de entropía a agregar al valor hexadecimal. El valor predeterminado es 0.
     * @param string $encoding (opcional) La codificación de caracteres. El valor predetermente es 'UTF-8'.
     *
     * @return string La representación hexadecimal de 40 bits del carácter, ajustada con la entropía.
     *
     * @throws ValueError Si el parámetro `$char` no contiene exactamente un solo carácter.
     *
     * @example
     * $hex = $data->to_hex('A'); // Devuelve "0000000000000041"
     * $hex_with_entropy = $data->to_hex40('A', 1); // Devuelve "0000000000000042" si se agrega una entropía de 1.
     */
    public function to_hex(string $char, int $entropy = 0, string $encoding = 'UTF-8'): string {

        /** @var int $length */
        $length = mb_strlen($char, $encoding);

        if ($length != 1) {
            throw new ValueError("Solo se permite un carácter a la vez", 500);
        }

        /** @var string $hex */
        $hex = bin2hex($char);

        /** @var int|float $decimal */
        $decimal = hexdec($hex) + $entropy + $this->value;

        // Asegurarse de que la salida sea de 40 bits (10 dígitos hexadecimales)
        return str_pad(
            string: dechex($decimal),
            length: 10,
            pad_string: '0',
            pad_type: STR_PAD_LEFT
        );
    }

    /**
     * Codifica la cadena de texto a otro formato utilizando una entropía opcional.
     *
     * El método transforma cada carácter de la cadena de entrada en una representación hexadecimal
     * modificada por una suma acumulada basada en la entropía proporcionada y una función matemática
     * con base en el índice del carácter y su valor.
     *
     * @param string $input Cadena de texto que se desea codificar.
     * @param string|null $entropy Cadena opcional utilizada como entropía para alterar la codificación.
     *
     * @return string Retorna la cadena codificada como una representación hexadecimal modificada.
     */
    public function encode(string $input, ?string $entropy = null): string {
        /** @var int|float $sum Suma acumulada derivada de los caracteres de la entropía. */
        $sum = 0;

        /** @var string $string_data Cadena resultante tras la transformación. */
        $string_data = "";
        $this->set_entropy_value($sum, $entropy);
        $this->set_entropy($input, $string_data, $sum);


        return $string_data;
    }


    /**
     * Decodifica el mensaje codificado
     *
     * @param string $encoded Contenido codificado
     * @param string|null $entropy Entropía utilizada para la codificación previa
     * @return string
     * 
     * @throws StorageException
     */
    public function get_decode(string $encoded, ?string $entropy = null): string {
        $this->expand_zero($encoded);
        /** @var string[] $blocks */
        $blocks = str_split($encoded, 10);
        $value = $this->get_reverse_entropy($blocks, $entropy);

        /** @var int $length */
        $length = mb_strlen($value, 'UTF-8');

        /** @var bool $is_pair */
        $is_pair = ($length & 1) == 0;

        if (!$is_pair) {
            throw new StorageException("Es posible que la llave de la entropía sea inválida o los datos se hayan corrompidos", 403);
        }

        return $value;
    }

    /**
     * Devuelve el contenido legible en formato legible
     *
     * @param string $encode Cadena codificada
     * @param string|null $entropy Entropía utilizada previamente
     * @return string
     */
    public function get_text(string $encode, ?string $entropy = null): string {
        return hex2bin($this->get_decode($encode, $entropy));
    }

    /**
     * Compacta una secuencia continua de ceros convirtiéndola en una representación hexadecimal.
     *
     * Este método detecta la primera secuencia de ceros consecutivos en la cadena de entrada y la
     * reemplaza por una cadena de dos caracteres hexadecimales (`0x00`).
     *
     * Por ejemplo, una entrada como `"00000ABC"` devolverá `"01ABC"`
     *
     * @param string $input Cadena de texto a ser analizada y compactada.
     *
     * @return void
     */
    protected function compact_zero(string &$input): void {
        /** @var string|false $input Reemplazo de la secuencia de ceros por la longitud en hexadecimal. */
        $input = preg_replace("/^0+/", '01', $input);

        if (!is_string($input)) {
            return;
        }
    }

    /**
     * Permite revertir el proceso de compactación de ceros
     *
     * @param string $input Entrada a ser analizada
     * @return void
     */
    public function expand_zero(string &$input): void {
        /** @var string[] $blocks */
        $blocks = explode("01", $input);

        /** @var string[] $buffer */
        $buffer = [];

        foreach ($blocks as $block) {
            if (!is_string($block) || empty(trim($block))) continue;
            $block = str_replace("ffff", "01", $block);
            $buffer[] = $this->get_padding_zero($block);
        }

        $input = implode("", $buffer);
    }

    /**
     * Rellena de ceros una cadena de texto incompleta
     *
     * @param string $input Entrada a ser analizada
     * @param boolean $right Rellena de ceros hacia la derecha si vale `true`. El valor por defecto es `false`.
     * @return string
     */
    private function get_padding_zero(string $input, bool $right = false): string {
        return str_pad(
            string: $input,
            length: 10,
            pad_string: '0',
            pad_type: $right ? STR_PAD_RIGHT : STR_PAD_LEFT
        );
    }

    /**
     * Establece el valor de la entroía a la cadena
     *
     * @param string $input Entrada a ser analizada
     * @param string $string_data Datos construidos a partir de la entropía
     * @param integer $sum Valor base de la entropía.
     * @return void
     */
    private function set_entropy(string $input, string &$string_data, int|float $sum = 0): void {

        /** @var string[] $buffer */
        $buffer = [];

        $this->foreach($input, function (string $char, int $index) use ($sum, &$buffer, &$string_data) {
            /** @var int $value */
            $value = $sum * $this->get_circular_value($index) + $this->get_circular_value($index);

            /** @var string $current_data */

            $current_data = $this->to_hex($char, $value);
            $current_data = str_replace("01", "ffff", $current_data);

            $this->last_offset = $index;
            $this->compact_zero($current_data);

            $buffer[] = $current_data;
        });

        $string_data = implode("", $buffer);
    }

    /**
     * Devuelve el valor original con la entropía revertida
     *
     * @param array $blocks Bloques de 40 bits a ser analizados
     * @param integer $sum Suma de entropía a ser revertida
     * @return string
     */
    private function get_reverse_entropy(array &$blocks, ?string $entropy = null): string {

        /** @var int $sum */
        $sum = 0;

        $this->set_entropy_value($sum, $entropy);

        /** @var string[] $buffer */
        $buffer = [];

        foreach ($blocks as $key => $block) {
            $buffer[] = $this->get_hex_value($block, $key, $sum);
        }

        return implode("", $buffer);
    }

    /**
     * Devuelve el valor hexadecimal de cada carácter
     *
     * @param string $block Bloque de bytes a ser analizado
     * @param integer $key Indice que permite calcular el valor de la entropía
     * @return string
     */
    private function get_hex_value(string $block, int $key, int $sum): string {
        /** @var int $entropy_value */
        $entropy_value = $sum * $this->get_circular_value($key) + $this->get_circular_value($key);

        /** @var int $block_value */
        $block_value = hexdec($block);

        /** @var string $value */
        $value = dechex($block_value - $this->value - $entropy_value);

        return str_pad(
            string: $value,
            length: 2,
            pad_string: '0',
            pad_type: STR_PAD_LEFT
        );
    }

    /**
     * Establece el valor de la entropía
     *
     * @return void
     */
    private function set_entropy_value(int &$sum, ?string $entropy = null): void {
        if (!is_string($entropy)) return;

        $this->foreach($entropy, function (string $char) use (&$sum, $entropy) {
            $sum += hexdec(bin2hex($char)) + strlen($entropy);
        });
    }

    /**
     * Devuelve valor circular
     *
     * @param integer|float $value Valor a ser analizado
     * @return integer
     */
    private function get_circular_value(int|float $value): int {
        return ($value * 31 + 17) % 100 + 10;
    }
}
