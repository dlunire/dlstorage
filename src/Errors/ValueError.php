<?php

namespace DLStorage\Errors;

use RuntimeException;

/**
 * Clase personalizada de excepción para errores de valor.
 *
 * Se lanza cuando un valor proporcionado no cumple con las condiciones requeridas
 * por una operación específica dentro del sistema DLStorage.
 *
 * Forma parte del núcleo de manejo de errores del framework DLUnire.
 *
 * @version v0.1.0
 * @author David E Luna M <dlunireframework@gmail.com>
 * @license MIT
 * @copyright 2025 David E Luna M
 * @package DLStorage
 * @project Códigos del Futuro
 * @organization DLUnire Framework
 */
final class ValueError extends RuntimeException {
}
