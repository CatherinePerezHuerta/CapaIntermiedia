<?php
session_start();
require_once '../Modelo/Conexion.php';
require_once '../Modelo/Lista.php';

// Verifica si el usuario está logueado
if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit();
}

// Crear una conexión manualmente
$conexion = new Conexion();
$conn = $conexion->conectar(); // Obtiene la conexión activa

// Procesa el formulario
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nombre = $_POST['title'];
    $descripcion = $_POST['description'];
    $publico = 1; // Puedes modificar según tu lógica
    $id_usuario = $_SESSION['user']['IDUsuario']; // Obtén el ID del usuario desde la sesión

    
    // Verificar si se subió una imagen
    $imagen = null;
    if (isset($_FILES['image']) && $_FILES['image']['error'] == UPLOAD_ERR_OK) {
        $imagen = file_get_contents($_FILES['image']['tmp_name']);
    }

    // Crear la lista con la conexión activa
    $lista = new Lista($conn);
    $resultado = $lista->crearLista($nombre, $descripcion, $publico, $id_usuario, $imagen);

    if ($resultado) {
        //echo "Lista creada exitosamente.";
        header("Location: ../Vista/crearlistas.php");
    } else {
        echo "Error al crear la lista.";
    }
}
?>
