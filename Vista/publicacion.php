<?php
// publicacion.php

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

// Verificar si se ha proporcionado el ID del producto en la URL
if (!isset($_GET['id'])) {
    // Si no hay ID de producto, redirigir o mostrar un mensaje de error
    header("Location: main.php");
    exit();
}

$productID = intval($_GET['id']); // Asegurarse de que es un entero

// Crear una instancia de la conexión
$conexion = new Conexion();
$conn = $conexion->conectar();

// Obtener los detalles del producto
$sql_producto = "SELECT * FROM productos WHERE id_producto = ? AND aprobado = 1";
$stmt_producto = $conn->prepare($sql_producto);
$stmt_producto->bind_param("i", $productID);
$stmt_producto->execute();
$result_producto = $stmt_producto->get_result();

if ($result_producto->num_rows == 0) {
    // Producto no encontrado o no aprobado
    echo "Producto no encontrado.";
    exit();
}

$producto = $result_producto->fetch_assoc();

// Obtener las imágenes del producto
$sql_imagenes = "SELECT ruta_imagen FROM imagenesproducto WHERE id_producto = ?";
$stmt_imagenes = $conn->prepare($sql_imagenes);
$stmt_imagenes->bind_param("i", $productID);
$stmt_imagenes->execute();
$result_imagenes = $stmt_imagenes->get_result();

$imagenes = [];
while ($imagen = $result_imagenes->fetch_assoc()) {
    $imagenes[] = '../' . $imagen['ruta_imagen'];
}

// Obtener los videos del producto
$sql_videos = "SELECT ruta_video FROM videosproducto WHERE id_producto = ?";
$stmt_videos = $conn->prepare($sql_videos);
$stmt_videos->bind_param("i", $productID);
$stmt_videos->execute();
$result_videos = $stmt_videos->get_result();

$videos = [];
while ($video = $result_videos->fetch_assoc()) {
    $videos[] = '../' . $video['ruta_video'];
}

// Combinar imágenes y videos en un solo array de medios
$mediaItems = [];

// Agregar imágenes al array de medios
foreach ($imagenes as $imgSrc) {
    $mediaItems[] = ['type' => 'image', 'src' => $imgSrc];
}

// Agregar videos al array de medios
foreach ($videos as $videoSrc) {
    $mediaItems[] = ['type' => 'video', 'src' => $videoSrc];
}

// Obtener las categorías del producto
$sql_categorias = "SELECT c.nombre_categoria FROM categorias c
                   INNER JOIN productoscategorias pc ON c.id_categoria = pc.id_categoria
                   WHERE pc.id_producto = ?";
$stmt_categorias = $conn->prepare($sql_categorias);
$stmt_categorias->bind_param("i", $productID);
$stmt_categorias->execute();
$result_categorias = $stmt_categorias->get_result();

$categorias = [];
while ($categoria = $result_categorias->fetch_assoc()) {
    $categorias[] = $categoria['nombre_categoria'];
}

// Obtener las valoraciones del producto
$sql_valoraciones = "SELECT u.User, v.calificacion, v.comentario, v.fecha_valoracion FROM valoraciones v
                    INNER JOIN usuario u ON v.id_usuario = u.IDUsuario
                    WHERE v.id_producto = ?
                    ORDER BY v.fecha_valoracion DESC";
$stmt_valoraciones = $conn->prepare($sql_valoraciones);
$stmt_valoraciones->bind_param("i", $productID);
$stmt_valoraciones->execute();
$result_valoraciones = $stmt_valoraciones->get_result();

$valoraciones = [];
$sum_calificaciones = 0;
while ($valoracion = $result_valoraciones->fetch_assoc()) {
    $valoraciones[] = $valoracion;
    $sum_calificaciones += $valoracion['calificacion'];
}
$stmt_valoraciones->close();

// Calcular el promedio de las valoraciones
$promedio_valoracion = 0;
$cantidad_valoraciones = count($valoraciones);
if ($cantidad_valoraciones > 0) {
    $promedio_valoracion = $sum_calificaciones / $cantidad_valoraciones;
    // Redondear a una decimal
    $promedio_valoracion = round($promedio_valoracion, 1);
}

