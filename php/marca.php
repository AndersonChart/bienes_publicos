<?php
require_once '../bd/conexion.php';
session_start();

class marca {

    //Estas dos primeras sentencias son obligatorias para conectar el objeto con la base de datos
    private $pdo;

    public function __construct() {
        $this->pdo = Conexion::conectar();
    }

    public function existeCodigo($codigo, $excluirId = null) {
        $sql = "SELECT COUNT(*) FROM marca WHERE marca_codigo = ?";
        $params = [$codigo];

        if ($excluirId !== null) {
            $sql .= " AND marca_id != ?";
            $params[] = $excluirId;
        }

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchColumn() > 0;
    }


    public function existeNombre($nombre, $excluirId = null) {
        $sql = "SELECT COUNT(*) FROM marca WHERE marca_nombre = ?";
        $params = [$nombre];

        if ($excluirId !== null) {
            $sql .= " AND marca_id != ?";
            $params[] = $excluirId;
        }

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchColumn() > 0;
    }

    // Para crear nuevo registro
    public function crear($codigo, $nombre, $imagen, $estado) {
        $stmt = $this->pdo->prepare("INSERT INTO marca (marca_codigo, marca_nombre, marca_imagen, marca_estado) VALUES (?, ?, ?, ?)");
        return $stmt->execute([$codigo, $nombre, $imagen, $estado]);
    }


    // Para generar las listas
    public function leer_por_estado($estado = 1) {
        $stmt = $this->pdo->prepare("SELECT * FROM marca WHERE marca_estado = ?");
        $stmt->execute([$estado]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Leer un registro por ID (para actualizar un registro)
    public function leer_por_id($id) {
        $sql = "SELECT *
                FROM marca
                WHERE marca_id = ?";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }


    // Actualizar registro
    public function actualizar($codigo, $nombre, $imagen, $estado, $id) {
        $stmt = $this->pdo->prepare("UPDATE marca SET marca_codigo = ?, marca_nombre = ?, marca_imagen = ?, marca_estado = ? WHERE marca_id = ?");
        return $stmt->execute([$codigo, $nombre, $imagen, $estado, $id]);
    }

    // Desincorporar registro
    public function desincorporar($id) {
        $stmt = $this->pdo->prepare("UPDATE marca SET marca_estado = 0 WHERE marca_id = ?");
        return $stmt->execute([$id]);
    }

    // Recuperar registro
    public function recuperar($id) {
        $stmt = $this->pdo->prepare("UPDATE marca SET marca_estado = 1 WHERE marca_id = ?");
        return $stmt->execute([$id]);
    }
}
?>