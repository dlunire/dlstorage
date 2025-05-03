# `SaveData`

---

## Introducción

La clase `SaveData` extiende `DataStorage` y proporciona una solución lista para usar en producción para guardar y recuperar datos binarios en archivos con extensión `.dlstorage`. Forma parte del paquete `DLStorage\Storage` y está diseñada para escenarios donde se requiere persistencia confiable sin depender de bases de datos, con énfasis en la protección de datos sensibles, como tokens o credenciales.

`SaveData` implementa validaciones de integridad, control de versión, firmas de datos y manejo automático de directorios. Soporta escritura protegida, lectura estructurada y verificación de la huella binaria, siendo ideal para entornos que necesitan un sistema de almacenamiento robusto y seguro.

---

## Propiedades

La clase `SaveData` no define nuevas propiedades, sino que hereda las siguientes de `DataStorage`:

* **`private string $version = "v0.1.0"`**:
  Almacena la versión actual del formato de archivo binario (`v0.1.0`). Se usa en la cabecera del archivo para garantizar compatibilidad. Su representación hexadecimal es `76 30 2e 31 2e 30`.

* **`private string $signature = "DLStorage"`**:
  Firma de la cabecera del archivo. Identifica el formato del archivo como válido para el sistema. Su representación hexadecimal es `44 4c 53 74 6f 72 61 67 65`.

---

## Métodos Protegidos

`SaveData` es una clase `final` y no define nuevos métodos protegidos, pero hereda los siguientes de `DataStorage`:

* **`protected function get_signature(): string`**
  **Descripción**: Devuelve la firma del archivo en formato hexadecimal.
  **Ejemplo Interno**:

  ```php
  $signature = $this->get_signature(); // Devuelve "444c53746f72616765"
  ```

* **`protected function get_version(): string`**
  **Descripción**: Devuelve la versión del archivo en formato hexadecimal.
  **Ejemplo Interno**:

  ```php
  $version = $this->get_version(); // Devuelve "76302e312e30"
  ```

* **`protected function read_filename(string $filename, int $from = 1, int $to = 1): string`**
  **Descripción**: Lee un rango de bytes de un archivo binario. Lanza una `StorageException` si el rango es inválido o excede el tamaño del archivo.
  **Ejemplo Interno**:

  ```php
  $content = $this->read_filename($filename, 1, 9); // Lee los primeros 9 bytes
  ```

---

## Métodos Públicos

Los métodos públicos de la clase `SaveData` constituyen la interfaz principal para guardar y recuperar datos binarios. Estos métodos están diseñados para ser utilizados directamente por desarrolladores, ofreciendo una solución sencilla y segura para la persistencia de datos con validaciones automáticas y manejo de errores.

* **`public function save_data(string $filename, string $data, ?string $entropy = NULL): void`**
  **Descripción**: Guarda datos transformados en un archivo binario con extensión `.dlstorage`. Codifica los datos, genera una cabecera con firma, versión y tamaños, y escribe el archivo, verificando su creación.
  **Parámetros**:

  * `$filename`: Nombre del archivo (sin extensión).
  * `$data`: Datos crudos a transformar y guardar.
  * `$entropy`: Llave de entropía opcional para modificar la transformación (recomendada para mayor seguridad).

  **Ejemplo**:

  ```php
  $storage = new SaveData();
  try {
      $storage->save_data("respaldo/config", "Datos sensibles", "clave🔐");
      echo "Archivo guardado correctamente.";
  } catch (StorageException $e) {
      echo "Error: " . $e->getMessage();
  }
  ```

  **Notas**: Lanza una `StorageException` si no se puede crear el archivo o faltan permisos de escritura.

