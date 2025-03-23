<?php

$servidor="localhost";
$usuario="root";
$contraseña="";
$baseDatos="ecopacking";

//Crear conexion

$conexion = new mysqli($servidor, $usuario, $contraseña, $baseDatos);

//Validacion de conexion
if($conexion->connect_error){
    die("Error de conexion" . $conexion->connect_error);
}

// echo"Conexion OK a la BBDD";

?>