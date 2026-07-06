# Contexto del Modelo de Transformación de Bytes (MTB)

## Instalación

Para instalar `DLStorage` en tus aplicaciones, debe escribir el siguiente comando:

```bash
composer require dlunire/dlstorage
```

Si está utilizando PHP Vanilla, te recomendaría utilizar un subdirectorio en tu proyecto raíz, posiblemente `public/` o `html_public/` y agregues este fragento:

```php
<?php
declare(strict_types=1);

// include dirname(__DIR__) . "/vendor/autoload.php";
include dirname(__DIR__) . DIRECTORY_SEPARATOR . "vendor" . DIRECTORY_SEPARATOR . "autoload.php";
```

En tu `index.php`.

---

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
