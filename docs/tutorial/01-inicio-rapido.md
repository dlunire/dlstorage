# 01 — Inicio rápido

DLStorage persiste datos en archivos **`.dlstorage`**: un contenedor binario con firma, versión y payload transformado mediante el **Modelo de Transformación de Bytes (MTB)**. La misma entropía usada al escribir es necesaria para leer.

> DLStorage **no** es ofuscación ni cifrado AES. El MTB transforma bytes con una llave para reducir exposición accidental de credenciales en texto plano ([02-modelo-transformacion-bytes.md](02-modelo-transformacion-bytes.md)).

## Instalación

```bash
composer require dlunire/dlstorage
```

## Ejemplo mínimo

```php
<?php
declare(strict_types=1);

use DLStorage\Storage\Storage;

require dirname(__DIR__) . '/vendor/autoload.php';

$storage = new Storage(
    filename: 'config/credenciales',
    entropy:  'MiLlaveDeTransformacion'
);

$storage->generate(json_encode([
    'db_host' => '127.0.0.1',
    'db_user' => 'app',
]));

echo $storage->readfile();
```

Resultado en disco:

```text
storage/config/credenciales.dlstorage
```

El contenido **no** es legible como `.env`; al abrirlo en un editor hex o texto verás la firma `DLStorage` y bytes transformados.

## Punto de entrada recomendado

| Clase | Uso |
|-------|-----|
| `DLStorage\Storage\Storage` | API pública: `generate()`, `readfile()` |
| `DLStorage\Storage\SaveData` | Persistencia binaria (herencia interna) |
| `DLStorage\Storage\Data` | Codificación/decodificación MTB |

En aplicaciones DLUnire, `DLCore\Auth\EncryptedCredentials` envuelve `SaveData` y gestiona la entropía en `$HOME` ([10-integracion-dlcore.md](10-integracion-dlcore.md)).

## Flujo resumido

```
Texto plano (JSON, .env serializado, etc.)
    └── Data::encode($content, $entropy)
            └── bloques hex de 10 chars por byte
    └── SaveData::save_data()
            └── cabecera binaria + hex2bin(payload)
    └── archivo .dlstorage en disco

Lectura inversa con la misma $entropy
    └── read_storage_data() → get_content() → texto original
```

## Parámetro `storage`

| Valor | Ruta resultante |
|-------|-----------------|
| `true` (defecto) | `{raíz_proyecto}/storage/{filename}.dlstorage` |
| `false` | `{raíz_proyecto}/{filename}.dlstorage` |

```php
$storage->generate($data, storage: true);   // storage/documento.dlstorage
$storage->generate($data, storage: false);  // backup/documento.dlstorage
```

## Sin extensión manual

No añadas `.dlstorage` al constructor; la biblioteca la agrega al escribir y leer.

## Relación con DLCore

| Escenario | Enfoque |
|-----------|---------|
| Desarrollo, CI, secretos en gestor externo | `.env.type` en DLCore |
| Instalación guiada, credenciales fuera de texto plano | `.dlstorage` + entropía en `$HOME` |

## Siguiente paso

Propósito del MTB, modelo de amenazas y qué **no** pretende resolver en [02-modelo-transformacion-bytes.md](02-modelo-transformacion-bytes.md).