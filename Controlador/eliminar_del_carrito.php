<?php
session_start();
require_once '../Modelo/Conexion.php';

// Verificar si el usuario ha iniciado sesión y tiene el rol 'comprador'
if (!isset($_SESSION['user']) || strtolower($_SESSION['user']['rol']) != 'comprador') {
    header("Location: login.php");
    exit();
}

$userID = $_SESSION['user']['IDUsuario'];

// Verificar si se recibió el ID del producto
if (!isset($_POST['id_producto'])) {
    header("Location: Carrito.php");
    exit();
}

$productID = intval($_POST['id_producto']);

// Crear conexión
$conexion = new Conexion();
$conn = $conexion->conn;

// Obtener el carrito del usuario
$sql_carrito = "SELECT id_carrito FROM carritocompras WHERE id_usuario = ?";
$stmt_carrito = $conn->prepare($sql_carrito);
$stmt_carrito->bind_param("i", $userID);
$stmt_carrito->execute();
$result_carrito = $stmt_carrito->get_result();

if ($result_carrito->num_rows == 0) {
    // No hay carrito, redirigir
    header("Location: Carrito.php");
    exit();
}

$carrito = $result_carrito->fetch_assoc();
$id_carrito = intval($carrito['id_carrito']);

// Obtener la cantidad actual del producto en el carrito
$sql_cantidad = "SELECT cantidad FROM carritoproductos WHERE id_carrito = ? AND id_producto = ?";
$stmt_cantidad = $conn->prepare($sql_cantidad);
$stmt_cantidad->bind_param("ii", $id_carrito, $productID);
$stmt_cantidad->execute();
$result_cantidad = $stmt_cantidad->get_result();

if ($result_cantidad->num_rows == 0) {
    // Producto no está en el carrito
    header("Location: Carrito.php");
    exit();
}

$producto_carrito = $result_cantidad->fetch_assoc();
$cantidad_en_carrito = intval($producto_carrito['cantidad']);

// Eliminar el producto del carrito
$sql_eliminar = "DELETE FROM carritoproductos WHERE id_carrito = ? AND id_producto = ?";
$stmt_eliminar = $conn->prepare($sql_eliminar);
$stmt_eliminar->bind_param("ii", $id_carrito, $productID);
$stmt_eliminar->execute();
$stmt_eliminar->close();

// Restaurar la cantidad disponible en productos
$sql_restaurar = "UPDATE productos SET cantidad_disponible = cantidad_disponible + ? WHERE id_producto = ?";
$stmt_restaurar = $conn->prepare($sql_restaurar);
$stmt_restaurar->bind_param("ii", $cantidad_en_carrito, $productID);
$stmt_restaurar->execute();
$stmt_restaurar->close();

// Redirigir al carrito con un mensaje de éxito
header("Location: ../Vista/Carrito.php?success=producto_eliminado");
exit();
?>
