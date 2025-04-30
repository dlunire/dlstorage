# Modelo de Transformación de Bytes (MTB)

Se trata de un modelo aún en desarrollo

---

### **Definiciones**

1. **Mensaje de Entrada \( M \)**  
   El mensaje de entrada representado como una secuencia de bytes:
   \[
   M = \{ m_1, m_2, \dots, m_n \}, \text{ donde } m_i \in \mathbb{Z}_{256}
   \]
   El mensaje se particiona en bloques de longitud fija de 5 bytes (40 bits).

2. **Clave de Entropía \( E \)**  
   La clave de entropía representada como una secuencia de caracteres de entropía codificados numéricamente en Unicode (por ejemplo, UTF-8 o UTF-32):
   \[
   E = \{ e_1, e_2, \dots, e_k \}, \text{ donde } e_i \in \mathbb{Z}_{256}
   \]

3. **Tamaño de Bloque \( b \)**  
   El tamaño fijo del bloque es de 5 bytes (40 bits).

4. **Desplazamiento Base \( P \)**  
   Un valor fijo de desplazamiento que proporciona un cambio base o relleno:
   Ejemplo:  
   \[
   P = 0x220000 = 2228224
   \]

5. **Función de Desorden \( f_{\text{desorden}} \)**  
   Una función no lineal que altera la secuencialidad de los índices para evitar patrones triviales:
   Ejemplo:
   \[
   f_{\text{desorden}}(i) = (i \cdot 31 + 17) \mod n + 10
   \]

---

### **Transformación de Bloques**

Para cada bloque de 5 bytes \( B_j = \{ m_{j_1}, m_{j_2}, \dots, m_{j_b} \} \), la transformación se define como:

\[
T(B_j) = \left( \sum_{i=1}^{b} \left( m_{f_{\text{desorden}}(i)} + E_{(i \bmod k)} \right) \right) + P
\]

Donde:
- \( m_{f_{\text{desorden}}(i)} \) es el byte en la posición alterada no linealmente.
- \( E_{(i \bmod k)} \) es el valor numérico del carácter de entropía, indexado cíclicamente por \( i \bmod k \).
- \( P \) es el desplazamiento base que garantiza un cambio mínimo en la codificación.

---

### **Codificación Completa**

La secuencia completa codificada se representa como:

\[
C = \{ T(B_1), T(B_2), \dots, T(B_m) \}
\]

Donde:
\[
m = \left\lceil \frac{n}{b} \right\rceil
\]

---

### **Observaciones**

1. **No Dependencia de Factorización**:  
   A diferencia de algoritmos clásicos como RSA, este modelo no depende de la dificultad computacional de factorizar números grandes.

2. **Sensibilidad a la Entropía**:  
   Cualquier pequeña alteración en la clave \( E \) genera resultados completamente diferentes.

3. **Estructura No Lineal**:  
   La función de desorden \( f_{\text{desorden}} \) asegura que los índices utilizados no sigan una progresión lineal, evitando correlaciones directas.

4. **Base Adaptable**:  
   El valor de \( P \) puede ser modificado como parámetro adicional para evitar la reutilización de claves y reforzar la seguridad.

---

### **Posibles Extensiones**

- **Funciones de Mezclado Adicionales**:  
   Incorporar funciones adicionales, como S-Boxes o permutaciones condicionales, para aumentar la complejidad de la transformación.

- **Detección de Errores**:  
   Introducir mecanismos de validación, como sumas de control o técnicas de codificación robusta para la detección de corrupción de datos.

- **Optimización para Codificación Simétrica**:  
   Optimizar el modelo para una codificación y decodificación simétrica eficiente sobre flujos binarios o en tiempo real.

---

Este formato ya está listo para que lo tomes y lo utilices en **Canva** o cualquier plataforma de diseño de documentos. Solo sigue los pasos que te mencioné antes y personaliza el diseño a tu gusto.

Si tienes alguna duda sobre el proceso o necesitas algún ajuste, no dudes en decirme. ¡Estoy aquí para ayudarte!