// Cerrar el statement de producto y la conexión
$stmt_producto->close();
// $conn->close(); // **NO** cerrar manualmente para evitar el error "object is already closed"
?>
<!DOCTYPE html>
<html lang="es">
<!--Ventana de publicación-->
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($producto['nombre_producto']); ?> - Mercado Vivo</title>
    <link rel="stylesheet" href="publicacion.css">
    <link rel="stylesheet" href="header.css">
    <link rel="stylesheet" href="footer.css">
    <style>
        /* Estilos para la Tabla */
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        table th, table td {
            padding: 12px;
            border: 1px solid #ddd;
            text-align: left;
        }

        table th {
            background-color: #f2f2f2;
        }

        /* Estilos para los Detalles Expandidos */
        .detalles {
            display: none;
            background-color: #f9f9f9;
        }

        .detalles td {
            border: none;
            padding: 10px;
        }

        /* Estilos para los Productos en los Detalles */
        .producto {
            display: flex;
            align-items: center;
            margin-bottom: 15px;
            border-bottom: 1px solid #ddd;
            padding-bottom: 10px;
        }

        .producto img {
            width: 100px;
            height: 100px;
            object-fit: cover;
            margin-right: 20px;
            border: 1px solid #ccc;
            border-radius: 5px;
        }

        .producto-info {
            display: flex;
            flex-direction: column;
        }

        .producto-info span {
            margin-bottom: 5px;
        }

        /* Estilos para Mensajes de Éxito y Error */
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

        /* Estilos para la Flecha de Expansión */
        .arrow {
            cursor: pointer;
            transition: transform 0.3s;
        }

        .arrow.expanded {
            transform: rotate(90deg);
        }

        /* Estilos para el Promedio de Valoraciones */
        .promedio-valoracion {
            font-size: 1.2em;
            margin-bottom: 10px;
        }

        /* Estilos para la Sección de Valoraciones */
        .caja-valoraciones {
            margin-top: 30px;
        }

        .valoracion {
            border: 1px solid #ddd;
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 10px;
        }

        .valoracion strong {
            display: block;
            margin-bottom: 5px;
        }

        .valoracion .fecha {
            font-size: 0.9em;
            color: #555;
            margin-bottom: 5px;
        }

        .valoracion .calificacion {
            font-weight: bold;
            color: #f39c12;
        }
    </style>
</head>

