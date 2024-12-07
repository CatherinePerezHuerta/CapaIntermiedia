



<!DOCTYPE html>
<html lang="en">
    <!--Ventana de Registro de Usuario-->
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:ital,wght@0,100;0,300;0,400;0,500;0,700;0,900;1,100;1,300;1,400;1,500;1,700;1,900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="register.css">
    <link rel="stylesheet" href="header.css">
    <link rel="stylesheet" href="footer.css">
    <title>Registrar Usuario</title>
</head>

<body>
    <header class="topnav">
        <a class="imagen" href="main.html">
            <img src="./Imágenes/LOGOTIPO.png" alt=".">
        </a>
        <div class="busqueda">   
            <!--a class="active" href="main.html">Inicio</a>
            <a href="publicar.html">Publicar</a-->
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
            <h2>Registro de Usuario</h2>
            <?php
                   session_start();
                   if (isset($_SESSION['mensaje'])) {
                       echo "<p class='mensaje'>" . $_SESSION['mensaje'] . "</p>";
                       unset($_SESSION['mensaje']); // Eliminar el mensaje después de mostrarlo
                   }
                   ?>

            <form id="registroForm" action="../Controlador/ControladorUsuario.php" method="POST">
                <div class="form-group">
                    <input placeholder="Nombre" type="text" id="Nombre" name="Nombre" required>
                </div>
                <div class="form-group">
                    <input placeholder="Apellido Paterno" type="text" id="APaterno" name="APaterno" required>
                </div>
                <div class="form-group">
                    <input placeholder="Apellido Materno" type="text" id="AMaterno" name="AMaterno" required>
                </div>
                <div class="form-group">
                    <p>Fecha de Nacimiento:</p>
                    <input placeholder="Fecha de Nacimiento" type="date" id="fechaNacimiento" name="fechaNacimiento" required>
                </div>
                <div class="form-group">
                    <select name="genero" id="genero" required>
                        <option value="">Seleccione su género</option>
                        <option value="masculino">Masculino</option>
                        <option value="femenino">Femenino</option>
                        <option value="otro">Otro</option>
                        <option value="noEspecificado">Prefiere no decirlo</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <input placeholder=" Nombre de Usuario" type="text" id="User" name="User" required>
                    </div>
                <div class="form-group">
                    <input placeholder="Correo Electrónico" type="email" id="Correo" name="Correo" required>
                </div>
                <div class="form-group">
                    <input placeholder="Contraseña" type="password" id="Contrasena" name="Contrasena" required>
                </div>
                <div class="form-group">
                    <input placeholder="Confirmar Contraseña" type="password" id="contraseña2" name="contraseña2" required>
                </div>
                <div class="form-group">
                    <select name="rol" id="rol" required>
                        <option value="">Seleccione su rol</option>
                        <option value="administrador">Administrador</option>
                        <option value="comprador">Comprador</option>
                        <option value="vendedor">Vendedor</option>
                    </select>
                </div>
                <div class="form-group">
                    <select name="privacidad" id="privacidad" required>
                        <option value="">Seleccione su privacidad de perfil</option>
                        <option value="privado">Privado</option>
                        <option value="publico">Público</option>
                    </select>
                </div>
                <div class="form-group">
                    <input type="submit" value="Registrar">
                </div>
            </form>
            
        </div>
    </main>
    <footer>
        <p>2024 Mercado Vivo. Todos los derechos reservados. Israel&Catherine Co. Property.</p>
    </footer>
</body>

</html>
