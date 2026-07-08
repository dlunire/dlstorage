# 02 — Modelo de Transformación de Bytes (MTB)

Antes de evaluar DLStorage como «seguridad», hay que entender **qué problema diseña resolver** el MTB. No es un algoritmo criptográfico estándar ni un truco de ofuscación sin contrato.

## Qué es el MTB

El **Modelo de Transformación de Bytes** es un pipeline determinista que:

1. Recorre la entrada **byte a byte** (`ForTrait::foreach_string()`).
2. Aplica un **desplazamiento numérico** derivado de la posición del byte y una **llave de entropía** (cadena).
3. Expande cada byte en un **bloque hexadecimal de 10 caracteres**.
4. Compacta y marca secuencias especiales (`01` → `ffff`, ceros iniciales).
5. Empaqueta el resultado en un contenedor binario con firma `DLStorage`.

La operación es **reversible** cuando se conoce la misma entropía y el formato. Eso no la define como ofuscación opaca: tiene estructura documentada, cabecera versionada y validación de firma.

## Qué no es

| Etiqueta incorrecta | Por qué no aplica |
|---------------------|-------------------|
| **Ofuscación** | Implica ocultar sin contrato formal; el MTB expone firma, versión y pipeline reproducible |
| **AES / ChaCha20** | No busca indistinguibilidad bajo ataques criptográficos formales |
| **Hash irreversible** | La lectura recupera el texto original con la entropía correcta |
| **Entropía de Shannon** | `get_entropy_value()` es una heurística interna (suma de bytes + coeficiente), no RNG del payload |

## Problema que resuelve

En PHP, Node y otros entornos, las credenciales suelen vivir en `.env` legible:

```text
DB_HOST=localhost
DB_DATABASE=mi_app
DB_USERNAME=deploy
DB_PASSWORD=secreto_visible
```

Si el archivo se expone por mala configuración del servidor, backup, vulnerabilidad o error humano, las cadenas quedan **inmediatamente** disponibles.

El MTB transforma esa información a **representación binaria dependiente de entropía**, eliminando las cadenas originales del archivo en disco. El diseño contempla además:

- Separar la **llave de transformación** del contenedor (en DLUnire: entropía en `$HOME`, payload en el proyecto).
- Preparar despliegues multitenant (objetivo DLCore; **en desarrollo**, pendiente de DLParse — [10-integracion-dlcore.md](10-integracion-dlcore.md)).

## Modelo de amenazas (diseño)

El MTB está pensado principalmente para:

| Amenaza | Mitigación del MTB |
|---------|-------------------|
| Exposición accidental de `.env` en el árbol web | Payload no legible como texto plano |
| Lectura casual con editor o `cat` | Sin cadenas `DB_PASSWORD=…` visibles |
| Inspección hex sin llave | Dificulta recuperación **inmediata** |
| Copia del `.dlstorage` sin la entropía | El contenedor solo no basta para leer |

## Lo que no pretende

No protege contra un adversario con:

- Acceso root al servidor y lectura de `$HOME/.dlunire/…` (donde vive la entropía en DLCore).
- Conocimiento de la llave de transformación.
- Análisis del algoritmo MTB con muestras suficientes (transformación reversible conocida).

Si necesitas resistencia frente a adversarios con acceso completo al SO o análisis criptográfico avanzado, combina MTB con **controles de infraestructura** (permisos, secret managers, TLS) y, si aplica, cifrado criptográfico estándar en otra capa.

## MTB frente a ofuscación (conceptual)

```
Ofuscación informal          MTB (DLStorage)
────────────────────         ────────────────────
Sin formato estándar    →    Firma + versión + tamaños
Sin validación          →    StorageException si firma inválida
Objetivo vago           →    Reducir exposición de config en texto plano
```

## Evaluación correcta

Preguntas útiles al auditar DLStorage:

1. ¿El `.dlstorage` está fuera del document root público?
2. ¿La entropía vive separada del payload (p. ej. `$HOME`)?
3. ¿Los permisos de lectura del usuario PHP son mínimos?
4. ¿Se usa la **misma** entropía en `generate()` y `readfile()`?

Preguntas **secundarias** (cripto formal): difusión, confusión, IND-CPA — observaciones válidas, pero no son el criterio principal de diseño.

## Jerarquía de implementación

```
Storage (API pública)
    └── SaveData      → cabecera binaria + file_put_contents
            └── DataStorage → StorageTrait (rutas, firma)
                    └── Data → encode() / get_decode()  ← núcleo MTB
```

## Siguiente paso

Estructura física del archivo `.dlstorage` en [03-formato-binario.md](03-formato-binario.md).