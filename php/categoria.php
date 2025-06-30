<?php

require_once 'bd/conexion.php';

class categoria {

    //Estas dos primeras sentencias son obligatorias para conectar el objeto con la base de datos
    private $pdo;

    public function __construct() {
        $this->pdo = Conexion::conectar();
    }

    // Para crear nuevo registro
    public function crear($nombre,$descripcion) {
        $stmt = $this->pdo->prepare("INSERT INTO categoria (categoria_nombre, categoria_descripcion) VALUES (?, ?)");
        return $stmt->execute([$nombre,$descripcion]);
    }

    // Leer todos los registros activos: para enlistar datos
    public function leer_todos() {
        $stmt = $this->pdo->query("SELECT * FROM categoria WHERE categoria_estado = 1");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Leer un registro por ID (para actualizar un registro)
    public function leer_por_id($id) {
        $stmt = $this->pdo->prepare("SELECT * FROM categoria WHERE categoria_id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Actualizar registro
    public function actualizar($nombre,$descripcion, $id) {
        $stmt = $this->pdo->prepare("UPDATE categoria SET categoria_nombre = ?, categoria_descripcion = ? WHERE categoria_id = ?");
        return $stmt->execute([$nombre,$descripcion, $id]);
    }

    // Desincorporar registro
    public function deshabilitar($id) {

    // 1. Reasignar los bienes del registro eliminado a uno por defecto "Ninguno"
    $stmt1 = $this->pdo->prepare("UPDATE bien SET categoria_id = 1 WHERE categoria_id = ?");
    $stmt1->execute([$id]);

    // 2. Desincorporar el registro
    $stmt2 = $this->pdo->prepare("UPDATE categoria SET categoria_estado = 0 WHERE categoria_id = ?");
    return $stmt2->execute([$id]);
    }
}
?>