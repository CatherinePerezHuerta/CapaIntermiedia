<?php
session_start();
require_once '../Modelo/Conexion.php';

// Verificar si el usuario ha iniciado sesión y es administrador
if (!isset($_SESSION['user']) || strtolower($_SESSION['user']['rol']) !== 'administrador') {
    header("Location: login.php");
    exit();
}

// Obtener el nombre de usuario y rol
$username = $_SESSION['user']['User'];
$role = strtolower($_SESSION['user']['rol']);

// Crear una instancia de la conexión
$conexion = new Conexion();
$conn = $conexion->conn;

// Obtener productos no aprobados
$sql_productos = "SELECT * FROM productos WHERE aprobado = 0";
$result_productos = $conn->query($sql_productos);

?>
<!DOCTYPE html>
<html lang="en">
<!--Ventana principal-->
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Productos</title>
    <link rel="stylesheet" href="admin.css">
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
    <!-- Barra lateral-->
    <aside>
        <div class="sidebar">
            <a href="mainUser.php">Inicio</a>
            <?php if ($role == 'vendedor'): ?>
                <a href="publicar.php">Publicar</a>
                <a href="CrearCategorias.php">Crear Categoria</a>
            <?php endif; ?>

            <?php if ($role == 'administrador'): ?>
                <a href="GestionProductos.php">Gestion Productos</a>
                <a href="GestionUsuarios.php">Gestion Usuarios</a>
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
            <h1>Panel de Administrador</h1>
            <div class="container">
                <!-- Gestión de Productos -->
                <div class="panel">
                    <h2>Publicaciones Pendientes de Aprobación</h2>
                    <?php if ($result_productos->num_rows > 0): ?>
                        <?php while ($producto = $result_productos->fetch_assoc()): ?>
                            <div class="item">
                                <!-- Información del producto -->
                                <div class="producto-info">
                                    <h3><?php echo htmlspecialchars($producto['nombre_producto']); ?></h3>
                                    <p><?php echo htmlspecialchars($producto['descripcion']); ?></p>
                                    <p><strong>Precio:</strong> <?php echo htmlspecialchars($producto['precio']); ?></p>
                                    <p><strong>Cantidad Disponible:</strong> <?php echo htmlspecialchars($producto['cantidad_disponible']); ?></p>
                                </div>

                                <!-- Imágenes del producto -->
                                <?php
                                // Obtener imágenes
                                $sql_imagenes = "SELECT ruta_imagen FROM imagenesproducto WHERE id_producto = ?";
                                $stmt_imagenes = $conn->prepare($sql_imagenes);
                                $stmt_imagenes->bind_param("i", $producto['id_producto']);
                                $stmt_imagenes->execute();
                                $result_imagenes = $stmt_imagenes->get_result();
                                ?>
                                <div class="imagenes">
                                    <?php while ($imagen = $result_imagenes->fetch_assoc()): ?>
                                        <img src="<?php echo '../' . htmlspecialchars($imagen['ruta_imagen']); ?>" alt="Imagen del producto">
                                    <?php endwhile; ?>
                                </div>

                                <!-- Videos del producto -->
                                <?php
                                // Obtener videos
                                $sql_videos = "SELECT ruta_video FROM videosproducto WHERE id_producto = ?";
                                $stmt_videos = $conn->prepare($sql_videos);
                                $stmt_videos->bind_param("i", $producto['id_producto']);
                                $stmt_videos->execute();
                                $result_videos = $stmt_videos->get_result();
                                ?>
                                <div class="videos">
                                    <?php while ($video = $result_videos->fetch_assoc()): ?>
                                        <video controls>
                                            <source src="<?php echo '../' . htmlspecialchars($video['ruta_video']); ?>" type="video/mp4">
                                            Tu navegador no soporta la reproducción de video.
                                        </video>
                                    <?php endwhile; ?>
                                </div>

                                <!-- Botones de acción -->
                                <div class="acciones">
                                    <form action="../Controlador/aprobar_producto.php" method="post">
                                        <input type="hidden" name="id_producto" value="<?php echo $producto['id_producto']; ?>">
                                        <button type="submit" name="accion" value="aprobar" class="approve">Aprobar</button>
                                       
                                        <button type="submit" name="accion" value="rechazar" class="reject">Rechazar</button>
                                    </form>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <p>No hay publicaciones pendientes de aprobación.</p>
                    <?php endif; ?>
                </div>
            </div>
        </main>
</section>
<footer>
    <p>2024 Mercado Vivo. Todos los derechos reservados. Israel&Catherine Co. Property.</p>
</footer>
</body>
</html>
