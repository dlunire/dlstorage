<?php

include dirname(__DIR__) . "/vendor/autoload.php";

use DLStorage\Storage\DataStorage;

# Zona de prueba
final class TuClase extends DataStorage {
}

/** @var TuClase $data */
$data = new TuClase();

// $data->test(2500, 2700);

/** @var string $decoded */
$decoded = $data->get_content('Tu hash a analizar aquí', 'Una buena entropía que puede ser utilizada');

// header("Content-Type: image/bmp; charset=UTF-8");
// header("Content-Type: application/pdf; charset=UTF-8");
// header("Content-Type: text/html; charset=UTF-8");
header("Content-Type: application/json; charset=UTF-8");


$entropy = "Una buena entropía que puede ser utilizada";

// var_dump($content_image);
$value = $data->encode('David Luna', $entropy);
echo $value . "\n";
// echo $data->get_decode($value, 'Una buena entropía que puede ser utilizada') . "\n";
$new_value = $data->get_content($value, $entropy);

print_r($new_value);

echo "\n";
echo $data->validate_saved_data('file.dlstorage');
