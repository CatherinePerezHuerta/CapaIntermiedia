<?php

session_start();
$id_usuario_creador = $_SESSION['user']['IDUsuario']; // Asegúrate de que 'IDUsuario' es la clave correcta

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $data = [
        'nombre_categoria' => trim($_POST['nombrec']),
        'descripcion' => trim($_POST['description']),
        'id_usuario_creador' => $id_usuario_creador
    ];

    require_once '../Modelo/Categoria.php';
    $Categoria = new Categoria();

    //$resultado =  $Categoria->register($data);

    $nombre_categoria = trim($_POST['nombrec']);

// Verificar si la categoría ya existe
if ($Categoria->exists($nombre_categoria)) {
    // La categoría ya existe
    $_SESSION['error_message'] = 'La categoría ya existe.';
    header("Location: ../Vista/CrearCategorias.php");
    exit();
} else {
    // Proceder a registrar la categoría
    $resultado =  $Categoria->register($data);

    if ($resultado === true) {
        $_SESSION['success_message'] = 'Categoría creada exitosamente.';
        header("Location: ../Vista/CrearCategorias.php");
    } else {
        $_SESSION['error_message'] = 'Error al crear la categoría.';
        header("Location: ../Vista/CrearCategorias.php");
    }
    exit();
}
}

?>