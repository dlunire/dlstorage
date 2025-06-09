<?php

namespace DLStorage\Storage;

use DLStorage\Errors\StorageException;
use DLStorage\Traits\BinaryLengthTrait;
use DLStorage\Traits\ForTrait;
use ValueError;

/**
 * Generación y manipulación de datos binarios con firmas en la cabecera o principio del archivo.
 *
 * Esta clase proporciona una interfaz para el manejo de datos en formato binario personalizado. Permite generar
 * firmas, convertir caracteres a binario y calcular entropía. Es una clase base diseñada para ser utilizada en
 * adaptadores concretos de almacenamiento en sistemas que requieren estructuras binarizadas para identificar y
 * validar datos. Además, esta clase está diseñada para integrarse con proyectos relacionados con la
 * manipulación avanzada de datos binarios, como *Códigos del Futuro* y el *DLUnire Framework*.
 *
 * *Códigos del Futuro* (@cdelfuturo) es una iniciativa de innovación tecnológica enfocada en soluciones avanzadas
 * de almacenamiento y procesamiento de datos. Puedes seguirnos en nuestras redes sociales para más contenido:
 * - [YouTube](https://www.youtube.com/@codigosdelfuturo)
 * - [X](https://x.com/cdelfuturo)
 * - [TikTok](https://www.tiktok.com/@codigosdelfuturo)
 *
 * *DLUnire Framework* es una plataforma de desarrollo web que integra herramientas de alto rendimiento para la
 * creación de aplicaciones modernas con una arquitectura robusta y flexible. Puedes acceder a más información en los
 * siguientes enlaces:
 * - [Sitio web](https://dlunire.pro)
 * - [Repositorio en GitHub](https://github.com/dlunire)
 *
 * @package    DLStorage\Storage
 * @version    v0.1.0
 * @license    MIT
 * @author     David E. Luna M. <dlunireframework@gmail.com>
 * @copyright  Copyright (c) 2025 David E. Luna M.
 * @link       https://www.dlunire.pro Proyecto *DLUnire Framework*
 * @link       https://github.com/dlunire Repositorio *DLUnire Framework*
 */
abstract class Data {

    use ForTrait;
    use BinaryLengthTrait;

