<?php
session_start();
require_once '../Modelo/Conexion.php';

// Verificar si el usuario ha iniciado sesión
if (!isset($_SESSION['user'])) {
    header("Location: ../Vista/login.php");
    exit();
}

// Verificar que se hayan enviado los datos necesarios
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['id_lista']) && isset($_POST['id_producto'])) {
    $id_lista = $_POST['id_lista'];
    $id_producto = $_POST['id_producto'];
    $id_usuario = $_SESSION['user']['IDUsuario'];

    $conexion = new Conexion();
    $conn = $conexion->conectar();

    // Verificar que la lista pertenece al usuario logueado
    $sql = "SELECT * FROM listas WHERE id_lista = ? AND id_usuario = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('ii', $id_lista, $id_usuario);
    $stmt->execute();
    $resultado = $stmt->get_result();

    if ($resultado->num_rows > 0) {
        // Insertar en la tabla listasproductos
        $sql_insert = "INSERT INTO listasproductos (id_lista, id_producto) VALUES (?, ?)";
        $stmt_insert = $conn->prepare($sql_insert);
        $stmt_insert->bind_param('ii', $id_lista, $id_producto);
        if ($stmt_insert->execute()) {
            // Redirigir de vuelta a la publicación con un mensaje de éxito
            header("Location: ../Vista/publicacion.php?id=$id_producto&mensaje=Producto agregado a la lista");
        } else {
            // Error al insertar
            header("Location: ../Vista/publicacion.php?id=$id_producto&error=No se pudo agregar el producto a la lista");
        }
        $stmt_insert->close();
    } else {
        // La lista no pertenece al usuario
        header("Location: ../Vista/publicacion.php?id=$id_producto&error=No tienes permiso para modificar esta lista");
    }
    $stmt->close();
    $conn->close();
} else {
    header("Location: ../Vista/publicacion.php?error=Datos incompletos");
}


?>
