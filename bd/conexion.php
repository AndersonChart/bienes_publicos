<?php
// Clase para gestionar la conexión a la base de datos usando PDO
class Conexion {
    public static function conectar() {
        $host = 'localhost';
        $db = 'bienes_demo';
        $user = 'root';
        $pass = '';
        try {
            $pdo = new PDO("mysql:host=$host;dbname=$db", $user, $pass);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            return $pdo;
        } catch (PDOException $e) {
            die("Error de conexión: " . $e->getMessage());
        }
    }
}
?>