    private int $last_offset = 0;

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
     * Decodifica un mensaje previamente codificado utilizando un esquema de entropía.
     * 
     * Este método toma una cadena de datos codificados y, si se proporciona la entropía utilizada durante 
     * la codificación, intenta revertir la transformación para recuperar el mensaje original. Si la entropía 
     * es incorrecta o si los datos han sido corrompidos, el método lanzará una excepción.
     *
     * @param string $encoded El contenido codificado que se va a decodificar.
     * @param string|null $entropy La entropía que se utilizó durante la codificación, si está disponible.
     *                             Si no se proporciona, el proceso de decodificación puede fallar si se requiere.
     * 
     * @return string La cadena decodificada, es decir, el mensaje original antes de la codificación.
     * 
     * @throws StorageException Si la longitud del mensaje resultante no es un número par, lo que indica 
     *                          que los datos podrían estar corruptos o que la entropía utilizada para la 
     *                          codificación no es válida.
     *
     * @note Este método asume que los datos codificados fueron segmentados en bloques de 10 caracteres 
     *       y que cualquier adición de ceros al final del mensaje original fue tratada previamente.
     * 
     * @example
     * try {
     *     $decoded_message = $data->get_decode($encodedData, $entropyKey);
     * } catch (StorageException $e) {
     *     echo "Error de decodificación: " . $e->getMessage();
     * }
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
            throw new StorageException("Es posible que la llave de la entropía sea inválida o los datos se hayan corrompidos.", 403);
        }

        return $value;
    }

    /**
     * Reconstruye y devuelve el contenido original a partir de una cadena codificada.
     *
     * La salida puede contener datos binarios o texto, dependiendo del contenido original.
     * 
     * @param string $encode  Cadena codificada en formato hexadecimal.
     * @param string|null $entropy  Entropía utilizada durante la codificación, si corresponde.
     * @return string  Contenido reconstruido en formato binario.
     */
    public function get_content(string $encode, ?string $entropy = null): string {
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
     * Revertir la compactación de ceros en una cadena de texto.
     *
     * Este método toma una cadena de texto que ha sido previamente compactada, es decir, donde las secuencias 
     * de ceros consecutivos han sido sustituidas por el marcador especial "01". El proceso de compactación se 
     * realiza normalmente para reducir el tamaño de los datos, pero al decodificar o procesar la información 
     * nuevamente, es necesario restaurar los ceros a su forma original.
     *
     * El método también maneja posibles transformaciones adicionales, como la restauración de valores "ffff" 
     * a "01" dentro de los bloques de datos.
     *
     * @param string &$input Entrada que contiene la cadena compactada, la cual será modificada por el método 
     *                       para restaurar los ceros originales. 
     *                       Este parámetro se pasa por referencia y se actualizará con la cadena resultante.
     * 
     * @return void No retorna ningún valor. La cadena de entrada es modificada directamente.
     * 
     * @note Este proceso es necesario cuando los datos han sido compactados previamente y es esencial para 
     *       restaurar su formato original antes de cualquier procesamiento adicional. Si la entrada contiene 
     *       bloques de datos válidos, estos serán reconstruidos adecuadamente con ceros restaurados.
     * 
     * @example Ejemplo
     * 
     * ```
     * $data = "01ABC01DE"; // Cadena compactada
     * $data_processor->expand_zero($data);
     * echo $data; // La cadena ahora tendrá los ceros restaurados.
     * ```
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
     * Rellena una cadena de texto incompleta con ceros hasta alcanzar una longitud de 10 caracteres.
     *
     * Este método agrega ceros al principio o al final de la cadena según el valor del parámetro `$right`. 
     * Si `$right` es `true`, se rellenará con ceros al final de la cadena; si es `false`, se rellenará al principio. 
     * El valor por defecto es `false`, lo que implica que los ceros se agregarán al principio.
     *
     * @param string $input Cadena de texto que se desea rellenar.
     * @param bool $right Si es `true`, rellena con ceros a la derecha. Por defecto es `false`, lo que rellena a la izquierda.
     * 
     * @return string La cadena de texto original, completada con ceros hasta alcanzar una longitud de 10 caracteres.
     *
     * @example
     * $padded = $this->get_padding_zero("123");  // Devuelve "0000000123"
     * $padded_right = $this->get_padding_zero("123", true);  // Devuelve "1230000000"
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
     * Establece y construye la cadena de datos a partir de la entropía proporcionada.
     *
     * Este método procesa la cadena de entrada (`$input`) aplicando un algoritmo basado en entropía, 
     * el cual ajusta cada carácter de la cadena según un valor calculado a partir de su índice y la 
     * entropía base (`$sum`). El resultado de este proceso es una cadena codificada que se almacena 
     * en la variable `$string_data`.
     *
     * Para cada carácter de la entrada, se calcula un valor en función de la entropía y se convierte 
     * a su representación hexadecimal. Además, se realizan transformaciones adicionales, como la 
     * compactación de secuencias de ceros y la sustitución de ciertos valores (p. ej., `01` por `ffff`).
     *
     * El resultado final es una cadena procesada que tiene en cuenta la entropía y otras transformaciones 
     * necesarias para su almacenamiento o manipulación.
     *
     * @param string $input Cadena de entrada que será procesada y transformada.
     * @param string &$string_data Cadena de salida construida a partir de la entropía. Este parámetro se modifica por referencia.
     * @param int|float $sum Valor base de la entropía. Determina cómo se ajusta cada valor durante el proceso. El valor predeterminado es 0.
     *
     * @return void
     *
     * @example
     * // Construir la cadena a partir de un input y la entropía base.
     * $data->set_entropy("Hello, World", $string_data, 5);
     * echo $string_data;  // Resultado de la cadena transformada con la entropía aplicada.
     */
    private function set_entropy(string $input, string &$string_data, int|float $sum = 0): void {

        /** @var string[] $buffer */
        $buffer = [];

        $this->foreach_string($input, function (int $byte, int $index) use ($sum, &$buffer, &$test) {

            /** @var int $entropy */
            $entropy = $this->get_entropy($index, $sum);

            /** @var string $current_data */
            $current_data = $this->to_hex40($byte, $entropy);

            $current_data = str_replace("01", "ffff", $current_data);

            $this->compact_zero($current_data);

            $buffer[] = $current_data;
        });

        $string_data = implode("", $buffer);
    }

    /**
     * Revierte la entropía aplicada a los bloques y devuelve el valor original.
     *
     * Este método procesa los bloques de datos, revertiendo el efecto de la entropía aplicada previamente 
     * para obtener la cadena original. Cada bloque de 40 bits es analizado y transformado utilizando el valor 
     * de entropía proporcionado o el valor calculado, en caso de que no se pase explícitamente. Los bloques 
     * se reordenan y se procesan para recuperar los datos antes de ser codificados con la entropía.
     *
     * El valor de la entropía puede ser proporcionado como un parámetro opcional, o en su defecto, se utilizará 
     * el valor predeterminado. Este proceso implica aplicar un cálculo inverso de la entropía y reconstruir la 
     * cadena original a partir de los bloques dados.
     *
     * @param array $blocks Bloques de 40 bits que contienen los datos codificados, que serán procesados 
     *                       para revertir la entropía.
     * @param string|null $entropy (Opcional) Valor de entropía utilizado en la codificación. Si no se proporciona, 
     *                             se utilizará un valor predeterminado o calculado automáticamente.
     *
     * @return string La cadena original obtenida después de revertir la entropía aplicada.
     *
     * @example Ejemplo
     * ```
     * // Revertir la entropía de una serie de bloques.
     * $blocks = ["block1", "block2", "block3"];
     * $original_data = $data->get_reverse_entropy($blocks, "entropy_value");
     * echo $original_data;  // Resultado de la cadena original antes de la entropía.
     * ```
     */
    private function get_reverse_entropy(array &$blocks, ?string $entropy = null): string {

        /** @var int $sum */
        $sum = 0;

        $this->set_entropy_value($sum, $entropy);

        /** @var string[] $buffer */
        $buffer = [];

        foreach ($blocks as $key => $block) {
            $buffer[] = $this->from_hex40($block, $key, $sum);
        }

        return implode("", $buffer);
    }

    /**
     * Obtiene el código numérico de un carácter basado en su representación binaria hexadecimal,
     * ajustado por su posición en la cadena.
     *
     * Este método toma un carácter de tipo string y calcula su valor numérico acumulado. Primero,
     * convierte el carácter a su representación binaria en formato hexadecimal mediante `bin2hex()`, 
     * luego convierte esta representación a un valor decimal con `hexdec()`. A continuación, se ajusta 
     * este valor sumando el índice del carácter incrementado en 1.
     *
     * La operación realizada en este método permite obtener un valor de entropía numérica basado en el 
     * valor binario del carácter, mientras que también toma en cuenta la posición del carácter en la 
     * cadena, lo que lo hace útil para cálculos de entropía acumulada.
     *
     * ### Fórmula aplicada:
     * 
     * Sea \( e_i \) un carácter en la cadena, su valor numérico ajustado será:
     * 
     * \[
     * f(e_i, i) = \text{hexdec}(\text{bin2hex}(e_i)) + (i + 1)
     * \]
     * 
     * Donde:
     * - \( e_i \) es el carácter procesado.
     * - \( i \) es el índice del carácter en la cadena.
     * - La función \( f(e_i, i) \) representa el valor numérico ajustado del carácter, teniendo en cuenta su
     *   valor binario y su posición en la cadena.
     * 
     * @param string $char Carácter de tipo string cuyo valor numérico se desea obtener.
     * @param int $index Índice del carácter dentro de la cadena de entropía.
     * 
     * @return int Valor decimal que representa el código numérico ajustado del carácter basado en su
     *         representación binaria en formato hexadecimal y su índice en la cadena.
     *  
     * @throws InvalidArgumentException Si el parámetro proporcionado no es una cadena de caracteres válida.
     *
     * @example For example
     * ```php
     * $char = 'A';
     * $index = 0;
     * $code = $data->get_char_code($char, $index);
     * echo $code;  // Salida: 66 (65 + (0 + 1))
     * ```
     */
    private function get_char_code(string $char, int $index): int {
        return hexdec(bin2hex($char)) + $index;
    }
}
