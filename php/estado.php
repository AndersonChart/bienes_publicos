<?php
require_once '../bd/conexion.php';
session_start();

class estado {

    private $pdo;

    public function __construct() {
        $this->pdo = Conexion::conectar();
    }

    public function leer_todas() {
        $stmt = $this->pdo->prepare("SELECT estado_id, estado_nombre 
                                    FROM estado 
                                    WHERE estado_id != 4 
                                    ORDER BY estado_nombre ASC");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>
