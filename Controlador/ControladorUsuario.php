<?php
// Controlador/ControladorUsuario.php
session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    require_once '../Modelo/User.php';

    // Recibir y limpiar los datos del formulario
    $data = [
        'Nombre' => trim($_POST['Nombre']),
        'APaterno' => trim($_POST['APaterno']),
        'AMaterno' => trim($_POST['AMaterno']),
        'fechaNacimiento' => trim($_POST['fechaNacimiento']),
        'genero' => trim($_POST['genero']),
        'User' => trim($_POST['User']),
        'Correo' => trim($_POST['Correo']),
        'Contrasena' => trim($_POST['Contrasena']),
        'rol' => trim($_POST['rol']),
        'privacidad' => trim($_POST['privacidad'])
    ];

    // Validar que las contraseñas coinciden
    if ($data['Contrasena'] !== trim($_POST['contraseña2'])) {
        $_SESSION['mensaje'] = "Las contraseñas no coinciden.";
        header("Location: ../Vista/register.php");
        exit();
    }

    $user = new User();

    // Intentar registrar al usuario
    $resultado = $user->register($data);

    if ($resultado === true) {
        $_SESSION['mensaje'] = "Registro exitoso. Ingrese sus credenciales.";
        header("Location: ../Vista/login.php");
    } else {
        $_SESSION['mensaje'] = $resultado;
        header("Location: ../Vista/register.php");
    }
    exit();
}

?>
