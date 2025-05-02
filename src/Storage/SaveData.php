<?php

declare(strict_types=1);

namespace DLStorage\Storage;

/**
 * Permite guardar y recuperar datos binarios utilizando el sistema de almacenamiento gestionado,
 * sin necesidad de una implementación personalizada.
 *
 * Internamente aplica validaciones de integridad, control de versión, firma de datos
 * y manejo de directorios. Ideal para escenarios donde se necesita una solución lista
 * para uso directo en producción.
 *
 * Compatible con el sistema de transformación de bytes y validación automática del archivo.
 * Soporta escritura protegida, lectura estructurada y verificación de la huella binaria.
 *
 * @version v0.1.0
 * @package DLStorage\Storage
 * @license MIT
 * @author David E Luna M
 * @copyright 2025 David E Luna
 *
 * @see DataStorage Define los métodos y estructuras comunes de bajo nivel.
 * @see StorageException Maneja los errores específicos del almacenamiento binario.
 *
 * @example Guardar datos binarios
 * ```php
 * use DLStorage\Storage\SaveData;
 *
 * $storage = new SaveData();
 * $storage->save_binary_data("respaldo/config.bin", $contenido);
 * ```
 *
 * @example Leer datos previamente guardados
 * ```php
 * $contenido = $storage->read_filename("respaldo/config.bin");
 * ```
 *
 * @note Recomendado para entornos donde no se desea utilizar bases de datos,
 * pero se requiere persistencia confiable con control de integridad.
 */
final class SaveData extends DataStorage {

    /**
     * Guarda la información en formato binario
     *
     * @param string $filename Nombre de archivo a ser creado
     * @param string $data Datos crudos a ser transformados byte por byte
     * @param string|null $entropy Llave de entropía
     * @return void
     */
    public function save_data(string $filename, string $data, ?string $entropy = NULL): void {

        /** @var string $encode */
        $encode = $this->encode($data, $entropy);

        /** @var string $file */
        $file = $this->get_file_path($filename, true);

        $signature = $this->signature;
        $version = $this->version;

        /** @var string $new_data */
        $new_data = "{$this->signature}{$this->version}{$encode}";
    }
}
