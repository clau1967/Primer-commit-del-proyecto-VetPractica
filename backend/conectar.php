<?php
$conexion = mysqli_connect("localhost", "vetsantiago", "veterinaria123", "veterinaria");

if (!$conexion) {
    die("Error de conexión: " . mysqli_connect_error());
}
?>
