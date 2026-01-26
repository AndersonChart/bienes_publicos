<?php
require_once '../bd/conexion.php';
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

class desincorporacion {
    private $pdo;

    public function __construct() {
        $this->pdo = Conexion::conectar();
        $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }

    // Listar desincorporaciones
    public function leer_por_estado($estado = 1) {
        $sql = "SELECT ajuste_id, ajuste_fecha, ajuste_descripcion, ajuste_tipo, ajuste_estado
                FROM ajuste
                WHERE ajuste_tipo = 0 AND ajuste_estado = ?
                ORDER BY ajuste_fecha DESC, ajuste_id DESC";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([(int)$estado]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Leer desincorporación por ID
    public function leer_por_id($id) {
        $sql = "SELECT ajuste_id, ajuste_fecha, ajuste_descripcion, ajuste_tipo, ajuste_estado
                FROM ajuste
                WHERE ajuste_id = ? AND ajuste_tipo = 0";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([(int)$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}