<?php
require_once '../bd/conexion.php';
session_start();

class rol {

    private $pdo;

    public function __construct() {
        $this->pdo = Conexion::conectar();
    }

    public function leer_todas() {
        $stmt = $this->pdo->prepare("SELECT rol_id, rol_nombre FROM rol ORDER BY rol_nombre ASC");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>
