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

## 📌 Notas Finales

* Pronto se incluirán más módulos como validadores, conversores y controladores de versión de datos.
* Si deseas soporte personalizado o tienes preguntas, contacta a través del correo del autor.
