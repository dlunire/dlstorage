<?php

/**
 * DLUnire
 * Copyright (C) 2026 David E Luna M
 *
 * Operando bajo el establecimiento de comercio "DLUnire",
 * NIT 700551569-1, matrícula mercantil Nº 10007069
 * (matrícula mercantil personal Nº 10007068).
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as
 * published by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public
 * License along with this program. If not, see
 * <https://www.gnu.org/licenses/>.
 */

declare(strict_types=1);

namespace DLStorage\Utilities;

use IteratorAggregate;

/**
 * Colección de arrays con API orientada a métodos encadenables y seguimiento de longitud.
 *
 * Implementa `IteratorAggregate` para permitir iteración con `foreach`. Los métodos
 * públicos siguen la convención `snake_case` de DLStorage, salvo `getIterator()`
 * requerido por la interfaz de PHP.
 *
 * @package    DLStorage\Utilities
 * @version    v0.2.0
 * @license    AGPL-3.0-or-later
 * @author     David E. Luna M. <info@dlunire.dev>
 * @copyright  Copyright (c) 2026 David E. Luna M.
 *
 * @template T
 *
 * @note Los métodos `item`, `first`, `last`, `pop` y `shift` lanzan
 *       {@see \OutOfBoundsException} ante índices inválidos o arrays vacíos.
 */
abstract class FastArray implements IteratorAggregate {
    /**
     * Almacenamiento interno de elementos.
     *
     * @var array<int, mixed>
     */
    private array $data;

    /**
     * Longitud del array.
     *
     * @var integer $length
     */
    private int $length;

    /**
     * Constructor que inicializa el array y su longitud.
     *
     * @param array $data
     */
    public function __construct(array $data = []) {
        $this->clear();
        $this->add($data);
    }

    /**
     * Agrega un nuevo elemento al final del array interno, preservando el orden de inserción.
     * 
     * @param mixed $value Valor que será insertado en el array. 
     *                     Se permiten valores de cualquier tipo de dato admitido por PHP.
     * 
     * @return void
     * 
     * @note Este método incrementa automáticamente la propiedad {@see $length} para reflejar
     *       el tamaño actualizado del array.
     */
    public function push(mixed $value): void {
        $this->data[] = $value;
        ++$this->length;
    }

    /**
     * Elimina todos los elementos del array interno y reinicia su longitud.
     * 
     * Este método deja la estructura en su estado inicial, con un array vacío y
     * la propiedad {@see $length} restablecida a `0`.
     * 
     * @return void
     * 
     * @note A diferencia de la reasignación manual del array, este método asegura
     *       la consistencia entre los datos internos y la longitud registrada.
     */
    public function clear(): void {
        $this->data = [];
        $this->length = 0;
    }

    /**
     * Obtiene el array interno de datos sin modificaciones.
     * 
     * Devuelve la representación cruda del array utilizado internamente 
     * para almacenar los elementos. La estructura mantiene el orden de 
     * inserción y puede contener valores de cualquier tipo.
     * 
     * @return array<int,mixed> Array crudo de elementos almacenados.
     * 
     * @note Devuelve la referencia interna; las modificaciones externas al array
     *       retornado afectan el estado de la instancia.
     */
    public function get(): array {
        return $this->data;
    }


    /**
     * Obtiene la cantidad de elementos almacenados en el array interno.
     *
     * Devuelve el número total de elementos actualmente contenidos 
     * en la colección, manteniendo coherencia con las operaciones 
     * de inserción y eliminación realizadas.
     *
     * @return int Número de elementos en el array interno.
     */
    public function length(): int {
        return $this->length;
    }

    /**
     * Agrega múltiples elementos al array interno.
     *
     * Combina los elementos del array proporcionado con los ya existentes,
     * manteniendo el orden de inserción. Si el conjunto está vacío, 
     * la operación no tiene efecto.
     *
     * @param array<int,mixed> $data Conjunto de elementos a agregar.
     * @return void
     */
    public function add(array $data): void {
        if (empty($data)) {
            return;
        }
        $this->data = array_merge($this->data, $data);
        $this->length += count($data);
    }

    /**
     * Obtiene un elemento específico del array interno por su índice.
     *
     * Valida que el índice se encuentre dentro de los límites del array.
     * En caso contrario, lanza una excepción.
     *
     * @param int $index Índice del elemento a recuperar (basado en cero).
     * @throws \OutOfBoundsException Si el índice está fuera de los límites del array.
     * @return mixed Elemento almacenado en la posición indicada.
     */
    public function item(int $index): mixed {
        if ($index < 0 || $index >= $this->length) {
            throw new \OutOfBoundsException("Índice fuera de los límites del array", 400);
        }
        return $this->data[$index];
    }

    /**
     * Obtiene el primer elemento del array interno.
     *
     * Este método devuelve el elemento almacenado en la primera posición
     * del array. Si el array está vacío, se lanza una excepción.
     *
     * @return mixed Primer elemento del array.
     * @throws \OutOfBoundsException Si el array no contiene elementos.
     */
    public function first(): mixed {
        if ($this->length === 0) {
            throw new \OutOfBoundsException("El array está vacío", 400);
        }
        return $this->data[0];
    }

