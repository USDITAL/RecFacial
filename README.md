# Proyecto-DAW

Proyecto DAW 2024-25

## Descripción

Este proyecto se basa en el desarrollo de una aplicación para el fichaje facial de empleados de una empresa para llevar un control horario y de ausencias.
Ideado, diseñado y desarrollado por Raquel.M, Isis.T y David.M

## Estructura

Las partes más representativas de la estructura de la aplicación son:

Un front-end para el perfil de conserje(controlador automático), desarrollado en JavaScript y la librería FaceApi, que tomará la imagen de los empleados según lleguen a su centro
de trabajo y analizará sus rasgos para generar un descriptor que enviará al servidor para confirmar su identidad. 

Un servidor Node.JS para escuchar las peticiones y realizar las verificaciones en JavaScript comparándo los datos almacenados en la BBDD a través de peticiones REST a PHP.

Un front-end personal en el que podrán consultar sus asistencias y realizar peticiones.

Un back-end donde los administradores podrán realizar altas de empleados, modificaciones de los datos y baja de empleados.

Una base de datos alojada en el mismo servidor que guardará la información relativa a las ausencias, empleados, entrenamientos faciales, etc...

El frontend del usuario y el backend se puede realizar con PHP (Para la conexión con la base de datos y lógica general) 
y JavaScript (Para el control de formularios y generación de ventanas) También usará REST para comunicar y actualizar información.

La base de datos MySQL se administra con el SGBD PHPMyAdmin.

El servidor puede ser montado en un pc que corra una máquina virtual con Ubuntu donde se instale LAMP y se puede dar acceso externo mediante la aplicación
NOIP.
