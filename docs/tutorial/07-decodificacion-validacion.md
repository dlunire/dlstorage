# 07 — Decodificación y validación

La lectura invierte el pipeline de codificación: expandir marcadores, dividir bloques, revertir `from_hex40()` y validar integridad antes de devolver el texto original.

## Cadena de métodos

```
read_storage_data()
    └── extrae payload hex del archivo
    └── delete_padding()
    └── get_content($hex, $entropy)
            └── get_decode()
            └── hex2bin()
```

## `get_decode()`

```php
public function get_decode(string $encoded, ?string $entropy = null): string
```

Pasos:

1. **`expand_zero()`** — restaura bloques de 10 caracteres desde marcadores `01` y `ffff`.
2. **`str_split($encoded, 10)`** — un bloque por byte original.
3. **`get_reverse_entropy()`** — `from_hex40()` por cada bloque con índice y `sum`.
4. **Validación** — `mb_strlen($value, 'UTF-8')` debe ser **par**; si no, `EncodeException` (403).

El mensaje 403 indica llave incorrecta o datos corruptos:

```text
Es posible que la llave de la entropía sea inválida o los datos se hayan corrompido.
```

## `expand_zero()`

Complemento de `compact_zero()`:

```
explode por "01"
    └── descartar segmentos vacíos
    └── str_replace "ffff" → "01"
    └── str_pad cada bloque a 10 chars con ceros a la izquierda
    └── implode
```

## `get_content()`

Atajo que aplica `hex2bin()` sobre el resultado de `get_decode()`:

```php
$original = $this->get_content($payload_hex, $entropy);
```

Es lo que `read_storage_data()` devuelve al llamador.

## Validación de firma (antes del MTB)

La decodificación MTB solo ocurre si:

1. El archivo existe.
2. Los bytes 1–9 coinciden con `DLStorage`.

Si falla la firma → `StorageException` sin intentar `get_decode()`.

## Integridad práctica

| Comprobación | Dónde |
|--------------|-------|
| Archivo presente | `file_exists` en `read_storage_data` |
| Firma `DLStorage` | bytes 1–9 |
| Longitud par post-decode | `get_decode()` |
| Rango de lectura | `read_filename()` con offsets 1-based |

## Round-trip de prueba

```php
$entropy = 'llave_de_prueba';
$original = '{"ok":true,"n":42}';

$storage = new Storage('roundtrip/test', $entropy);
$storage->generate($original);

$decoded = (new Storage('roundtrip/test', $entropy))->readfile();

assert($decoded === $original);
```

## PoC de auditoría

El repositorio incluye `poc/decode.php` para análisis de seguridad **autorizado** del formato. Documenta la estructura y la reversibilidad del MTB cuando se conoce la llave. No sustituye la API de producción; úsalo solo en entornos de auditoría propios ([11-operacion-segura.md](11-operacion-segura.md)).

## Errores frecuentes

| Síntoma | Causa | Solución |
|---------|-------|----------|
| EncodeException 403 | Entropía distinta | Misma llave que en `generate()` |
| Bloques de longitud ≠ 10 | Payload manualmente editado | Regenera el archivo |
| `hex2bin` falla | Hex impar tras decode | Indica corrupción o llave errónea |
| Texto cortado | Archivo truncado | Verifica `payload_size` en cabecera |

## Siguiente paso

Resolución de rutas y directorio `storage/` en [08-rutas-permisos.md](08-rutas-permisos.md).