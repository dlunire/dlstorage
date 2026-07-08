# 05 — Entropía y llave de transformación

En DLStorage, **entropía** designa la **llave de transformación**: una cadena que deriva parámetros numéricos (`sum`, `coefficient`) usados en cada byte del MTB. No es entropía criptográfica del payload ni un IV aleatorio por archivo.

## Contrato básico

```php
$storage = new Storage('datos', entropy: 'MiLlaveSecreta');
$storage->generate($contenido);

// Misma llave obligatoria
$storage2 = new Storage('datos', entropy: 'MiLlaveSecreta');
echo $storage2->readfile();
```

| Escritura | Lectura | Resultado |
|-----------|---------|-----------|
| `entropy: 'A'` | `entropy: 'A'` | OK |
| `entropy: 'A'` | `entropy: 'B'` | `EncodeException` o datos inválidos |
| `entropy: null` | `entropy: null` | OK (suma base 0) |

## Derivación: `get_entropy_value()`

`BinaryLengthTrait::get_entropy_value(string $input)`:

1. Descompone la cadena en bytes (`unpack("C*", …)`).
2. Suma todos los valores → `$sum_bytes`.
3. Calcula `$this->coefficient` con `calculate_coefficient($sum_bytes)`.
4. Retorna `$sum_bytes + strlen($input)`.

Ese entero es el **`sum`** usado en `to_hex40()` / `from_hex40()` para cada posición.

```php
// Pseudoflujo
$sum = get_entropy_value($entropy_key);
$desplazamiento_en_byte_i = 2 * get_circular_value($i) + $sum;
```

`get_circular_value()` acota el resultado en **[17, 116]** mediante aritmética modular.

## Coeficiente

```php
coefficient = max(1, abs(intval((37 * seed + 113) % 0xffffffffffff)))
```

Donde `seed` es la suma de bytes de la llave (no el valor retornado por `get_entropy_value`).

El coeficiente afecta el desplazamiento por índice y dispersa la transformación entre posiciones.

## Seed fijo del MTB

`BinaryLengthTrait::$seed = 530000` se suma en cada `to_hex40()` y se resta en `from_hex40()`. Es constante del modelo, no configurable por aplicación.

## Entropía desde archivo

`get_entropy_file(string $filename)` lee hasta 16 MiB del archivo y devuelve `get_entropy_value($content)`, o `0` si no existe. Útil para métricas internas; no sustituye una llave definida por el operador.

## Separación llave / contenedor (DLUnire)

En producción con DLCore, el patrón recomendado es:

```
Llave (entropía)     →  $HOME/.dlunire/{FILE_PATH}{dominio}/{hash}
Contenedor MTB       →  {proyecto}/{FILE_PATH}/database.dlstorage
```

La llave **no** viaja dentro del `.dlstorage`. Detalle en [10-integracion-dlcore.md](10-integracion-dlcore.md).

## Buenas prácticas

1. **Longitud y aleatoriedad**: usa cadenas largas y no predecibles como llave de transformación en instalaciones manuales.
2. **No versionar la llave** en git junto al `.dlstorage`.
3. **Rotación**: para cambiar llave, decodifica con la antigua, reescribe con la nueva.
4. **No confundir** con `random_bytes()` del payload: el MTB no genera la llave automáticamente en `Storage` simple.

## Errores frecuentes

| Síntoma | Causa | Solución |
|---------|-------|----------|
| 403 entropía inválida | Llave incorrecta | Verifica cadena exacta (espacios, UTF-8) |
| Mismo archivo, distinto entorno | `$HOME` distinto en DLCore | Migra entropía y contenedor juntos |
| `sum = 0` inesperado | `entropy: null` | Pasa llave explícita si la necesitas |

## Siguiente paso

Recorrido byte a byte en `encode()` en [06-pipeline-codificacion.md](06-pipeline-codificacion.md).