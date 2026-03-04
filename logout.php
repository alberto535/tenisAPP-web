<?php
/*
    Autor: Alberto Ortiz Arribas
    Fecha: 01-03-2025
    Resumen: Realiza la desconexión al sistema y redirige al login.
*/

session_start();
session_destroy(); // Destruir toda la sesión
header('Location: login.html'); // Redirigir al login
exit();
?>