<?php
session_start();
require_once '../Modelo/Conexion.php';

if (!isset($_SESSION['user'])) {
    header("Location: ../Vista/login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id_lista = $_POST['id_lista'];
    $accion = $_POST['accion'];

    // Mapear acciones a valores de 'publico'
    switch ($accion) {
        case 'mostrar':
            $publico = 1;
            break;
        case 'ocultar':
            $publico = -1;
            break;
        case 'eliminar':
            $publico = -2;
            break;
        default:
            header("Location: ../Vista/listas.php");
            exit();
    }

    $conexion = new Conexion();
    $conn = $conexion->conectar();

    $sql = "UPDATE listas SET publico = ? WHERE id_lista = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('ii', $publico, $id_lista);
    $stmt->execute();
    $stmt->close();

    header("Location: ../Vista/listas.php");
    exit();
}
?>