<body>
    <header class="topnav">
        <a class="imagen" href="main.php">
            <img src="./Imágenes/LOGOTIPO.png" alt=".">
        </a>
        <div class="busqueda">   
            <form class="search-form" action="./busqueda.php" method="GET">
                <input type="text" name="search" placeholder="Buscar..." required>
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
            <div class="publicacion">
                <div class="card">
                    <section class="image">
                        <?php if (!empty($mediaItems)): ?>
                            <div class="carousel-container">
                                <button class="carousel-button prev-button">&#10094;</button>
                                <div class="carousel-slide">
                                    <?php foreach ($mediaItems as $index => $media): ?>
                                        <div class="carousel-item <?php echo $index === 0 ? 'active' : ''; ?>">
                                            <?php if ($media['type'] == 'image'): ?>
                                                <img src="<?php echo htmlspecialchars($media['src']); ?>" alt="Imagen del producto">
                                            <?php elseif ($media['type'] == 'video'): ?>
                                                <video width="100%" height="100%" controls>
                                                    <source src="<?php echo htmlspecialchars($media['src']); ?>" type="video/mp4">
                                                    Tu navegador no soporta la reproducción de video.
                                                </video>
                                            <?php endif; ?>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                                <button class="carousel-button next-button">&#10095;</button>
                            </div>
                        <?php else: ?>
                            <img src="../ruta/por/defecto.jpg" alt="Imagen por defecto">
                        <?php endif; ?>
                    </section>
                    <section class="texto">
                        <div class="titulo">
                            <span><?php echo htmlspecialchars($producto['nombre_producto']); ?></span>
                        </div>
                        <div class="precio">
                            <?php if ($producto['para_cotizar'] == 0): ?>
                                <span>$<?php echo htmlspecialchars($producto['precio']); ?></span>
                            <?php else: ?>
                                <span>Para Cotizar</span>
                            <?php endif; ?>
                        </div>

                        <div class="descripcion">
                            <span><?php echo nl2br(htmlspecialchars($producto['descripcion'])); ?></span>
                        </div>
                        <!-- Mostrar el promedio de valoraciones -->
                        <div class="promedio-valoracion">
                            <?php if ($cantidad_valoraciones > 0): ?>
                                <span>Valoración Promedio: <?php echo $promedio_valoracion; ?>/10 (<?php echo $cantidad_valoraciones; ?> valoraciones)</span>
                            <?php else: ?>
                                <span>Sin valoraciones aún.</span>
                            <?php endif; ?>
                        </div>
                        <section class="categorias">
                            <?php foreach ($categorias as $categoria): ?>
                                <a href="categoria.php?nombre=<?php echo urlencode($categoria); ?>"><?php echo htmlspecialchars($categoria); ?></a>
                            <?php endforeach; ?>
                        </section>
                        <section>
                            <!-- Cantidad disponible -->
                            <label>Cantidad disponible: <?php echo htmlspecialchars($producto['cantidad_disponible']); ?></label>
                            <?php if ($producto['cantidad_disponible'] == 0): ?>
                                <small id="noStockMessage" style="color: red;">No hay disponibilidad.</small>
                            <?php endif; ?>
                        </section>
                          <!-- Botones para "Agregar al carrito", "Mensaje" y "Agregar a lista" -->
                          
                            <?php if ($producto['cantidad_disponible'] > 0): ?>
                                <?php if ($role == 'comprador'): ?>
                                    <div class="botones-accion">
                                      
                                        <form action="enviar_mensaje.php" method="POST" style="display: inline;">
                                            <input type="hidden" name="id_producto" value="<?php echo $productID; ?>">
                                            <button type="submit" class="boton-mensaje">Mensaje</button>
                                        </form>
                                        <!-- Botón para abrir la ventana modal -->
                                        <button type="button" class="boton-agregar-lista" onclick="abrirModal()">Agregar a lista</button>
                                        <?php if ($producto['para_cotizar'] == 0): ?>
                                        <form action="../Controlador/agregar_al_carrito.php" method="POST" style="display: inline;">
                                            <input type="hidden" name="id_producto" value="<?php echo $productID; ?>">
                                            <button type="submit" class="boton-comprar">Agregar al carrito</button>
                                            <label for="cantidad">Cantidad:</label>
                                            <input type="number" id="cantidad" name="cantidad" min="1" max="<?php echo htmlspecialchars($producto['cantidad_disponible']); ?>" value="1" required>
                                        </form>
                                        <?php endif; ?>
                                    </div>
                                <?php else: ?>
                                    <p>Debes <a href="login.php">iniciar sesión</a> para realizar estas acciones.</p>
                                <?php endif; ?>
                            <?php else: ?>
                                <p>Producto no disponible.</p>
                            <?php endif; ?>

                    </section>
                </div>
            </div>
            <!-- Sección de valoraciones -->
            <div class="caja-valoraciones">
                <h2>Valoraciones y Comentarios</h2>
                <!-- Mensajes de Éxito y Error para Valoraciones -->
                <?php if (isset($_SESSION['success'])): ?>
                    <div class="mensaje-exito"><?php echo htmlspecialchars($_SESSION['success']); ?></div>
                    <?php unset($_SESSION['success']); ?>
                <?php endif; ?>

                <?php if (isset($_SESSION['errors']) && !empty($_SESSION['errors'])): ?>
                    <div class="mensaje-error">
                        <?php foreach ($_SESSION['errors'] as $error): ?>
                            <p><?php echo htmlspecialchars($error); ?></p>
                        <?php endforeach; ?>
                    </div>
                    <?php unset($_SESSION['errors']); ?>
                <?php endif; ?>

                <?php if ($loggedIn): ?>
                    <form id="valoracion-form" action="../Controlador/procesar_valoracion.php" method="POST">
                        <input type="hidden" name="id_producto" value="<?php echo $productID; ?>">
                        <div class="form-group">
                            <label for="userRating">Calificación (1-10):</label>
                            <input type="number" id="userRating" name="calificacion" min="1" max="10" required>
                        </div>
                        <div class="form-group">
                            <label for="comentario">Comentario:</label>
                            <textarea id="comentario" name="comentario" rows="4" required></textarea>
                        </div>
                        <input type="submit" value="Enviar">
                    </form>
                <?php else: ?>
                    <p>Debes <a href="login.php">iniciar sesión</a> para dejar una valoración o comentario.</p>
                <?php endif; ?>
                <div class="valoraciones-lista">
                    <?php if (!empty($valoraciones)): ?>
                        <?php foreach ($valoraciones as $valoracion): ?>
                            <div class="valoracion">
                                <strong><?php echo htmlspecialchars($valoracion['User']); ?></strong>
                                <div class="fecha"><?php echo htmlspecialchars(date("d/m/Y H:i", strtotime($valoracion['fecha_valoracion']))); ?></div>
                                <div class="calificacion">Calificación: <?php echo htmlspecialchars($valoracion['calificacion']); ?>/10</div>
                                <p><?php echo nl2br(htmlspecialchars($valoracion['comentario'])); ?></p>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p>No hay valoraciones ni comentarios aún. ¡Sé el primero en valorar!</p>
                    <?php endif; ?>
                </div>
            </div>
        </main>

                <!-- Ventana Modal -->
                <div id="modalListas" class="modal">
                    <div class="modal-content">
                        <span class="close" onclick="cerrarModal()">&times;</span>
                        <h2>Mis Listas</h2>
                        <?php
                        // Obtener las listas del usuario donde el producto no ha sido agregado
                        $conexion = new Conexion();
                        $conn = $conexion->conectar();
                        $sql = "SELECT * FROM listas WHERE id_usuario = ? AND id_lista NOT IN (SELECT id_lista FROM listasproductos WHERE id_producto = ?)";
                        $stmt = $conn->prepare($sql);
                        $stmt->bind_param('ii', $userID, $productID);
                        $stmt->execute();
                        $resultado = $stmt->get_result();
                        $listas = $resultado->fetch_all(MYSQLI_ASSOC);
                        $stmt->close();
                        ?>
                        <div class="list-container">
                            <?php if (!empty($listas)): ?>
                                <?php foreach ($listas as $lista): ?>
                                    <div class="list">
                                        <section class="image">
                                            <?php if ($lista['imagen']): ?>
                                                <img src="data:image/jpeg;base64,<?php echo base64_encode($lista['imagen']); ?>" alt="<?php echo htmlspecialchars($lista['nombre_lista']); ?>">
                                            <?php else: ?>
                                                <img src="ruta/imagen_por_defecto.jpg" alt="No hay imagen">
                                            <?php endif; ?>
                                        </section>
                                        <section class="titulo">
                                            <span><?php echo htmlspecialchars($lista['nombre_lista']); ?></span>
                                        </section>
                                        <section class="descripcion">
                                            <span><?php echo htmlspecialchars($lista['descripcion']); ?></span>
                                        </section>
                                        <section class="manage">
                                            <form action="../Controlador/agregar_a_lista.php" method="post">
                                                <input type="hidden" name="id_lista" value="<?php echo $lista['id_lista']; ?>">
                                                <input type="hidden" name="id_producto" value="<?php echo $productID; ?>">
                                                <button type="submit">Agregar</button>
                                            </form>
                                        </section>
                                    </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <p>El producto ya ha sido agregado a todas tus listas.</p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>



    </section>
    <footer>
        <p>2024 Mercado Vivo. Todos los derechos reservados. Israel&Catherine Co. Property.</p>
    </footer>
    <!-- Agregar el script JavaScript al final del body -->
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        console.log('El script del carrusel se está ejecutando.');
        const carouselContainer = document.querySelector('.carousel-container');
        if (carouselContainer) {
            console.log('Carrusel encontrado.');
            const items = carouselContainer.querySelectorAll('.carousel-item');
            console.log('Número de elementos en el carrusel:', items.length);
            items.forEach((item, index) => {
                console.log(`Elemento ${index}:`, item);
            });
            let currentIndex = 0;

            function showItem(index) {
                items.forEach((item, i) => {
                    item.classList.toggle('active', i === index);
                });
            }

            const prevButton = carouselContainer.querySelector('.prev-button');
            const nextButton = carouselContainer.querySelector('.next-button');

            prevButton.addEventListener('click', function() {
                currentIndex = (currentIndex === 0) ? items.length - 1 : currentIndex - 1;
                showItem(currentIndex);
            });

            nextButton.addEventListener('click', function() {
                currentIndex = (currentIndex === items.length - 1) ? 0 : currentIndex + 1;
                showItem(currentIndex);
            });
        } else {
            console.log('Carrusel no encontrado.');
        }
    });

        function abrirModal() {
        document.getElementById('modalListas').style.display = 'block';
        }

        function cerrarModal() {
            document.getElementById('modalListas').style.display = 'none';
        }

        // Cerrar el modal cuando el usuario hace clic fuera del contenido
        window.onclick = function(event) {
            var modal = document.getElementById('modalListas');
            if (event.target == modal) {
                modal.style.display = 'none';
            }
        }
    </script>


</body>
</html>
