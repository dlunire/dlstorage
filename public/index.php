<?php

use DLStorage\Storage\DataStorage;

include dirname(__DIR__) . "/vendor/autoload.php";

// Zona de prueba
final class Test extends DataStorage {
}

$data = new Test();

// header("Content-Type: image/bmp; charset=UTF-8");
// header("Content-Type: application/pdf; charset=UTF-8");
// header("Content-Type: text/html; charset=UTF-8");
header("Content-Type: application/json; charset=UTF-8");


$entropy = "Una buena entropía que puede ser utilizada";

// var_dump($content_image);
$value = $data->encode('Ciencias de la computación', 'Una buena entropía que puede ser utilizada');
// echo $value;
// echo $data->get_decode($value, 'Una buena entropía que puede ser utilizada') . "\n";
$new_value = $data->get_content($value, 'Una buena entropía que puede ser utilizada');

print_r($new_value);
