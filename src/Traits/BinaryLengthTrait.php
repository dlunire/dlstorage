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

namespace DLStorage\Traits;

use DLStorage\Errors\StorageException;

/**
 * Transformación numérica de bytes, entropía y bloques hexadecimales de 40 bits.
 *
 * Complementa {@see StorageTrait} con operaciones de codificación (`to_hex40`),
 * decodificación (`from_hex40`), cálculo de coeficientes y métricas de entropía
 * heurísticas sobre cadenas y archivos binarios.
 *
 * @package   DLStorage\Traits
 * @version   v0.2.0
 * @license   AGPL-3.0-or-later
 * @author    David E. Luna M. <info@dlunire.dev>
 * @copyright Copyright (c) 2026 David E. Luna M.
 *
 * @see \DLStorage\Storage\Data Clase base que incorpora este trait.
 */
trait BinaryLengthTrait {

    use StorageTrait;

    /** @var int $coefficient */
    public int $coefficient = 0;

    /** @var int $entropy_value  */
    protected int $entropy_value = 0;


    /**
     * Semilla base utilizada para cálculos internos relacionados con transformaciones numéricas.
     *
     * Esta propiedad representa un valor entero inicial (semilla) que puede emplearse como punto
     * de partida para algoritmos de transformación, generación pseudoaleatoria, o alteraciones
     * matemáticas controladas dentro del contexto del sistema.
     *
     * El valor predeterminado es `530000`, y su uso específico dependerá de la implementación
     * interna del algoritmo. Puede influir en procesos como desplazamientos, cálculos con entropía,
     * o generación de claves derivadas.
     *
     * @var int
     */
    protected int $seed = 530000;


    /**
     * Calcula la suma aritmética de todos los bytes de una cadena binaria.
     *
     * Descompone `$input` con `unpack("C*", ...)` y devuelve `array_sum()` de los
     * valores resultantes. No devuelve la longitud en bytes; para eso use `strlen()`.
     *
     * @param string $input Cadena binaria de entrada.
     *
     * @return int Suma de los valores byte a byte (0–255 por carácter).
     *
     * @example
     * ```php
     * $suma = $this->get_binary_length($contenido);
     * ```
     */
    public function get_binary_length(string $input): int {
        /** @var array<int,int> $bytes */
        $bytes = unpack("C*", $input);

        return array_sum($bytes);
    }

    /**
     * Convierte un byte a su representación hexadecimal de 40 bits, con posibilidad de aplicar entropía.
     *
     * Toma un valor entero correspondiente a un byte (entre 0 y 255) y lo transforma en una
     * cadena hexadecimal de 40 bits (10 dígitos hexadecimales). Puede aplicar un desplazamiento adicional
     * mediante un valor entero de entropía.
     *
     * ⚠️ **No valida** que el byte esté en el rango 0–255. Se asume que el valor ha sido validado previamente.
     *
     * ### Ejemplo de uso:
     * ```php
     * $hex = $this->to_hex40(0x41);            // Devuelve "0000000000000041"
     * $hex = $this->to_hex40(0x41, 1);         // Devuelve "0000000000000042"
     * $hex = $this->to_hex40(ord('A'), 2);     // Devuelve "0000000000000043"
     * ```
     *
     * @param int $byte     Valor entero a convertir (debe representar un byte).
     * @param int $entropy  [opcional] Valor adicional a sumar al byte antes de convertir. Por defecto es 0.
     *
     * @return string Representación hexadecimal de 40 bits del valor resultante (siempre 10 caracteres hexadecimales).
     */
    protected function to_hex40(int $byte, int $entropy = 0): string {
        /** @var int $value */
        $value = $byte + $entropy + $this->seed;

        // Asegurar 40 bits = 10 dígitos hexadecimales
        return str_pad(
            string: dechex($value),
            length: 10,
            pad_string: '0',
            pad_type: STR_PAD_LEFT
        );
    }

