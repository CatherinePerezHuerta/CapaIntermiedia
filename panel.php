<?php
session_start();

// Verificar si el usuario ha iniciado sesión
if (!isset($_SESSION['user'])) {
    // Si no ha iniciado sesión, redirigir al login
    header("Location: login.php");
    exit();
}
// Obtener el nombre de usuario y rol
$username = $_SESSION['user']['User']; // Ajusta 'User' si es necesario
$role = strtolower($_SESSION['user']['rol']); // Convertir a minúsculas para consistencia


?>

<!DOCTYPE html>
<html lang="es">
<!-- Página del Panel de Ventas -->
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel de Ventas</title>
    <!-- Fuentes y estilos -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Roboto:wght@100;300;400;500;700;900&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="estilosPanel.css">
</head>

<body>
    <!-- Encabezado -->
    <header class="topnav">
        <a class="imagen" href="main.html">
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

    <!-- Contenido de la página con barra lateral -->
    <section class="pagina">
        <!-- Barra lateral -->
        <aside>
            <div class="sidebar">
                <a href="mainUser.php">Inicio</a>
                <?php if ($role == 'vendedor' || $role == 'administrador'): ?>
                    <a href="publicar.php">Publicar</a>
                    <a href="CrearCategorias.php">Crear Categoría</a>
                    <a href="GestionProductos.php">Gestión de Productos</a>
                    <a href="panel.php">Panel de Ventas</a>
                <?php endif; ?>

                <?php if ($role == 'comprador' || $role == 'administrador'): ?>
                    <a href="crearlistas.php">Crear Lista</a>
                    <a href="listas.php">Listas</a>
                    <a href="Carrito.php">Carrito</a>
                    <a href="Historial.php">Historial</a>
                <?php endif; ?>
                <a href="messages.php">Mensajes</a>
            </div>
        </aside>

        <!-- Contenido principal -->
        <main>
            <!-- Resumen de Ventas y Productos -->
            <div class="dashboard">
                <h2>Panel de Control - Resumen</h2>
                <div class="resumen-cards">
                    <div class="resumen-card">
                        <h3>Total de Ventas</h3>
                        <p><?php echo $ventasTotales; ?></p>
                    </div>
                    <div class="resumen-card">
                        <h3>Productos Publicados</h3>
                        <p><?php echo $productosTotales; ?></p>
                    </div>
                </div>
            </div>

            <!-- Lista de Productos -->
            <div class="cards">
                <h2>Gestión de Productos</h2>
                <?php while($producto = $productos->fetch_assoc()): ?>
                    <div class="card">
                        <section class="image">
                            <img src="<?php echo $producto['imagen']; ?>" alt="<?php echo htmlspecialchars($producto['nombre']); ?>">
                        </section>
                        <section class="descripcion">
                            <span><?php echo htmlspecialchars($producto['nombre']); ?></span>
                            <span>$<?php echo number_format($producto['precio'], 2); ?></span>
                            <form action="eliminar_producto.php" method="POST">
                                <input type="hidden" name="producto_id" value="<?php echo $producto['id']; ?>">
                                <button type="submit" class="btn-eliminar">Eliminar</button>
                            </form>
                        </section>
                    </div>
                <?php endwhile; ?>
            </div>
        </main>
    </section>

    <!-- Footer -->
    <footer>
        <p>2024 Mercado Vivo. Todos los derechos reservados. Israel&Catherine Co. Property.</p>
    </footer>
</body>
</html>
