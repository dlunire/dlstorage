# Contexto del Modelo de Transformación de Bytes (MTB)

## Instalación

DLStorage se instala mediante Composer. Ejecute el siguiente comando en la raíz de su proyecto:

```bash
composer require dlunire/dlstorage
```

Una vez instalada la biblioteca, Composer generará el autoload necesario para utilizar las clases del paquete.

---

## Configuración inicial

Antes de utilizar `Storage`, incluya el archivo `autoload.php` generado por Composer en su `index.php` o en el punto de entrada principal de su aplicación.

```php
<?php
declare(strict_types=1);

use DLStorage\Storage\Storage;

include dirname(__DIR__)
    . DIRECTORY_SEPARATOR
    . "vendor"
    . DIRECTORY_SEPARATOR
    . "autoload.php";

/** @var Storage $storage */
$storage = new Storage(
    filename: "test-file",
    entropy: "Entropia de prueba"
);

$storage->generate("Ciencias de la computación", true);
echo $storage->readfile(true);
```

En este ejemplo:

* se carga el autoload de Composer;
* se crea una instancia de `Storage`;
* se genera un archivo con contenido;
* se lee nuevamente el contenido almacenado.

> **Nota:** No es necesario especificar una extensión. DLStorage agrega automáticamente la extensión `.dlstorage` al archivo generado.

---

## Uso de `Storage`

La clase `Storage` constituye la interfaz principal de **DLStorage** para generar y recuperar archivos utilizando el formato binario del proyecto.

### Crear una instancia

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

DLStorage agregará automáticamente la extensión `.dlstorage` al archivo generado.

---

## Generar un archivo

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

Si desea guardar el archivo dentro del directorio `storage`, puede indicar explícitamente el segundo parámetro como `true`.

```php
$storage->generate("Hola mundo", true);
```

---

## Guardar fuera del directorio `storage`

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

## Leer un archivo

Para recuperar el contenido almacenado utilice `readfile()`.

```php
$storage = new Storage("documento");

$contenido = $storage->readfile();

echo $contenido;
```

Si el archivo fue generado dentro del directorio `storage`, también puede indicarse explícitamente el segundo parámetro como `true`.

```php
$contenido = $storage->readfile(true);
```

---

## Leer archivos fuera del directorio `storage`

Si el archivo no se encuentra dentro del directorio `storage`, indique el segundo parámetro.

```php
$storage = new Storage("backup/documento");

$contenido = $storage->readfile(
    storage: false
);
```

---

## Obtener la firma del formato

La biblioteca permite consultar la firma binaria del formato soportado.

```php
$firma = $storage->get_current_signature();
```

Este método devuelve la firma binaria correspondiente al formato actual de DLStorage y puede utilizarse para validaciones o comprobaciones de compatibilidad.

---

## Obtener la versión del formato

También es posible consultar la versión binaria del formato actual.

```php
$version = $storage->get_current_version();
```

Este valor resulta útil cuando una aplicación necesita comprobar la compatibilidad entre diferentes versiones del formato de almacenamiento.

---

## Ejemplo completo

```php
<?php
declare(strict_types=1);

use DLStorage\Storage\Storage;

include dirname(__DIR__)
    . DIRECTORY_SEPARATOR
    . "vendor"
    . DIRECTORY_SEPARATOR
    . "autoload.php";

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

---

## Evaluación

Antes de analizar este proyecto, es importante comprender su objetivo de diseño para evitar evaluarlo bajo un modelo de amenazas distinto al que pretende resolver.

## Propósito

El Modelo de Transformación de Bytes (MTB) **no fue diseñado como un algoritmo criptográfico estándar**, ni pretende sustituir algoritmos como AES, ChaCha20 o similares.

Su objetivo es transformar información sensible —principalmente archivos de configuración de aplicaciones— desde un formato de texto plano hacia un formato binario dependiente de una entropía, reduciendo la exposición accidental de credenciales y dificultando su recuperación directa.

El problema que intenta resolver es diferente al de un cifrado general.

## Problema que resuelve

En la mayoría de aplicaciones PHP, Node.js y otros entornos, las credenciales suelen almacenarse en archivos `.env` con contenido legible:

```text
DB_HOST=localhost
DB_DATABASE=...
DB_USERNAME=...
DB_PASSWORD=...
```

Si dicho archivo es expuesto por una mala configuración del servidor, una copia de respaldo, una vulnerabilidad o un error humano, las credenciales quedan inmediatamente disponibles.

MTB transforma esa información en una representación binaria dependiente de una entropía, eliminando completamente las cadenas de texto originales.

Además, el diseño contempla que estos archivos no permanezcan dentro del directorio del proyecto, sino que sean almacenados en el directorio `$HOME` del usuario del sistema operativo, fuera del árbol de archivos públicamente accesible y preparados para un entorno multitenant.

## Modelo de amenazas

El modelo de amenazas considerado es principalmente:

* Exposición accidental de archivos de configuración.
* Lectura directa de archivos por errores de despliegue.
* Descarga de archivos sensibles desde el servidor.
* Inspección manual mediante editores de texto o herramientas hexadecimales.
* Dificultar la recuperación inmediata de credenciales.

No pretende proteger contra un adversario con acceso completo al sistema operativo, privilegios elevados o capacidades propias de un análisis criptográfico avanzado.

## Lo que no pretende

MTB no busca demostrar las propiedades criptográficas clásicas de:

* difusión completa (Avalanche Effect),
* Confusión criptográfica,
* Indistinguibilidad bajo ataques de texto conocido,
* Seguridad demostrable frente a modelos formales de criptografía.

Si el análisis se enfoca exclusivamente bajo esos criterios, inevitablemente concluirá que el sistema no debe considerarse un cifrado criptográfico moderno, lo cual es una afirmación correcta, pero responde a una pregunta distinta de aquella para la que fue diseñado.

## Solicitud de evaluación

La evaluación debe centrarse en determinar si el modelo cumple adecuadamente su propósito:

* Transformar información sensible a un formato binario;
* Reducir la exposición de credenciales frente al almacenamiento tradicional en archivos `.env`;
* Analizar posibles debilidades dentro de ese contexto específico;
* Proponer mejoras que aumenten la robustez del modelo sin cambiar su objetivo de diseño.

Si durante el análisis se detectan propiedades criptográficas interesantes o limitaciones importantes, estas son bienvenidas, pero deben considerarse como observaciones complementarias y no como el criterio principal de evaluación.
