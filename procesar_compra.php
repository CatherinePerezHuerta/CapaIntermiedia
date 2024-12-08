<?php
session_start();
require_once '../Modelo/Conexion.php';

// Verificar si el usuario ha iniciado sesión
if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit();
}

$userID = $_SESSION['user']['IDUsuario'];
$role = strtolower($_SESSION['user']['rol']);

// Verificar que el rol sea 'comprador'
if ($role !== 'comprador') {
    header("Location: ../Vista/carrito.php");
    exit();
}

// Crear conexión
$conexion = new Conexion();
$conn = $conexion->conn;

// Obtener los datos del formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $metodo_pago = $_POST['metodo_pago'];
    $total = floatval($_POST['total']);

    // Validar método de pago
    if (!in_array($metodo_pago, ['tarjeta', 'clabe', 'paypal'])) {
        header("Location: ../Vista/carrito.php?error=metodo_pago_invalido");
        exit();
    }

    // Inicializar variables para los métodos de pago
    $nombre_banco_tarjeta = $numero_tarjeta = $cvv = $tipo_tarjeta = null;
    $nombre_banco_clabe = $clave_interbancaria = null;
    $paypal_email = null;

    // Validar y asignar los campos según el método de pago
    if ($metodo_pago === 'tarjeta') {
        $nombre_banco_tarjeta = trim($_POST['nombre_banco_tarjeta']);
        $numero_tarjeta = trim($_POST['numero_tarjeta']);
        $cvv = trim($_POST['cvv']);
        $tipo_tarjeta = trim($_POST['tipo_tarjeta']);

        // Validar campos
        if (empty($nombre_banco_tarjeta) || !preg_match('/^\d{16}$/', $numero_tarjeta) || !preg_match('/^\d{3,4}$/', $cvv) || !in_array($tipo_tarjeta, ['debito', 'credito'])) {
            header("Location: ../Vista/carrito.php?error=datos_tarjeta_invalidos");
            exit();
        }
    } elseif ($metodo_pago === 'clabe') {
        $nombre_banco_clabe = trim($_POST['nombre_banco_clabe']);
        $clave_interbancaria = trim($_POST['clave_interbancaria']);

        // Validar campos
        if (empty($nombre_banco_clabe) || !preg_match('/^\d{22}$/', $clave_interbancaria)) {
            header("Location: ../Vista/carrito.php?error=datos_clabe_invalidos");
            exit();
        }
    } elseif ($metodo_pago === 'paypal') {
        $paypal_email = trim($_POST['paypal_email']);

        // Validar campo
        if (empty($paypal_email) || !filter_var($paypal_email, FILTER_VALIDATE_EMAIL)) {
            header("Location: ..Vista/carrito.php?error=datos_paypal_invalido");
            exit();
        }
    }

    // Iniciar transacción
    $conn->begin_transaction();

    try {
        // Obtener el carrito del usuario
        $sql_carrito = "SELECT id_carrito FROM carritocompras WHERE id_usuario = ?";
        $stmt_carrito = $conn->prepare($sql_carrito);
        $stmt_carrito->bind_param("i", $userID);
        $stmt_carrito->execute();
        $result_carrito = $stmt_carrito->get_result();

        if ($result_carrito->num_rows == 0) {
            throw new Exception("Carrito no encontrado.");
        }

        $carrito = $result_carrito->fetch_assoc();
        $id_carrito = intval($carrito['id_carrito']);
        $stmt_carrito->close();

        // Obtener los productos en el carrito
        $sql_productos = "SELECT p.id_producto, p.nombre_producto, p.precio, cp.cantidad, p.id_vendedor
                          FROM carritoproductos cp
                          INNER JOIN productos p ON cp.id_producto = p.id_producto
                          WHERE cp.id_carrito = ?";
        $stmt_productos = $conn->prepare($sql_productos);
        $stmt_productos->bind_param("i", $id_carrito);
        $stmt_productos->execute();
        $result_productos = $stmt_productos->get_result();

        if ($result_productos->num_rows == 0) {
            throw new Exception("El carrito está vacío.");
        }

        $productos = [];
        while ($producto = $result_productos->fetch_assoc()) {
            $productos[] = $producto;
        }
        $stmt_productos->close();

        // Insertar en la tabla pedidos
        $fecha_pedido = date('Y-m-d H:i:s');
        $sql_pedido = "INSERT INTO pedidos (id_usuario, fecha_pedido, metodo_pago, total) VALUES (?, ?, ?, ?)";
        $stmt_pedido = $conn->prepare($sql_pedido);
        $stmt_pedido->bind_param("issi", $userID, $fecha_pedido, $metodo_pago, $total);
        if (!$stmt_pedido->execute()) {
            throw new Exception("Error al insertar el pedido.");
        }
        $id_pedido = $stmt_pedido->insert_id;
        $stmt_pedido->close();

        // Insertar en la tabla pedidosproductos
        $sql_pedidosproductos = "INSERT INTO pedidosproductos (id_pedido, id_producto, cantidad, precio_unitario) VALUES (?, ?, ?, ?)";
        $stmt_pedidosproductos = $conn->prepare($sql_pedidosproductos);
        foreach ($productos as $producto) {
            $id_producto = intval($producto['id_producto']);
            $cantidad = intval($producto['cantidad']);
            $precio_unitario = floatval($producto['precio']);
            $stmt_pedidosproductos->bind_param("iiid", $id_pedido, $id_producto, $cantidad, $precio_unitario);
            if (!$stmt_pedidosproductos->execute()) {
                throw new Exception("Error al insertar pedidosproductos.");
            }
        }
        $stmt_pedidosproductos->close();

        // Insertar en la tabla ventas
        $sql_venta = "INSERT INTO ventas (id_vendedor, id_pedido, fecha_venta, total_venta) VALUES (?, ?, ?, ?)";
        $stmt_venta = $conn->prepare($sql_venta);
        foreach ($productos as $producto) {
            $id_vendedor = intval($producto['id_vendedor']);
            $id_pedido = intval($id_pedido);
            $fecha_venta = date('Y-m-d H:i:s');
            $total_venta = floatval($producto['precio']) * intval($producto['cantidad']);

            $stmt_venta->bind_param("iisd", $id_vendedor, $id_pedido, $fecha_venta, $total_venta);
            if (!$stmt_venta->execute()) {
                throw new Exception("Error al insertar ventas.");
            }
        }
        $stmt_venta->close();

        // Vaciar el carrito después de la compra
        $sql_vaciar_carrito = "DELETE FROM carritoproductos WHERE id_carrito = ?";
        $stmt_vaciar = $conn->prepare($sql_vaciar_carrito);
        $stmt_vaciar->bind_param("i", $id_carrito);
        if (!$stmt_vaciar->execute()) {
            throw new Exception("Error al vaciar el carrito.");
        }
        $stmt_vaciar->close();

        // Confirmar la transacción
        $conn->commit();

        // Redirigir con mensaje de éxito
        header("Location: ../Vista/carrito.php?success=compra_realizada");
        exit();
    } catch (Exception $e) {
        // Revertir la transacción en caso de error
        $conn->rollback();

        // Redirigir con mensaje de error
        header("Location: ../Vista/carrito.php?error=" . urlencode($e->getMessage()));
        exit();
    }
} else {
    header("Location: ../Vista/carrito.php");
    exit();
}
?>
