<?php
session_start();
require_once '../Modelo/Conexion.php'; // Ajusta la ruta si es necesario

// Verificar si el usuario ha iniciado sesión
if (!isset($_SESSION['user'])) {
    // Si no ha iniciado sesión, redirigir al login
    header("Location: login.php");
    exit();
}

// Obtener el nombre de usuario y su rol
$username = $_SESSION['user']['User']; // Ajusta 'User' si es necesario
$role = strtolower($_SESSION['user']['rol']); // Convertir a minúsculas para consistencia
$id_usuario = $_SESSION['user']['IDUsuario']; // ID del usuario logueado

// Verificar si se ha proporcionado el ID de la lista en la URL
if (!isset($_GET['id_lista'])) {
    // Si no hay ID de lista, redirigir o mostrar un mensaje de error
    header("Location: listas.php");
    exit();
}

$id_lista = intval($_GET['id_lista']); // Asegurarse de que es un entero

// Crear una conexión a la base de datos
$conexion = new Conexion();
$conn = $conexion->conectar();

// Verificar que la lista pertenece al usuario
$sql_lista = "SELECT * FROM listas WHERE id_lista = ? AND id_usuario = ?";
$stmt_lista = $conn->prepare($sql_lista);
$stmt_lista->bind_param('ii', $id_lista, $id_usuario);
$stmt_lista->execute();
$result_lista = $stmt_lista->get_result();

if ($result_lista->num_rows == 0) {
    // La lista no existe o no pertenece al usuario
    echo "No tienes acceso a esta lista.";
    exit();
}

$lista = $result_lista->fetch_assoc();

// Obtener los productos asociados a la lista
$sql_productos = "SELECT p.*
                  FROM productos p
                  INNER JOIN listasproductos lp ON p.id_producto = lp.id_producto
                  WHERE lp.id_lista = ?";
$stmt_productos = $conn->prepare($sql_productos);
$stmt_productos->bind_param('i', $id_lista);
$stmt_productos->execute();
$result_productos = $stmt_productos->get_result();

$productos = $result_productos->fetch_all(MYSQLI_ASSOC);

?>
<!DOCTYPE html>
<html lang="en">
<!--Página para mostrar productos de una lista-->
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Productos de la Lista <?php echo htmlspecialchars($lista['nombre_lista']); ?></title>
    <link rel="stylesheet" href="ProductosLista.css">
    <link rel="stylesheet" href="header.css">
    <link rel="stylesheet" href="footer.css">
</head>

<body>
    <header class="topnav">
        <a class="imagen" href="main.html">
            <img src="./Imágenes/LOGOTIPO.png" alt=".">
        </a>
        <div class="busqueda">   
            <form class="search-form" action="./busqueda.html" method="GET">
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
                    <a href="CrearCategorias.php">Crear Categoria</a>
                <?php endif; ?>

                <?php if ($role == 'administrador'): ?>
                    <a href="GestionProductos.php">Gestion Productos</a>
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
                <h2>Productos en la lista: <?php echo htmlspecialchars($lista['nombre_lista']); ?></h2>
                <?php if (!empty($productos)): ?>
                    <?php foreach ($productos as $producto): ?>
                        <div class="card">
                            <section class="image">
                                <?php
                                // Obtener la imagen principal del producto
                                $sql_imagen = "SELECT ruta_imagen FROM imagenesproducto WHERE id_producto = ? LIMIT 1";
                                $stmt_imagen = $conn->prepare($sql_imagen);
                                $stmt_imagen->bind_param('i', $producto['id_producto']);
                                $stmt_imagen->execute();
                                $result_imagen = $stmt_imagen->get_result();
                                if ($result_imagen->num_rows > 0) {
                                    $imagen = $result_imagen->fetch_assoc();
                                    $ruta_imagen = '../' . $imagen['ruta_imagen'];
                                } else {
                                    $ruta_imagen = 'ruta/imagen_por_defecto.jpg'; // Cambia esto a la ruta de tu imagen por defecto
                                }
                                ?>
                                <img src="<?php echo htmlspecialchars($ruta_imagen); ?>" alt="<?php echo htmlspecialchars($producto['nombre_producto']); ?>">
                            </section>
                            <section class="descripcion">
                                <span><?php echo htmlspecialchars($producto['nombre_producto']); ?></span>
                                <span>
                                    <?php if ($producto['para_cotizar'] == 0): ?>
                                        $<?php echo htmlspecialchars($producto['precio']); ?>
                                    <?php else: ?>
                                        Para Cotizar
                                    <?php endif; ?>
                                </span>
                                <p><?php echo htmlspecialchars($producto['descripcion']); ?></p>
                                <!-- Botón para eliminar el producto de la lista -->
                                <form action="../Controlador/eliminar_producto_de_lista.php" method="post">
                                    <input type="hidden" name="id_lista" value="<?php echo $id_lista; ?>">
                                    <input type="hidden" name="id_producto" value="<?php echo $producto['id_producto']; ?>">
                                    <button type="submit" class="btn-eliminar">Eliminar</button>
                                </form>
                            </section>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p>No hay productos en esta lista.</p>
                <?php endif; ?>
            </div>
        </main>
    </section>
    <footer>
        <p>2024 Mercado Vivo. Todos los derechos reservados. Israel&Catherine Co. Property.</p>
    </footer>
</body>
</html>
