# 11 — Operación segura y límites del MTB

Este capítulo cierra el tutorial con prácticas de despliegue y una evaluación honesta del **Modelo de Transformación de Bytes**: qué aporta en producción y dónde termina su alcance.

## Checklist de producción

| Ítem | Acción |
|------|--------|
| Contenedor fuera de HTTP | `.dlstorage` no bajo `public/` |
| Llave separada | Entropía en `$HOME` (DLCore) o HSM/gestor externo |
| Permisos mínimos | `750` en directorios sensibles; propietario PHP |
| Misma llave en R/W | Documenta la procedencia de la entropía |
| Backup cifrado del backup | Copias de `.dlstorage` + material de llave protegidos |
| No loguear secretos | Evita `readfile()` en logs de depuración |
| Rotación planificada | Re-encode con nueva llave si hay compromiso |

## Lo que el MTB aporta

- Elimina cadenas legibles tipo `DB_PASSWORD=` del archivo en disco.
- Formato identificable (`DLStorage`) con versión y validación.
- Transformación reversible **solo** con la llave correcta.
- Integración con bootloader DLUnire (modo multitenant en DLCore aún en desarrollo; pendiente de DLParse).

## Lo que el MTB no sustituye

| Necesidad | Usar además |
|-----------|-------------|
| Cifrado frente a adversario con root | Controles de SO, secret managers, cifrado estándar |
| Integridad autenticada (tamper-proof) | Firmas HMAC / AEAD en otra capa |
| Rotación automática de secretos | Proceso operativo o vault externo |
| Cumplimiento PCI/FIPS solo con MTB | Evaluación de compliance aparte |

El MTB reduce **exposición accidental**; no es demostración de seguridad criptográfica frente a análisis avanzado con acceso al algoritmo y muestras.

## MTB no es ofuscación

La ofuscación suele describir transformaciones ad hoc para dificultar lectura sin contrato. DLStorage publica:

- Firma y versión de formato.
- Pipeline encode/decode documentado.
- Excepciones tipadas y propósito de diseño explícito.

El término correcto en el ecosistema DLUnire es **Modelo de Transformación de Bytes**.

## Auditoría interna

`poc/decode.php` demuestra que el formato es reversible con conocimiento de la llave — herramienta para el **propietario** del código en pruebas de seguridad autorizadas. No despliegues el PoC en servidores públicos.

## Comparativa rápida

| | `.env` texto plano | `.dlstorage` MTB | AES-256-GCM |
|--|-------------------|------------------|-------------|
| Legible con `cat` | Sí | No | No |
| Requiere llave | No | Sí (entropía) | Sí |
| Formato estándar cripto | — | No (MTB propio) | Sí |
| Objetivo principal | Config simple | Menos exposición accidental | Confidencialidad fuerte |

## `FastArray` y utilidades

`DLStorage\Utilities\FastArray` es una colección auxiliar del paquete (push, pop, slice, iteración). No participa en el MTB; consulta `docs/api/` si la usas en código legacy.

## Documentación adicional

| Recurso | Enlace |
|---------|--------|
| Índice del tutorial | [README.md](README.md) |
| Referencia `Storage` | [doc/Storage.md](../../doc/Storage.md) |
| API phpDocumentor | [docs/api/](../../api/) |
| DLCore — credenciales | [13-credenciales-cifradas.md](https://github.com/dlunire/dlcore/blob/master/docs/tutorial/13-credenciales-cifradas.md) |
| CHANGELOG | [CHANGELOG.md](../../CHANGELOG.md) |

## Fin del tutorial

Con los **11 capítulos** cubres el MTB de DLStorage: propósito, formato binario, entropía, pipelines de codificación/decodificación, rutas, errores, integración DLCore y límites operativos.

Para cambios de versión del paquete, consulta [CHANGELOG.md](../../CHANGELOG.md). Para la API generada, ejecuta `make docs` en el repositorio (phpDocumentor).