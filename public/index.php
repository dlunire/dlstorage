<?php

/**
 * IMPORTANTE:
 * 
 * Cuando corras esta aplicación como prueba, asegúrate que los archivos a los que apuntes
 * existan en la ruta seleccionada.
 * 
 * En esta prueba se asume que el archivo seleccionado existe en el directorio `storage`. Para esta
 * prueba (porque no aplica en producción) te recomiendo crear el directorio `storage` y copiar los archivos 
 * allí o simplemente, modifique la clase Storage para apuntar a archivos fuera de `storage`.
 * 
 * Este archivo no se debe utilizar para implementarlo en tu proyecto. Debe ser utilizado solo para ejecución de
 * pruebas de codificación.
 */

declare(strict_types=1);

include dirname(__DIR__) . "/vendor/autoload.php";

use DLStorage\Storage\SaveData;

/**
 * Clase utilizada para probar la codificación y decodificación
 */
final class Storage extends SaveData {
    public string $entropy = "Llave de entropía";

    /**
     * Devuelve el contenido en pantalla
     *
     * @return void
     */
    public function print(string $filename, string $mimetype = "text/plain"): void {
        $content = $this->get_file_content($filename);
        header("content-type: {$mimetype}", true, 200);

        echo $content;
        exit;
    }

    /**
     * Codifica el contenido de un archivo origen y lo almacena en un archivo destino.
     *
     * El archivo origen es leído desde el directorio `storage` (por defecto) o desde la raíz
     * del proyecto, según el valor del parámetro `$storage`. Su contenido se obtiene mediante
     * {@see get_file_content()}, y posteriormente se procesa a través del método interno
     * {@see save_data()} aplicando la entropía definida en la clase.
     *
     * El resultado de la codificación se guarda en el archivo destino especificado en `$target_file`.
     *
     * @method void file_encode(string $target_file, string $source, bool $storage = true)
     *
     * @param string $target_file Ruta relativa del archivo destino donde se almacenará el resultado
     *                            de la codificación.
     *
     * @param string $source Nombre del archivo origen cuyo contenido será codificado.
     *                       Puede ubicarse en el directorio raíz o en el subdirectorio `storage`,
     *                       dependiendo del parámetro `$storage`.
     *
     * @param bool $storage Indica si `$source` se encuentra en el directorio `storage`.  
     *                      Por defecto es `true`. Si es `false`, se buscará directamente en la raíz
     *                      del proyecto.
     *
     * @return void No devuelve valor; el resultado de la codificación se escribe en `$target_file`.
     *
     * @throws StorageException Si el archivo origen (`$source`) no existe o no puede accederse.
     *
     * @example
     * ```php
     * // Codificar un archivo de origen ubicado en `storage` y guardar el resultado en otro destino:
     * $storage->file_encode('encoded/source.dat', 'credentials/token.dlstorage');
     *
     * // Codificar un archivo de origen desde la raíz del proyecto:
     * $storage->file_encode('encoded/config.sec', 'config/app.php', false);
     * ```
     *
     * @internal Este método depende de:
     *  - {@see get_file_content()} para obtener el contenido del archivo origen.
     *  - {@see save_data()} para procesar y guardar el contenido codificado.
     *  - La propiedad `$entropy` definida en la clase para reforzar el proceso de codificación.
     */
    public function file_encode(string $target_file, string $source, bool $storage = true): void {
        $this->save_data(
            filename: $target_file,
            data: $this->get_file_content($source, $storage),
            entropy: $this->entropy
        );
    }

    /**
     * Decofica el archivo
     *
     * @param string $filename Archivo binario a ser leído y decodificado.
     * @param string $mimetype Formato de archivo 
     * @return void
     */
    public function file_decode(string $filename, string $mimetype = "text/plain"): void {
        $content = $this->read_storage_data($filename, $this->entropy);
        header("content-type: {$mimetype}", true, 200);
        print_r($content);
        exit;
    }

    /**
     * Muestra el contenido codificado
     *
     * @param string $content Contenido codificado
     * @return void
     */
    public function show_encoded_text(string $content): void {
        /** @var string $encoded */
        $encoded = $this->encode($content, $this->entropy);

        /** @var string $original */
        $original = bin2hex($content);

        header("content-type: text/plain", true, 200);
        print_r("\$encoded: {$encoded}");
        echo "\n";
        print_r("\$original: {$original}");

        exit;
    }
}

$storage = new Storage();
// $storage->print('test.mp3', 'audio/mp3');

// $storage->entropy = $storage->get_file_content('test.mp3');

$storage->entropy = "Esta es una prueba que éstoy realizando.";
// $storage->show_encoded_text("AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA");
$storage->file_encode('dibujo', 'dibujo.pdf');
$storage->file_decode('dibujo', 'application/pdf');
