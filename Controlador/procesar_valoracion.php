<?php
// procesar_valoracion.php

session_start();
require_once '../Modelo/Conexion.php';

// Verificar si el usuario ha iniciado sesión
if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit();
}

$userID = $_SESSION['user']['IDUsuario'];
$productID = isset($_POST['id_producto']) ? intval($_POST['id_producto']) : 0;
$calificacion = isset($_POST['calificacion']) ? intval($_POST['calificacion']) : 0;
$comentario = isset($_POST['comentario']) ? trim($_POST['comentario']) : '';

// Validaciones básicas
$errors = [];
if ($productID <= 0) {
    $errors[] = "Producto inválido.";
}
if ($calificacion < 1 || $calificacion > 10) {
    $errors[] = "La calificación debe estar entre 1 y 10.";
}
if (empty($comentario)) {
    $errors[] = "El comentario no puede estar vacío.";
}

if (!empty($errors)) {
    // Puedes manejar los errores de la manera que prefieras
    // Por ejemplo, redirigir con mensajes de error
    $_SESSION['errors'] = $errors;
    header("Location: publicacion.php?id=" . $productID);
    exit();
}

// Crear una instancia de la conexión
$conexion = new Conexion();
$conn = $conexion->conectar();

// Insertar la valoración
$sql_insert = "INSERT INTO valoraciones (id_producto, id_usuario, calificacion, comentario, fecha_valoracion) VALUES (?, ?, ?, ?, NOW())";
$stmt_insert = $conn->prepare($sql_insert);
$stmt_insert->bind_param("iiis", $productID, $userID, $calificacion, $comentario);

if ($stmt_insert->execute()) {
    // Valoración insertada exitosamente
    $_SESSION['success'] = "Tu valoración ha sido registrada.";
} else {
    // Error al insertar la valoración
    $_SESSION['errors'] = ["Error al registrar tu valoración: " . $stmt_insert->error];
}

$stmt_insert->close();
$conn->close();

// Redirigir de vuelta a la página de publicación
header("Location: ../Vista/publicacion.php?id=" . $productID);
exit();
?>
