# Guía de Uso de `FastArray`

`FastArray` es una clase avanzada de manipulación de arrays dentro de **DLStorage**. Proporciona métodos seguros, iteradores y operaciones inspiradas en arrays de alto nivel como en JavaScript, manteniendo compatibilidad con PHP moderno.

Sin embargo, esta guía todavía no se encuentra actualizado del todo, pero proporciona los usos más comunes de `FastArray`.

---

### 1. Creación de un `FastArray`

```php
use DLStorage\Utilities\FastArray;

$array = new class([1, 2, 3, 4, 5]) extends FastArray {};
```

> Importante: `FastArray` es abstracta, por lo que se debe instanciar mediante herencia anónima o una clase concreta que la extienda.

---

### 2. Agregar elementos

```php
$array->push(6);            // Agrega al final
$array->add([7, 8, 9]);     // Agrega múltiples elementos
```

---

### 3. Acceso a elementos

```php
$first = $array->first();   // 1
$last = $array->last();     // 9
$item = $array->item(2);    // 3
```

> Los métodos `first()`, `last()` e `item()` lanzan `\OutOfBoundsException` si el índice no existe o el array está vacío.

---

### 4. Eliminación de elementos

```php
$removedLast = $array->pop();   // Elimina y devuelve el último
$removedFirst = $array->shift(); // Elimina y devuelve el primero
```

---

### 5. Extracción de subarrays

```php
// Extrae sin modificar el array original
$subArray = $array->slice(1, 3); // Elementos [2, 3, 4]

// Extrae y reemplaza elementos, devuelve eliminados
$removed = $array->splide(1, 2, [10, 11]); 
// $array ahora: [1, 10, 11, 4, 5]
// $removed contiene: [2, 3]
```

---

### 6. Longitud y array crudo

```php
$count = $array->length();   // Número de elementos actuales
$raw = $array->to_array();   // Array nativo PHP
```

---

### 7. Iteración

```php
foreach ($array as $value) {
    echo $value . PHP_EOL;
}

// También se puede usar get_iterator() directamente
$iterator = $array->get_iterator();
while ($iterator->valid()) {
    echo $iterator->current() . PHP_EOL;
    $iterator->next();
}
```

---

### 8. Encadenamiento de métodos

```php
$removed = $array
    ->slice(0, 3)
    ->splide(1, 1, [99])
    ->to_array();

print_r($removed); // Array resultante de operaciones encadenadas
```

---

### 9. Notas y buenas prácticas

* Manipular siempre arrays mediante métodos de `FastArray` para mantener la consistencia de la propiedad `length`.
* Las operaciones `splide()` y `slice()` devuelven **nuevas instancias**, por lo que se pueden encadenar sin modificar el array original (excepto `splide()`, que modifica el array base al eliminar elementos).
* Evita acceder directamente a `$data` fuera de la clase; usa `to_array()` si necesitas el array crudo.