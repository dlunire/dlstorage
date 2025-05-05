<?php

declare(strict_types=1);

namespace DLStorage\Traits;

/**
 * Trait ForTrait
 *
 * Proporciona una abstracción reutilizable del bucle `for` para recorrer cadenas de texto o arreglos
 * y ejecutar un `callback` sobre cada elemento. Diseñado para ofrecer una forma más legible y declarativa
 * de iterar datos secuenciales.
 *
 * ### Ejemplo de uso con cadena (equivalente a un bucle `for` clásico)
 *
 * ```php
 * <?php
 * $entropy = "ABC123";
 * $sum = 0;
 *
 * // Forma clásica
 * for ($i = 0; $i < strlen($entropy); ++$i) {
 *     $sum += intval(mb_ord($entropy[$i]));
 * }
 *
 * // Con ForTrait
 * $this->foreach($entropy, function (string $char) use (&$sum) {
 *     $sum += intval(mb_ord($char));
 * });
 * ```
 *
 * ### Ejemplo de uso con arreglo:
 *
 * ```php
 * <?php
 * $items = ['a', 'b', 'c'];
 * $this->foreach($items, function ($item) {
 *     echo $item . PHP_EOL;
 * });
 * ```
 *
 * @version     v0.0.1
 * @package     DLStorage\Traits
 * @license     MIT
 * @author      David Eduardo Luna Montilla <tu-email@ejemplo.com>
 * @copyright   Copyright (c) 2025 David Eduardo Luna Montilla
 */
trait ForTrait {
    /**
     * Itera sobre una cadena de caracteres o un arreglo, ejecutando un callback por cada elemento.
     *
     * Este método proporciona una forma unificada de recorrer secuencialmente estructuras lineales
     * (cadenas o arreglos). Para cada carácter (en cadenas) o elemento (en arreglos), se ejecuta 
     * una función de devolución de llamada proporcionada por el usuario. La función recibe el valor 
     * actual, su índice y la estructura completa como argumentos.
     *
     * ### Comportamiento:
     * - Si `$data` es una cadena, se itera carácter por carácter usando índices numéricos.
     *   > ⚠️ Esto no es seguro para cadenas multibyte (UTF-8) con caracteres especiales. Utiliza `mb_*` si es necesario.
     * - Si `$data` es un arreglo, se itera elemento por elemento por posición numérica.
     *
     * ### Ejemplo de uso:
     * ```php
     * $this->foreach(['a', 'b', 'c'], function ($value, $index, $all) {
     *     echo "Elemento #{$index}: {$value}\n";
     * });
     *
     * $this->foreach("XYZ", function ($char, $i) {
     *     echo "Carácter {$i}: {$char}\n";
     * });
     * ```
     *
     * @param string|array $data     Datos a iterar. Puede ser una cadena (`string`) o un arreglo de cualquier tipo.
     * @param callable     $callback Función anónima a ejecutar por cada carácter o elemento.
     *                               Recibe tres parámetros: `mixed $value`, `int $index`, `string|array $original`.
     *
     * @return void No devuelve ningún valor; opera por efectos colaterales del callback.
     */
    public function foreach(string|array $data, callable $callback): void {
        /** @var int $length Longitud de la cadena o cantidad de elementos del arreglo */
        $length = is_array($data) ? count($data) : strlen($data);

        for ($index = 0; $index < $length; ++$index) {
            $callback($data[$index], (int) $index, $data);
        }
    }


    /**
     * Itera sobre cada byte de una cadena de texto en su representación binaria.
     *
     * Esta función convierte la cadena de entrada en una secuencia de bytes (valores enteros entre 0 y 255)
     * utilizando codificación binaria sin firmar. Por cada byte iterado, se invoca la función de devolución 
     * de llamada proporcionada por el usuario, a la cual se le pasan el byte actual, su índice dentro de la 
     * secuencia y el arreglo completo de bytes.
     *
     * ### Ejemplo de uso:
     * ```php
     * $this->foreach_string("ABC", function ($byte, $index, $bytes) {
     *     echo "Byte en posición {$index}: {$byte}\n";
     * });
     * ```
     *
     * @param string   $input    Cadena de entrada a ser analizada como secuencia de bytes.
     * @param callable $callback Función anónima (closure) que se ejecuta por cada byte.
     *                           Recibe tres parámetros: `int $byte`, `int $index`, `int[] $bytes`.
     *
     * @return void No retorna ningún valor. Ejecuta el callback por efecto colateral.
     */
    public function foreach_string(string $input, callable $callback): void {
        /** @var array<int,int> $bytes */
        $bytes = array_values(unpack("C*", $input));

        foreach ($bytes as $key => $byte) {
            $callback($byte, $key, $bytes);
        }
    }
}
