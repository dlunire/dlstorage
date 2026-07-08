# Tutorial de uso — DLStorage

Guía progresiva para el **Modelo de Transformación de Bytes (MTB)** y el formato `.dlstorage` de DLUnire. Cada capítulo es independiente, pero se recomienda seguir el orden indicado.

| # | Tema | Archivo |
|---|------|---------|
| 1 | Inicio rápido | [01-inicio-rapido.md](01-inicio-rapido.md) |
| 2 | Modelo de Transformación de Bytes (MTB) | [02-modelo-transformacion-bytes.md](02-modelo-transformacion-bytes.md) |
| 3 | Formato binario `.dlstorage` | [03-formato-binario.md](03-formato-binario.md) |
| 4 | Clase `Storage` | [04-clase-storage.md](04-clase-storage.md) |
| 5 | Entropía y llave de transformación | [05-entropia.md](05-entropia.md) |
| 6 | Pipeline de codificación | [06-pipeline-codificacion.md](06-pipeline-codificacion.md) |
| 7 | Decodificación y validación | [07-decodificacion-validacion.md](07-decodificacion-validacion.md) |
| 8 | Rutas, `storage/` y permisos | [08-rutas-permisos.md](08-rutas-permisos.md) |
| 9 | Excepciones y diagnóstico | [09-errores-diagnostico.md](09-errores-diagnostico.md) |
| 10 | Integración con DLCore | [10-integracion-dlcore.md](10-integracion-dlcore.md) |
| 11 | Operación segura y límites del MTB | [11-operacion-segura.md](11-operacion-segura.md) |

## Terminología

| Término | Significado en DLStorage |
|---------|-------------------------|
| **MTB** | Modelo de Transformación de Bytes — transformación reversible byte a byte con entropía |
| **Entropía** | Llave de transformación (cadena) que deriva parámetros numéricos; no es Shannon ni RNG del payload |
| **Payload** | Cuerpo transformado del archivo `.dlstorage` |
| **Ofuscación** | **No** es el modelo de DLStorage; la transformación tiene contrato y propósito distinto (véase cap. 2) |

## Convención de nombres

En el ecosistema DLUnire se usa **snake_case** en métodos y variables de aplicación (`get_key_entropy`, `$file_path`). Las **clases** siguen PascalCase (`Storage`, `EncryptedCredentials`).

## Requisitos

- PHP **8.2+** (recomendado `declare(strict_types=1)`)
- Composer
- Opcional: [`dlunire/dlcore`](https://packagist.org/packages/dlunire/dlcore) para `EncryptedCredentials` y bootloader de credenciales

## Instalación

```bash
composer require dlunire/dlstorage
```

## Documentación de referencia

| Tema | Enlace |
|------|--------|
| README del paquete | [README.md](../../README.md) |
| Referencia de módulos | [docs/README.md](../README.md) |
| `Storage` (guía breve) | [doc/Storage.md](../../doc/Storage.md) |
| API phpDocumentor | [docs/api/](../api/) |
| DLCore — credenciales | [13-credenciales-cifradas.md](https://github.com/dlunire/dlcore/blob/master/docs/tutorial/13-credenciales-cifradas.md) |