<?php
session_start();

// Verificar si el usuario ha iniciado sesión
if (!isset($_SESSION['user'])) {
    // Si no ha iniciado sesión, redirigir al login
    header("Location: login.php");
    exit();
}

// Obtener el ID del usuario desde la sesión
$userID = $_SESSION['user']['IDUsuario']; // Ajusta 'IDUsuario' si es necesario
$role = strtolower($_SESSION['user']['rol']); // Convertir a minúsculas para consistencia



// Incluir el modelo User
require_once '../Modelo/User.php';

// Crear una instancia de User
$userModel = new User();

// Obtener los datos del usuario
$userData = $userModel->getUserByID($userID);

if (!$userData) {
    echo "Usuario no encontrado.";
    exit();
}

// Obtener el nombre de usuario para mostrar en la interfaz
$username = $userData['User']; // Ajusta 'User' si es necesario
$domicilioData = $userModel->getDomicilioByUserID($userID);

?>


<!DOCTYPE html>
<html lang="en">
<!--Ventana de Usuario-->
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Roboto:ital,wght@0,100;0,300;0,400;0,500;0,700;0,900;1,100;1,300;1,400;1,500;1,700;1,900&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="usuario.css">
    <link rel="stylesheet" href="header.css">
    <link rel="stylesheet" href="footer.css">
    <title>Perfil de Usuario</title>
</head>

