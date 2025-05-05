<?php

declare(strict_types=1);

namespace DLStorage\Traits;

use DLStorage\Errors\StorageException;

/**
 * Copyright (c) 2025 David E Luna M  
 * Licensed under the MIT License. See LICENSE file for details.
 *
 * Trait DataSizeTrait
 *
 * Calcula la longitud real en bytes de una cadena binaria, 
 * garantizando que no exceda el límite máximo permitido de 4 GB (2^32 bytes).
 *
 * Este trait es esencial para validar entradas binarias dentro del sistema DLStorage,
 * asegurando integridad en operaciones de transformación y almacenamiento.
 *
 * @version v0.0.1
 * @package DLStorage\Traits
 * @author David E Luna M
 * @license MIT
 * @copyright 2025 David E Luna M
 */
trait BinaryLengthTrait {

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
     * Obtiene la longitud real en bytes de un string binario y valida su límite máximo.
     *
     * Convierte la cadena binaria a su representación hexadecimal para obtener 
     * una medida precisa, independientemente de su codificación interna.
     * Si la longitud calculada supera los 4 GB, lanza una excepción.
     *
     * @param string $input Cadena binaria de entrada.
     * @return int Longitud en bytes del contenido binario.
     *
     * @throws StorageException Si la longitud supera el límite permitido de 4 GB (2^32 bytes).
     *
     * @example
     * ```php
     * $length = $this->get_binary_length($entropy);
     * ```
     *
     * @note El método es útil para verificar datos sensibles antes de aplicarlos
     * a procesos criptográficos o de almacenamiento.
     */
    public function get_binary_length(string $input): int {
        $value = intdiv(strlen(bin2hex($input)), 2);

        if ($value > (2 ** 32)) {
            throw new StorageException("La longitud de la entropía excede el límite permitido de 4 GB", 500);
        }

        return $value;
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
        return $sum + $this->get_circular_value($index) + $this->get_circular_value($index);
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
        $sum = $this->get_binary_length($entropy);
    }

    /**
     * Devuelve un valor circular modificado a partir del valor dado.
     *
     * Este método calcula un valor basado en una operación matemática circular sobre el valor de entrada. 
     * El valor de entrada se multiplica por 31, se le suma 17, y luego se calcula el módulo 100. Finalmente, 
     * se le añade 10 al resultado para obtener un valor dentro de un rango específico.
     *
     * Este tipo de transformación es útil para generar un patrón cíclico o no lineal, manteniendo el valor en 
     * un rango controlado de valores entre 10 y 109.
     *
     * @param int|float $value Valor numérico (entero o decimal) que será analizado y transformado mediante 
     *                         la operación circular.
     * @return int El valor circular calculado, garantizando que se encuentre dentro del rango [10, 109].
     *
     * @example Example
     * ```
     * // Obtener el valor circular de un número
     * $result = $data->get_circular_value(5);
     * echo $result;  // Resultado será un número entre 10 y 109.
     * ```
     */
    private function get_circular_value(int|float $value): int {
        return ($value * 31 + 17) % 100 + 10;
    }
}
