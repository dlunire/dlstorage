# 08 — Rutas, `storage/` y permisos

`StorageTrait` resuelve rutas absolutas desde el directorio raíz del proyecto y opcionalmente antepone `storage/`. Los permisos del SO determinan si `generate()` puede crear carpetas y escribir.

## `get_document_root()`

```php
$root = $storage->get_document_root();
// realpath(dirname(getcwd()))
```

Asume que el CWD está **un nivel debajo** de la raíz (típico: `public/`). Si `getcwd()` no refleja eso, las rutas fallarán.

### Fijar raíz explícitamente

En integraciones custom, ejecuta desde la raíz o ajusta el CWD antes de instanciar `Storage`. DLCore usa su propio `Path` para credenciales; DLStorage puro depende de `getcwd()`.

## `get_file_path()`

```php
// storage: true  → {root}/storage/{filename}
// storage: false → {root}/{filename}
```

Normaliza `\` y `/` al separador del sistema. Con `create_dir: true` (usado en `save_data`):

- Extrae el directorio padre del archivo.
- `mkdir($dir, 0755, true)` si no existe.
- Lanza `StorageException` si un **archivo** ocupa el nombre del directorio.

## Convenciones de despliegue

| Tipo de dato | `storage` | Ubicación típica |
|--------------|-----------|------------------|
| Config de app generada por MTB | `true` | `storage/config.dlstorage` |
| Credenciales DLCore | `false` | `{FILE_PATH}/database.dlstorage` |
| Backups locales | `false` | `backup/fecha/archivo.dlstorage` |

Mantén archivos `.dlstorage` **fuera** del document root público (`public/`).

## Permisos recomendados

```bash
chmod 750 storage
chown www-data:www-data storage
```

El usuario que ejecuta PHP (FPM, Apache) debe poder leer lo que necesita y escribir solo donde `generate()` persiste.

## Lectura parcial: `read_filename()`

```php
$bytes = $this->read_filename($ruta_absoluta, from: 1, to: 9);
```

Índices **1-based**, modo binario `rb`. Usado internamente para firma y tamaños. Lanza:

| Código | Motivo |
|--------|--------|
| 404 | Archivo inexistente (vía `validate_filename`) |
| 416 | Rango fuera del tamaño del archivo |
| 500 | Seek o lectura fallida |

## Validar sin decodificar

```php
$vault = new class extends \DLStorage\Storage\SaveData {};
$ok = $vault->validate_saved_data('config/credenciales');
```

Comprueba solo la firma de los primeros 9 bytes.

## Multitenant (DLCore — en desarrollo)

La ruta del **contenedor** puede vivir en el proyecto; la **entropía** incorpora el host normalizado en `$HOME/.dlunire/…`. El modo multitenant completo en DLCore **aún no está listo** (depende de DLParse). No mezcles contenedores de distintos dominios con la misma llave hasta que el flujo SaaS esté documentado ([10-integracion-dlcore.md](10-integracion-dlcore.md), [cap. 13 DLCore](https://github.com/dlunire/dlcore/blob/master/docs/tutorial/13-credenciales-cifradas.md)).

## Errores frecuentes

| Síntoma | Causa | Solución |
|---------|-------|----------|
| Error 500 al crear | Permisos o archivo bloquea directorio | Revisa propietario y nombre de ruta |
| Lee archivo distinto | `storage: true` vs `false` | Unifica el flag |
| Raíz vacía | `realpath` falló | Ajusta CWD o despliega desde raíz conocida |
| Archivo en `public/` | Ruta mal elegida | Mueve a `storage/` fuera de HTTP |

## Siguiente paso

`StorageException`, `EncodeException` y códigos semánticos en [09-errores-diagnostico.md](09-errores-diagnostico.md).