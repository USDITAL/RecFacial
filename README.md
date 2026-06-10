# Proyecto-DAW: Sistema de Fichaje Facial

Sistema integral de control horario y gestión de ausencias basado en reconocimiento facial, desarrollado como proyecto final de DAW para el curso 2024-25.

---

## 🚀 Descripción del Proyecto
Este sistema automatiza el registro de jornada laboral mediante el análisis biométrico de los empleados. Al llegar al puesto de trabajo, el sistema captura la imagen del trabajador, procesa su descriptor facial y verifica su identidad de forma segura y cifrada.

## 🏗️ Arquitectura del Sistema
El sistema utiliza una arquitectura distribuida que separa la lógica de negocio de la seguridad biométrica:

1. **Frontend:** Captura el rostro del empleado y comunica con el servicio de reconocimiento y el backend.
2. **Node.js (Service):** Procesa el reconocimiento facial utilizando FaceApi. Recibe los datos del frontend, realiza la comparación y devuelve la validación.
3. **PHP (Backend):** Actúa como orquestador. Gestiona la lógica de negocio, accede a la BBDD MySQL, registra las asistencias y comunica los resultados al usuario.
4. **Base de Datos:** Almacena la información de empleados, ausencias y descriptores (cifrados).

## 🛠️ Tecnologías Utilizadas

### Backend y Base de Datos
* **PHP 7+**
* **MySQL** (gestionado con phpMyAdmin)
* **Composer** (gestión de dependencias)

### Frontend y Servicios
* **JavaScript**
* **Node.js**
* **FaceApi** (reconocimiento facial)
* **HTML5 / CSS3**

### Dependencias Principales
* `setasign/fpdf`: Generación de informes en PDF.
* `phpmailer/phpmailer`: Envío de notificaciones por email.
* `dompdf/dompdf`: Conversión de HTML a PDF.
* `phpunit/phpunit`: Pruebas unitarias.

## 🔒 Seguridad y Privacidad
La seguridad es un pilar fundamental del proyecto:
* **Cifrado:** Todos los vectores biométricos se almacenan cifrados en la base de datos.
* **Gestión de Claves:** La clave de cifrado reside fuera del espacio web, siendo accesible únicamente por el backend PHP.
* **Transmisión Segura:** Los datos enviados por la red están protegidos mediante **SSL**.
* **Privacidad de Datos:** Solo se transmite el vector de la persona presente ante la cámara; nunca se exponen los datos biométricos almacenados en la base de datos.
* **Concurrencia:** Cada proceso de identificación posee un ID único asociado, permitiendo el manejo de múltiples registros simultáneos sin colisiones.

## 📋 Requisitos Previos
* **Servidor Web:** Entorno compatible con PHP 7+ y MySQL (Ej: XAMPP, WAMP o un entorno LAMP en Ubuntu).
* **Node.js:** Instalado en el servidor para ejecutar el servicio de reconocimiento facial.
* **Composer:** Instalado globalmente o localmente para gestionar las dependencias de PHP.

## ⚙️ Instalación

1. **Clonar el repositorio:**
  ```
  git clone [https://github.com/USDITAL/RecFacial.git](https://github.com/USDITAL/RecFacial.git)
  ```
2. **Instalar dependencias de PHP:**
Navega a la raíz del proyecto y ejecuta:
  ```
  composer install
  ```
3. **Configurar la Base de Datos:**

Crea una base de datos en tu servidor MySQL.

Importa el archivo SQL ubicado en docs/BBDD mediante phpMyAdmin.

4. **Levantar el servicio de reconocimiento:**

Navega a la carpeta correspondiente al servicio de Node.js.

Instala los paquetes necesarios: npm install.

Ejecuta el servidor: node server.js.

5. **Configuración de red (Opcional):**

Si el despliegue es en una red local, puedes utilizar servicios como NOIP para obtener acceso externo seguro a tu servidor.

Proyecto desarrollado por Raquel.M, Isis.T y David.M | Curso 2024-25
