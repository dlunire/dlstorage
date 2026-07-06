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
 * Colección indexada con API encadenable y contador de longitud explícito.
 *
 * Mantiene un array interno y la propiedad `$length` sincronizada con las
 * operaciones de inserción y eliminación. Implementa `IteratorAggregate` para
 * `foreach`; los métodos públicos usan `snake_case`, salvo `getIterator()`.
 *
 * @package    DLStorage\Utilities
 * @version    v0.2.0
 * @license    AGPL-3.0-or-later
 * @author     David E. Luna M. <info@dlunire.dev>
 * @copyright  Copyright (c) 2026 David E. Luna M.
 *
 * @template T
 *
 * @abstract Debe extenderse para instanciar. Los métodos que devuelven `static`
 *           crean instancias de la subclase concreta.
 *
 * @note `item`, `first`, `last`, `pop` y `shift` lanzan {@see \OutOfBoundsException}
 *       con código `400` si el índice es inválido o la colección está vacía.
 */
abstract class FastArray implements IteratorAggregate {
    /**
     * Elementos almacenados con índices numéricos consecutivos.
     *
     * @var array<int, mixed>
     */
    private array $data;

    /**
     * Número de elementos; se actualiza en cada mutación, no se infiere con `count()`.
     */
    private int $length;

    /**
     * Inicializa la colección vacía y agrega los elementos de `$data`.
     *
     * @param array<int, mixed> $data Elementos iniciales (puede ser vacío).
     */
    public function __construct(array $data = []) {
        $this->clear();
        $this->add($data);
    }

    /**
     * Añade un elemento al final e incrementa `$length`.
     *
     * @param mixed $value Valor a insertar.
     */
    public function push(mixed $value): void {
        $this->data[] = $value;
        ++$this->length;
    }

    /**
     * Vacía el array interno y restablece `$length` a 0.
     */
    public function clear(): void {
        $this->data = [];
        $this->length = 0;
    }

    /**
     * Devuelve el array interno.
     *
     * @return array<int, mixed> Copia del array (semántica copy-on-write de PHP al modificar).
     */
    public function get(): array {
        return $this->data;
    }

    /**
     * Devuelve el número de elementos registrado en `$length`.
     *
     * @return int Cantidad actual de elementos.
     */
    public function length(): int {
        return $this->length;
    }

    /**
     * Concatena los elementos de `$data` al final mediante `array_merge`.
     *
     * No hace nada si `$data` está vacío.
     *
     * @param array<int, mixed> $data Elementos a agregar.
     */
    public function add(array $data): void {
        if (empty($data)) {
            return;
        }
        $this->data = array_merge($this->data, $data);
        $this->length += count($data);
    }

    /**
     * Obtiene el elemento en el índice indicado.
     *
     * @param int $index Posición basada en cero (`0` … `$length - 1`).
     *
     * @return mixed Elemento en esa posición.
     *
     * @throws \OutOfBoundsException Si `$index` está fuera de rango (código 400).
     */
    public function item(int $index): mixed {
        if ($index < 0 || $index >= $this->length) {
            throw new \OutOfBoundsException("Índice fuera de los límites del array", 400);
        }
        return $this->data[$index];
    }

    /**
     * Devuelve el primer elemento sin eliminarlo.
     *
     * @return mixed Elemento en el índice 0.
     *
     * @throws \OutOfBoundsException Si la colección está vacía (código 400).
     */
    public function first(): mixed {
        if ($this->length === 0) {
            throw new \OutOfBoundsException("El array está vacío", 400);
        }
        return $this->data[0];
    }

    /**
     * Devuelve el último elemento sin eliminarlo.
     *
     * @return mixed Elemento en el índice `$length - 1`.
     *
     * @throws \OutOfBoundsException Si la colección está vacía (código 400).
     */
    public function last(): mixed {
        if ($this->length === 0) {
            throw new \OutOfBoundsException("El array está vacío", 400);
        }

        return $this->data[$this->length - 1];
    }

    /**
     * Elimina y devuelve el último elemento con `array_pop`.
     *
     * @return mixed Valor extraído.
     *
     * @throws \OutOfBoundsException Si la colección está vacía (código 400).
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
     * Elimina y devuelve el primer elemento con `array_shift`.
     *
     * Reindexa los índices numéricos restantes.
     *
     * @return mixed Valor extraído.
     *
     * @throws \OutOfBoundsException Si la colección está vacía (código 400).
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
     * Crea un `ArrayIterator` sobre el array interno.
     *
     * @return \Traversable<int, mixed>
     */
    public function get_iterator(): \Traversable {
        return new \ArrayIterator($this->data);
    }

    /**
     * Implementación de `IteratorAggregate` (convención `camelCase` de PHP).
     *
     * @return \Traversable<int, mixed>
     */
    public function getIterator(): \Traversable {
        return $this->get_iterator();
    }

    /**
     * Elimina y/o reemplaza un tramo del array interno con `array_splice`.
     *
     * Modifica `$this->data` in-place, recalcula `$length` con `count()` y devuelve
     * los elementos eliminados en una nueva instancia de la subclase concreta.
     *
     * @param int                $offset       Índice de inicio (negativo cuenta desde el final).
     * @param int|null           $length       Cantidad a eliminar; `null` elimina hasta el final.
     * @param array<mixed>|mixed $replacement  Valores de reemplazo; si no es array, se envuelve en uno.
     *
     * @return static Nueva instancia con los elementos eliminados.
     *
     * @example
     * ```php
     * $array = new class([1, 2, 3, 4, 5]) extends FastArray {};
     * $removed = $array->splide(1, 2, [8, 9]); // $array: [1, 8, 9, 4, 5]
     * $removed->get();                          // [2, 3]
     * ```
     */
    public function splide(int $offset, ?int $length = null, mixed $replacement = []): static {
        /** @var array<int, mixed> $removed */
        $removed = array_splice($this->data, $offset, $length, is_array($replacement) ? $replacement : [$replacement]);
        $this->length = count($this->data);

        return new static($removed);
    }

    /**
     * Devuelve un subarray sin modificar la instancia actual (`array_slice`).
     *
     * @param int      $offset        Índice inicial (negativo cuenta desde el final).
     * @param int|null $length        Cantidad de elementos; `null` hasta el final.
     * @param bool     $preserve_keys Conserva índices originales si es `true`.
     *
     * @return static Nueva instancia con la porción seleccionada.
     *
     * @example
     * ```php
     * $array = new class([10, 20, 30, 40, 50]) extends FastArray {};
     * $sub = $array->slice(1, 3);              // [20, 30, 40]
     * $array->get();                           // [10, 20, 30, 40, 50] sin cambios
     * ```
     */
    public function slice(int $offset, ?int $length = null, bool $preserve_keys = false): static {
        return new static(array_slice($this->data, $offset, $length, $preserve_keys));
    }

    /**
     * Alias de {@see get()}: devuelve el array interno completo.
     *
     * @return array<int, mixed>
     */
    public function to_array(): array {
        return $this->data;
    }
}