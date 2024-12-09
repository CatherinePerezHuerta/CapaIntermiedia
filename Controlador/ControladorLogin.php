<?php
// Controlador/ControladorLogin.php
session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    require_once '../Modelo/User.php';

    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    $user = new User();

    $resultado = $user->login($username, $password);

    if ($resultado['success']) {
        // Inicio de sesión exitoso
        $_SESSION['user'] = [
            'IDUsuario' => $resultado['user']['IDUsuario'],
            'User' => $resultado['user']['User'],
            'Correo' => $resultado['user']['Correo'],
            'rol' => strtolower($resultado['user']['Rol'])
            // Agrega otros campos si los necesitas
        ];
       // echo "El rol del usuario es: " . $_SESSION['user']['rol'];

        header("Location: ../Vista/mainUser.php");
    } else {
        // Error en el inicio de sesión
        $_SESSION['mensaje'] = $resultado['message'];
        header("Location: ../Vista/login.php");
    }
    exit();
}
?>
