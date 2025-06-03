<?php

$servidor="localhost";
$usuario="u981249563_ekologistic";
$contraseña="Ek0l0g1st1c";
$baseDatos="u981249563_ekologisticdb";

//Crear conexion

$conexion = new mysqli($servidor, $usuario, $contraseña, $baseDatos);

//Validacion de conexion
if($conexion->connect_error){
    die("Error de conexion" . $conexion->connect_error);
}

// echo"Conexion OK a la BBDD";

?>