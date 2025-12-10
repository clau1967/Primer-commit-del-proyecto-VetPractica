<?php
include 'backend/conectar.php';

$result = $conexion->query("SELECT id_cliente, nombre, apellido FROM clientes");

$clientes = [];
while($row = $result->fetch_assoc()){
    $clientes[] = $row;
}

echo json_encode($clientes);
?>
