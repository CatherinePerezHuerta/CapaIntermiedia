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
$sql_productos = "SELECT * FROM usuario WHERE Aprobado = 0";
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
                    <h2>Administradores Pendientes de Aprobación</h2>
                    <?php if ($result_productos->num_rows > 0): ?>
                        <?php while ($producto = $result_productos->fetch_assoc()): ?>
                            <div class="item">
                                <!-- Información del producto -->
                                  
                                <div class="producto-info">
                                    <h3><?php echo htmlspecialchars($producto['Nombre'].' '. $producto['APaterno'].' '.$producto['AMaterno']); ?></h3>
                                    <p><?php echo htmlspecialchars($producto['User']); ?></p>
                                    <p><strong>Correo:</strong> <?php echo htmlspecialchars($producto['Correo']); ?></p>
                                    
                                </div>
                                <!-- Botones de acción -->
                                <div class="acciones">
                                    <form action="../Controlador/aprobar_usuario.php" method="post">
                                        <input type="hidden" name="IDUsuario" value="<?php echo $producto['IDUsuario']; ?>">
                                        <button type="submit" name="accion" value="aprobar" class="approve">Aprobar</button>
                                       
                                        <button type="submit" name="accion" value="rechazar" class="reject">Rechazar</button>
                                    </form>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <p>No hay Administradores pendientes de aprobación.</p>
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
