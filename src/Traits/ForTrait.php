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
     * Itera sobre una cadena o arreglo y ejecuta un `callback` por cada carácter o elemento.
     *
     * Si se proporciona una cadena, la iteración se realiza carácter por carácter (como un bucle `for`).
     * Si se proporciona un arreglo, se itera elemento por elemento.
     *
     * @param string|string[] $data     Datos a iterar. Puede ser una cadena (`string`) o un arreglo (`array`).
     * @param callable     $callback Función anónima que se ejecuta por cada elemento. Recibe como parámetro
     *                               el carácter o valor actual de la iteración.
     *
     * @return void
     */
    public function foreach(string|array $data, callable $callback): void {
        /** @var int $length Longitud de la cadena o cantidad de elementos del arreglo */
        $length = is_array($data) ? count($data) : strlen($data);

        for ($index = 0; $index < $length; ++$index) {
            $callback($data[$index], (int) $index, $data);
        }
    }
}