    /**
     * Obtiene el valor hexadecimal de un bloque de bytes con entropía revertida.
     *
     * Este método toma un bloque de bytes representado como una cadena hexadecimal y calcula su valor
     * después de revertir la entropía aplicada previamente. El valor de cada carácter se obtiene utilizando 
     * un cálculo que involucra un valor de entropía, el cual se determina a partir del índice del bloque y un 
     * valor base (`$sum`). Este valor se ajusta a un formato hexadecimal de dos dígitos para su posterior uso.
     *
     * El cálculo del valor final se basa en la resta de la entropía y un valor predeterminado, asegurando que 
     * la cadena resultante sea correctamente ajustada en longitud para representar un valor hexadecimal válido.
     *
     * @param string $block Bloque de bytes representado en formato hexadecimal, que será procesado para 
     *                      obtener el valor ajustado.
     * @param integer $key Índice que permite calcular el valor de la entropía, utilizado para el ajuste 
     *                     del valor hexadecimal.
     * @param integer $sum Valor base de la entropía que se usará en el cálculo de ajuste del valor final.
     *
     * @return string El valor hexadecimal calculado, con la entropía revertida, representado como una cadena 
     *                de dos dígitos.
     *
     * @example Ejemplo
     * ```
     * // Obtener el valor hexadecimal de un bloque con entropía revertida.
     * $block = "abc123";
     * $key = 1;
     * $sum = 10;
     * $hex_value = $data->get_hex_value($block, $key, $sum);
     * echo $hex_value;  // Resultado de la cadena hexadecimal ajustada.
     * ```
     */
    protected function from_hex40(string $block, int $key, int $sum): string {
        /** @var int $entropy_value */
        $entropy = $this->get_entropy($key, $sum);

        /** @var int $block_value */
        $block_value = hexdec($block);

        /** @var string $value */
        $value = dechex($block_value - ($this->seed + $entropy));

        return str_pad(
            string: $value,
            length: 2,
            pad_string: '0',
            pad_type: STR_PAD_LEFT
        );
    }

    /**
     * Calcula un valor de entropía basado en un índice y una suma acumulada.
     *
     * Este método genera un valor de entropía combinando la suma provista con dos llamadas al método
     * `get_circular_value($index)`. La operación está diseñada para producir una variación controlada
     * del valor original (`$sum`) a partir de datos circulares o predefinidos almacenados internamente.
     *
     * ⚠️ Este método **no garantiza entropía criptográfica**. Su propósito está orientado a sistemas
     * de transformación interna o dispersión de valores, no a funciones criptográficamente seguras.
     *
     * ### Fórmula general:
     * ```
     * entropy = sum + get_circular_value(index) + get_circular_value(index)
     * ```
     *
     * @param int $index Índice base usado para obtener valores circulares predefinidos.
     * @param int $sum   Suma acumulativa o valor base sobre el cual se aplicará el incremento de entropía.
     *
     * @return int Valor de entropía ajustado según el índice y suma proporcionados.
     *
     * @see self::get_circular_value()
     */
    protected function get_entropy(int $index, int $sum): int {
        return 2 * ($this->get_circular_value($index)) + $sum;
    }


    /**
     * Calcula y acumula un valor de entropía basado en los caracteres de una cadena dada.
     *
     * Recorre cada carácter de la cadena de entropía proporcionada, convirtiéndolo
     * a su representación hexadecimal (mediante `bin2hex()`), luego a decimal (con `hexdec()`),
     * y lo suma a un valor acumulado `$sum`, junto con un incremento progresivo según la posición.
     *
     * La suma resultante refleja una entropía combinada entre el valor binario de cada carácter
     * y su posición relativa. Esto permite generar un valor final que varía incluso si los caracteres
     * son iguales pero cambian de orden, contribuyendo así a una mayor dispersión numérica.
     *
     * Si no se proporciona una cadena válida, el valor de `$sum` no se modifica.
     *
     * @param int &$sum Valor acumulado que se actualizará con la contribución de cada carácter.
     * @param string|null $entropy Cadena base usada para calcular la entropía acumulada.
     *
     * @return void
     *
     * @example Ejemplo de uso
     * ```php
     * $sum = 0;
     * $entropy = "Una llave de entropía por acá";
     * $data->set_entropy_value($sum, $entropy);
     * echo $sum; // Muestra el valor acumulado de entropía.
     * ```
     */
    protected function set_entropy_value(int &$sum, ?string $entropy = null): void {
        if (!is_string($entropy)) return;
        $sum = $this->get_entropy_value($entropy);
    }

