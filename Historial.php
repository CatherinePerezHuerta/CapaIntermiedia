<?php
session_start();
require_once '../Modelo/Conexion.php';

// Verificar si el usuario ha iniciado sesión
if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit();
}

// Obtener el ID del usuario, nombre de usuario y rol
$userID = $_SESSION['user']['IDUsuario'];
$username = $_SESSION['user']['User'];
$role = strtolower($_SESSION['user']['rol']);

// Crear conexión
$conexion = new Conexion();
$conn = $conexion->conn;

// Obtener los pedidos del usuario
$sql_pedidos = "SELECT id_pedido, fecha_pedido, metodo_pago, total FROM pedidos WHERE id_usuario = ? ORDER BY fecha_pedido DESC";
$stmt_pedidos = $conn->prepare($sql_pedidos);
if (!$stmt_pedidos) {
    die("Error en la preparación de la consulta de pedidos: " . $conn->error);
}
$stmt_pedidos->bind_param("i", $userID);
$stmt_pedidos->execute();
$result_pedidos = $stmt_pedidos->get_result();

$pedidos = [];
while ($pedido = $result_pedidos->fetch_assoc()) {
    $pedidos[] = $pedido;
}
$stmt_pedidos->close();

// Obtener los detalles de cada pedido
$detalles_pedidos = [];

if (!empty($pedidos)) {
    // Preparar una consulta para obtener los detalles de los productos por cada pedido
    $sql_detalles = "
        SELECT 
            p.nombre_producto, 
            ip.ruta_imagen, 
            pp.cantidad, 
            pp.precio_unitario
        FROM 
            pedidosproductos pp
        INNER JOIN 
            productos p ON pp.id_producto = p.id_producto
        LEFT JOIN 
            imagenesproducto ip ON p.id_producto = ip.id_producto
        WHERE 
            pp.id_pedido = ?
        GROUP BY 
            p.id_producto
    ";
    $stmt_detalles = $conn->prepare($sql_detalles);
    if (!$stmt_detalles) {
        die("Error en la preparación de la consulta de detalles: " . $conn->error);
    }

    foreach ($pedidos as $pedido) {
        $id_pedido = $pedido['id_pedido'];
        $stmt_detalles->bind_param("i", $id_pedido);
        $stmt_detalles->execute();
        $result_detalles = $stmt_detalles->get_result();

        $productos = [];
        while ($row = $result_detalles->fetch_assoc()) {
            // Si no hay imagen asociada, usa una imagen por defecto
            if (empty($row['ruta_imagen'])) {
                $row['ruta_imagen'] = 'uploads/productos/imagen_por_defecto.jpg'; // Ajusta esta ruta según corresponda
            } else {
                // Asegúrate de que la ruta de la imagen es correcta
                $row['ruta_imagen'] = '../' . $row['ruta_imagen'];
            }
            $productos[] = $row;
        }

        $detalles_pedidos[$id_pedido] = $productos;
    }

    $stmt_detalles->close();
}

//$conn->close(); // Opcional: Solo si no usas el destructor para cerrar

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Historial de Compras - Mercado Vivo</title>
    <link rel="stylesheet" href="Historial.css">
    <link rel="stylesheet" href="header.css">
    <link rel="stylesheet" href="footer.css">
    <style>
        /* Estilos para la Tabla */
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        table th, table td {
            padding: 12px;
            border: 1px solid #ddd;
            text-align: left;
        }

        table th {
            background-color: #f2f2f2;
        }

        /* Estilos para los Detalles Expandidos */
        .detalles {
            display: none;
            background-color: #f9f9f9;
        }

        .detalles td {
            border: none;
            padding: 10px;
        }

        /* Estilos para los Productos en los Detalles */
        .producto {
            display: flex;
            align-items: center;
            margin-bottom: 15px;
            border-bottom: 1px solid #ddd;
            padding-bottom: 10px;
        }

        .producto img {
            width: 100px;
            height: 100px;
            object-fit: cover;
            margin-right: 20px;
            border: 1px solid #ccc;
            border-radius: 5px;
        }

        .producto-info {
            display: flex;
            flex-direction: column;
        }

        .producto-info span {
            margin-bottom: 5px;
        }

        /* Estilos para Mensajes de Éxito y Error */
        .mensaje-exito {
            background-color: #d4edda;
            color: #155724;
            padding: 15px;
            margin-bottom: 20px;
            border: 1px solid #c3e6cb;
            border-radius: 5px;
        }

        .mensaje-error {
            background-color: #f8d7da;
            color: #721c24;
            padding: 15px;
            margin-bottom: 20px;
            border: 1px solid #f5c6cb;
            border-radius: 5px;
        }

        /* Estilos para el Botón */
        .btn-detalles {
            background-color: #007BFF;
            color: white;
            padding: 8px 12px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }

        .btn-detalles:hover {
            background-color: #0056b3;
        }

        /* Estilos para la Flecha de Expansión */
        .arrow {
            cursor: pointer;
            transition: transform 0.3s;
        }

        .arrow.expanded {
            transform: rotate(90deg);
        }
    </style>
</head>

