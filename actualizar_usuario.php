<?php
session_start();

// Verificar si el usuario ha iniciado sesión
if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit();
}

// Obtener el ID del usuario
$userID = $_SESSION['user']['IDUsuario'];

// Incluir el modelo User
require_once '../Modelo/User.php';

// Crear una instancia de User
$userModel = new User();

// Obtener los datos del formulario
$nombre = $_POST['Nombre'];
$apaterno = $_POST['APaterno'];
$amaterno = $_POST['AMaterno'];
$fechaNacimiento = $_POST['fechaNacimiento'];
$genero = $_POST['genero'];
$privacidad = $_POST['privacidad'];

// Datos de domicilio
$pais = $_POST['pais'];
$estado = $_POST['estado'];
$ciudad = $_POST['ciudad'];
$colonia = $_POST['colonia'];
$CP = $_POST['CP'];
$calle = $_POST['calle'];
$NoExt = $_POST['NoExt'];

// Manejo del avatar (subida de imagen)
$imageData = null;
if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] == UPLOAD_ERR_OK) {
    // Validar el tipo de archivo y el tamaño máximo permitido (opcional)
    $allowedTypes = ['image/jpeg', 'image/png'];
    $maxSize = 2 * 1024 * 1024; // 2MB

    $fileType = $_FILES['avatar']['type'];
    $fileSize = $_FILES['avatar']['size'];

    if (in_array($fileType, $allowedTypes) && $fileSize <= $maxSize) {
        $imageData = file_get_contents($_FILES['avatar']['tmp_name']);
    } else {
        echo "Archivo no válido. Solo se permiten imágenes JPEG y PNG de hasta 2MB.";
        exit();
    }
}

// Preparar los datos para actualizar el usuario
$userData = [
    'Nombre' => $nombre,
    'APaterno' => $apaterno,
    'AMaterno' => $amaterno,
    'fechaNacimiento' => $fechaNacimiento,
    'Genero' => $genero,
    'Privacidad' => $privacidad
    // La imagen se manejará por separado
];

// Actualizar los datos del usuario, incluyendo la imagen si existe
$actualizado = $userModel->updateUser($userID, $userData, $imageData);

// Actualizar los datos del domicilio
$domicilioActualizado = $userModel->updateDomicilio($userID, [
    'Pais' => $pais,
    'Estado' => $estado,
    'ciudad' => $ciudad,
    'colonia' => $colonia,
    'CP' => $CP,
    'calle' => $calle,
    'NoExterno' => $NoExt
]);

if ($actualizado && $domicilioActualizado) {
    // Actualización exitosa
    header("Location: ../Vista/usuario.php");
    exit();
} else {
    // Error en la actualización
    echo "Error al actualizar los datos.";
}
?>
