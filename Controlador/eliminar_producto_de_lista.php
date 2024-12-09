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
        // Eliminar el producto de la lista
        $sql_delete = "DELETE FROM listasproductos WHERE id_lista = ? AND id_producto = ?";
        $stmt_delete = $conn->prepare($sql_delete);
        $stmt_delete->bind_param('ii', $id_lista, $id_producto);
        if ($stmt_delete->execute()) {
            // Redirigir de vuelta a la lista con un mensaje de éxito
            header("Location: ../Vista/productosLista.php?id_lista=$id_lista&mensaje=Producto eliminado de la lista");
        } else {
            // Error al eliminar
            header("Location: ../Vista/productosLista.php?id_lista=$id_lista&error=No se pudo eliminar el producto de la lista");
        }
        $stmt_delete->close();
    } else {
        // La lista no pertenece al usuario
        header("Location: ../Vista/listas.php?error=No tienes permiso para modificar esta lista");
    }
    $stmt->close();
    $conn->close();
} else {
    header("Location: ../Vista/listas.php?error=Datos incompletos");
}
?>