<body>
    <header class="topnav">
        <a class="imagen" href="mainUser.php">
            <img src="./Imágenes/LOGOTIPO.png" alt="Logo">
        </a>
        <div class="busqueda">   
            <form class="search-form" action="./busqueda.php" method="GET">
                <input type="text" name="search" placeholder="Buscar..." required>
                <button type="submit">Buscar</button>
            </form>
        </div>
        <div class="boton">
            <a href="usuario.php"><?php echo htmlspecialchars($username); ?></a>
            <a href="../Controlador/logout.php">Salir</a>
        </div>
    </header>
    <section class="pagina">
        <aside>
            <div class="sidebar">
                <a href="mainUser.php">Inicio</a>
                <?php if ($role == 'vendedor'): ?>
                    <a href="publicar.php">Publicar</a>
                    <a href="CrearCategorias.php">Crear Categoría</a>
                <?php endif; ?>

                <?php if ($role == 'administrador'): ?>
                    <a href="GestionProductos.php">Gestión Productos</a>
                <?php endif; ?>

                <?php if ($role == 'comprador'): ?>
                    <a href="crearlistas.php">Crear Lista</a>
                    <a href="listas.php">Listas</a>
                    <a href="Carrito.php">Carrito</a>
                    <a href="Historial.php">Historial</a>
                <?php endif; ?>
                <a href="messages.php">Mensajes</a>
            </div>
        </aside>
        <main>
            <div class="cards">
                <h2>Historial de Compras</h2>

                <!-- Mensajes de Éxito y Error -->
                <?php if (isset($_GET['success']) && $_GET['success'] == 'compra_realizada'): ?>
                    <div class="mensaje-exito">¡Compra realizada exitosamente!</div>
                <?php endif; ?>

                <?php if (isset($_GET['error'])): ?>
                    <div class="mensaje-error"><?php echo htmlspecialchars($_GET['error']); ?></div>
                <?php endif; ?>

                <?php if (!empty($pedidos)): ?>
                    <table>
                        <thead>
                            <tr>
                                <th></th> <!-- Columna para la flecha -->
                                <th>ID Pedido</th>
                                <th>Fecha del Pedido</th>
                                <th>Método de Pago</th>
                                <th>Total</th>
                                <th>Acción</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($pedidos as $pedido): ?>
                                <tr>
                                    <td>
                                        <span class="arrow" data-pedido-id="<?php echo htmlspecialchars($pedido['id_pedido']); ?>">&#9654;</span>
                                    </td>
                                    <td><?php echo htmlspecialchars($pedido['id_pedido']); ?></td>
                                    <td><?php echo htmlspecialchars($pedido['fecha_pedido']); ?></td>
                                    <td><?php echo htmlspecialchars(ucfirst($pedido['metodo_pago'])); ?></td>
                                    <td>$<?php echo number_format($pedido['total'], 2); ?></td>
                                    <td>
                                        <!-- Puedes agregar más acciones aquí si lo deseas -->
                                        <!-- <button class="btn-detalles">Ver Detalles</button> -->
                                    </td>
                                </tr>
                                <tr class="detalles" id="detalles-<?php echo htmlspecialchars($pedido['id_pedido']); ?>">
                                    <td colspan="6">
                                        <?php if (isset($detalles_pedidos[$pedido['id_pedido']]) && !empty($detalles_pedidos[$pedido['id_pedido']])): ?>
                                            <?php foreach ($detalles_pedidos[$pedido['id_pedido']] as $producto): ?>
                                                <div class="producto">
                                                    <img src="<?php echo htmlspecialchars($producto['ruta_imagen']); ?>" alt="<?php echo htmlspecialchars($producto['nombre_producto']); ?>">
                                                    <div class="producto-info">
                                                        <span><strong>Producto:</strong> <?php echo htmlspecialchars($producto['nombre_producto']); ?></span>
                                                        <span><strong>Cantidad:</strong> <?php echo htmlspecialchars($producto['cantidad']); ?></span>
                                                        <span><strong>Precio Unitario:</strong> $<?php echo number_format($producto['precio_unitario'], 2); ?></span>
                                                    </div>
                                                </div>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <p>No hay detalles disponibles para este pedido.</p>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>

                <?php else: ?>
                    <p>No has realizado ninguna compra aún.</p>
                <?php endif; ?>
            </div>
        </main>
    </section>
    <footer>
        <p>2024 Mercado Vivo. Todos los derechos reservados. Israel & Catherine Co. Property.</p>
    </footer>

    <!-- Script JavaScript para manejar la expansión de detalles -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var arrows = document.querySelectorAll('.arrow');
            arrows.forEach(function(arrow) {
                arrow.addEventListener('click', function() {
                    var pedidoId = this.getAttribute('data-pedido-id');
                    var detallesRow = document.getElementById('detalles-' + pedidoId);
                    if (detallesRow.style.display === 'table-row') {
                        detallesRow.style.display = 'none';
                        this.classList.remove('expanded');
                    } else {
                        detallesRow.style.display = 'table-row';
                        this.classList.add('expanded');
                    }
                });
            });
        });
    </script>
</body>
</html>