<body>
    <header class="topnav">
        <a class="imagen" href="main.html">
            <img src="./Imágenes/LOGOTIPO.png" alt=".">
        </a>
        <div class="busqueda">   
            <!--a class="active" href="main.html">Inicio</a>
            <a href="publicar.html">Publicar</a-->
            <form class="search-form" action="#" method="GET">
                <input type="text" name="search" placeholder="Buscar...">
                <button type="submit">Buscar</button>
            </form>
        </div>
        <div class="boton">
            <a href="usuario.php"><?php echo htmlspecialchars($username); ?></a>
            <a href="../Controlador/logout.php">Salir</a>
        </div>
    </header>
    <section class="pagina">
    <aside>
            <div class="sidebar">
                <a href="mainUser.php">Inicio</a>
                <?php if ($role == 'vendedor'): ?>
                    <a href="publicar.php">Publicar</a>
                    <a href="CrearCategorias.php">Crear Categoria</a>
                    <a href="productosaprobados.php">Productos Aprobados</a>
                    <a href="productoseliminados.php">Productos No Aprobados</a>
                <?php endif; ?>
                <?php if ($role == 'administrador'): ?>
                <a href="GestionProductos.php">Gestion Productos</a>
                <a href="GestionUsuarios.php">Gestion Usuarios</a>
                <?php endif; ?>

                <?php if ($role == 'comprador'): ?>
                    <a href="crearlistas.php">Crear Lista</a>
                    <a href="listas.php">Listas</a>
                    <a href="Carrito.php">Carrito</a>
                    <a href="Historial.php">Historial</a>
                <?php endif; ?>
                <a href="messages.php">Mensajes</a>
            </div>
        </aside>


    <main>
        <div class="container">
            <h2>Perfil de Usuario</h2>
            <?php


                // Mostrar el valor de la variable en la página
                echo "El rol es: " . $role;
                ?>


            <form id="registroForm" action="../Controlador/actualizar_usuario.php" method="post" enctype="multipart/form-data">

                <section class="columnas">
                    <div class="column">
                    <!-- MOSTRAR IMAGEN DE PERFIL O MENSAJE -->
                    <?php
                        // Determinar el tipo MIME
                        $mime_type = 'image/jpeg'; // Valor predeterminado
                        $finfo = finfo_open(FILEINFO_MIME_TYPE);
                        $detected_type = finfo_buffer($finfo, $userData['Imagen']);
                        if ($detected_type) {
                            $mime_type = $detected_type;
                        }
                        finfo_close($finfo);
                        ?>
                        <div class="profile-picture">
                            <?php if (!empty($userData['Imagen'])): ?>
                                <img src="data:<?php echo $mime_type; ?>;base64,<?php echo base64_encode($userData['Imagen']); ?>" alt="Foto de perfil">
                            <?php else: ?>
                                <p>Sin foto</p>
                                <button type="button" onclick="document.getElementById('avatar').click();">Agregar foto</button>
                            <?php endif; ?>
                        </div>

                    <!-- INPUT OCULTO PARA SUBIR IMAGEN -->
                   
                           <!-- RESTO DE CAMPOS DEL FORMULARIO -->
                        <div class="form-group">
                            <input placeholder="Nombre" type="text" id="Nombre" name="Nombre"  value="<?php echo htmlspecialchars($userData['Nombre']); ?>" required>
                        </div>
                        <div class="form-group">
                            <input placeholder="Apellido Paterno" type="text" id="APaterno" name="APaterno"  value="<?php echo htmlspecialchars($userData['APaterno']); ?>" required>
                        </div>
                        <div class="form-group">
                            <input placeholder="Apellido Materno" type="text" id="AMaterno" name="AMaterno"  value="<?php echo htmlspecialchars($userData['AMaterno']); ?>"required>
                        </div>
                         <br>
                        <div class="form-group">
                            <p>Fecha de Nacimiento:</p>
                            <input placeholder="Fecha de Nacimiento" type="date" id="fechaNacimiento" name="fechaNacimiento"  value="<?php echo htmlspecialchars($userData['fechaNacimiento']); ?>" required>
                        </div>
                        <div class="linea-en-blanco"></div>
                        <div class="form-group">
                            <!-- Campo Género -->
                            <select name="genero" id="genero" required>
                                <option value="">Seleccione su género</option>
                                <option value="masculino" <?php if($userData['Genero'] == 'masculino') echo 'selected'; ?>>Masculino</option>
                                <option value="femenino" <?php if($userData['Genero'] == 'femenino') echo 'selected'; ?>>Femenino</option>
                                <option value="otro" <?php if($userData['Genero'] == 'otro') echo 'selected'; ?>>Otro</option>
                                <option value="noEspecificado" <?php if($userData['Genero'] == 'noEspecificado') echo 'selected'; ?>>Prefiere no decirlo</option>
                            </select>
                        </div>
                        <div class="linea-en-blanco"></div>
                        <div class="form-group"></div>
                        <!-- Campo Privacidad -->
                            <select name="privacidad" id="privacidad" required>
                                <option value="">Seleccione su privacidad de perfil</option>
                                <option value="privado" <?php if($userData['Privacidad'] == 'privado') echo 'selected'; ?>>Privado</option>
                                <option value="publico" <?php if($userData['Privacidad'] == 'publico') echo 'selected'; ?>>Público</option>
                            </select>
                             <br>
                            <div class="linea-en-blanco"></div>
                        <input type="file" id="avatar" name="avatar" accept="image/png, image/jpeg" />
                    </div>
                    <div class="column">
                        <div class="div-group">
                            <div class="form-group">
                            <input placeholder="País de Residencia" type="text" id="pais" name="pais" value="<?php echo htmlspecialchars($domicilioData['Pais'] ?? ''); ?>" required>
                            </div>
                        </div>
                        <div class="div-group">
                            <div class="form-group">
                            <input placeholder="Estado" type="text" id="estado" name="estado" value="<?php echo htmlspecialchars($domicilioData['Estado'] ?? ''); ?>" required>
                            </div>
                            <div class="form-group">
                            <input placeholder="Ciudad" type="text" id="ciudad" name="ciudad" value="<?php echo htmlspecialchars($domicilioData['ciudad'] ?? ''); ?>" required>
                            </div>
                        </div>
                        <div class="div-group">
                            <div class="form-group">
                            <input placeholder="Colonia" type="text" id="colonia" name="colonia" value="<?php echo htmlspecialchars($domicilioData['colonia'] ?? ''); ?>" required>
                            </div>
                            <div class="form-group">
                            <input placeholder="Código Postal" type="text" id="CP" name="CP" value="<?php echo htmlspecialchars($domicilioData['CP'] ?? ''); ?>" required>
                            </div>
                        </div>
                        <div class="div-group">
                            <div class="form-group">
                            <input placeholder="Calle" type="text" id="calle" name="calle" value="<?php echo htmlspecialchars($domicilioData['calle'] ?? ''); ?>" required>
                            </div>
                            <div class="form-group">
                            <input placeholder="Número externo" type="text" id="NoExt" name="NoExt" value="<?php echo htmlspecialchars($domicilioData['NoExterno'] ?? ''); ?>" required>
                            </div>
                        </div>
                    </div>
                </section>
                <div class="linea-en-blanco"></div>
                <input type="submit" value="Actualizar">
            </form>
        </div>
    </main>
</section>
    <footer>
        <p>2024 Mercado Vivo. Todos los derechos reservados. Israel&Catherine Co. Property.</p>
    </footer>
</body>
<script src="Validacion/validacioUsuario.js"></script>
</html>