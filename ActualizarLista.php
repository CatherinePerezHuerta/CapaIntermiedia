<?php
session_start();
require_once '../Modelo/Conexion.php';

if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit();
}

$username = $_SESSION['user']['User'];
$role = strtolower($_SESSION['user']['rol']);
$id_usuario = $_SESSION['user']['IDUsuario'];

if (!isset($_GET['id_lista'])) {
    header("Location: listas.php");
    exit();
}

$id_lista = $_GET['id_lista'];

$conexion = new Conexion();
$conn = $conexion->conectar();

$sql = "SELECT * FROM listas WHERE id_lista = ? AND id_usuario = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param('ii', $id_lista, $id_usuario);
$stmt->execute();
$resultado = $stmt->get_result();
$lista = $resultado->fetch_assoc();
$stmt->close();

if (!$lista) {
    header("Location: listas.php");
    exit();
}
?>


<!DOCTYPE html>
<html lang="en">
<!--Venatana de para subir publicación-->
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Roboto:ital,wght@0,100;0,300;0,400;0,500;0,700;0,900;1,100;1,300;1,400;1,500;1,700;1,900&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="crearlistas.css">
    <link rel="stylesheet" href="header.css">
    <link rel="stylesheet" href="footer.css">
    <title>Publicar</title>
</head>

<body>
    <header class="topnav">
        <a class="imagen" href="main.html">
            <img src="./Imágenes/LOGOTIPO.png" alt=".">
        </a>
        <div class="busqueda">   
            <!--a class="active" href="main.html">Inicio</a>
            <a href="publicar.html">Publicar</a-->
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
            <div class="container">
                <h1>Actualizar Lista</h1>
                <form action="../Controlador/actualizar_lista.php" method="post" enctype="multipart/form-data">
                    <input type="hidden" name="id_lista" value="<?php echo $lista['id_lista']; ?>">
                    <div class="form-group">
                        <label for="title">Título:</label>
                        <input type="text" id="title" name="title" value="<?php echo htmlspecialchars($lista['nombre_lista']); ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="description">Descripción:</label>
                        <input type="text" id="description" name="description" value="<?php echo htmlspecialchars($lista['descripcion']); ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="image">Imagen (opcional):</label>
                        <input type="file" id="image" name="image" accept="image/*">
                        <?php if ($lista['imagen']): ?>
                            <p>Imagen actual:</p>
                            <img src="data:image/jpeg;base64,<?php echo base64_encode($lista['imagen']); ?>" alt="<?php echo htmlspecialchars($lista['nombre_lista']); ?>" style="max-width: 200px;">
                        <?php endif; ?>
                    </div>
                    <input type="submit" value="Actualizar Lista">
                </form>
            </div>
        </main>
</section>
    <footer>
        <p>2024 Mercado Vivo. Todos los derechos reservados. Israel&Catherine Co. Property.</p>
    </footer>
</body>

</html>