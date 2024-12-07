<?php
session_start();

// Verificar si el usuario ha iniciado sesión
if (!isset($_SESSION['user'])) {
    // Si no ha iniciado sesión, redirigir al login
    header("Location: login.php");
    exit();
}

// Obtener el nombre de usuario
$username = $_SESSION['user']['User']; // Ajusta 'User' si es necesario
$role = strtolower($_SESSION['user']['rol']); // Convertir a minúsculas para consistencia


// Obtener las categorías desde la base de datos
require_once '../Modelo/Conexion.php';
$conexion = new Conexion();
$conn = $conexion->conn;

$sql = "SELECT id_categoria, nombre_categoria FROM categorias";
$result = $conn->query($sql);

$categorias = array();
if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $categorias[] = $row;
    }
}

// Obtener mensajes de error o éxito
$error_message = '';
$success_message = '';
if (isset($_SESSION['error_message'])) {
    $error_message = $_SESSION['error_message'];
    unset($_SESSION['error_message']);
}
if (isset($_SESSION['success_message'])) {
    $success_message = $_SESSION['success_message'];
    unset($_SESSION['success_message']);
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
    <link rel="stylesheet" href="publicar.css">
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
        <div class="container">
            <h1>Subir Publicación</h1>
                <!-- Mostrar mensaje de error si existe -->
                <?php if (!empty($error_message)): ?>
                    <div class="error-message">
                        <?php echo htmlspecialchars($error_message); ?>
                    </div>
                <?php endif; ?>

                <!-- Mostrar mensaje de éxito si existe -->
                <?php if (!empty($success_message)): ?>
                    <div class="success-message">
                        <?php echo htmlspecialchars($success_message); ?>
                    </div>
                <?php endif; ?>

            <form action="../Controlador/guardar_publicacion.php" method="post" enctype="multipart/form-data">
                <!-- Nombre -->
                <div class="form-group">
                    <label for="name">Nombre:</label>
                    <input type="text" id="name" name="name" required>
                </div>
                
                <!-- Descripción -->
                <div class="form-group">
                    <label for="description">Descripción:</label>
                    <input type="text" id="description" name="description" required>
                </div>
                
                <!-- Imágenes (mínimo 3) -->
                <div class="form-group">
                    <label for="images">Imágenes (mínimo 3):</label>
                    <input type="file" id="images" name="images[]" accept="image/*" multiple required>
                    <small>Debes subir al menos 3 imágenes.</small>
                </div>
                
                <!-- Video (mínimo 1) -->
                <div class="form-group">
                    <label for="video">Video (mínimo 1):</label>
                    <input type="file" id="video" name="video" accept="video/*" required>
                    <small>Sube al menos un video.</small>
                </div>
                
                <!-- Categoría -->
                    <div class="form-group">
                        <label for="category">Categoría:</label>
                        <select id="category" name="category" required>
                            <option value="">Selecciona una categoría</option>
                            <?php foreach ($categorias as $categoria): ?>
                                <option value="<?php echo htmlspecialchars($categoria['id_categoria']); ?>">
                                    <?php echo htmlspecialchars($categoria['nombre_categoria']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                
                <!-- Cotización o Venta -->
                <div class="form-group">
                    <label for="type">Tipo:</label>
                    <select id="type" name="type" required onchange="togglePrice(this)">
                        <option value="cotizar">Para Cotizar</option>
                        <option value="vender">Para Vender</option>
                    </select>
                </div>
                
                <!-- Precio (solo si es para vender) -->
                <div class="form-group" id="priceField" style="display:none;">
                    <label for="price">Precio:</label>
                    <input type="text" id="price" name="price">
                </div>
                
                <!-- Cantidad disponible -->
                <div class="form-group">
                    <label for="quantity">Cantidad disponible:</label>
                    <input type="number" id="quantity" name="quantity" min="0" required>
                </div>
    
                <!-- Botón de envío -->
                <input type="submit" value="Subir">
            </form>
        </div>
    </main>
</section>
    <footer>
        <p>2024 Mercado Vivo. Todos los derechos reservados. Israel&Catherine Co. Property.</p>
    </footer>
</body>
<script>
        function togglePrice(select) {
        const priceField = document.getElementById('priceField');
        if (select.value === 'vender') {
            priceField.style.display = 'block';
            document.getElementById('price').required = true;
        } else {
            priceField.style.display = 'none';
            document.getElementById('price').required = false;
        }
    }
</script>
</html>