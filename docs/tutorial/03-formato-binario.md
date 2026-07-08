# 03 — Formato binario `.dlstorage`

Un archivo `.dlstorage` es un contenedor con cabecera tipada y payload transformado. Conocer el layout ayuda a depurar integraciones y a distinguir el MTB de un blob opaco.

## Layout físico

```
[ firma 9B ][ tamaño_cabecera 4B ][ versión NB ][ tamaño_payload 4B ][ payload MB ]
```

| Sección | Tamaño | Contenido por defecto |
|---------|--------|------------------------|
| Firma | 9 bytes ASCII | `DLStorage` |
| Tamaño cabecera | 4 bytes (uint32 BE en hex interno) | Longitud del campo versión |
| Versión | N bytes | `v0.1.0` (formato; biblioteca puede ser `v0.2.x`) |
| Tamaño payload | 4 bytes | Longitud del payload en bytes |
| Payload | M bytes | Resultado de `encode()` convertido con `hex2bin()` |

La versión en cabecera (`v0.1.0`) es la del **formato de archivo**, no necesariamente la versión del paquete Composer (`v0.2.1`).

## Lectura por offsets (1-based)

`SaveData::read_storage_data()` sigue este orden:

```
Bytes 1–9     → firma (debe coincidir con get_signature())
Bytes 10–13   → header_size (entero)
Bytes 14..    → versión (header_size bytes)
Siguientes 4  → payload_size
Resto         → payload hex → get_content() → texto original
```

## Firma y compatibilidad

```php
$storage = new Storage('test');
$firma_bin = $storage->get_current_signature();  // 9 bytes "DLStorage"
$version_bin = $storage->get_current_version();  // bytes de "v0.1.0"
```

Si la firma no coincide al leer, `StorageException`: *«no es un archivo DLStorage»* (código 500).

`StorageTrait::validate_saved_data()` comprueba solo los primeros 9 bytes.

## Payload: de hex a binario

Al **escribir**:

1. `Data::encode()` produce una cadena hexadecimal (bloques de 10 caracteres por byte de entrada).
2. Se calcula `payload_size` con `get_section_size()`.
3. Si la longitud hex es impar, `normalize_hex_payload()` antepone `0` y ajusta el tamaño.
4. Se concatena: `signature + header_size + version + payload_size + encode` (todo en hex).
5. `hex2bin()` convierte el archivo completo a binario en disco.

Al **leer**, `delete_padding()` colapsa ceros iniciales redundantes del payload hex antes de decodificar.

## Ejemplo conceptual (hex dump)

```
Offset  Contenido
──────  ─────────────────────────────
0x00    44 4c 53 74 6f 72 61 67 65   "DLStorage"
0x09    00 00 00 06                 tamaño versión = 6
0x0D    76 30 2e 31 2e 30           "v0.1.0"
0x13    00 00 12 34                 tamaño payload
0x17    …                           payload transformado
```

Los valores exactos dependen del contenido y la entropía.

## Extensión y rutas

- El nombre en disco siempre termina en `.dlstorage`.
- Rutas con `/` se normalizan al separador del SO en el constructor de `Storage`.
- Con `storage: true`, la ruta base es `{document_root}/storage/`.

## Validación rápida en terminal

```bash
xxd -l 32 storage/documento.dlstorage
# Debe comenzar con: 444c 5374 6f72 6167 65  ("DLStorage")
```

## Errores frecuentes

| Síntoma | Causa | Solución |
|---------|-------|----------|
| «No es un archivo DLStorage» | Archivo corrupto o no es `.dlstorage` | Regenera con `generate()` |
| Payload truncado | Copia parcial del archivo | Vuelve a copiar el binario completo |
| Versión ilegible | Herramienta editó bytes de cabecera | No editar manualmente; usar API |
| Tamaño impar al decodificar | Entropía distinta a la de escritura | Misma llave en lectura ([05-entropia.md](05-entropia.md)) |

## Siguiente paso

API de alto nivel con `Storage::generate()` y `readfile()` en [04-clase-storage.md](04-clase-storage.md).