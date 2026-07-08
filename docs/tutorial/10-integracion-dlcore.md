# 10 — Integración con DLCore

En DLUnire, DLStorage no suele usarse aislado: **`DLCore\Auth\EncryptedCredentials`** une el MTB con entropía persistente en `$HOME` y variables `FILE_PATH`, `DATABASE`, `MULTITENANT` en `.env.type`.

> Tutorial DLCore completo del vault: [13-credenciales-cifradas.md](https://github.com/dlunire/dlcore/blob/master/docs/tutorial/13-credenciales-cifradas.md).

## Dos piezas separadas

```
┌──────────────────────────────────────────────────────────┐
│  Llave de transformación (entropía)                       │
│  $HOME/.dlunire/{FILE_PATH}{dominio}/{sha256(basename)}  │
│  EntropyValue::get_key_entropy()                         │
└──────────────────────────────────────────────────────────┘
                            │
                            ▼ misma cadena en encode/decode
┌──────────────────────────────────────────────────────────┐
│  Contenedor .dlstorage (payload MTB)                      │
│  {raíz}/{FILE_PATH}/database.dlstorage                   │
│  EncryptedCredentials → SaveData                         │
└──────────────────────────────────────────────────────────┘
```

La llave **no** se almacena dentro del `.dlstorage`.

## Variables `.env.type`

```envtype
FILE_PATH: string = "/credentials"
DATABASE: boolean = true
MULTITENANT: boolean = true
```

| Variable | Rol |
|----------|-----|
| `FILE_PATH` | Directorio lógico para contenedor y ruta de entropía |
| `DATABASE` | Habilita flujo de instalación de credenciales BD |
| `MULTITENANT` | **En desarrollo** en DLCore — pendiente de DLParse; ver [cap. 13 DLCore](https://github.com/dlunire/dlcore/blob/master/docs/tutorial/13-credenciales-cifradas.md) |

## Guardar credenciales

```php
use DLCore\Auth\EncryptedCredentials;

$vault = new EncryptedCredentials();
$entropy = $vault->get_key_entropy('file_path');

$payload = json_encode([
    'host'     => '127.0.0.1',
    'port'     => 3306,
    'database' => 'mi_app',
    'username' => 'app',
    'password' => 'secreto',
]);

$vault->save_data(
    filename: 'database',
    data: $payload,
    entropy: $entropy,
    storage: false
);
```

`EncryptedCredentials` extiende `DLStorage\Storage\SaveData` y usa el trait `EntropyValue`.

## Leer credenciales

```php
$entropy = $vault->get_key_entropy('file_path');

$json = $vault->read_storage_data(
    filename: 'database',
    entropy: $entropy,
    storage: false
);

$config = json_decode($json, true);
```

## Cuándo usar `.env.type` vs MTB

| Enfoque | Ideal para |
|---------|------------|
| `.env.type` | Dev, CI, secretos en Vault/K8s |
| `.dlstorage` + `$HOME` | Instalación guiada, operador configura una vez |

La mayoría de proyectos bastan con `.env.type` ([tutorial DLCore — cap. 02](https://github.com/dlunire/dlcore/blob/master/docs/tutorial/02-variables-entorno.md)).

## Dependencia Composer

```json
{
    "require": {
        "dlunire/dlcore": "^2.0"
    }
}
```

`dlcore` declara `dlunire/dlstorage` como dependencia transitiva.

## Bootloader

Con `DATABASE: true`, el skeleton puede mostrar un asistente que persiste credenciales vía `EncryptedCredentials` en el primer arranque. El contenedor queda en el proyecto; la llave en el home del usuario PHP.

## Migración entre servidores

1. Copia el `.dlstorage`.
2. Copia o regenera la entropía en el `$HOME` destino con el mismo `FILE_PATH` y dominio.
3. Verifica permisos del usuario PHP en ambos entornos.

Sin la pieza en `$HOME`, el contenedor no decodifica.

## Siguiente paso

Límites del MTB, checklist de producción y relación con auditoría en [11-operacion-segura.md](11-operacion-segura.md).