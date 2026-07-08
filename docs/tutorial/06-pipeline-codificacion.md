# 06 — Pipeline de codificación

`Data::encode()` implementa el núcleo del MTB: cada byte de entrada se convierte en un bloque hexadecimal de **10 caracteres**, alterado por entropía y reglas de compactación.

## Entrada y salida

```php
// Uso interno vía SaveData::save_data()
$hex_payload = $this->encode($data, $entropy);
```

| Entrada | Salida |
|---------|--------|
| `string` binario (texto o bytes) | Cadena hex concatenada (múltiplos de 10 chars por byte) |

## Pasos por byte

Para cada byte en `$input` con índice `$index`:

```
1. sum ← get_entropy_value($entropy) o 0 si null
2. desplazamiento ← get_entropy($index, $sum)
3. bloque ← to_hex40($byte, desplazamiento)
       = str_pad(dechex($byte + desplazamiento + 530000), 10, '0', LEFT)
4. bloque ← str_replace('01', 'ffff', $bloque)
5. bloque ← compact_zero($bloque)   // ceros iniciales → marcador 01
6. acumular en buffer
```

`foreach_string()` usa `unpack("C*", $input)` — recorrido por **byte**, no por carácter Unicode multibyte.

## `to_hex40()` y `from_hex40()`

Par inverso del MTB:

| Método | Fórmula |
|--------|---------|
| `to_hex40($byte, $entropy)` | `dechex($byte + $entropy + seed)` → 10 chars |
| `from_hex40($block, $key, $sum)` | `dechex(hexdec($block) - (seed + get_entropy($key, $sum)))` → 2 chars |

## Marcador `01` y `ffff`

La secuencia `01` en un bloque es especial:

- En codificación: `01` dentro del bloque se reemplaza por `ffff` para evitar ambigüedad con el separador de compactación.
- En decodificación: `ffff` vuelve a `01` ([07-decodificacion-validacion.md](07-decodificacion-validacion.md)).

## `compact_zero()`

```php
preg_replace("/^0+/", '01', $bloque);
```

Los ceros iniciales del bloque hex se colapsan al marcador `01`, reduciendo tamaño del payload sin perder información reversible vía `expand_zero()`.

## Ejemplo conceptual

Un solo byte (ilustrativo, valores ficticios):

```
Byte entrada:     0x48  ('H')
Desplazamiento:   calculado por índice 0 + sum
Bloque 10 chars:  "0000812a4f"  (ejemplo)
Tras reglas 01/ffff y compact_zero → bloque final en payload
```

El payload completo es la concatenación de todos los bloques.

## UTF-8 y texto

El MTB opera sobre **bytes de la cadena PHP**. Texto UTF-8 multibyte se transforma byte a byte; no hay normalización Unicode previa. Para JSON de configuración, `json_encode()` con UTF-8 es el patrón habitual.

## Jerarquía de clases

```
Data::encode()
    ├── ForTrait::foreach_string()
    ├── BinaryLengthTrait::get_entropy(), to_hex40()
    └── compact_zero() por bloque
```

## Depuración

No expongas el hex intermedio en producción. En desarrollo, puedes extender `SaveData` temporalmente y registrar `strlen($encode)` tras `encode()` antes de `hex2bin()`.

## Errores frecuentes

| Síntoma | Causa | Solución |
|---------|-------|----------|
| Payload más grande que el original | Expansión 1 byte → 10 hex chars | Esperado; MTB no comprime |
| Caracteres corruptos tras round-trip | Entrada no byte-safe | Evita UTF-8 inválido en fuente |
| Valores distintos en otro SO | Mismo algoritmo, mismos bytes | Verifica `entropy` y contenido idéntico |

## Siguiente paso

`get_decode()`, `expand_zero()` y validación de longitud par en [07-decodificacion-validacion.md](07-decodificacion-validacion.md).