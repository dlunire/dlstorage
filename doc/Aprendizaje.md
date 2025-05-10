# 📘 Ruta de Aprendizaje: **Rust + Compiladores + Ciencias de la Computación**

**Perfil base**: Desarrollador backend con dominio avanzado de PHP, experiencia con diseño de frameworks, interés en eficiencia, criptografía y diseño de lenguajes.

---

## 🟢 Fase 1 – Fundamentos sólidos de Rust (0 a 1)

**Objetivo**: Aprender Rust de forma idiomática y comprender el sistema de tipos, propiedad y errores en tiempo de compilación.

### Contenido clave:

* Ownership, Borrowing, Lifetimes
* `Result`, `Option`, `match`, `if let`, `while let`
* Structs, enums, traits, impl
* Crates, módulos y `cargo`
* Tipos estáticos, inferencia y mutabilidad
* Macros básicas

### Recursos:

* 📖 [The Rust Book](https://doc.rust-lang.org/book/)
* 🧠 [Rustlings](https://github.com/rust-lang/rustlings)
* 💻 [Exercism.io – Rust Track](https://exercism.org/tracks/rust)

### Proyectos sugeridos:

* Reescribir utilidades de PHP en Rust (UUID, parser INI, CLI tools)
* Logger tipo Monolog en Rust
* JSON validator

---

## 🟡 Fase 2 – Interoperabilidad con C/PHP, uso de FFI y bajo nivel

**Objetivo**: Integrar Rust como backend eficiente para PHP, y comenzar a escribir bibliotecas en Rust que interactúan con C.

### Contenido clave:

* `extern "C"`, `#[no_mangle]`, uso de `libc`
* Creación de bibliotecas `cdylib` para usar desde PHP vía `FFI`
* Interacción con punteros, slices y estructuras C
* Seguridad de memoria en el límite entre C y Rust
* Carga de archivos binarios, streams, lectura de buffers

### Recursos:

* 🔬 [The Rust FFI Omnibus](https://jakegoulding.com/rust-ffi-omnibus/)
* 📕 [Unsafe Rust](https://doc.rust-lang.org/nomicon/)

### Proyectos sugeridos:

* Biblioteca Rust que reemplace `unpack()` en PHP
* Biblioteca de criptografía básica para usar desde PHP (`FFI::cdef`)
* Librería binaria para entropía segura

---

## 🔵 Fase 3 – Teoría de compiladores y diseño de lenguajes

**Objetivo**: Construir las bases para crear tu propio lenguaje y motor de plantillas.

### Contenido clave:

* Teoría formal: autómatas, gramáticas, lenguajes regulares y libres de contexto
* Parsers en Rust: `pest`, `nom`, `lalrpop`
* Tokens, AST, semántica y errores
* Escritores de código, interpretación vs compilación
* Type-checkers

### Recursos:

* 📘 [Crafting Interpreters](https://craftinginterpreters.com/)
* 📚 [Compilers: Principles, Techniques & Tools – Aho et al.](https://en.wikipedia.org/wiki/Compilers:_Principles,_Techniques,_and_Tools)
* 🛠 [`rust-lang/rustc-dev-guide`](https://rustc-dev-guide.rust-lang.org/)

### Proyectos sugeridos:

* Parser para tu motor de plantillas inspirado en Blade
* AST + intérprete para lenguaje estilo PHP fuertemente tipado
* Mini lenguaje de expresiones (`@if`, `@foreach`, etc.)

---

## 🔴 Fase 4 – Ciencias de la computación y arquitectura

**Objetivo**: Comprender cómo funcionan lenguajes, procesadores, sistemas de archivos y estructuras a bajo nivel.

### Contenido clave:

* Representación binaria, entropía, codificación y teoría de la información
* Algoritmos y estructuras de datos en Rust (`Vec`, `HashMap`, trees)
* Cómo se compila a ensamblador, código de máquina y LLVM
* Sistemas de archivos y manejo de memoria
* Arquitectura x86, ARM y concepto de CPU/RAM

### Recursos:

* 🧠 [CS50 – Harvard](https://cs50.harvard.edu/)
* 📘 [The Elements of Computing Systems](https://www.nand2tetris.org/)
* 📘 \[Information Theory – Cover & Thomas]
* 🧵 [Phil Opp’s: Writing an OS in Rust](https://os.phil-opp.com/)

### Proyectos sugeridos:

* Cargar binarios de manera eficiente desde PHP usando Rust
* Visualizador de entropía en archivos
* Interpretador embebido en Rust para un lenguaje simple

---

## 🟣 Fase 5 – Diseño de lenguaje propio y compilador nativo

**Objetivo**: Construir un lenguaje compilado o interpretado con sintaxis inspirada en PHP pero con seguridad Rust.

### Contenido clave:

* Gramáticas EBNF y diseño de sintaxis
* Implementación de compiladores: AST → Bytecode/LLVM IR
* Backends: Cranelift, LLVM o WebAssembly
* Validación semántica estricta y tipos estáticos
* Diseño de runtime seguro

### Recursos:

* 🧪 [Cranelift](https://github.com/bytecodealliance/wasmtime/blob/main/cranelift)
* 🛠 [LLVM for Rust](https://docs.rs/inkwell/latest/inkwell/)
* 📘 [LLVM Language Reference Manual](https://llvm.org/docs/LangRef.html)

### Proyectos sugeridos:

* Lenguaje propio compilado a Wasm o x86
* CLI Compiler: convierte plantillas con `@if` y `@foreach` en HTML
* Generador de documentación técnica y validación estática

---

## 🎓 Evaluación y certificación

**Meta a largo plazo**:

* Publicar un whitepaper técnico del lenguaje/motor de plantillas
* Mostrar contribuciones en GitHub/GitLab
* Contactar universidades o programas open-learning (MIT, Stanford, etc.)
* Enviar propuesta de reconocimiento de saberes si es aplicable

---

## 📦 Herramientas que deberías dominar:

| Área                    | Herramienta                                      |
| ----------------------- | ------------------------------------------------ |
| Crates esenciales       | `serde`, `rayon`, `thiserror`, `anyhow`, `regex` |
| Testing                 | `cargo test`, `proptest`, `criterion.rs`         |
| Documentación           | `rustdoc`, `mdBook`, `docusaurus`                |
| Estilo                  | `clippy`, `rustfmt`, `cargo-audit`               |
| Sistema de construcción | `build.rs`, `cargo features`                     |
