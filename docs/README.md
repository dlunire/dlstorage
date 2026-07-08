# Documentación DLStorage

## Tutorial de uso (recomendado)

Guía progresiva en español: [tutorial/README.md](tutorial/README.md)

DLStorage implementa el **Modelo de Transformación de Bytes (MTB)**: convierte datos sensibles (configuración, credenciales) desde texto plano hacia un **formato binario dependiente de entropía**. No es ofuscación ni sustituto de AES/ChaCha20.

| Capítulo | Tema |
|----------|------|
| 1 | [Inicio rápido](tutorial/01-inicio-rapido.md) |
| 2 | [Modelo de Transformación de Bytes (MTB)](tutorial/02-modelo-transformacion-bytes.md) |
| 3 | [Formato binario `.dlstorage`](tutorial/03-formato-binario.md) |
| 4 | [Clase `Storage`](tutorial/04-clase-storage.md) |
| 5 | [Entropía y llave de transformación](tutorial/05-entropia.md) |
| 6 | [Pipeline de codificación](tutorial/06-pipeline-codificacion.md) |
| 7 | [Decodificación y validación](tutorial/07-decodificacion-validacion.md) |
| 8 | [Rutas, `storage/` y permisos](tutorial/08-rutas-permisos.md) |
| 9 | [Excepciones y diagnóstico](tutorial/09-errores-diagnostico.md) |
| 10 | [Integración con DLCore](tutorial/10-integracion-dlcore.md) |
| 11 | [Operación segura y límites del MTB](tutorial/11-operacion-segura.md) |

---

## Referencia por módulo

| Tema | Archivo |
|------|---------|
| Uso de `Storage` | [doc/Storage.md](../doc/Storage.md) |
| Contexto MTB (README) | [README.md](../README.md) |
| API generada (phpDocumentor) | [docs/api/index.html](api/index.html) |
| PoC de auditoría interna | [poc/decode.php](../poc/decode.php) |

## Ecosistema DLUnire

| Capa | Paquete | Tutorial |
|------|---------|----------|
| Transformación de bytes | `dlunire/dlstorage` | Este tutorial |
| Kernel (vault de credenciales) | `dlunire/dlcore` | [Cap. 13 — Credenciales cifradas](https://github.com/dlunire/dlcore/blob/master/docs/tutorial/13-credenciales-cifradas.md) |