<?php
session_start();
require_once '../Modelo/Conexion.php';

// Verificar si el usuario ha iniciado sesión y es vendedor
if (!isset($_SESSION['user']) || strtolower($_SESSION['user']['rol']) !== 'vendedor') {
    header("Location: login.php");
    exit();
}

// Obtener datos del formulario
$id_producto = $_POST['id_producto'];
$nombre = $_POST['name'];
$descripcion = $_POST['description'];
$categoria = $_POST['category'];
$tipo = $_POST['type'];
$precio = ($tipo == 'vender') ? $_POST['price'] : null;
$cantidad = $_POST['quantity'];
$id_vendedor = $_SESSION['user']['IDUsuario'];
$para_cotizar = ($tipo == 'cotizar') ? 1 : 0;

// Crear una instancia de la conexión
$conexion = new Conexion();
$conn = $conexion->conn;

// Verificar que el producto pertenece al vendedor
$sql_check = "SELECT id_producto FROM productos WHERE id_producto = ? AND id_vendedor = ?";
$stmt_check = $conn->prepare($sql_check);
$stmt_check->bind_param("ii", $id_producto, $id_vendedor);
$stmt_check->execute();
$result_check = $stmt_check->get_result();

if ($result_check->num_rows === 0) {
    // El producto no pertenece al vendedor
    header("Location: ../Vista/productosaprobados.php");
    exit();
}

// Actualizar el producto y establecer aprobado = 0
$sql_producto = "UPDATE productos SET nombre_producto = ?, descripcion = ?, para_cotizar = ?, precio = ?, cantidad_disponible = ?, aprobado = 0 WHERE id_producto = ?";
$stmt_producto = $conn->prepare($sql_producto);
$stmt_producto->bind_param("ssidii", $nombre, $descripcion, $para_cotizar, $precio, $cantidad, $id_producto);

if ($stmt_producto->execute()) {

    // Actualizar la categoría en la tabla productoscategorias
    $sql_prod_cat = "UPDATE productoscategorias SET id_categoria = ? WHERE id_producto = ?";
    $stmt_prod_cat = $conn->prepare($sql_prod_cat);
    $stmt_prod_cat->bind_param("ii", $categoria, $id_producto);
    $stmt_prod_cat->execute();

    // Manejo de imágenes (si se subieron nuevas imágenes)
    if (!empty($_FILES['images']['name'][0])) {
        // Crear la carpeta uploads si no existe
        $upload_dir = '../uploads/';
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }

        // Crear una carpeta específica para el producto si no existe
        $product_dir = $upload_dir . 'producto_' . $id_producto . '/';
        if (!is_dir($product_dir)) {
            mkdir($product_dir, 0777, true);
        }

        // Eliminar las imágenes antiguas
        $sql_delete_images = "DELETE FROM imagenesproducto WHERE id_producto = ?";
        $stmt_delete_images = $conn->prepare($sql_delete_images);
        $stmt_delete_images->bind_param("i", $id_producto);
        $stmt_delete_images->execute();

        // Manejar las nuevas imágenes
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
    }

    // Manejo del video (si se subió un nuevo video)
    if (!empty($_FILES['video']['tmp_name'])) {
        // Crear la carpeta uploads si no existe
        $upload_dir = '../uploads/';
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }

        // Crear una carpeta específica para el producto si no existe
        $product_dir = $upload_dir . 'producto_' . $id_producto . '/';
        if (!is_dir($product_dir)) {
            mkdir($product_dir, 0777, true);
        }

        // Eliminar el video antiguo
        $sql_delete_video = "DELETE FROM videosproducto WHERE id_producto = ?";
        $stmt_delete_video = $conn->prepare($sql_delete_video);
        $stmt_delete_video->bind_param("i", $id_producto);
        $stmt_delete_video->execute();

        // Manejar el nuevo video
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

    // Establecer mensaje de éxito en la sesión
    $_SESSION['success_message'] = 'Publicación actualizada exitosamente. Pendiente de aprobación.';

    header("Location: ../Vista/productosaprobados.php");
    exit();
} else {
    echo "Error al actualizar la publicación: " . $stmt_producto->error;
}

// Cerrar la conexión
$stmt_producto->close();
?>