    /**
     * Calcula un valor circular a partir de un valor numérico dado.
     *
     * Este método aplica una transformación matemática al valor de entrada mediante la fórmula:
     * `($this->coefficient * valor + 17) % 100 + 17`. Esto garantiza que el valor resultante esté en un rango
     * específico y controlado, útil para generar patrones cíclicos o distribuciones uniformes en un espacio
     * acotado. La constante `17` actúa como desplazamiento para evitar valores demasiado bajos.
     *
     * Se lanza una excepción si el valor excede el límite de seguridad definido por `0xffffffffffffff`,
     * protegiendo así contra entradas numéricas excesivamente grandes que puedan afectar el sistema.
     *
     * @param int|float $value Valor numérico que será transformado mediante la operación circular.
     * @return int Valor transformado dentro del rango [17, 116].
     *
     * @throws StorageException Si el valor supera el límite seguro.
     *
     * @example For example
     * ```php
     * // Ejemplo de uso del valor circular
     * $resultado = $objeto->get_circular_value(5);
     * echo $resultado; // Un número entero entre 17 y 116
     * ```
     */
    private function get_circular_value(int|float $value): int {
        /** @var int|float $max_value */
        $max_value = 0xffffffffffffff;

        if ($value > $max_value) {
            throw new StorageException("El valor proporcionado excede el límite máximo permitido ({$max_value}) y representa un riesgo para la integridad del sistema.", 500);
        }

        return abs(($this->coefficient * $value + 17) % 100 + 17);
    }


    /**
     * Calcula una métrica de "entropía" simplificada basada en el contenido de un archivo.
     *
     * Esta función intenta abrir un archivo y leer hasta un máximo de 16,777,215 bytes (0xFFFFFF).
     * Si el archivo no se encuentra en la ruta original, se intenta localizar usando `get_file_path`.
     * Una vez leído el contenido, se utiliza `unpack("C*", ...)` para obtener los valores
     * byte a byte. Luego, la entropía es calculada como la suma de estos bytes más
     * la longitud del contenido leído.
     *
     * Nota: Esta no es una medida de entropía criptográfica, sino una métrica heurística
     * del contenido binario del archivo.
     *
     * @param string $filename Ruta del archivo a analizar.
     * @return int Suma de los bytes del archivo y su longitud.
     */
    public function get_entropy_file(string $filename): int {
        /** @var int $value Valor máximo de lectura en bytes (0xFFFFFFFF = 4GB) */
        $value = 0xffffff;

        /** @var string $file Ruta final del archivo a analizar */
        $file = $filename;

        if (!file_exists($filename)) {
            $file = $this->get_file_path($filename);
        }

        if (!file_exists($file)) {
            return 0;
        }

        /** @var int $size Tamaño total del archivo */
        $size = filesize($file);

        /** @var string $content Contenido del archivo leído hasta el máximo permitido */
        $content = $this->read_filename($file, 1, $size > $value ? $value : $size);

        return $this->get_entropy_value($content);
    }

    /**
     * Devuelve la entropía base o `seed` en función de los bytes como argumento.
     *
     * Esta implementación no calcula entropía estadística (como la fórmula de Shannon),
     * sino un valor entero simple, derivado de la suma total de los bytes más la longitud
     * del input. Es útil como valor base determinista para sistemas de validación,
     * generación de semillas pseudoaleatorias u otras heurísticas.
     *
     * @param string $input Secuencia binaria que representa los datos a analizar.
     * @return int Valor entero representando una entropía base aproximada.
     */
    public function get_entropy_value(string $input): int {
        /** @var array<int,int> $bytes */
        $bytes = unpack("C*", $input);

        /** @var int $sum */
        $sum = array_sum($bytes);

        $this->calculate_coefficient($sum);

        return $sum + strlen($input);
    }

    /**
     * Calcula y asigna un coeficiente seguro a partir de una semilla numérica.
     *
     * Este método genera un coeficiente acotado mediante una operación modular y lo asigna
     * internamente a la propiedad `$this->coefficient`. El valor obtenido se asegura de estar dentro
     * del rango seguro definido por [1, 1_000_000], evitando así valores nulos o excesivos que puedan
     * comprometer operaciones posteriores.
     *
     * La fórmula utilizada es: `abs((semilla * 37 + 113) % max_coefficient)`, con un ajuste final
     * para garantizar un valor mínimo de 1.
     *
     * @param int|float $seed Valor semilla utilizado para derivar el coeficiente de forma determinista.
     * @return void
     */
    private function calculate_coefficient(int|float $seed): void {
        /** @var int $min_coefficient */
        $min_coefficient = 1;

        /** @var int $max_coefficient */
        $max_coefficient = 0xffffffffffff;

        /** @var int|float $coefficient */
        $coefficient = abs(intval((37 * $seed + 113) % $max_coefficient));

        $this->coefficient = max($coefficient, $min_coefficient);
    }
}
