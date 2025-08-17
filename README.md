# DLStorage

**DLStorage** es una biblioteca desarrollada por **Códigos del Futuro** y **David E Luna M** como parte del ecosistema del **DLUnire Framework**. Su objetivo principal es ofrecer una solución eficiente para el almacenamiento y gestión de datos binarios, tanto dentro como fuera del framework.

---

## Guía de uso

### Índice de contenido

1. [DataStorage](./doc/DataStorage.md "Define una base para almacenar datos transformados")
2. [SaveData](./doc/SaveData.md "La clase `SaveData` extiende `DataStorage` y proporciona una solución lista para usar")
3. [FastArray](./doc/FastArray.md "")

## 📌 Propósito

**DLStorage** permite almacenar, manipular y recuperar datos binarios de forma segura y eficiente. Está especialmente diseñada para escenarios donde se requieren operaciones sobre archivos binarios, como configuraciones, cachés u otros recursos que necesitan persistencia de bajo nivel.

Aunque está optimizada para el framework **DLUnire**, **DLStorage** puede utilizarse de manera independiente en cualquier proyecto PHP moderno.

---

## 🚀 Funcionalidades

* 🔒 **Almacenamiento binario estructurado**: gestión eficiente de datos binarios.
* 🔀 **Compatibilidad directa con DLUnire Framework**.
* 📈 **Diseño escalable y modular**, ideal para proyectos de distintos tamaños.
* 📂 **Lectura y escritura optimizada en archivos `.dlstorage`**.

---

## 📦 Instalación

Instalación mediante **Composer**:

```bash
composer require dlunire/dlstorage
```

> Composer se encargará de descargar automáticamente todas las dependencias necesarias.

---

## ✅ Requisitos

* PHP 8.2 o superior
* Composer
* (Opcional) DLUnire Framework para integración directa

---

## 📚 Documentación

La documentación técnica de las clases principales está disponible en el directorio `doc/`:

* [DataStorage](doc/DataStorage.md) – Documentación base del sistema de almacenamiento binario.
* [SaveData](doc/SaveData.md) – Clase concreta para guardar y recuperar datos con control de cabecera.

> Nuevos archivos y módulos serán añadidos conforme avance el desarrollo.

---

## 🛠️ Uso

> ⚠️ Este proyecto se encuentra en etapa inicial. Las interfaces y métodos pueden cambiar en futuras versiones.

Actualmente, se recomienda revisar los archivos de documentación para entender la estructura y firma de las clases.

---

## 🤝 Contribuciones

Se agradece cualquier contribución. Puedes:

* Abrir un *pull request*.
* Reportar un *issue* para errores o sugerencias.
* Proponer mejoras o nuevas funcionalidades.

---

## 👤 Autor

**David E Luna M** – Fundador de **Códigos del Futuro** y autor del **DLUnire Framework**.

📧 Contacto: [dlunireframework@gmail.com](mailto:dlunireframework@gmail.com)

---

## 📄 Licencia

**DLStorage** está licenciado bajo la [MIT License](LICENSE).

---

## 📁 Estructura del Proyecto

```text
src/
├─ Storage/       # Clases de almacenamiento principal
├─ Interfaces/    # Interfaces para implementación extensible
doc/
├─ DataStorage.md
├─ SaveData.md
```

---

## FastArray

`FastArray` es una clase abstracta de **DLStorage** que proporciona una interfaz avanzada para manipulación de arrays, integrando iteradores, acceso seguro y métodos inspirados en estructuras de alto nivel.

---

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
* `includes(mixed $value): bool` – Retorna `true` si el valor está contenido.

---

## 📌 Notas Finales

* Próximamente se incluirán módulos adicionales como validadores, conversores y controladores de versión de datos.
* Para soporte personalizado o consultas, contactar al autor vía correo electrónico.
