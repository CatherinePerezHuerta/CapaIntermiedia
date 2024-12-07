<?php
session_start();
require_once '../Modelo/Conexion.php';

// Verificar si el usuario ha iniciado sesión y es vendedor
if (!isset($_SESSION['user']) || strtolower($_SESSION['user']['rol']) !== 'vendedor') {
    header("Location: login.php");
    exit();
}

$id_vendedor = $_SESSION['user']['IDUsuario'];

// Obtener el id del producto
if (isset($_GET['id_producto'])) {
    $id_producto = $_GET['id_producto'];
} else {
    header("Location: productosaprobados.php");
    exit();
}

// Crear una instancia de la conexión
$conexion = new Conexion();
$conn = $conexion->conn;

// Obtener los datos del producto incluyendo el id_categoria
$sql_producto = "SELECT productos.*, productoscategorias.id_categoria
                 FROM productos
                 LEFT JOIN productoscategorias ON productos.id_producto = productoscategorias.id_producto
                 WHERE productos.id_producto = ? AND productos.id_vendedor = ?";
$stmt_producto = $conn->prepare($sql_producto);
$stmt_producto->bind_param("ii", $id_producto, $id_vendedor);
$stmt_producto->execute();
$result_producto = $stmt_producto->get_result();

if ($result_producto->num_rows === 0) {
    // El producto no existe o no pertenece al vendedor
    header("Location: productosaprobados.php");
    exit();
}

$producto = $result_producto->fetch_assoc();

// Obtener el nombre de usuario y rol
$username = $_SESSION['user']['User'];
$role = strtolower($_SESSION['user']['rol']);

// Obtener las categorías
$sql_categorias = "SELECT id_categoria, nombre_categoria FROM categorias";
$result_categorias = $conn->query($sql_categorias);

$categorias = array();
if ($result_categorias->num_rows > 0) {
    while($row = $result_categorias->fetch_assoc()) {
        $categorias[] = $row;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<!--Ventana para actualizar publicación-->
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Actualizar Publicación</title>
    <link rel="stylesheet" href="publicar.css">
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
            <h1>Actualizar Publicación</h1>
            <form action="../Controlador/actualizar_publicacion.php" method="post" enctype="multipart/form-data">
                <input type="hidden" name="id_producto" value="<?php echo $producto['id_producto']; ?>">
                <!-- Nombre -->
                <div class="form-group">
                    <label for="name">Nombre:</label>
                    <input type="text" id="name" name="name" required value="<?php echo htmlspecialchars($producto['nombre_producto']); ?>">
                </div>
                
                <!-- Descripción -->
                <div class="form-group">
                    <label for="description">Descripción:</label>
                    <input type="text" id="description" name="description" required value="<?php echo htmlspecialchars($producto['descripcion']); ?>">
                </div>
                
                <!-- Imágenes (puedes subir nuevas imágenes) -->
                <div class="form-group">
                    <label for="images">Imágenes (puedes subir nuevas imágenes):</label>
                    <input type="file" id="images" name="images[]" accept="image/*" multiple>
                </div>
                
                <!-- Video (puedes subir un nuevo video) -->
                <div class="form-group">
                    <label for="video">Video (puedes subir un nuevo video):</label>
                    <input type="file" id="video" name="video" accept="video/*">
                </div>
                
                <!-- Categoría -->
                <div class="form-group">
                    <label for="category">Categoría:</label>
                    <select id="category" name="category" required>
                        <option value="">Selecciona una categoría</option>
                        <?php foreach ($categorias as $categoria): ?>
                            <option value="<?php echo htmlspecialchars($categoria['id_categoria']); ?>" <?php if ($producto['id_categoria'] == $categoria['id_categoria']) echo 'selected'; ?>>
                                <?php echo htmlspecialchars($categoria['nombre_categoria']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <!-- Cotización o Venta -->
                <div class="form-group">
                    <label for="type">Tipo:</label>
                    <select id="type" name="type" required onchange="togglePrice(this)">
                        <option value="cotizar" <?php if ($producto['para_cotizar'] == 1) echo 'selected'; ?>>Para Cotizar</option>
                        <option value="vender" <?php if ($producto['para_cotizar'] == 0) echo 'selected'; ?>>Para Vender</option>
                    </select>
                </div>
                
                <!-- Precio (solo si es para vender) -->
                <div class="form-group" id="priceField" style="<?php echo ($producto['para_cotizar'] == 0) ? 'display:block;' : 'display:none;'; ?>">
                    <label for="price">Precio:</label>
                    <input type="text" id="price" name="price" value="<?php echo htmlspecialchars($producto['precio']); ?>">
                </div>
                
                <!-- Cantidad disponible -->
                <div class="form-group">
                    <label for="quantity">Cantidad disponible:</label>
                    <input type="number" id="quantity" name="quantity" min="0" required value="<?php echo htmlspecialchars($producto['cantidad_disponible']); ?>">
                </div>
    
                <!-- Botón de envío -->
                <input type="submit" value="Actualizar">
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
