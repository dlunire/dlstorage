# Changelog

Todos los cambios importantes de este proyecto serán documentados en este archivo.

## [Unreleased]
### Added
- N/A

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
