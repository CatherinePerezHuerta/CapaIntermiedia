<?php
// Modelo/User.php

require_once 'Conexion.php';

class User {
    private $conn;
    private $conexion; // Añadido: mantener referencia al objeto Conexion

    public function __construct() {
        $this->conexion = new Conexion(); // Mantener referencia
        $this->conn = $this->conexion->conn;
    }

    // Método para registrar un nuevo usuario
    public function register($data) {
        // Verificar si el correo o el usuario ya existen
        if ($this->userExists($data['User'], $data['Correo'])) {
            return "El nombre de usuario o correo ya están en uso. Por favor, elige otros.";
        }

        // Hashear la contraseña
        $hashed_password = password_hash($data['Contrasena'], PASSWORD_DEFAULT);

        // Determinar el valor de Aprobacion
        if ($data['rol'] == 'administrador') {
            $aprobacion = 0;
        } else {
            $aprobacion = 1;
        }

        // Preparar la consulta para insertar el usuario
        $stmt = $this->conn->prepare("INSERT INTO usuario (Nombre, APaterno, AMaterno, fechaNacimiento, genero, User, Correo, Contrasena, rol, privacidad, FechaIngreso, Aprobado)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), ?)");

        $stmt->bind_param(
            "ssssssssssi",
            $data['Nombre'],
            $data['APaterno'],
            $data['AMaterno'],
            $data['fechaNacimiento'],
            $data['genero'],
            $data['User'],
            $data['Correo'],
            $hashed_password,
            $data['rol'],
            $data['privacidad'],
            $aprobacion
        );

        // Iniciar una transacción para asegurar la consistencia
        $this->conn->begin_transaction();

        try {
            if ($stmt->execute()) {
                // Obtener el ID del nuevo usuario
                $id_usuario = $stmt->insert_id;

                // Si el rol es 'comprador', insertar en carritocompras
                if (strtolower($data['rol']) === 'comprador') {
                    $stmt_carrito = $this->conn->prepare("INSERT INTO carritocompras (id_usuario) VALUES (?)");
                    $stmt_carrito->bind_param('i', $id_usuario);
                    if (!$stmt_carrito->execute()) {
                        throw new Exception("Error al crear el carrito de compras: " . $stmt_carrito->error);
                    }
                    $stmt_carrito->close();
                }

                // Commit de la transacción
                $this->conn->commit();
                return true; // Registro exitoso
            } else {
                throw new Exception("Error en el registro de usuario: " . $stmt->error);
            }
        } catch (Exception $e) {
            // Rollback de la transacción en caso de error
            $this->conn->rollback();
            return $e->getMessage();
        } finally {
            $stmt->close();
        }
    }


    // Método para iniciar sesión
    public function login($username, $password) {
        // Buscar al usuario por correo electrónico
        $stmt = $this->conn->prepare("SELECT * FROM usuario WHERE Correo = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();
    
        if ($result->num_rows > 0) {
            $user = $result->fetch_assoc();
            // Verificar la contraseña
            if (password_verify($password, $user['Contrasena'])) {
                // Verificar si el usuario está aprobado
                if ($user['Aprobado'] == 1) {
                    // Inicio de sesión exitoso
                    return ['success' => true, 'user' => $user];
                } else {
                    // Usuario no aprobado
                    return ['success' => false, 'message' => 'Su cuenta aún no ha sido aprobada. Por favor, contacte al administrador.'];
                }
            } else {
                // Contraseña incorrecta
                return ['success' => false, 'message' => 'Correo o contraseña incorrectos. Inténtalo de nuevo.'];
            }
        } else {
            // Usuario no encontrado
            return ['success' => false, 'message' => 'Correo o contraseña incorrectos. Inténtalo de nuevo.'];
        }
    }
    

    // Método para verificar si el usuario o correo ya existen
    private function userExists($username, $email) {
        $stmt = $this->conn->prepare("SELECT * FROM usuario WHERE User = ? OR Correo = ?");
        $stmt->bind_param("ss", $username, $email);
        $stmt->execute();
        $result = $stmt->get_result();

        return ($result->num_rows > 0);
    }

 
// Método para obtener la información del usuario por ID, incluyendo el domicilio
// Método para obtener la información del usuario por ID, incluyendo el domicilio y la imagen
public function getUserByID($userID) {
    $stmt = $this->conn->prepare("
        SELECT u.*, d.Pais, d.Estado, d.ciudad, d.colonia, d.CP, d.calle, d.NoExterno
        FROM usuario u
        LEFT JOIN Domicilio d ON u.IDUsuario = d.id_usuario
        WHERE u.IDUsuario = ?
    ");
    $stmt->bind_param("i", $userID);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        return $result->fetch_assoc();
    } else {
        return false; // Usuario no encontrado
    }
}


// Método para obtener el domicilio del usuario por ID de usuario
public function getDomicilioByUserID($userID) {
    $stmt = $this->conn->prepare("SELECT * FROM Domicilio WHERE id_usuario = ?");
    $stmt->bind_param("i", $userID);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        return $result->fetch_assoc();
    } else {
        return false; // Domicilio no encontrado
    }
}

// Método para actualizar los datos del usuario
public function updateUser($userID, $data, $imageData = null) {
    // Construir la consulta dinámica
    $fields = [];
    $types = '';
    $params = [];
    
    foreach ($data as $key => $value) {
        if ($value !== null) {
            $fields[] = "$key = ?";
            $types .= 's';
            $params[] = $value;
        }
    }
    
    // Si hay imagen, agregarla
    if ($imageData !== null) {
        $fields[] = "imagen = ?";
        $types .= 's'; // Usamos 's' en lugar de 'b'
        $params[] = $imageData;
    }
    
    $fields[] = "IDUsuario = ?";
    $types .= 'i';
    $params[] = $userID;
    
    $sql = "UPDATE usuario SET " . implode(', ', array_slice($fields, 0, -1)) . " WHERE IDUsuario = ?";
    
    $stmt = $this->conn->prepare($sql);
    if (!$stmt) {
        echo "Error en la preparación de la consulta: " . $this->conn->error;
        return false;
    }
    
    // Vincular los parámetros
    $stmt->bind_param($types, ...$params);
    
    if ($stmt->execute()) {
        return true;
    } else {
        echo "Error en la ejecución de la consulta: " . $stmt->error;
        return false;
    }
}



// Método para actualizar o insertar el domicilio del usuario
public function updateDomicilio($userID, $data) {
    // Verificar si el usuario ya tiene un domicilio
    $stmt = $this->conn->prepare("SELECT id_domicilio FROM Domicilio WHERE id_usuario = ?");
    $stmt->bind_param("i", $userID);
    $stmt->execute();
    $result = $stmt->get_result();

    $fields = [];
    $params = [];
    $types = '';

    foreach ($data as $key => $value) {
        $fields[] = "$key = ?";
        $params[] = $value;
        $types .= 's';
    }

    $params[] = $userID;
    $types .= 'i';

    if ($result->num_rows > 0) {
        // Actualizar
        $sql = "UPDATE Domicilio SET " . implode(', ', $fields) . " WHERE id_usuario = ?";
    } else {
        // Insertar
        $sql = "INSERT INTO Domicilio (" . implode(', ', array_keys($data)) . ", id_usuario) VALUES (" . rtrim(str_repeat('?, ', count($data)), ', ') . ", ?)";
    }

    $stmt = $this->conn->prepare($sql);
    $stmt->bind_param($types, ...$params);

    return $stmt->execute();
}


}
?>