    /**
     * Obtiene el último elemento del array interno.
     *
     * Este método devuelve el elemento almacenado en la última posición
     * del array. Si el array está vacío, se lanza una excepción.
     *
     * @return mixed Último elemento del array.
     * @throws \OutOfBoundsException Si el array no contiene elementos.
     */
    public function last(): mixed {
        if ($this->length === 0) {
            throw new \OutOfBoundsException("El array está vacío", 400);
        }

        return $this->data[$this->length - 1];
    }

    /**
     * Extrae y devuelve el último elemento del array interno.
     *
     * Este método elimina el último elemento almacenado en el array 
     * y lo retorna. La longitud del array se ajusta automáticamente 
     * al decrementar en una unidad.
     *
     * @return mixed Último elemento eliminado y devuelto.
     * @throws \OutOfBoundsException Si el array no contiene elementos.
     */
    public function pop(): mixed {
        if ($this->length === 0) {
            throw new \OutOfBoundsException("El array está vacío", 400);
        }
        $value = array_pop($this->data);
        $this->length--;
        return $value;
    }

    /**
     * Extrae y devuelve el primer elemento del array interno.
     *
     * Este método elimina el primer elemento almacenado en el array 
     * y lo retorna. La longitud del array se ajusta automáticamente 
     * al decrementar en una unidad.
     *
     * @return mixed Primer elemento eliminado y devuelto.
     * @throws \OutOfBoundsException Si el array no contiene elementos.
     */
    public function shift(): mixed {
        if ($this->length === 0) {
            throw new \OutOfBoundsException("El array está vacío", 400);
        }

        $value = array_shift($this->data);
        $this->length--;

        return $value;
    }

    /**
     * Devuelve un iterador para recorrer el array.
     *
     * @return \Traversable
     */
    public function get_iterator(): \Traversable {
        return new \ArrayIterator($this->data);
    }

    /**
     * Devuelve un iterador para recorrer el array. Este método es parte de la interfaz IteratorAggregate, por
     * lo que está utilizando `camelCase` en lugar de `snake_case`.
     *
     * @return \Traversable
     */
    public function getIterator(): \Traversable {
        return $this->get_iterator();
    }

    /**
     * Modifica el array interno eliminando y/o reemplazando elementos, y devuelve los elementos eliminados.
     *
     * Este método utiliza `array_splice` para:
     * 1. Eliminar hasta `$length` elementos a partir del índice `$offset`.
     * 2. Reemplazar los elementos eliminados con los contenidos de `$replacement`.
     *
     * La operación **modifica directamente el array interno** y reindexa los índices numéricos.
     * Los elementos eliminados se devuelven como un **nuevo objeto FastArray**.
     *
     * @param int $offset Índice de inicio para la eliminación/reemplazo. Puede ser negativo para contar desde el final.
     * @param int|null $length Número de elementos a eliminar. Si es `null`, elimina hasta el final del array.
     * @param array<mixed>|mixed $replacement Elementos que reemplazarán a los eliminados. Si no es un array, se convierte automáticamente en uno.
     *
     * @return static Una nueva instancia de FastArray que contiene los elementos eliminados.
     *
     * @example
     * ```php
     * $array = new FastArray([1, 2, 3, 4, 5]);
     * $removed = $array->splide(1, 2, [8, 9]); // $array ahora: [1, 8, 9, 4, 5]
     * print_r($removed->get());               // [2, 3]
     * ```
     */
    public function splide(int $offset, ?int $length = null, mixed $replacement = []): static {
        /** @var array $removed */
        $removed = array_splice($this->data, $offset, $length, is_array($replacement) ? $replacement : [$replacement]);
        $this->length = count($this->data);

        return new static($removed);
    }


    /**
     * Devuelve una porción del array interno sin modificarlo.
     *
     * Utiliza `array_slice` para obtener un subarray a partir de `$offset` y con longitud `$length`.
     * A diferencia de `splide`, **no modifica el array interno**, sino que devuelve una nueva instancia de FastArray
     * que contiene únicamente los elementos seleccionados.
     *
     * @param int $offset Índice inicial para la porción. Puede ser negativo para contar desde el final del array.
     * @param int|null $length Número de elementos a incluir. Si es `null`, se seleccionan hasta el final del array.
     * @param bool $preserve_keys Indica si se deben conservar los índices originales. Por defecto es `false`.
     *
     * @return static Una nueva instancia de FastArray que contiene la porción seleccionada.
     *
     * @example
     * 
     * ```php
     * $array = new FastArray([10, 20, 30, 40, 50]);
     * $sub = $array->slice(1, 3);             // $sub contiene [20, 30, 40]
     * print_r($array->get());                  // Array original permanece [10, 20, 30, 40, 50]
     * $sub_preserved = $array->slice(1, 3, true); // Conserva índices: [1 => 20, 2 => 30, 3 => 40]
     * ```
     */
    public function slice(int $offset, ?int $length = null, bool $preserve_keys = false): static {
        return new static(array_slice($this->data, $offset, $length, $preserve_keys));
    }

    /**
     * Devuelve el contenido completo del array interno como un array crudo.
     *
     * Este método proporciona acceso directo a los datos almacenados en la instancia,
     * sin alterar el array original. Es útil cuando se necesita trabajar con los valores
     * fuera de la clase, o pasarlos a funciones que requieren un array nativo de PHP.
     *
     * @method array<int,mixed> toArray()
     *
     * @return array<int,mixed> El array crudo que contiene todos los elementos internos.
     *
     * @example
     * ```php
     * $raw = $fastArray->toArray();
     * print_r($raw);
     * ```
     */
    public function to_array(): array {
        return $this->data;
    }
}
