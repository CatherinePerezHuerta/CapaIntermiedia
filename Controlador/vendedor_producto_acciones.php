<?php
session_start();
require_once '../Modelo/Conexion.php';

// Verificar si el usuario ha iniciado sesión y es vendedor
if (!isset($_SESSION['user']) || strtolower($_SESSION['user']['rol']) !== 'vendedor') {
    header("Location: login.php");
    exit();
}

// Verificar que se haya enviado el formulario correctamente
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id_producto'], $_POST['accion'])) {
    $id_producto = $_POST['id_producto'];
    $accion = $_POST['accion'];
    $id_vendedor = $_SESSION['user']['IDUsuario'];

    // Crear una instancia de la conexión
    $conexion = new Conexion();
    $conn = $conexion->conn;

    // Verificar que el producto pertenece al vendedor
    $sql_check = "SELECT id_producto FROM productos WHERE id_producto = ? AND id_vendedor = ?";
    $stmt_check = $conn->prepare($sql_check);
    $stmt_check->bind_param("ii", $id_producto, $id_vendedor);
    $stmt_check->execute();
    $result_check = $stmt_check->get_result();

    if ($result_check->num_rows === 0) {
        // El producto no pertenece al vendedor
        header("Location: ../Vista/productosaprobados.php");
        exit();
    }

    if ($accion === 'mostrar') {
        // Actualizar el producto para marcarlo como mostrando
        $sql = "UPDATE productos SET aprobado = 1 WHERE id_producto = ?";
    } elseif ($accion === 'esconder') {
        // Actualizar el producto para marcarlo como oculto
        $sql = "UPDATE productos SET aprobado = 2 WHERE id_producto = ?";
    } elseif ($accion === 'eliminar') {
        // Actualizar el producto para marcarlo como eliminado
        $sql = "UPDATE productos SET aprobado = -2 WHERE id_producto = ?";
    } elseif ($accion === 'actualizar') {
        // Redirigir a la página de actualización
        header("Location: ../Vista/ActualizarProducto.php?id_producto=" . $id_producto);
        exit();
    } else {
        // Acción no válida
        header("Location: ../Vista/productosaprobados.php");
        exit();
    }

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id_producto);
    if ($stmt->execute()) {
        // Redirigir de vuelta a la página de gestión
        header("Location: ../Vista/productosaprobados.php");
        exit();
    } else {
        echo "Error al actualizar el producto: " . $stmt->error;
    }
} else {
    // Datos no válidos, redirigir
    header("Location: ../Vista/productosaprobados.php");
    exit();
}
?>
