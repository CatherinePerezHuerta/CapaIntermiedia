<?php
session_start();
require_once '../Modelo/Conexion.php'; 


// Check if the user is logged in
$loggedIn = isset($_SESSION['user']);
if ($loggedIn) {
    // Get the username
    $username = $_SESSION['user']['User']; // Adjust 'User' if necessary
}
// Verificar si el usuario ha iniciado sesión
//if (!isset($_SESSION['user'])) {
 //   // Si no ha iniciado sesión, redirigir al login
//    header("Location: login.php");
 //   exit();
//}

// Obtener el nombre de usuario
//$username = $_SESSION['user']['User']; // Ajusta 'User' si es necesario
// Crear una instancia de la conexión
$conexion = new Conexion();
$conn = $conexion->conn;

// Obtener las publicaciones recientes (por ejemplo, los últimos 10 productos aprobados)
$sql_publicaciones = "SELECT * FROM productos WHERE aprobado = 1 ORDER BY fecha_publicacion DESC LIMIT 10";
$result_publicaciones = $conn->query($sql_publicaciones);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Roboto:ital,wght@0,100;0,300;0,400;0,500;0,700;0,900;1,100;1,300;1,400;1,500;1,700;1,900&display=swap"
        rel="stylesheet">
    <title>Mercado Vivo</title>
    <link rel="stylesheet" href="main.css">
    <link rel="stylesheet" href="header.css">
    <link rel="stylesheet" href="footer.css">
</head>

<body>
    <header class="topnav">
        <a class="imagen" href="main.html">
            <img src="./Imágenes/LOGOTIPO.png" alt=".">
        </a>
        <div class="busqueda">   
            <!--a class="active" href="main.html">Inicio</a>
            <a href="publicar.html">Publicar</a-->
            <form class="search-form" action="busqueda.php" method="GET">
                <input type="text" name="search" placeholder="Buscar...">
                <button type="submit">Buscar</button>
            </form>
        </div>
        <div class="boton">
    <?php if ($loggedIn): ?>
        <a href="usuario.php"><?php echo htmlspecialchars($username); ?></a>
        <a href="../Controlador/logout.php">Salir</a>
    <?php else: ?>
        <a href="login.php">Iniciar sesión</a>
        <a href="register.php">Registrarse</a>
    <?php endif; ?>
</div>

    </header>
    <main>
        <section class="bienvenida">
            <h1>Encuentra lo que necesitas, vive lo que deseas.</h1>
        </section>
        <section class="dashboard">
            <h2>Más recientes</h2>
            <div class="cards">
                <?php if ($result_publicaciones->num_rows > 0): ?>
                    <?php while ($publicacion = $result_publicaciones->fetch_assoc()): ?>
                        <?php
                        // Obtener imágenes
                        $sql_imagenes = "SELECT ruta_imagen FROM imagenesproducto WHERE id_producto = ? LIMIT 1";
                        $stmt_imagenes = $conn->prepare($sql_imagenes);
                        $stmt_imagenes->bind_param("i", $publicacion['id_producto']);
                        $stmt_imagenes->execute();
                        $result_imagenes = $stmt_imagenes->get_result();
                        $imagen = $result_imagenes->fetch_assoc();
                        ?>
                        <a class="card" href="publicacion.php?id=<?php echo $publicacion['id_producto']; ?>">
                            <section class="image">
                                <?php if ($imagen): ?>
                                    <img src="<?php echo '../' . htmlspecialchars($imagen['ruta_imagen']); ?>" alt="Imagen del producto">
                                <?php else: ?>
                                    <img src="../ruta/por/defecto.jpg" alt="Imagen por defecto">
                                <?php endif; ?>
                            </section>
                            <section class="descripcion">
                                <span><?php echo htmlspecialchars($publicacion['nombre_producto']); ?></span>
                                <span class="price">$<?php echo htmlspecialchars($publicacion['precio']); ?></span>
                            </section>
                        </a>
                    <?php endwhile; ?>
                <?php else: ?>
                    <p>No hay publicaciones recientes.</p>
                <?php endif; ?>
            </div>
        </section>
    </main>
    <footer>
        <p>2024 Mercado Vivo. Todos los derechos reservados. Israel&Catherine Co. Property.</p>
    </footer>
</body>

</html>