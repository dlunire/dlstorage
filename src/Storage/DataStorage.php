<?php

declare(strict_types=1);

namespace DLStorage\Storage;

use DLStorage\Traits\StorageTrait;

/**
 * Define una base para almacenar datos en archivos binarios u otros medios persistentes,
 * sin utilizar una base de datos.
 * 
 * En su lugar, puede generarse un token de referencia que pueda ser almacenado en una
 * base de datos si es necesario.
 *
 * @package    DLStorage
 * @version    v0.1.0
 * @license    MIT
 * @author     David E Luna M <dlunireframework@gmail.com>
 * @copyright  2025 Códigos del Futuro, DLUnire Framework
 * 
 * @abstract
 */
abstract class DataStorage extends Data {
    use StorageTrait;
}
