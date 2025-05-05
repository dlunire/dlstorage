<?php

use DLStorage\Storage\SaveData;

include dirname(__DIR__) . "/vendor/autoload.php";

/** @var SaveData $storage */
$storage = new SaveData();

// $foto = "/home/david/Imágenes/Fotos/X/Nai/image.jpg";
$foto = "/home/david/Imágenes/Fotos/X/Nai/2.jpg";

$readme = $storage->get_file_path('README.md');
$content = file_get_contents($foto);
// $content = "entropía";
$payload = file_get_contents($readme);
// $payload = "ciencias de datos | computación";

$storage->save_data('ciencia', $payload, $content);

echo $storage->read_storage_data('ciencia', $content);
