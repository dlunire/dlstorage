<?php

namespace DLStorage\Errors;

use RuntimeException;

/**
 * Clase de excepción lanzada cuando un valor proporcionado es inválido
 * en el contexto de operaciones internas de DLStorage.
 *
 * Esta clase extiende de RuntimeException y se utiliza para representar
 * errores relacionados con valores fuera de rango, tipos no aceptados, 
 * o parámetros incorrectos en operaciones binarias personalizadas.
 *
 * Puede utilizarse para validar entradas en métodos que trabajen con estructuras 
 * binarias de bajo nivel, formatos personalizados, o procesos de serialización/deserialización.
 *
 * @example
 * throw new ValueError("El tamaño excede el límite permitido para 32 bits.");
 *
 * @version v0.0.1
 * @package DLStorage\Errors
 * @author David E Luna M
 * @license MIT
 * @copyright 2025 David E Luna M
 */
final class ValueError extends RuntimeException {
}
