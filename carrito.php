<?php
session_start();
require_once '../Modelo/Conexion.php';

// Verificar si el usuario ha iniciado sesión
if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit();
}

// Obtener el nombre de usuario y rol
$userID = $_SESSION['user']['IDUsuario'];
$username = $_SESSION['user']['User'];
$role = strtolower($_SESSION['user']['rol']);

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
    $productos_carrito = [];
    $tiene_domicilio = false;
} else {
    $carrito = $result_carrito->fetch_assoc();
    $id_carrito = intval($carrito['id_carrito']);

    // Verificar si el usuario tiene un domicilio registrado
    $sql_domicilio = "SELECT * FROM domicilio WHERE id_usuario = ?";
    $stmt_domicilio = $conn->prepare($sql_domicilio);
    $stmt_domicilio->bind_param("i", $userID);
    $stmt_domicilio->execute();
    $result_domicilio = $stmt_domicilio->get_result();

    $tiene_domicilio = ($result_domicilio->num_rows > 0);
    $stmt_domicilio->close();

    // Obtener los productos en el carrito
    $sql_productos = "SELECT p.id_producto, p.nombre_producto, p.precio, cp.cantidad
                      FROM carritoproductos cp
                      INNER JOIN productos p ON cp.id_producto = p.id_producto
                      WHERE cp.id_carrito = ?";
    $stmt_productos = $conn->prepare($sql_productos);
    $stmt_productos->bind_param("i", $id_carrito);
    $stmt_productos->execute();
    $result_productos = $stmt_productos->get_result();
    $productos_carrito = [];

    while ($producto = $result_productos->fetch_assoc()) {
        // Obtener una sola imagen asociada al producto
        $sql_imagen = "SELECT ruta_imagen FROM imagenesproducto WHERE id_producto = ? LIMIT 1";
        $stmt_imagen = $conn->prepare($sql_imagen);
        $stmt_imagen->bind_param("i", $producto['id_producto']);
        $stmt_imagen->execute();
        $result_imagen = $stmt_imagen->get_result();
        $imagen = $result_imagen->fetch_assoc();

        // Asignar la imagen al producto
        $producto['ruta_imagen'] = $imagen ? $imagen['ruta_imagen'] : null;

        $productos_carrito[] = $producto;
        $stmt_imagen->close();
    }
    $stmt_productos->close();
}

$stmt_carrito->close();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Carrito de Compras - Mercado Vivo</title>
    <link rel="stylesheet" href="carrito.css">
    <link rel="stylesheet" href="header.css">
    <link rel="stylesheet" href="footer.css">
</head>

