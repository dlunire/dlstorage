# 09 — Excepciones y diagnóstico

DLStorage usa excepciones tipadas con códigos semánticos (404, 403, 416, 500) para distinguir fallos de ruta, formato y transformación MTB.

## `StorageException`

**Namespace:** `DLStorage\Errors\StorageException`  
**Base:** `RuntimeException`

| Código | Situación típica |
|--------|------------------|
| 404 | Archivo no encontrado |
| 416 | Rango de lectura fuera del tamaño |
| 500 | Firma inválida, permisos, mkdir fallido, lectura vacía |

Ejemplos de mensajes:

```text
El archivo «database.dlstorage» no existe en la ruta indicada.
El archivo «x.dlstorage» no es un archivo DLStorage.
Error al crear el archivo. Asegúrese de establecer los permisos de escritura
```

### Captura en aplicación

```php
use DLStorage\Errors\StorageException;
use DLStorage\Storage\Storage;

try {
    $data = (new Storage('config', $entropy))->readfile();
} catch (StorageException $e) {
    if ($e->getCode() === 404) {
        // primer arranque: generar contenedor
    }
    throw $e;
}
```

## `EncodeException`

**Namespace:** `DLStorage\Errors\EncodeException`

Lanzada por `Data::get_decode()` cuando la longitud del resultado no es par — casi siempre **entropía incorrecta** o payload corrupto.

```php
use DLStorage\Errors\EncodeException;

try {
    $plain = $storage->readfile();
} catch (EncodeException $e) {
    // código 403 — revisar llave de transformación
}
```

## Árbol de decisión

```
readfile() falla
    ├── StorageException 404 → ruta o storage flag
    ├── StorageException 500 + firma → archivo no es .dlstorage válido
    └── EncodeException 403 → entropía o corrupción del payload
```

## Checklist de diagnóstico

1. ¿Existe el archivo en la ruta esperada (`ls storage/`)?
2. ¿Los primeros 9 bytes son `DLStorage` (`xxd -l 9`)?
3. ¿Misma `entropy` en constructor que al generar?
4. ¿Mismo parámetro `storage` en `generate()` y `readfile()`?
5. ¿Permisos de lectura para el usuario PHP?

## Herramientas

| Herramienta | Uso |
|-------------|-----|
| `xxd` / `hexdump` | Inspeccionar cabecera |
| `get_current_signature()` | Comparar firma esperada |
| `validate_saved_data()` | Test rápido de firma |
| `poc/decode.php` | Auditoría interna del formato (no producción) |
| phpDocumentor `docs/api/` | Referencia de métodos |

## Logs en DLUnire

Combina con logs de DLCore ([tutorial — logs](https://github.com/dlunire/dlcore/blob/master/docs/tutorial/16-logs-avanzados.md)) sin volcar entropía ni payload decodificado en producción.

## Errores frecuentes

| Síntoma | Causa | Solución |
|---------|-------|----------|
| 500 «no es DLStorage» | Archivo editado o corrupto | Regenera con `generate()` |
| 403 sistemático | Llave mal copiada | Compara bytes de la cadena entropía |
| 404 intermitente | Ruta multitenant distinta | Alinea `FILE_PATH` y host |
| Excepción en mkdir | Padre es archivo | Renombra conflicto |

## Siguiente paso

`EncryptedCredentials`, `EntropyValue` y bootloader en [10-integracion-dlcore.md](10-integracion-dlcore.md).