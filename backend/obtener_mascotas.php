<?php
include 'backend/conectar.php';

$id_cliente = $_GET['id_cliente'];

$stmt = $conexion->prepare("SELECT id_mascota, nombre FROM mascotas WHERE id_cliente=?");
$stmt->bind_param("i", $id_cliente);
$stmt->execute();
$result = $stmt->get_result();

$mascotas = [];
while($row = $result->fetch_assoc()){
    $mascotas[] = $row;
}

echo json_encode($mascotas);
?>
