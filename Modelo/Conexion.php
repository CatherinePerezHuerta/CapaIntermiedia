<?php
// Modelo/Conexion.php

class Conexion {
    private $servername = "localhost";
    private $username = "root";  // Cambia si tienes un usuario diferente
    private $password = "";      // Cambia si tienes una contraseña
    private $dbname = "bdtienda";
    public $conn;

    public function __construct() {
        // Crear conexión
        $this->conn = new mysqli($this->servername, $this->username, $this->password, $this->dbname);
        // Verificar conexión
        if ($this->conn->connect_error) {
            die("Conexión fallida: " . $this->conn->connect_error);
        }
        // Establecer el conjunto de caracteres a utf8
        $this->conn->set_charset("utf8");
    }
    public function conectar() {
        return $this->conn;
    }
    
    public function __destruct() {
        $this->conn->close();
    }
}
?>
