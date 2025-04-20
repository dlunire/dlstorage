<?php

use DLStorage\Storage\DataStorage;

include dirname(__DIR__) . "/vendor/autoload.php";

// Zona de prueba
final class Test extends DataStorage {
}

$data = new Test();
