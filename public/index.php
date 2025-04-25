<?php

use DLStorage\Storage\DataStorage;

include dirname(__DIR__) . "/vendor/autoload.php";

// Zona de prueba
final class Test extends DataStorage {
}

$data = new Test();

header("Content-Type: text/plain; charset=UTF-8");


$value = $data->encode('david', 'Una buena entropía que puede ser utilizada');

$reverse = $value;

echo "{$value}\n";
$data->decode($reverse);

echo "{$reverse}\n";
// echo hex2bin($value);

// echo gzcompress($value);

// 0000035bc40000035bfd0000035c510000035c820000035cb9
// 0000035bc40000035bfd0000035c510000035c820000035cb9