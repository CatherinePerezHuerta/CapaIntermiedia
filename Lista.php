<?php
require_once 'Conexion.php';

class Lista {
    private $conn;

    public function __construct($conn) {
        $this->conn = $conn; // Asigna la conexión pasada
    }

    public function crearLista($nombre, $descripcion, $publico, $id_usuario, $imagen = null) {
        try {
            if (!$this->conn || $this->conn->connect_error) {
                throw new Exception("Conexión no válida");
            }

            $sql = "INSERT INTO listas (nombre_lista, descripcion, publico, id_usuario, imagen) 
                    VALUES (?, ?, ?, ?, ?)";
            $stmt = $this->conn->prepare($sql);
            if (!$stmt) {
                throw new Exception("Error al preparar la consulta: " . $this->conn->error);
            }

            if ($imagen !== null) {
                $stmt->bind_param('ssiib', $nombre, $descripcion, $publico, $id_usuario, $imagen);
                $stmt->send_long_data(4, $imagen);
            } else {
                $stmt->bind_param('ssiis', $nombre, $descripcion, $publico, $id_usuario, $imagen);
            }

            $stmt->execute();
            $stmt->close();

            return true;
        } catch (Exception $e) {
            echo "Error al crear la lista: " . $e->getMessage();
            return false;
        }
    }
}
?>
