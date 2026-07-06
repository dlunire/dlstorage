# Uso de `Storage`

La clase `Storage` constituye la interfaz principal de **DLStorage** para generar y recuperar archivos utilizando el formato binario del proyecto.

## Crear una instancia

Para comenzar, cree una instancia indicando el nombre del archivo.

```php
use DLStorage\Storage\Storage;

$storage = new Storage("documento");
```

También es posible suministrar una entropía personalizada.

```php
$storage = new Storage(
    filename: "documento",
    entropy: "Mi entropía personalizada"
);
```

> **Nota:** No es necesario especificar una extensión. DLStorage agrega automáticamente la extensión `.dlstorage` al archivo generado.

---

# Generar un archivo

El método `generate()` recibe el contenido que será almacenado.

```php
$storage->generate("Hola mundo");
```

Por defecto, el archivo será generado dentro del directorio:

```text
storage/
```

Por ejemplo, el código anterior producirá un archivo similar a:

```text
storage/documento.dlstorage
```

---

# Guardar fuera del directorio `storage`

Si desea guardar el archivo exactamente en la ruta indicada, establezca el segundo parámetro en `false`.

```php
$storage = new Storage("backup/documento");

$storage->generate(
    content: "Hola mundo",
    storage: false
);
```

En este caso, el archivo generado será:

```text
backup/documento.dlstorage
```

---

# Leer un archivo

Para recuperar el contenido almacenado utilice `readfile()`.

```php
$storage = new Storage("documento");

$contenido = $storage->readfile();

echo $contenido;
```

---

# Leer archivos fuera del directorio `storage`

Si el archivo no se encuentra dentro del directorio `storage`, indique el segundo parámetro.

```php
$storage = new Storage("backup/documento");

$contenido = $storage->readfile(
    storage: false
);
```

---

# Obtener la firma del formato

La biblioteca permite consultar la firma binaria del formato soportado.

```php
$firma = $storage->get_current_signature();
```

Este método devuelve la firma binaria correspondiente al formato actual de DLStorage y puede utilizarse para validaciones o comprobaciones de compatibilidad.

---

# Obtener la versión del formato

También es posible consultar la versión binaria del formato actual.

```php
$version = $storage->get_current_version();
```

Este valor resulta útil cuando una aplicación necesita comprobar la compatibilidad entre diferentes versiones del formato de almacenamiento.

---

# Ejemplo completo

```php
use DLStorage\Storage\Storage;

$storage = new Storage(
    filename: "usuarios",
    entropy: "MiClaveDeEntropia"
);

// Guardar información
$storage->generate(json_encode([
    "id" => 1,
    "nombre" => "David"
]));

// Recuperar información
$contenido = $storage->readfile();

echo $contenido;
```

El archivo generado será:

```text
storage/usuarios.dlstorage
```

---

# Notas

* No es necesario indicar la extensión del archivo; DLStorage agrega automáticamente `.dlstorage`.
* Las rutas son normalizadas automáticamente para el sistema operativo donde se ejecuta la aplicación.
* La misma entropía utilizada para generar un archivo debe utilizarse posteriormente para leerlo correctamente.
* Si no se especifica una entropía, DLStorage utilizará el comportamiento predeterminado de la biblioteca.
