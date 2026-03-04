🏆 Herramienta para el Desarrollo de Campeonatos Amateur de Tenis – Aplicación Web
📖 Descripción

Esta aplicación web ha sido desarrollada como parte del Trabajo Fin de Grado en Ingeniería Informática (Especialidad en Software).

El sistema permite la gestión integral de ligas amateur de tenis, proporcionando funcionalidades tanto para:

👤 Usuarios/Jugadores

👨‍💼 Administradores

La aplicación funciona en entorno local mediante XAMPP, utilizando PHP + MySQL como backend.

🏗️ Arquitectura del Sistema

La aplicación sigue una arquitectura cliente-servidor:

Frontend: HTML + CSS

Backend: PHP

Base de datos: MySQL

Entorno local: XAMPP (Apache + MySQL)

Gestión de BD: phpMyAdmin

💻 Requisitos del Sistema

XAMPP instalado

Navegador web (Chrome, Firefox, Edge…)

PHP 8+

MySQL

phpMyAdmin

⚙️ Instalación
1️⃣ Instalar XAMPP

Descargar desde:

https://www.apachefriends.org

Instalar y activar:

Apache

MySQL

🗄️ 2️⃣ Creación de la Base de Datos

Abrir:

http://localhost/phpmyadmin
Crear nueva base de datos (ejemplo):
liga_tenis
📊 Estructura de la Base de Datos

La base de datos debe crearse siguiendo el modelo relacional mostrado en el diagrama proporcionado en el TFG. Es el siguiente:

![ESQUEMA DE LA BASE DE DATOS](https://github.com/alberto535/tenisAPP-web/blob/master/BasesDeDatos.drawio.png?raw=true)


Tablas necesarias:

administradores

usuarios

ligas

jornada

jornada_partidos

partidos

clasificacion

Relaciones principales:

Un administrador crea y gestiona ligas

Una liga contiene jornadas

Una jornada genera partidos

Los usuarios pertenecen a una liga y división

Los partidos pertenecen a una jornada

La clasificación está asociada a liga y división

⚠️ Es obligatorio respetar:

Claves primarias (PK)

Claves foráneas (FK)

Estados (activo, pendiente, procesado)

Se recomienda importar el script SQL si está disponible.

📂 3️⃣ Copiar Archivos del Proyecto

Copiar todos los archivos del proyecto dentro de:

C:\xampp\htdocs\

Ejemplo:

C:\xampp\htdocs\liga_tenis\
▶️ 4️⃣ Ejecutar la Aplicación

Iniciar Apache y MySQL desde XAMPP.

Abrir en navegador:

http://localhost/liga_tenis/login.html
🔐 Funcionalidades
-. Usuario

Registro

Login

Edición de perfil

Ver clasificación

Ver resultados

Insertar resultados

Aceptar/rechazar resultados

-. Administrador

Aceptar usuarios

Eliminar usuarios

Crear liga

Generar jornadas

Finalizar jornadas

Terminar liga

Consultar ligas pasadas

🔒 Seguridad

Validación de DNI

Validación de teléfono (9 dígitos)

Validación de contraseña segura

Gestión de estados de partidos

Envío de correos mediante PHPMailer

👨‍🎓 Autor

Alberto Ortiz Arribas
Grado en Ingeniería Informática – Especialidad Software
Universidad de Córdoba
Junio 2025
