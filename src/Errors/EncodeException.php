<?php

namespace DLStorage\Errors;

use RuntimeException;
use Throwable;

/**
 * Excepción lanzada cuando un valor proporcionado no cumple con las condiciones
 * requeridas por una operación específica dentro del sistema DLStorage.
 *
 * Esta clase forma parte del núcleo de manejo de errores del framework DLUnire.
 *
 * @package DLStorage
 * @project Códigos del Futuro
 * @organization DLUnire Framework
 * @author David E Luna M <dlunireframework@gmail.com>
 * @copyright Copyright (c) 2025 David E Luna M
 * @license MIT License
 * @version v0.1.0
 */
final class EncodeException extends RuntimeException {
    /**
     * Constructor personalizado para StorageException.
     *
     * @param string         $message  Mensaje descriptivo del error.
     * @param int            $code     Código de error (por defecto 500).
     * @param Throwable|null $previous Excepción anterior, si la hay.
     */
    public function __construct(string $message, int $code = 500, ?Throwable $previous = null) {
        parent::__construct($message, $code, $previous);
    }
}
