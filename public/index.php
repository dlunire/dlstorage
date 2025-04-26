<?php

use DLStorage\Storage\DataStorage;

include dirname(__DIR__) . "/vendor/autoload.php";

// Zona de prueba
final class Test extends DataStorage {
}

$data = new Test();

header("Content-Type: text/plain; charset=UTF-8");


$value = $data->encode('Entorno de programación', 'Una buena entropía que puede ser utilizada');
echo $data->get_decode($value, 'Una buena entropía que puede ser utilizada') . "\n";
