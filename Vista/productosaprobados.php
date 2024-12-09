<?php
session_start();
require_once '../Modelo/Conexion.php';

// Verificar si el usuario ha iniciado sesión y es vendedor
if (!isset($_SESSION['user']) || strtolower($_SESSION['user']['rol']) !== 'vendedor') {
    header("Location: login.php");
    exit();
}

// Obtener el nombre de usuario y rol
$username = $_SESSION['user']['User'];
$role = strtolower($_SESSION['user']['rol']);
$id_vendedor = $_SESSION['user']['IDUsuario'];

// Crear una instancia de la conexión
$conexion = new Conexion();
$conn = $conexion->conn;

// Obtener productos aprobados o escondidos del vendedor
$sql_productos = "SELECT * FROM productos WHERE aprobado IN (1,2) AND id_vendedor = ?";
$stmt_productos = $conn->prepare($sql_productos);
$stmt_productos->bind_param("i", $id_vendedor);
$stmt_productos->execute();
$result_productos = $stmt_productos->get_result();

?>
<!DOCTYPE html>
<html lang="en">
<!--Ventana principal-->
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Productos</title>
    <link rel="stylesheet" href="productosaprobados.css">
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
                <a href="productosaprobados.php">Productos Aprobados</a>
                <a href="productoseliminados.php">Productos No Aprobados</a>
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
            <h1>Panel de Vendedor</h1>
            <div class="container">
                <!-- Gestión de Productos -->
                <div class="panel">
                    <h2>Mis Publicaciones</h2>
                    <?php if ($result_productos->num_rows > 0): ?>
                        <?php while ($producto = $result_productos->fetch_assoc()): ?>
                            <div class="item">
                                <!-- Información del producto -->
                                <div class="producto-info">
                                    <h3><?php echo htmlspecialchars($producto['nombre_producto']); ?></h3>
                                    <p><?php echo htmlspecialchars($producto['descripcion']); ?></p>
                                    <p><strong>Precio:</strong> <?php echo htmlspecialchars($producto['precio']); ?></p>
                                    <p><strong>Cantidad Disponible:</strong> <?php echo htmlspecialchars($producto['cantidad_disponible']); ?></p>
                                    <?php if ($producto['aprobado'] == 1): ?>
                                        <p><strong>Estado:</strong> Mostrando</p>
                                    <?php elseif ($producto['aprobado'] == 2): ?>
                                        <p><strong>Estado:</strong> Oculto</p>
                                    <?php endif; ?>
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
                                    <form action="../Controlador/vendedor_producto_acciones.php" method="post">
                                        <input type="hidden" name="id_producto" value="<?php echo $producto['id_producto']; ?>">
                                        <button type="submit" name="accion" value="mostrar" class="approve">Mostrar</button>
                                        <button type="submit" name="accion" value="esconder" class="reject">Esconder</button>
                                        <button type="submit" name="accion" value="actualizar" class="update">Actualizar</button>
                                        <button type="submit" name="accion" value="eliminar" class="delete">Eliminar</button>
                                    </form>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <p>No hay publicaciones para mostrar.</p>
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
