# `DataStorage`

---

## Introducción

La clase abstracta `DataStorage` define una base para almacenar datos transformados en archivos binarios u otros medios persistentes, sin utilizar una base de datos tradicional con la finalidad de proteger credenciales, como tokens, entre otras.

Esta clase extiende a `Data` y forma parte del paquete `DLStorage`. Está diseñada para ser utilizada como base para clases concretas que implementen mecanismos específicos de persistencia.

---

## Propiedades


- **`private string $version = "v0.1.0`:** Almacena la versión del archivo, donde `v0.1.0`es la versión actual.

- **`private string $signature = 'DLStorage'`:** Firma de la cabecera del archivo. 

    Esta propiedad almacena la firma que identifica de manera única el formato del archivo de almacenamiento de datos transformados. La firma es una secuencia de caracteres que se coloca al inicio del archivo, sirviendo como una "marca" para indicar que elarchivo ese reconocido por el sistema y sigue el formato adecuado.

## Métodos protegidos

Los métodos protegidos de la clase `DataStorage` están diseñados para ser utilizados por clases derivadas que extiendan esta clase abstracta.

Estos métodos proporcionan funcionalidades internas clave, como el acceso a la firma y versión del formato de archivo en formato hexadecimal, y la lectura de rangos de bytes desde archivos binarios. Aunque no son accesibles directamente desde fuera de la clase, permiten a los desarrolladores que implementen clases hijas personalizar y aprovechar la lógica de almacenamiento de datos de manera segura y eficiente. A continuación, se describen los métodos protegidos disponibles, con ejemplos de cómo pueden usarse en clases derivadas.

- **`protected function get_signature(): string`:** Devuelve la firma del archivo en formato hexadecimal. Convierte los bytes binarios de la propiedad `$signature` a una representación legible como hexadecimal.

    Ejemplo de uso:

    ```php
    $signature = $this->get_signature();
    ```

- **`protected function get_version(): string`:** Devuelve la versión del archivo en formato hexadecimal. Convierte los bytes binarios de la propiedad `$version` a una representación legible como cadena hexadecimal.

    Ejemplo de uso:

    ```php
    $version = $this->get_version();
    ```

- **`read_filename`**: Lee un rango de bytes de un archivo binario.

    Este método permite leer una porción de un archivo binario, esepecificando los índices de inicio y fin del rango a leer. Si el rango es inválido o excede el tamaño del archivo se lanza la exceptión `StorageException`.

    **Sintaxis:**

    ```php
    protected function read_filename(string $filename, int $from = 1, int $to = 1): string
    ```

    **Ejemplo de uso**

    Leemos los primeros 9 bytes del archivo binario `.dlstorage`:
    ```php
    /** @var string $content */
    $content = $this->get_filename($filename, 1, 9);
    ```

- **`validate_filename`:** Valida si el archivo existe y no es un directorio. También valida si es legible.

    **Sintaxis:**

    ```php
    protected function validate_filename(strin $filename): void;
    ```

    **Ejemplo de uso:**
    
    ```php
    $file = $this->get_file_path($filename);
    $this->validate_filename($file);
    ```

## Métodos públicos

- **`get_document_root`:** Devuelve la ruta absoluta del directorio raíz de la aplicación. Esto es útil para establecer rutas base dentro de la aplicación evitando problemas de rutas relativas al trabajar con diferentes entornos de desarrollo.

    **Sintaxis:**

    ```php
    public function get_document_root(): string;
    ```

    **Ejemplo de uso:**

    ```php
    $root = $this->get_document_root(); // /var/www/html/my-app
    ```

- **`validate_saved_data`:** Valida si se trata de un archivo estructura binaria válida

    **Sintaxis:**

    ```php
    public function validate_saved_data(string $file): bool;
    ```

    **Ejemplo de uso:**

    ```php
    $is_valid = $this->validate_saved_data('file.dlstorage');
    ```

- **`get_file_path`**: Devuelve la ruta absoluta completa donde se almacenará un archivo dentro delsistema de almacenamiento gestionado por la clase. Puede opcionalmente crear el directorio contenedor si no existe.

    Si se establece `$create_dir` a `true`, la función asegura que el directorio padre del archivo exista, creándolo si es necesario.

    En el caso de que exista un archivo con el mismo nombre que el directorio se lanzará la excepción `StorageException`.

    - **Parámetros:**

        - `string $filename`: Nombre relativo del archivo (puede contener subdirectorio).
        - `bool $create_dir`: Si se debe crear el directorio contenedor si no existe. Por defecto es `false`.

    **Sintaxis:**

    ```php
    public functin get_file_path(string $filename, bool $create_dir = false): string;
    ```

    **Ejemplo de uso:**

    ```php
    $ruta = $storage->get_file_path("documentos/ejemplo.txt", true);
    // Resultado: /ruta/absoluta/al/proyecto/storage/documentos/ejemplo.txt
    ```