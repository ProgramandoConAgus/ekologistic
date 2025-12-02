<?php

$servidor="193.203.175.67";
$usuario="u981249563_ekologistic";
$contraseña="Pca@70071";
$baseDatos="u981249563_ekologisticdb";

//Crear conexion

$conexion = new mysqli($servidor, $usuario, $contraseña, $baseDatos);

//Validacion de conexion
if($conexion->connect_error){
    die("Error de conexion" . $conexion->connect_error);
}

// echo"Conexion OK a la BBDD";

?>