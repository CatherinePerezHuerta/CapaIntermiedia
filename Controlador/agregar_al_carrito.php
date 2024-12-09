<?php
session_start();
require_once '../Modelo/Conexion.php';

// Verificar si el usuario ha iniciado sesión y tiene el rol 'comprador'
if (!isset($_SESSION['user']) || strtolower($_SESSION['user']['rol']) != 'comprador') {
    header("Location: login.php");
    exit();
}

$userID = $_SESSION['user']['IDUsuario'];

// Verificar si se recibieron los datos necesarios
if (!isset($_POST['id_producto']) || !isset($_POST['cantidad'])) {
    header("Location: publicacion.php?id=" . intval($_POST['id_producto']));
    exit();
}

$productID = intval($_POST['id_producto']);
$cantidadSolicitada = intval($_POST['cantidad']);

// Validar cantidad solicitada
if ($cantidadSolicitada < 1) {
    // Cantidad inválida
    header("Location: publicacion.php?id=" . $productID . "&error=cantidad_invalida");
    exit();
}

// Crear conexión
$conexion = new Conexion();
$conn = $conexion->conn;

// Obtener la cantidad disponible del producto
$sql_stock = "SELECT cantidad_disponible FROM productos WHERE id_producto = ? AND aprobado = 1";
$stmt_stock = $conn->prepare($sql_stock);
$stmt_stock->bind_param("i", $productID);
$stmt_stock->execute();
$result_stock = $stmt_stock->get_result();

if ($result_stock->num_rows == 0) {
    // Producto no encontrado o no aprobado
    header("Location: publicacion.php?id=" . $productID . "&error=producto_no_disponible");
    exit();
}

$producto = $result_stock->fetch_assoc();
$cantidadDisponible = intval($producto['cantidad_disponible']);

if ($cantidadSolicitada > $cantidadDisponible) {
    // Cantidad solicitada excede la disponible
    header("Location: publicacion.php?id=" . $productID . "&error=cantidad_excede");
    exit();
}

// Obtener o crear el carrito del usuario
$sql_carrito = "SELECT id_carrito FROM carritocompras WHERE id_usuario = ?";
$stmt_carrito = $conn->prepare($sql_carrito);
$stmt_carrito->bind_param("i", $userID);
$stmt_carrito->execute();
$result_carrito = $stmt_carrito->get_result();

if ($result_carrito->num_rows > 0) {
    $carrito = $result_carrito->fetch_assoc();
    $id_carrito = intval($carrito['id_carrito']);
} else {
    // Crear un nuevo carrito
    $sql_insert_carrito = "INSERT INTO carritocompras (id_usuario) VALUES (?)";
    $stmt_insert_carrito = $conn->prepare($sql_insert_carrito);
    $stmt_insert_carrito->bind_param("i", $userID);
    if ($stmt_insert_carrito->execute()) {
        $id_carrito = $stmt_insert_carrito->insert_id;
    } else {
        // Error al crear el carrito
        header("Location: publicacion.php?id=" . $productID . "&error=crear_carrito");
        exit();
    }
    $stmt_insert_carrito->close();
}

// Verificar si el producto ya está en el carrito
$sql_verificar = "SELECT cantidad FROM carritoproductos WHERE id_carrito = ? AND id_producto = ?";
$stmt_verificar = $conn->prepare($sql_verificar);
$stmt_verificar->bind_param("ii", $id_carrito, $productID);
$stmt_verificar->execute();
$result_verificar = $stmt_verificar->get_result();

if ($result_verificar->num_rows > 0) {
    // Actualizar la cantidad existente
    $producto_carrito = $result_verificar->fetch_assoc();
    $nueva_cantidad = $producto_carrito['cantidad'] + $cantidadSolicitada;

    if ($nueva_cantidad > $cantidadDisponible) {
        // Cantidad total excede la disponible
        header("Location: publicacion.php?id=" . $productID . "&error=total_excede");
        exit();
    }

    $sql_actualizar = "UPDATE carritoproductos SET cantidad = ? WHERE id_carrito = ? AND id_producto = ?";
    $stmt_actualizar = $conn->prepare($sql_actualizar);
    $stmt_actualizar->bind_param("iii", $nueva_cantidad, $id_carrito, $productID);
    $stmt_actualizar->execute();
    $stmt_actualizar->close();
} else {
    // Insertar nuevo producto en el carrito
    $sql_insertar = "INSERT INTO carritoproductos (id_carrito, id_producto, cantidad) VALUES (?, ?, ?)";
    $stmt_insertar = $conn->prepare($sql_insertar);
    $stmt_insertar->bind_param("iii", $id_carrito, $productID, $cantidadSolicitada);
    $stmt_insertar->execute();
    $stmt_insertar->close();
}

// Actualizar la cantidad disponible en productos
$nueva_cantidad_disponible = $cantidadDisponible - $cantidadSolicitada;
$sql_actualizar_stock = "UPDATE productos SET cantidad_disponible = ? WHERE id_producto = ?";
$stmt_actualizar_stock = $conn->prepare($sql_actualizar_stock);
$stmt_actualizar_stock->bind_param("ii", $nueva_cantidad_disponible, $productID);
$stmt_actualizar_stock->execute();
$stmt_actualizar_stock->close();

// Redirigir a la página del carrito con un mensaje de éxito
header("Location: ../Vista/Carrito.php?success=producto_agregado");
exit();
?>
