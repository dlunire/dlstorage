# DLStorage

**DLStorage** es una biblioteca desarrollada por **Códigos del Futuro** y **David E Luna M** como parte del ecosistema del **DLUnire Framework**. Su propósito es proporcionar una solución eficiente para el almacenamiento de datos binarios, tanto dentro como fuera del framework.

## 📌 Propósito

**DLStorage** permite almacenar, gestionar y recuperar datos binarios de forma eficiente. Es ideal para escenarios donde se necesita manipular archivos binarios como configuraciones, cachés u otros recursos que requieren persistencia de bajo nivel.

Aunque está optimizada para el framework **DLUnire**, puede usarse de forma completamente independiente en otros entornos PHP modernos.

## 🚀 Funcionalidades

* 🔒 **Almacenamiento binario estructurado**
* 🔀 **Compatibilidad directa con DLUnire Framework**
* 📈 **Diseño escalable y modular**
* 📂 **Lectura y escritura optimizada en archivos `.dlstorage`**

## 📦 Instalación

Puedes instalar **DLStorage** fácilmente usando **Composer**:

```bash
composer require dlunire/dlstorage
```

Asegúrate de tener configurado Composer en tu proyecto. El paquete descargará automáticamente todas las dependencias necesarias.

## ✅ Requisitos

* PHP 8.2 o superior
* Composer
* (Opcional) DLUnire Framework para integración directa

## 📚 Documentación

La documentación técnica de las clases principales está disponible en el directorio `doc/`:

* [DataStorage](doc/DataStorage.md): Documentación base del sistema de almacenamiento binario.
* [SaveData](doc/SaveData.md): Clase concreta para guardar y recuperar datos con control de cabecera.

También se agregarán más archivos conforme avance el desarrollo.

## 🛠️ Uso

> ⚠️ Este proyecto se encuentra en etapa inicial. Las interfaces pueden cambiar.
> En futuras versiones se incluirán ejemplos detallados y una guía completa de integración.

Por el momento, puedes revisar los archivos mencionados en la sección de documentación para ver las estructuras y firmas actuales.

## 🤝 Contribuciones

¡Tu participación es bienvenida! Puedes abrir un *pull request* o reportar un *issue* si encuentras errores o deseas proponer mejoras.

## 👤 Autor

Este proyecto ha sido creado por **David E Luna M**, fundador de **Códigos del Futuro** y autor del **DLUnire Framework**.

📧 Contacto: [dlunireframework@gmail.com](mailto:dlunireframework@gmail.com)

## 📄 Licencia

**DLStorage** está licenciado bajo la [MIT License](LICENSE).

---

## 📁 Estructura del Proyecto

```text
src/
├— Storage/       # Clases de almacenamiento principal
├— Interfaces/    # Interfaces para implementación extensible
doc/
├— DataStorage.md
├— SaveData.md
```

---

## FastArray

### 🗂️ Métodos actuales de FastArray

| Método                                                                 | Parámetros                                    | Modifica array | Retorno            | Descripción                                                                    |
| ---------------------------------------------------------------------- | --------------------------------------------- | -------------- | ------------------ | ------------------------------------------------------------------------------ |
| `__construct(array $data = [])`                                        | Array inicial opcional                        | Sí             | `void`             | Inicializa el array y su longitud.                                             |
| `push(mixed $value)`                                                   | Valor a insertar                              | Sí             | `void`             | Agrega un elemento al final.                                                   |
| `pop()`                                                                | —                                             | Sí             | `mixed`            | Elimina y devuelve el último elemento.                                         |
| `shift()`                                                              | —                                             | Sí             | `mixed`            | Elimina y devuelve el primer elemento.                                         |
| `clear()`                                                              | —                                             | Sí             | `void`             | Vacía el array y reinicia la longitud.                                         |
| `get()`                                                                | —                                             | No             | `array<int,mixed>` | Devuelve una copia del array interno.                                          |
| `length()`                                                             | —                                             | No             | `int`              | Devuelve la cantidad de elementos.                                             |
| `add(array $data)`                                                     | Array de elementos                            | Sí             | `void`             | Agrega múltiples elementos al final.                                           |
| `item(int $index)`                                                     | Índice a obtener                              | No             | `mixed`            | Devuelve un elemento por índice, lanza excepción si es inválido.               |
| `first()`                                                              | —                                             | No             | `mixed`            | Devuelve el primer elemento, lanza excepción si está vacío.                    |
| `last()`                                                               | —                                             | No             | `mixed`            | Devuelve el último elemento, lanza excepción si está vacío.                    |
| `splide(int $offset, ?int $length = null, mixed $replacement = [])`    | Offset, longitud opcional, reemplazo opcional | Sí             | `FastArray`        | Elimina/reemplaza elementos y devuelve los eliminados en un nuevo `FastArray`. |
| `slice(int $offset, ?int $length = null, bool $preserve_keys = false)` | Offset, longitud opcional, preserva índices   | No             | `FastArray`        | Devuelve una porción del array como un nuevo `FastArray`.                      |
| `to_array()`                                                           | —                                             | No             | `array<int,mixed>` | Devuelve el array interno crudo.                                               |
| `get_iterator()`                                                       | —                                             | No             | `\Traversable`     | Devuelve un iterador (`ArrayIterator`) del array interno.                      |
| `getIterator()`                                                        | —                                             | No             | `\Traversable`     | Implementación de `IteratorAggregate`, devuelve `get_iterator()`.              |

---

### 🔮 Métodos planeados para futuras versiones

* `filter(callable $callback): FastArray` – Filtra elementos según condición.
* `map(callable $callback): FastArray` – Aplica función a cada elemento.
* `reduce(callable $callback, mixed $initial = null): mixed` – Reduce a un único valor.
* `unique(): FastArray` – Elimina elementos duplicados.
* `shuffle(): FastArray` – Reordena elementos aleatoriamente.
* `concat(FastArray|array $other): FastArray` – Concatena otro array o FastArray.
* `join(string $glue = ','): string` – Devuelve string concatenado de los elementos.
* `contains(mixed $value): bool` – Verifica si existe un valor.
* `keys(): FastArray` – Devuelve los índices.
* `values(): FastArray` – Devuelve los valores.
* `indexOf(mixed $value): int|null` – Devuelve el índice de un valor, `null` si no existe.
* `includes(mixed $value): bool` – Retorna true si el valor está contenido.


## 📌 Notas Finales

* Pronto se incluirán más módulos como validadores, conversores y controladores de versión de datos.
* Si deseas soporte personalizado o tienes preguntas, contacta a través del correo del autor.
