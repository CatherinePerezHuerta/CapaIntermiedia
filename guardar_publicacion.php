<?php
session_start();
require_once '../Modelo/Conexion.php';

// Verificar si el usuario ha iniciado sesión
if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit();
}

// Crear una instancia de la conexión
$conexion = new Conexion();
$conn = $conexion->conn;

// Obtener datos del formulario
$nombre = $_POST['name'];
$descripcion = $_POST['description'];
$categoria = $_POST['category'];
$tipo = $_POST['type'];
$precio = ($tipo == 'vender') ? $_POST['price'] : null;
$cantidad = $_POST['quantity'];
$id_vendedor = $_SESSION['user']['IDUsuario'];
$para_cotizar = ($tipo == 'cotizar') ? 1 : 0;

// Insertar en la tabla productos
$sql_producto = "INSERT INTO productos (nombre_producto, descripcion, para_cotizar, precio, cantidad_disponible, id_vendedor, aprobado)
                 VALUES (?, ?, ?, ?, ?, ?, 0)";
$stmt_producto = $conn->prepare($sql_producto);
$stmt_producto->bind_param("ssidii", $nombre, $descripcion, $para_cotizar, $precio, $cantidad, $id_vendedor);

if ($stmt_producto->execute()) {
    $id_producto = $stmt_producto->insert_id;

    // Crear la carpeta uploads si no existe
    $upload_dir = '../uploads/';
    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0777, true);
    }

    // **Opcional:** Crear una carpeta específica para el producto
    $product_dir = $upload_dir . 'producto_' . $id_producto . '/';
    if (!is_dir($product_dir)) {
        mkdir($product_dir, 0777, true);
    }

    // Manejo de imágenes
    foreach ($_FILES['images']['tmp_name'] as $key => $tmp_name) {
        $original_name = $_FILES['images']['name'][$key];
        $file_extension = pathinfo($original_name, PATHINFO_EXTENSION);
        $new_filename = 'producto_' . $id_producto . '_imagen_' . uniqid() . '.' . $file_extension;
        $ruta_imagen = $product_dir . $new_filename;

        if (move_uploaded_file($tmp_name, $ruta_imagen)) {
            $ruta_imagen_db = 'uploads/producto_' . $id_producto . '/' . $new_filename; // Ruta para la base de datos
            $sql_imagen = "INSERT INTO imagenesproducto (id_producto, ruta_imagen) VALUES (?, ?)";
            $stmt_imagen = $conn->prepare($sql_imagen);
            $stmt_imagen->bind_param("is", $id_producto, $ruta_imagen_db);
            $stmt_imagen->execute();
        }
    }

    // Manejo del video
    if (!empty($_FILES['video']['tmp_name'])) {
        $original_name = $_FILES['video']['name'];
        $file_extension = pathinfo($original_name, PATHINFO_EXTENSION);
        $new_filename = 'producto_' . $id_producto . '_video_' . uniqid() . '.' . $file_extension;
        $ruta_video = $product_dir . $new_filename;

        if (move_uploaded_file($_FILES['video']['tmp_name'], $ruta_video)) {
            $ruta_video_db = 'uploads/producto_' . $id_producto . '/' . $new_filename; // Ruta para la base de datos
            $sql_video = "INSERT INTO videosproducto (id_producto, ruta_video) VALUES (?, ?)";
            $stmt_video = $conn->prepare($sql_video);
            $stmt_video->bind_param("is", $id_producto, $ruta_video_db);
            $stmt_video->execute();
        }
    }
    // ... código existente ...

// Insertar en la tabla productoscategorias
$categoria_id = $_POST['category']; // Este es el id de la categoría seleccionada

$sql_prod_cat = "INSERT INTO productoscategorias (id_producto, id_categoria) VALUES (?, ?)";
$stmt_prod_cat = $conn->prepare($sql_prod_cat);
$stmt_prod_cat->bind_param("ii", $id_producto, $categoria_id);

if ($stmt_prod_cat->execute()) {
    // Establecer mensaje de éxito en la sesión
    $_SESSION['success_message'] = 'Publicación guardada exitosamente.';
    header("Location: ../Vista/publicar.php");
    exit();
} else {
    echo "Error al asociar la categoría al producto: " . $stmt_prod_cat->error;
}

// ... resto del código ...
} else {
    echo "Error al guardar la publicación: " . $stmt_producto->error;
}

// Cerrar la conexión
$stmt_producto->close();
?>
