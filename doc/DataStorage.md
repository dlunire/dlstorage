# `DataStorage`

---

## Introducción

La clase abstracta `DataStorage` define una base para almacenar datos transformados en archivos binarios u otros medios persistentes, evitando el uso de bases de datos tradicionales. Su objetivo principal es proteger credenciales sensibles, como tokens, claves o estructuras cifradas mediante transformaciones personalizadas.

Esta clase extiende a `Data` y forma parte del paquete `DLStorage`. Está diseñada para ser utilizada como clase base para implementaciones específicas que manejen la persistencia de datos en formatos estructurados binarios.

---

## Propiedades

* **`private string $version = "v0.1.0"`:** Define la versión del formato de archivo. En este caso, la versión actual es `v0.1.0`.

* **`private string $signature = 'DLStorage'`**: Firma binaria de validación.

  Esta propiedad representa la firma que identifica de manera inequívoca los archivos pertenecientes al sistema de almacenamiento. Se ubica en la cabecera del archivo y permite al sistema reconocer que dicho archivo cumple con el formato esperado de `DLStorage`.

---

## Métodos protegidos

Los métodos protegidos de `DataStorage` proporcionan utilidades internas esenciales para el manejo seguro del almacenamiento binario. Están destinados a ser utilizados exclusivamente por las clases derivadas que extiendan esta abstracción.

* **`protected function get_signature(): string`**
  Devuelve la firma del archivo en formato hexadecimal, convirtiendo los bytes binarios de `$signature` a una representación legible.

  **Ejemplo de uso:**

  ```php
  $signature = $this->get_signature();
  ```

* **`protected function get_version(): string`**
  Retorna la versión del archivo en formato hexadecimal, a partir de la propiedad `$version`.

  **Ejemplo de uso:**

  ```php
  $version = $this->get_version();
  ```

* **`protected function read_filename(string $filename, int $from = 1, int $to = 1): string`**
  Lee un rango de bytes dentro de un archivo binario, desde el índice `$from` hasta `$to`, ambos inclusive.

  Lanza una excepción `StorageException` si el archivo no existe, no es legible o si el rango es inválido.

  **Ejemplo de uso:**

  ```php
  $header = $this->read_filename("archivo.dlstorage", 1, 9);
  ```

* **`protected function validate_filename(string $filename): void`**
  Valida que el archivo exista, no sea un directorio y sea legible. Lanza una `StorageException` en caso contrario.

  **Ejemplo de uso:**

  ```php
  $file_path = $this->get_file_path("clave.dlstorage");
  $this->validate_filename($file_path);
  ```

---

## Métodos públicos

Los métodos públicos de `DataStorage` permiten la interacción controlada con el entorno de almacenamiento.

* **`public function get_document_root(): string`**
  Devuelve la ruta absoluta del directorio raíz del proyecto.

  Es útil para calcular rutas relativas y mantener coherencia al operar en distintos entornos de ejecución.

  **Ejemplo de uso:**

  ```php
  $root = $this->get_document_root(); // /var/www/html/mi-aplicacion
  ```

* **`public function validate_saved_data(string $file): bool`**
  Verifica si un archivo dado cumple con la estructura binaria esperada del formato `.dlstorage`.

  Devuelve `true` si el archivo es válido; de lo contrario, `false`.

  **Ejemplo de uso:**

  ```php
  if ($this->validate_saved_data("config.dlstorage")) {
      echo "Archivo válido";
  }
  ```

* **`public function get_file_path(string $filename, bool $create_dir = false): string`**
  Devuelve la ruta absoluta del archivo indicado, basado en la estructura de almacenamiento.

  Si el directorio padre no existe y `$create_dir` es `true`, se intentará crearlo. Si existe un archivo en el lugar del directorio requerido, se lanza una `StorageException`.

  * **Parámetros:**

    * `string $filename`: Ruta relativa del archivo (puede incluir subdirectorios).
    * `bool $create_dir`: Indica si debe crearse el directorio si no existe. Por defecto es `false`.

  **Ejemplo de uso:**

  ```php
  $path = $this->get_file_path("datos/credenciales.dlstorage", true);
  ```
