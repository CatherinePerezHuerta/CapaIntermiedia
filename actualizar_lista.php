<?php
session_start();
require_once '../Modelo/Conexion.php';

if (!isset($_SESSION['user'])) {
    header("Location: ../Vista/login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id_lista = $_POST['id_lista'];
    $title = $_POST['title'];
    $description = $_POST['description'];
    $id_usuario = $_SESSION['user']['IDUsuario'];

    $conexion = new Conexion();
    $conn = $conexion->conectar();

    // Manejo de la imagen
    if (isset($_FILES['image']) && $_FILES['image']['tmp_name']) {
        $imagen = file_get_contents($_FILES['image']['tmp_name']);

        $sql = "UPDATE listas SET nombre_lista = ?, descripcion = ?, imagen = ? WHERE id_lista = ? AND id_usuario = ?";
        $stmt = $conn->prepare($sql);

        // Vincular los parámetros
        $null = NULL; // Marcador de posición para el BLOB
        $stmt->bind_param('ssbii', $title, $description, $null, $id_lista, $id_usuario);

        // Enviar los datos del BLOB
        $stmt->send_long_data(2, $imagen); // Índice 2 corresponde a 'imagen'
    } else {
        $sql = "UPDATE listas SET nombre_lista = ?, descripcion = ? WHERE id_lista = ? AND id_usuario = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('ssii', $title, $description, $id_lista, $id_usuario);
    }

    $stmt->execute();
    $stmt->close();

    header("Location: ../Vista/listas.php");
    exit();
}
?>
