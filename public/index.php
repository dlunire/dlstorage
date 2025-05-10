<?php

use DLStorage\Storage\SaveData;

include dirname(__DIR__) . "/vendor/autoload.php";

/** @var SaveData $storage */
$storage = new SaveData();

// $foto = "/home/david/Imágenes/Fotos/X/Nai/image.jpg";
$foto = "/home/david/Imágenes/Fotos/X/Nai/2.jpg";
$video = "/home/david/Vídeos/Película/videoplayback.mp4";

$readme = $storage->get_file_path('README.md');
// $content = file_get_contents($video);
// $content = $storage->get_entropy_file($video);


// $entropy = $content;
$entropy = "Test";
$payload = "Esta es una prueba que estoy realizando con esto";

// // $content = "entropía";
// $payload = file_get_contents($foto);
// // $payload = "ciencias de datos | computación";

if (file_exists($readme)) {
    $payload = file_get_contents($readme);
}

// $payload = file_get_contents($readme);

// $storage->save_data('ciencia', $payload, $entropy);

// header("content-type: image/jpg");
echo $storage->read_storage_data('ciencia', $entropy);
