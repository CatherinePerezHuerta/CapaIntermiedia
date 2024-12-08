<?php
session_start();
require_once '../Modelo/Conexion.php';

// Verificar si el usuario ha iniciado sesión y es administrador
if (!isset($_SESSION['user']) || strtolower($_SESSION['user']['rol']) !== 'administrador') {
    header("Location: login.php");
    exit();
}

// Verificar que se haya enviado el formulario correctamente
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id_producto'], $_POST['accion'])) {
    $id_producto = $_POST['id_producto'];
    $accion = $_POST['accion'];

    // Crear una instancia de la conexión
    $conexion = new Conexion();
    $conn = $conexion->conn;

    if ($accion === 'aprobar') {
        // Actualizar el producto para marcarlo como aprobado
        $sql = "UPDATE productos SET aprobado = 1 WHERE id_producto = ?";
    } elseif ($accion === 'rechazar') {
        // Eliminar el producto de la base de datos
        $sql = "UPDATE productos SET aprobado = -1 WHERE id_producto = ?";
    } else {
        // Acción no válida
        header("Location: GestionProductos.php");
        exit();
    }

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id_producto);
    if ($stmt->execute()) {
        // Redirigir de vuelta a la página de gestión
        header("Location: ../Vista/GestionProductos.php");
        exit();
    } else {
        echo "Error al actualizar el producto: " . $stmt->error;
    }
} else {
    // Datos no válidos, redirigir
    header("Location: GestionProductos.php");
    exit();
}
?>
