# Changelog

Todos los cambios importantes de este proyecto serán documentados en este archivo.

---

## [0.2.1] - 2026-07-06

### Added
- Infraestructura de documentación API con phpDocumentor: `phpdoc.xml`, targets `docs` y `docs-clean` en el `Makefile`, y salida en `docs/api/`.
- Plantilla personalizada `.phpdoc/template/` para la documentación HTML, con diseño inspirado en Vite y selector de tema claro/oscuro.
- Dependencia de desarrollo `phpdocumentor/phpdocumentor`.

### Changed
- Actualización integral de bloques PHPDoc (`/** ... */`) en todo el código fuente (`src/`), con descripciones alineadas al comportamiento real de cada clase, método y propiedad.
- Cabeceras de licencia AGPL unificadas en los archivos PHP del paquete.
- Se añade `declare(strict_types=1)` en `Data.php`.

### Fixed
- Target `docs` del `Makefile` declarado como `.PHONY` para evitar conflicto con el directorio `docs/` y garantizar la regeneración de la documentación.

---

## [0.2.0] - 2026-07-05

### BREAKING CHANGES
* **Licencia**: Se cambió la licencia del paquete de `MIT` a **`AGPL-3.0-or-later`**, como parte del modelo de licenciamiento dual del ecosistema DLUnire. Nota: al ser un paquete `0.x`, este cambio se refleja como incremento de versión menor conforme a Semantic Versioning, no de versión mayor. Ver `LICENSE` y `LICENSING.md` en el repositorio principal (`dlunire/dlunire`) para el detalle completo. El campo `license` en `composer.json` fue actualizado en consecuencia.

---

## [v0.1.3] - 2025-10-29
- Actualizaciones

## [v0.1.2] - 2025-08-17
### Added
- Se agrega la clase `FastArray` como colección avanzada de arrays:
  - Métodos actuales: `push`, `pop`, `shift`, `clear`, `get`, `length`, `add`, `item`, `first`, `last`, `splide`, `slice`, `to_array`, `get_iterator`, `getIterator`.
  - Compatibilidad con iteración directa gracias a `IteratorAggregate`.
  - Métodos planeados para futuras versiones: `filter`, `map`, `reduce`, `unique`, `shuffle`, `concat`, `join`, `contains`, `keys`, `values`, `indexOf`, `includes`.
- Documentación técnica inicial incluida para `FastArray` y ejemplos de uso.

## [v0.1.1] - 2025-08-10
### Added
- Se agregan parámetros opcionales para la clase de almacenamiento que permiten elegir si el archivo se guarda en el directorio `storage` o directamente en el directorio raíz del proyecto.
- Mejora en la flexibilidad del sistema de rutas para lectura/escritura de archivos binarios.

## [v0.1.0] - 2025-04-19
### Added
- Implementación básica de la biblioteca DLStorage.
- Soporte para PSR-4 autoloading.
- Configuración inicial de Composer para gestión de dependencias y autoload.
- Creación de la estructura de directorios en `src/` para la futura expansión.
- Registro en Packagist para facilitar la instalación a través de Composer.
- Licencia MIT incluida.
