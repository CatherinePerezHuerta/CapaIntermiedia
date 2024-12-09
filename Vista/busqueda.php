<?php
// busqueda.php

session_start();
require_once '../Modelo/Conexion.php'; 

// Verificar si el usuario ha iniciado sesión
$loggedIn = isset($_SESSION['user']);
if ($loggedIn) {
    // Obtener el nombre de usuario y rol
    $userID = $_SESSION['user']['IDUsuario']; // Ajusta 'IDUsuario' si es necesario
    $username = $_SESSION['user']['User']; // Ajusta 'User' si es necesario
    $role = strtolower($_SESSION['user']['rol']); // Convertir a minúsculas para consistencia
} else {
    $username = 'Invitado';
    $role = 'invitado';
}

// Obtener los parámetros de búsqueda desde GET
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$fecha_venta = isset($_GET['fecha-venta']) ? $_GET['fecha-venta'] : '';
$categoria = isset($_GET['categoria']) ? $_GET['categoria'] : '';
$producto = isset($_GET['producto']) ? trim($_GET['producto']) : '';
$calificacion = isset($_GET['calificacion']) ? intval($_GET['calificacion']) : '';
$precio = isset($_GET['precio']) ? floatval($_GET['precio']) : '';
$mes_anio = isset($_GET['mes-anio']) ? $_GET['mes-anio'] : '';
$categoria_agrupada = isset($_GET['categoria-agrupada']) ? $_GET['categoria-agrupada'] : '';

// Crear una instancia de la conexión
$conexion = new Conexion();
$conn = $conexion->conn;

// Construir la consulta SQL con filtros
$sql_publicaciones = "SELECT p.*, 
    (SELECT ruta_imagen FROM imagenesproducto ip WHERE ip.id_producto = p.id_producto LIMIT 1) AS ruta_imagen
    FROM productos p
    WHERE p.aprobado = 1";

// Array para almacenar los parámetros y tipos
$params = [];
$types = "";

// Añadir condiciones según los filtros
if (!empty($search)) {
    $sql_publicaciones .= " AND (p.nombre_producto LIKE ? OR p.descripcion LIKE ?)";
    $search_param = '%' . $search . '%';
    $params[] = $search_param;
    $params[] = $search_param;
    $types .= "ss";
}

if (!empty($categoria)) {
    $sql_publicaciones .= " AND p.id_producto IN (
        SELECT pc.id_producto FROM productoscategorias pc
        INNER JOIN categorias c ON pc.id_categoria = c.id_categoria
        WHERE c.nombre_categoria = ?
    )";
    $params[] = $categoria;
    $types .= "s";
}

if (!empty($producto)) {
    $sql_publicaciones .= " AND p.nombre_producto LIKE ?";
    $producto_param = '%' . $producto . '%';
    $params[] = $producto_param;
    $types .= "s";
}

if (!empty($calificacion)) {
    // Filtrar productos con promedio de calificaciones >= $calificacion
    $sql_publicaciones .= " AND (
        SELECT AVG(v.calificacion) FROM valoraciones v WHERE v.id_producto = p.id_producto
    ) >= ?";
    $params[] = $calificacion;
    $types .= "i";
}

if (!empty($precio)) {
    // Filtrar productos con precio <= $precio
    $sql_publicaciones .= " AND p.precio <= ?";
    $params[] = $precio;
    $types .= "d";
}

if (!empty($fecha_venta)) {
    // Filtrar productos por fecha de venta
    // Asumiendo que hay un campo 'fecha_publicacion' en 'productos'
    $fecha_formateada = date('Y-m-d H:i:s', strtotime($fecha_venta));
    $sql_publicaciones .= " AND p.fecha_publicacion = ?";
    $params[] = $fecha_formateada;
    $types .= "s";
}

if (!empty($mes_anio)) {
    // Filtrar productos por mes y año de publicación
    $sql_publicaciones .= " AND DATE_FORMAT(p.fecha_publicacion, '%Y-%m') = ?";
    $params[] = $mes_anio;
    $types .= "s";
}

if (!empty($categoria_agrupada)) {
    $sql_publicaciones .= " AND p.id_producto IN (
        SELECT pc.id_producto FROM productoscategorias pc
        INNER JOIN categorias c ON pc.id_categoria = c.id_categoria
        WHERE c.nombre_categoria = ?
    )";
    $params[] = $categoria_agrupada;
    $types .= "s";
}

// Ordenar los resultados, por ejemplo, por fecha_publicacion DESC
$sql_publicaciones .= " ORDER BY p.fecha_publicacion DESC";

