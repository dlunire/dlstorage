# 04 — Clase `Storage`

`DLStorage\Storage\Storage` es el punto de entrada recomendado. Encapsula ruta, entropía y delegación a `SaveData`.

## Constructor

```php
use DLStorage\Storage\Storage;

$storage = new Storage(
    filename: 'usuarios',
    entropy:  'MiLlaveDeTransformacion'
);

// Entropía omitida → suma base 0 en encode/decode
$storage = new Storage('documento');
```

| Parámetro | Descripción |
|-----------|-------------|
| `filename` | Ruta relativa **sin** `.dlstorage` |
| `entropy` | Llave de transformación MTB; debe repetirse al leer |

Al construir, `Storage` normaliza `/` → `DIRECTORY_SEPARATOR` byte a byte.

## `generate()` — escribir

```php
$storage->generate(
    content: json_encode(['id' => 1, 'nombre' => 'Ana']),
    storage: true
);
```

Equivalente interno a `SaveData::save_data($filename, $data, $entropy, $storage)`.

Crea directorios padre con `mkdir(..., 0755, true)` si `storage: true` y la ruta incluye subcarpetas.

## `readfile()` — leer

```php
$contenido = $storage->readfile(storage: true);
```

Devuelve el **texto original** (bytes de un solo carácter), no el hex intermedio.

La entropía del constructor debe ser la misma que al generar; si no, `EncodeException` (código 403) o contenido corrupto.

## Rutas con subdirectorios

```php
$storage = new Storage('backup/2026/credenciales', 'llave');
$storage->generate($payload, storage: false);
// → {raíz}/backup/2026/credenciales.dlstorage
```

## Metadatos del formato

```php
$firma = $storage->get_current_signature();
$version = $storage->get_current_version();
```

Útil para comprobar compatibilidad entre versiones del formato en migraciones.

## Uso directo de `SaveData`

Si necesitas pasar `filename` y `entropy` por llamada sin fijarlos en el constructor:

```php
use DLStorage\Storage\SaveData;

final class Vault extends SaveData {}

$vault = new Vault();
$vault->save_data('database', $json, $entropy, storage: false);
$plain = $vault->read_storage_data('database', $entropy, storage: false);
```

`EncryptedCredentials` en DLCore extiende este patrón ([10-integracion-dlcore.md](10-integracion-dlcore.md)).

## Lectura cruda sin decodificar

`get_file_content()` devuelve bytes del archivo **sin** validar firma ni aplicar MTB inverso:

```php
$raw = $vault->get_file_content('database.dlstorage', storage: false);
```

Solo para inspección o herramientas que parsean la cabecera manualmente.

## Ejemplo completo

```php
<?php
declare(strict_types=1);

use DLStorage\Storage\Storage;

require 'vendor/autoload.php';

$entropy = 'Entorno de programación';

$storage = new Storage('config/app', $entropy);

$storage->generate(<<<ENV
APP_NAME=Mi API
DB_HOST=127.0.0.1
DB_PASSWORD=no_visible_en_texto_plano
ENV
);

echo $storage->readfile();
```

## Errores frecuentes

| Síntoma | Causa | Solución |
|---------|-------|----------|
| Archivo no existe (404) | Ruta o flag `storage` distinto | Misma convención que en `generate()` |
| Contenido basura al leer | Entropía distinta | Usa la misma cadena en constructor |
| Directorio no creado | `storage: false` sin padre | Crea carpetas o usa `storage: true` |
| Permiso denegado (500) | `storage/` no escribible | `chmod` / propietario del usuario PHP |

## Siguiente paso

Cómo la llave de entropía deriva `sum` y `coefficient` en [05-entropia.md](05-entropia.md).