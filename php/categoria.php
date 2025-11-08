<?php
require_once '../bd/conexion.php';
session_start();

class categoria {

    private $pdo;

    public function __construct() {
        $this->pdo = Conexion::conectar();
    }

    public function leer_todas() {
        $stmt = $this->pdo->prepare("SELECT categoria_id, categoria_nombre FROM categoria ORDER BY categoria_nombre ASC");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>
