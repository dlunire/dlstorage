<?php

namespace DLStorage\Storage;

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
        $decimal = hexdec($hex) + $entropy + 220000;

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
     * @return void
     */
    public function decode(string $encoded, ?string $entropy = null) {
        $this->expand_zero($encoded);

        /** @var string[] $blocks */
        $blocks = str_split($encoded, 10);

        /** @var int|float $sum */
        $sum = 0;

        $this->set_entropy_value($sum, $entropy);

        foreach ($blocks as $key => $block) {

            $value = hexdec($block);
        }

        print_r($blocks);
    }

    /**
     * Convierte a formato legible el contenido binario encontrado en el archivo
     *
     * @param string $string_data Dato en su estado original
     * @return string
     */
    public function to_text(string $string_data): string {

        return "";
    }

    /**
     * Cuenta la cantidad de ceros consecutivos que aparecen como relleno en una cadena de entrada.
     *
     * Este método busca la primera secuencia de ceros consecutivos dentro del texto proporcionado,
     * y devuelve la longitud de dicha secuencia. Si no se encuentra ninguna secuencia de ceros,
     * retorna cero.
     *
     * @param string $input Cadena de texto que será analizada para detectar ceros consecutivos.
     *
     * @return int Retorna la cantidad de ceros consecutivos encontrados como relleno. Si no hay, retorna 0.
     */
    protected function count_zero(string $input): int {
        preg_match("/^0+/", $input, $match);

        /** @var string|null $padding_zero Primer grupo de ceros consecutivos encontrados. */
        $padding_zero = $match[0] ?? null;

        if (!is_string($padding_zero)) {
            return 0;
        }

        return mb_strlen($padding_zero);
    }


    /**
     * Compacta una secuencia continua de ceros convirtiéndola en una representación hexadecimal.
     *
     * Este método detecta la primera secuencia de ceros consecutivos en la cadena de entrada y la
     * reemplaza por una cadena de dos caracteres hexadecimales (`0xn`), donde `n` representa
     * la longitud de la secuencia original de ceros en formato hexadecimal, con relleno izquierdo.
     *
     * Por ejemplo, una entrada como `"00000ABC"` devolverá `"05ABC"`, ya que `0x05` representa
     * los cinco ceros iniciales, omitida la notación `0x` por simplicidad en la codificación.
     *
     * @param string $input Cadena de texto a ser analizada y compactada.
     *
     * @return void
     */
    protected function compact_zero(string &$input): void {

        /** @var int $length Longitud de la secuencia de ceros encontrada. */
        $length = $this->count_zero($input);

        /** @var string $hex_length Longitud convertida a hexadecimal (2 dígitos). */
        $hex_length = str_pad(
            string: dechex($length),
            length: 2,
            pad_string: '0',
            pad_type: STR_PAD_LEFT
        );

        /** @var string|false $input Reemplazo de la secuencia de ceros por la longitud en hexadecimal. */
        $input = preg_replace("/^0+/", $hex_length, $input);

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
        /** @var string $length_pattern */
        $length_pattern = "/[0][a0-9]/i";

        preg_match_all($length_pattern, $input, $matches);

        /** @var string[] $bytes */
        $bytes = $matches[0] ?? [];

        /** @var array<string,string> $dictionary */
        $dictionary = [];

        foreach ($bytes as $byte) {
            /** @var string $length */
            $length = hexdec($byte);

            $dictionary[$byte] = str_pad("", $length, '0');
        }

        foreach ($dictionary as $key => $zero) {
            $input = str_replace($key, $zero, $input);
        }
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

        $this->foreach($input, function (string $char, int $index) use ($sum, &$string_data) {
            /** @var string $current_data */
            $current_data = $this->to_hex(
                $char,
                $sum * ($index / 100) + sin($index + $sum)
            );

            $this->compact_zero($current_data);
            $this->last_offset = $index;

            $string_data .= $current_data;
        });
    }

    /**
     * Establece el valor de la entropía
     *
     * @return void
     */
    private function set_entropy_value(int &$sum, ?string &$entropy = null): void {
        if (!is_string($entropy)) return;

        $this->foreach($entropy, function (string $char) use (&$sum, $entropy) {
            $sum += hexdec(bin2hex($char)) + strlen($entropy);
        });
    }
}
