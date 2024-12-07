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

// Crear una conexión a la base de datos
$conexion = new Conexion();
$conn = $conexion->conectar();

// Obtener las listas del usuario logueado
$sql = "SELECT * FROM listas WHERE id_usuario = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $id_usuario);
$stmt->execute();
$resultado = $stmt->get_result();
$listas = $resultado->fetch_all(MYSQLI_ASSOC);
$stmt->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="stylesheet" href="listas.css">
    <link rel="stylesheet" href="header.css">
    <link rel="stylesheet" href="footer.css">
    <title>Listas</title>
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
        <section class="dashboard">
    <h2>Listas</h2>
    <div class="list-container">
    <?php foreach ($listas as $lista): ?>
    <div class="list">
        <!-- Enlace alrededor de las secciones clicables -->
        <a href="productosLista.php?id_lista=<?php echo $lista['id_lista']; ?>" class="list-link">
            <section class="image">
                <?php if ($lista['imagen']): ?>
                    <img src="data:image/jpeg;base64,<?php echo base64_encode($lista['imagen']); ?>" alt="<?php echo htmlspecialchars($lista['nombre_lista']); ?>">
                <?php else: ?>
                    <img src="ruta/imagen_por_defecto.jpg" alt="No hay imagen">
                <?php endif; ?>
            </section>
            <div class="info">
                <section class="titulo">
                    <span><?php echo htmlspecialchars($lista['nombre_lista']); ?></span>
                </section>
                <section class="descripcion">
                    <span><?php echo htmlspecialchars($lista['descripcion']); ?></span>
                </section>
                <section class="estado">
                    <span>Estado: <?php echo ($lista['publico'] == 1) ? 'Público' : (($lista['publico'] == -1) ? 'Oculto' : 'Eliminado'); ?></span>
                </section>
            </div>
        </a>
        <!-- Sección de gestión fuera del enlace -->
        <section class="manage">
            <form action="../Controlador/actualizar_estado_lista.php" method="post">
                <input type="hidden" name="id_lista" value="<?php echo $lista['id_lista']; ?>">
                <button type="submit" name="accion" value="mostrar">Mostrar</button>
                <button type="submit" name="accion" value="ocultar">Ocultar</button>
                <button type="submit" name="accion" value="eliminar">Eliminar</button>
                <button type="button" onclick="window.location.href='ActualizarLista.php?id_lista=<?php echo $lista['id_lista']; ?>'">Actualizar</button>
            </form>
        </section>
    </div>
<?php endforeach; ?>


    </div>
</section>
        </main>
    </section>
    <footer>
        <p>2024 Mercado Vivo. Todos los derechos reservados. Israel&Catherine Co. Property.</p>
    </footer>
</body>
</html>
