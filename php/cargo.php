<?php
require_once '../bd/conexion.php';
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

class recepcion {

    private $pdo;

    public function __construct() {
        $this->pdo = Conexion::conectar();
    }

    // Crear nueva recepción (ajuste tipo 1 = entrada)
    public function crear($fecha, $descripcion) {
        $stmt = $this->pdo->prepare(
            "INSERT INTO ajuste (ajuste_fecha, ajuste_descripcion, ajuste_tipo) 
             VALUES (?, ?, 1)"
        );
        return $stmt->execute([$fecha, $descripcion]);
    }

    // Listar todas las recepciones (solo tipo entrada)
    public function leer_todas() {
        $sql = "SELECT * FROM ajuste WHERE ajuste_tipo = 1 ORDER BY ajuste_fecha DESC";
        $stmt = $this->pdo->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Leer una recepción por ID
    public function leer_por_id($id) {
        $sql = "SELECT * FROM ajuste WHERE ajuste_id = ? AND ajuste_tipo = 1";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Actualizar recepción (fecha y descripción)
    public function actualizar($fecha, $descripcion, $id) {
        $stmt = $this->pdo->prepare(
            "UPDATE ajuste 
             SET ajuste_fecha = ?, ajuste_descripcion = ? 
             WHERE ajuste_id = ? AND ajuste_tipo = 1"
        );
        return $stmt->execute([$fecha, $descripcion, $id]);
    }

    // Asociar artículos (seriales) a una recepción
    public function agregar_articulo($ajuste_id, $serial_id) {
        $stmt = $this->pdo->prepare(
            "INSERT INTO ajuste_articulo (articulo_serial_id, ajuste_id) 
             VALUES (?, ?)"
        );
        return $stmt->execute([$serial_id, $ajuste_id]);
    }

    // Listar artículos asociados a una recepción
    public function leer_articulos($ajuste_id) {
        $sql = "SELECT * FROM ajuste_articulo WHERE ajuste_id = ?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$ajuste_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>