// Preparar la consulta
$stmt = $conn->prepare($sql_publicaciones);
if ($stmt === false) {
    die("Error en la preparación de la consulta: " . $conn->error);
}

// Si hay parámetros, vincularlos
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}

// Ejecutar la consulta
$stmt->execute();
$result_publicaciones = $stmt->get_result();

// Obtener las publicaciones
$publicaciones = [];
while ($publicacion = $result_publicaciones->fetch_assoc()) {
    $publicaciones[] = $publicacion;
}

// Cerrar el statement
$stmt->close();
?>
<!DOCTYPE html>
<html lang="es">
<!--Ventana de búsqueda-->
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Resultados de Búsqueda - Mercado Vivo</title>
    <link rel="stylesheet" href="busqueda.css">
    <link rel="stylesheet" href="header.css">
    <link rel="stylesheet" href="footer.css">
    <style>
        /* Estilos para las tarjetas de productos */
        .cards {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
            justify-content: center;
        }

        .card {
            display: block;
            width: 250px;
            border: 1px solid #ddd;
            border-radius: 5px;
            text-decoration: none;
            color: inherit;
            overflow: hidden;
            transition: box-shadow 0.3s ease;
        }

        .card:hover {
            box-shadow: 0 0 10px rgba(0,0,0,0.2);
        }

        .card .image img {
            width: 100%;
            height: 200px;
            object-fit: cover;
        }

        .card .descripcion {
            padding: 10px;
        }

        .card .descripcion span {
            display: block;
            margin-bottom: 5px;
        }

        .card .descripcion .price {
            color: #e74c3c;
            font-weight: bold;
        }

        /* Estilos para la sección de filtros */
        .filtros {
            margin: 20px;
            padding: 20px;
            border: 1px solid #ddd;
            border-radius: 5px;
        }

        .filtros h2 {
            margin-bottom: 15px;
        }

        .filtros form {
            display: flex;
            flex-wrap: wrap;
            gap: 15px;
        }

        .filtros .filtro {
            display: flex;
            flex-direction: column;
            flex: 1 1 200px;
        }

        .filtros label {
            margin-bottom: 5px;
            font-weight: bold;
        }

        .filtros .input-filtro {
            padding: 8px;
            border: 1px solid #ccc;
            border-radius: 3px;
        }

        .filtros .boton-filtro {
            padding: 10px 20px;
            background-color: #27ae60;
            color: white;
            border: none;
            border-radius: 3px;
            cursor: pointer;
        }

        .filtros .boton-filtro:hover {
            background-color: #219150;
        }

        /* Estilos para mensajes de error y éxito */
        .mensaje-exito {
            background-color: #d4edda;
            color: #155724;
            padding: 15px;
            margin-bottom: 20px;
            border: 1px solid #c3e6cb;
            border-radius: 5px;
        }

        .mensaje-error {
            background-color: #f8d7da;
            color: #721c24;
            padding: 15px;
            margin-bottom: 20px;
            border: 1px solid #f5c6cb;
            border-radius: 5px;
        }
    </style>
</head>

