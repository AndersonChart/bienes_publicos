<?php
require_once '../bd/conexion.php';
session_start();

class recepcion {

    private $pdo;

    public function __construct() {
        $this->pdo = Conexion::conectar();
    }

    // Crear nueva recepción (ajuste tipo entrada = 1, estado por defecto habilitado = 1)
    public function crear($fecha, $descripcion) {
        $stmt = $this->pdo->prepare(
                "INSERT INTO ajuste (ajuste_fecha, ajuste_descripcion, ajuste_tipo, ajuste_estado) 
                VALUES (?, ?, 1, 1)"
        );
        return $stmt->execute([$fecha, $descripcion]);
    }

    // Listar recepciones (entradas) por estado
    public function leer_por_estado($estado = 1) {
        $sql = "SELECT * 
                FROM ajuste 
                WHERE ajuste_tipo = 1 
                AND ajuste_estado = ? 
                ORDER BY ajuste_fecha DESC";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$estado]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Leer recepción por ID
    public function leer_por_id($id) {
        $sql = "SELECT * 
                FROM ajuste 
                WHERE ajuste_id = ? 
                AND ajuste_tipo = 1";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Anular recepción (deshabilitar = estado 0)
    public function anular($id) {
        $stmt = $this->pdo->prepare(
            "UPDATE ajuste 
            SET ajuste_estado = 0 
            WHERE ajuste_id = ? 
            AND ajuste_tipo = 1"
        );
        return $stmt->execute([$id]);
    }
}
?>
