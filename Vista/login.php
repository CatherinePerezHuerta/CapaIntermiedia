

<!DOCTYPE html>
<html lang="en">
    <!--Ventana de Inicio de Sesión-->
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:ital,wght@0,100;0,300;0,400;0,500;0,700;0,900;1,100;1,300;1,400;1,500;1,700;1,900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="login.css">
    <link rel="stylesheet" href="header.css">
    <link rel="stylesheet" href="footer.css">
    <title>Iniciar Sesión</title>
</head>

<body>
    <header class="topnav">
        <a class="imagen" href="main.html">
            <img src="./Imágenes/LOGOTIPO.png" alt=".">
        </a>
        <div class="busqueda">   
            <form class="search-form" action="./busqueda.html" method="GET">
                <input type="text" name="search" placeholder="Buscar..." required>
                <button type="submit">Buscar</button>
            </form>
        </div>
        <div class="boton">
            <a href="login.php">Iniciar sesión</a>
            <a href="register.php">Registrarse</a>
        </div>
    </header>
    <main>
        <div class="container">
            <h2>Iniciar sesión</h2>
            <?php
            session_start();
            if (isset($_SESSION['mensaje'])) {
                echo "<p class='mensaje'>" . $_SESSION['mensaje'] . "</p>";
                unset($_SESSION['mensaje']); // Eliminar el mensaje después de mostrarlo
            }
            ?>

            <form action="../Controlador/ControladorLogin.php" method="post">
                <input type="text" id="username" name="username" required placeholder="Correo electrónico">
                <input type="password" id="password" name="password" required placeholder="Contraseña">
                <!-- Resto del formulario -->
                <div class="linea-en-blanco"></div>
                <span>¿Aún no estás registrado? <a href="./register.php">Regístrate</a></span>
                <div class="linea-en-blanco"></div>
                <input type="submit" value="Iniciar sesión">
            </form>
        </div>
    </main>
    <footer>
        <p>2024 Mercado Vivo. Todos los derechos reservados. Israel&Catherine Co. Property.</p>
    </footer>
</body>

</html>