* **`public function read_storage_data(string $filename, ?string $entropy = NULL): string`**
  **Descripción**: Lee un archivo `.dlstorage` y recupera su contenido original, utilizando una llave de entropía para decodificar los datos. Valida la firma y la estructura del archivo antes de procesarlo.
  **Parámetros**:

  * `$filename`: Nombre del archivo (sin extensión).
  * `$entropy`: Llave de entropía opcional para revertir la transformación.

  **Ejemplo**:

  ```php
  try {
      $contenido = $storage->read_storage_data("respaldo/config", "clave🔐");
      echo $contenido; // Ejemplo: "Datos sensibles"
  } catch (StorageException $e) {
      echo "Error: " . $e->getMessage();
  }
  ```

  **Notas**: Lanza una `StorageException` si el archivo no existe, no es un archivo DLStorage válido, o el contenido es inválido.

* **`public function get_document_root(): string`** (Heredado de `DataStorage`)
  **Descripción**: Devuelve la ruta absoluta del directorio raíz de la aplicación.
  **Ejemplo**:

  ```php
  $root = $storage->get_document_root(); // Ejemplo: /var/www/html/my-app
  ```

* **`public function validate_saved_data(string $file): bool`** (Heredado de `DataStorage`)
  **Descripción**: Valida si un archivo tiene una estructura binaria válida, comprobando la firma `DLStorage`.
  **Parámetros**:

  * `$file`: Nombre relativo del archivo a validar.
    **Ejemplo**:

  ```php
  try {
      $is_valid = $storage->validate_saved_data("respaldo/config.dlstorage");
      echo $is_valid ? "Archivo válido" : "Archivo inválido";
  } catch (StorageException $e) {
      echo "Error: " . $e->getMessage();
  }
  ```

* **`public function get_file_path(string $filename, bool $create_dir = false): string`** (Heredado de `DataStorage`)
  **Descripción**: Devuelve la ruta absoluta para un archivo en el directorio `storage`. Si `$create_dir` es `true`, crea los directorios necesarios.
  **Parámetros**:

  * `$filename`: Nombre relativo del archivo.
  * `$create_dir`: Si se deben crear los directorios (por defecto `false`).

  **Ejemplo**:

  ```php
  $ruta = $storage->get_file_path("respaldo/config.dlstorage", true);
  // Resultado: /ruta/absoluta/al/proyecto/storage/respaldo/config.dlstorage
  ```

* **`public function test(int $start = 1, int $end = 1): void`** (Heredado de `DataStorage`)
  **Descripción**: Método de prueba que lee un rango de bytes de `README.md` y lo muestra en texto plano.
  **Parámetros**:

  * `$start`: Offset de inicio (basado en 1).
  * `$end`: Offset final (basado en 1).

  **Ejemplo**:

  ```php
  $storage->test(1, 10); // Muestra los primeros 10 bytes de README.md
  ```

---

## Métodos Privados

Los métodos privados de `SaveData` implementan lógica interna para normalizar datos, calcular tamaños y gestionar el formato de los archivos binarios. Se documentan aquí para desarrolladores que mantengan el sistema o necesiten entender su funcionamiento interno, aunque no son accesibles fuera de la clase.

* **`private function delete_padding(string $content): string`**
  **Descripción**: Normaliza una cadena hexadecimal reemplazando múltiples ceros iniciales por un solo `0`, eliminando el relleno añadido durante la codificación.
  **Parámetros**:

  * `$content`: Cadena hexadecimal con posibles ceros iniciales.

  **Ejemplo Interno**:

  ```php
  $normalized = $this->delete_padding("0000abc"); // Devuelve "0abc"
  ```

  **Notas**: Usar con precaución, ya que no distingue entre ceros de relleno y ceros originales.

* **`private function normalize_hex_payload(string &$size, string &$content): void`**
  **Descripción**: Asegura que una cadena hexadecimal tenga una longitud par, añadiendo un cero inicial si es necesario, para compatibilidad con `hex2bin()`. Actualiza el tamaño y contenido por referencia.
  **Parámetros**:

  * `$size`: Referencia al tamaño hexadecimal del payload.
  * `$content`: Referencia al contenido hexadecimal a normalizar.

  **Ejemplo Interno**:

  ```php
  $size = "00000008";
  $content = "abc";
  $this->normalize_hex_payload($size, $content);
  // Resultado: $content...
  ```
