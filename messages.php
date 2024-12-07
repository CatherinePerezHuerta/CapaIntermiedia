<?php
session_start();

// Verificar si el usuario ha iniciado sesión
if (!isset($_SESSION['user'])) {
    // Si no ha iniciado sesión, redirigir al login
    header("Location: login.php");
    exit();
}
// Obtener el nombre de usuario y el rol
$username = $_SESSION['user']['User']; // Ajusta 'User' si es necesario
$role = strtolower($_SESSION['user']['rol']); // Convertir a minúsculas para consistencia
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <!-- Metadatos y enlaces a archivos CSS -->
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mensajes</title>
    <!-- Enlaces a los archivos CSS -->
    <link rel="stylesheet" href="messages.css">
    <link rel="stylesheet" href="header.css">
    <link rel="stylesheet" href="footer.css">
    <!-- Incluimos el CSS proporcionado -->
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <!-- Encabezado -->
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
            <a href="usuario.php"><?php echo htmlspecialchars($username); ?></a>
            <a href="../Controlador/logout.php">Salir</a>
        </div>
    </header>

    <!-- Contenido principal -->
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
            <!-- Interfaz de chat -->
            <div class="container">
                <h1>Mensajes</h1>
                <!-- Barra para seleccionar el destinatario -->
                <div class="recipient-bar">
                    <label for="recipient">Enviar mensaje a:</label>
                    <select id="recipient" name="recipient">
                        <!-- Opciones de destinatarios; estas deberían generarse dinámicamente -->
                        <option value="usuario1">Usuario 1</option>
                        <option value="usuario2">Usuario 2</option>
                        <option value="usuario3">Usuario 3</option>
                    </select>
                </div>
                <!-- Área de mensajes -->
                <div class="chat-container">
                    <div class="chat-messages" id="chat-messages">
                        <!-- Aquí se mostrarán los mensajes -->
                        <div class="message received">
                            <p>Hola, ¿cómo estás?</p>
                            <span class="time">10:00 AM</span>
                        </div>
                        <div class="message sent">
                            <p>Bien, gracias. ¿Y tú?</p>
                            <span class="time">10:01 AM</span>
                        </div>
                        <!-- Puedes agregar más mensajes de ejemplo -->
                    </div>
                    <!-- Campo para enviar mensajes -->
                    <div class="chat-input">
                        <form action="send_message.php" method="POST">
                            <input type="hidden" name="recipient" value="" id="recipient-input">
                            <input type="text" name="message" placeholder="Escribe un mensaje..." required>
                            <button type="submit">Enviar</button>
                        </form>
                    </div>
                </div>
            </div>
        </main>
    </section>

    <!-- Pie de página -->
    <footer>
        <p>2024 Mercado Vivo. Todos los derechos reservados. Israel&Catherine Co. Property.</p>
    </footer>

    <!-- Script para manejar el destinatario seleccionado -->
    <script>
        const recipientSelect = document.getElementById('recipient');
        const recipientInput = document.getElementById('recipient-input');

        // Actualizar el campo oculto con el destinatario seleccionado
        recipientSelect.addEventListener('change', function() {
            recipientInput.value = this.value;
        });

        // Inicializar el campo oculto con el valor seleccionado inicialmente
        recipientInput.value = recipientSelect.value;
    </script>
</body>
</html>