<body>
    <header class="topnav">
        <a class="imagen" href="mainUser.php">
            <img src="./Imágenes/LOGOTIPO.png" alt="Logo">
        </a>
        <div class="busqueda">   
            <form class="search-form" action="busqueda.php" method="GET">
                <input type="text" name="search" placeholder="Buscar..." value="<?php echo htmlspecialchars($search); ?>">
                <button type="submit">Buscar</button>
            </form>
        </div>
        <div class="boton">
            <?php if ($loggedIn): ?>
                <a href="Usuario.php"><?php echo htmlspecialchars($username); ?></a>
                <a href="../Controlador/logout.php">Salir</a>
            <?php else: ?>
                <a href="login.php">Iniciar sesión</a>
                <a href="register.php">Registrarse</a>
            <?php endif; ?>
        </div>
    </header>
    <section class="pagina">
        <aside>
            <div class="sidebar">
                <a href="mainUser.php">Inicio</a>
                <?php if ($role == 'vendedor'): ?>
                    <a href="publicar.php">Publicar</a>
                    <a href="CrearCategorias.php">Crear Categoría</a>
                    <a href="productosaprobados.php">Productos Aprobados</a>
                    <a href="productoseliminados.php">Productos No Aprobados</a>
                <?php endif; ?>
                <?php if ($role == 'administrador'): ?>
                    <a href="GestionProductos.php">Gestión Productos</a>
                    <a href="GestionUsuarios.php">Gestión Usuarios</a>
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
            <section class="filtros">
                <h2>Filtros de Búsqueda</h2>
        
                <form class="filtros-detallada" action="busqueda.php" method="GET">
                    <!-- Incluir el parámetro de búsqueda existente -->
                    <input type="hidden" name="search" value="<?php echo htmlspecialchars($search); ?>">
                    
                    <div class="filtro">
                        <label for="fecha-venta">Fecha y Hora:</label>
                        <input type="datetime-local" id="fecha-venta" name="fecha-venta" placeholder="YYYY-MM-DD HH:MM" class="input-filtro" value="<?php echo isset($fecha_venta) ? htmlspecialchars($fecha_venta) : ''; ?>">
                    </div>
        
                    <div class="filtro">
                        <label for="categoria">Categoría:</label>
                        <select id="categoria" name="categoria" class="input-filtro">
                            <option value="">Todas</option>
                            <option value="ropa" <?php echo ($categoria == 'ropa') ? 'selected' : ''; ?>>Ropa</option>
                            <option value="accesorios" <?php echo ($categoria == 'accesorios') ? 'selected' : ''; ?>>Accesorios</option>
                            <option value="zapatos" <?php echo ($categoria == 'zapatos') ? 'selected' : ''; ?>>Zapatos</option>
                        </select>
                    </div>
        
                    <div class="filtro">
                        <label for="producto">Producto:</label>
                        <input type="text" id="producto" name="producto" placeholder="Producto" class="input-filtro" value="<?php echo htmlspecialchars($producto); ?>">
                    </div>
        
                    <div class="filtro">
                        <label for="calificacion">Calificación:</label>
                        <input type="number" id="calificacion" name="calificacion" min="1" max="10" placeholder="1-10" class="input-filtro" value="<?php echo htmlspecialchars($calificacion); ?>">
                    </div>
        
                    <div class="filtro">
                        <label for="precio">Precio máximo:</label>
                        <input type="number" id="precio" name="precio" placeholder="Precio" class="input-filtro" value="<?php echo htmlspecialchars($precio); ?>">
                    </div>
        
                    <button type="submit" class="boton-filtro">Filtrar</button>
                </form>
        
                <form class="filtros-agrupada" action="busqueda.php" method="GET">
                    <!-- Incluir el parámetro de búsqueda existente -->
                    <input type="hidden" name="search" value="<?php echo htmlspecialchars($search); ?>">
                    
                    <div class="filtro">
                        <label for="mes-anio">Mes-Año:</label>
                        <input type="month" id="mes-anio" name="mes-anio" class="input-filtro" value="<?php echo htmlspecialchars($mes_anio); ?>">
                    </div>
        
                    <div class="filtro">
                        <label for="categoria-agrupada">Categoría:</label>
                        <select id="categoria-agrupada" name="categoria-agrupada" class="input-filtro">
                            <option value="">Todas</option>
                            <option value="ropa" <?php echo ($categoria_agrupada == 'ropa') ? 'selected' : ''; ?>>Ropa</option>
                            <option value="accesorios" <?php echo ($categoria_agrupada == 'accesorios') ? 'selected' : ''; ?>>Accesorios</option>
                            <option value="zapatos" <?php echo ($categoria_agrupada == 'zapatos') ? 'selected' : ''; ?>>Zapatos</option>
                        </select>
                    </div>
        
                    <button type="submit" class="boton-filtro">Agrupar</button>
                </form>
            </section>
        
            <section class="dashboard">
                <h2>Resultados de la Búsqueda</h2>
                <div class="cards">
                    <?php if (!empty($publicaciones)): ?>
                        <?php foreach ($publicaciones as $publicacion): ?>
                            <a class="card" href="publicacion.php?id=<?php echo $publicacion['id_producto']; ?>">
                                <section class="image">
                                    <?php if ($publicacion['ruta_imagen']): ?>
                                        <img src="<?php echo htmlspecialchars($publicacion['ruta_imagen']); ?>" alt="Imagen del producto">
                                    <?php else: ?>
                                        <img src="../ruta/por/defecto.jpg" alt="Imagen por defecto">
                                    <?php endif; ?>
                                </section>
                                <section class="descripcion">
                                    <span><?php echo htmlspecialchars($publicacion['nombre_producto']); ?></span>
                                    <span class="price">$<?php echo htmlspecialchars($publicacion['precio']); ?></span>
                                </section>
                            </a>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p>No se encontraron publicaciones que coincidan con tu búsqueda.</p>
                    <?php endif; ?>
                </div>
            </section>
        </main>
    </section>
    <footer>
        <p>2024 Mercado Vivo. Todos los derechos reservados. Israel&Catherine Co. Property.</p>
    </footer>
</body>

</html>
