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
     * Codifica el archivo
     *
     * @param string $source Archivo fuente
     * @param string $target_file Archivo de destino
     * @return void
     */
    public function file_encode(string $source, string $target_file): void {
        $this->save_data($source, $this->get_file_content($target_file), $this->entropy);
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
}

$storage = new Storage();
// $storage->print('test.mp3', 'audio/mp3');

$storage->entropy = $storage->get_file_content('test.mp3');
$storage->entropy = "test";
$storage->file_encode('dibujo', 'dibujo.pdf');
$storage->file_decode('dibujo', 'application/pdf');
