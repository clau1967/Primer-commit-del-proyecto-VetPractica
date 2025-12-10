<?php
session_start();
if(!isset($_SESSION['usuario'])){
    header("Location: ../login.php");
    exit;
}

include 'conectar.php';

if($_SERVER['REQUEST_METHOD'] == 'POST'){
    $id_cliente = $_POST['id_cliente'];
    $id_mascota = $_POST['id_mascota'];
    $id_veterinario = $_POST['id_veterinario'];
    $id_consultorio = $_POST['id_consultorio'];
    $fecha_hora = $_POST['fecha_hora'];
    $motivo = $_POST['motivo'];

    $sql = "INSERT INTO citas (id_cliente, id_mascota, id_veterinario, id_consultorio, fecha_hora, motivo, estado)
            VALUES ('$id_cliente','$id_mascota','$id_veterinario','$id_consultorio','$fecha_hora','$motivo','Pendiente')";

    if($conexion->query($sql) === TRUE){
        header("Location: ../cita.php?msg=Cita agendada correctamente");
        exit;
    } else {
        echo "Error al guardar la cita: " . $conexion->error;
    }
}
?>