<body>
    <header class="topnav">
        <a class="imagen" href="main.php">
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
                <h2>Carrito de Compras</h2>

                <!-- Mensajes de Éxito y Error -->
                <?php if (isset($_GET['success']) && $_GET['success'] == 'compra_realizada'): ?>
                    <div class="mensaje-exito">¡Compra realizada exitosamente!</div>
                <?php endif; ?>

                <?php if (isset($_GET['error'])): ?>
                    <div class="mensaje-error"><?php echo htmlspecialchars($_GET['error']); ?></div>
                <?php endif; ?>

                <?php if (isset($_GET['success']) && $_GET['success'] == 'producto_agregado'): ?>
                    <div class="mensaje-exito">Producto agregado al carrito correctamente.</div>
                <?php endif; ?>

                <?php if (!empty($productos_carrito)): ?>
                    <?php foreach ($productos_carrito as $producto): ?>
                        <div class="card">
                            <section class="image">
                                <?php if (!empty($producto['ruta_imagen'])): ?>
                                    <img src="<?php echo htmlspecialchars('../' . $producto['ruta_imagen']); ?>" alt="<?php echo htmlspecialchars($producto['nombre_producto']); ?>">
                                <?php else: ?>
                                    <img src="../ruta/imagen_por_defecto.jpg" alt="No hay imagen">
                                <?php endif; ?>
                            </section>
                            <section class="descripcion">
                                <span><?php echo htmlspecialchars($producto['nombre_producto']); ?></span>
                                <span>Precio: $<?php echo number_format($producto['precio'], 2); ?></span>
                                <span>Cantidad: <?php echo intval($producto['cantidad']); ?></span>
                                <span>Subtotal: $<?php echo number_format($producto['precio'] * $producto['cantidad'], 2); ?></span>
                                <form action="../Controlador/eliminar_del_carrito.php" method="POST">
                                    <input type="hidden" name="id_producto" value="<?php echo intval($producto['id_producto']); ?>">
                                    <button type="submit" class="btn-eliminar">Eliminar</button>
                                </form>
                            </section>
                        </div>
                    <?php endforeach; ?>
                    <div class="total">
                        <?php
                        $total = 0;
                        foreach ($productos_carrito as $producto) {
                            $total += $producto['precio'] * $producto['cantidad'];
                        }
                        ?>
                        <h3>Total: $<?php echo number_format($total, 2); ?></h3>
                    </div>
                    <div class="boton-comprar-container">
                        <?php if ($tiene_domicilio): ?>
                            <button type="button" class="boton-comprar" onclick="abrirModal()">Comprar Todo</button>
                        <?php else: ?>
                            <button type="button" class="boton-comprar" disabled>No tiene domicilio</button>
                        <?php endif; ?>
                    </div>

                    <!-- Ventana Modal -->
                    <div id="modalPago" class="modal">
                        <div class="modal-content">
                            <span class="close" onclick="cerrarModal()">&times;</span>
                            <h2>Selecciona Método de Pago</h2>
                            <form id="formPago" action="../Controlador/procesar_compra.php" method="POST">
                                <div class="form-group">
                                    <label for="metodo_pago">Método de Pago:</label>
                                    <select id="metodo_pago" name="metodo_pago" required onchange="mostrarCampos()">
                                        <option value="">--Selecciona--</option>
                                        <option value="tarjeta">Tarjeta</option>
                                        <option value="clabe">Clave Interbancaria</option>
                                        <option value="paypal">PayPal</option>
                                    </select>
                                </div>
                                
                                <!-- Campos para Tarjeta -->
                                <div id="campos_tarjeta" class="campos-pago" style="display:none;">
                                    <div class="form-group">
                                        <label for="nombre_banco_tarjeta">Nombre del Banco:</label>
                                        <input type="text" id="nombre_banco_tarjeta" name="nombre_banco_tarjeta">
                                    </div>
                                    <div class="form-group">
                                        <label for="numero_tarjeta">Número de Tarjeta:</label>
                                        <input type="text" id="numero_tarjeta" name="numero_tarjeta" maxlength="16" pattern="\d{16}" title="Debe tener 16 dígitos">
                                    </div>
                                    <div class="form-group">
                                        <label for="cvv">CVV:</label>
                                        <input type="text" id="cvv" name="cvv" maxlength="4" pattern="\d{3,4}" title="Debe tener 3 o 4 dígitos">
                                    </div>
                                    <div class="form-group">
                                        <label for="tipo_tarjeta">Tipo de Tarjeta:</label>
                                        <select id="tipo_tarjeta" name="tipo_tarjeta">
                                            <option value="">--Selecciona--</option>
                                            <option value="debito">Débito</option>
                                            <option value="credito">Crédito</option>
                                        </select>
                                    </div>
                                </div>
                                
                                <!-- Campos para Clave Interbancaria -->
                                <div id="campos_clabe" class="campos-pago" style="display:none;">
                                    <div class="form-group">
                                        <label for="nombre_banco_clabe">Nombre del Banco:</label>
                                        <input type="text" id="nombre_banco_clabe" name="nombre_banco_clabe">
                                    </div>
                                    <div class="form-group">
                                        <label for="clave_interbancaria">Clave Interbancaria:</label>
                                        <input type="text" id="clave_interbancaria" name="clave_interbancaria" maxlength="22" pattern="\d{22}" title="Debe tener 22 dígitos">
                                    </div>
                                </div>
                                
                                <!-- Campos para PayPal -->
                                <div id="campos_paypal" class="campos-pago" style="display:none;">
                                    <div class="form-group">
                                        <label for="paypal_email">Correo de PayPal:</label>
                                        <input type="email" id="paypal_email" name="paypal_email">
                                    </div>
                                </div>
                                
                                <div class="form-group">
                                    <input type="hidden" name="total" value="<?php echo number_format($total, 2, '.', ''); ?>">
                                    <button type="submit" class="btn-pagar">Pagar</button>
                                </div>
                            </form>
                        </div>
                    </div>

                <?php else: ?>
                    <p>Tu carrito está vacío.</p>
                <?php endif; ?>
            </div>
        </main>
    </section>
    <footer>
        <p>2024 Mercado Vivo. Todos los derechos reservados. Israel&Catherine Co. Property.</p>
    </footer>

    <!-- Agregar el script JavaScript al final del body -->
    <script>
        // Función para abrir la modal
        function abrirModal() {
            document.getElementById('modalPago').style.display = 'block';
        }

        // Función para cerrar la modal
        function cerrarModal() {
            document.getElementById('modalPago').style.display = 'none';
        }

        // Cerrar la modal al hacer clic fuera del contenido
        window.onclick = function(event) {
            var modal = document.getElementById('modalPago');
            if (event.target == modal) {
                modal.style.display = 'none';
            }
        }

        // Función para mostrar los campos correspondientes según el método de pago seleccionado
        function mostrarCampos() {
            var metodo = document.getElementById('metodo_pago').value;
            var camposTarjeta = document.getElementById('campos_tarjeta');
            var camposClabe = document.getElementById('campos_clabe');
            var camposPaypal = document.getElementById('campos_paypal');

            // Ocultar todos los campos
            camposTarjeta.style.display = 'none';
            camposClabe.style.display = 'none';
            camposPaypal.style.display = 'none';

            // Eliminar el atributo 'required' de todos los campos
            var inputs = document.querySelectorAll('.campos-pago input, .campos-pago select');
            inputs.forEach(function(input) {
                input.removeAttribute('required');
            });

            // Mostrar el campo correspondiente y agregar 'required' a sus inputs
            if (metodo === 'tarjeta') {
                camposTarjeta.style.display = 'block';
                var inputsTarjeta = camposTarjeta.querySelectorAll('input, select');
                inputsTarjeta.forEach(function(input) {
                    input.setAttribute('required', 'required');
                });
            } else if (metodo === 'clabe') {
                camposClabe.style.display = 'block';
                var inputsClabe = camposClabe.querySelectorAll('input, select');
                inputsClabe.forEach(function(input) {
                    input.setAttribute('required', 'required');
                });
            } else if (metodo === 'paypal') {
                camposPaypal.style.display = 'block';
                var inputsPaypal = camposPaypal.querySelectorAll('input, select');
                inputsPaypal.forEach(function(input) {
                    input.setAttribute('required', 'required');
                });
            }
        }
    </script>
</body>
</html>
