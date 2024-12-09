<?php

require_once 'Conexion.php';

class Categoria {
    private $conn;
    private $conexion; // Añadido: mantener referencia al objeto Conexion

    public function __construct() {
        $this->conexion = new Conexion(); // Mantener referencia
        $this->conn = $this->conexion->conn;
    }

    public function register($data) {

        $stmt = $this->conn->prepare("INSERT INTO categorias (nombre_categoria,descripcion,id_usuario_creador)
            VALUES (?, ?, ?)");


    // Asumiendo que 'id_usuario_creador' es un entero
    $stmt->bind_param(
        "ssi",
        $data['nombre_categoria'],
        $data['descripcion'],
        $data['id_usuario_creador']
    );

    if ($stmt->execute()) {
        return true; // Registro exitoso
    } else {
        return "Error en el registro: " . $stmt->error;
    }

    }

    public function exists($nombre_categoria) {
        $stmt = $this->conn->prepare("SELECT COUNT(*) FROM categorias WHERE LOWER(nombre_categoria) = LOWER(?)");
        $stmt->bind_param("s", $nombre_categoria);
        $stmt->execute();
        $stmt->bind_result($count);
        $stmt->fetch();
        return $count > 0;
    }
    

}

?>