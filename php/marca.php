<?php
require_once 'bd/conexion.php';

class marca {

    //Estas dos primeras sentencias son obligatorias para conectar el objeto con la base de datos
    private $pdo;

    public function __construct() {
        $this->pdo = Conexion::conectar();
    }

    // Para crear nuevo registro
    public function crear($nombre,$img) {
        $stmt = $this->pdo->prepare("INSERT INTO marca (marca_nombre, marca_imagen) VALUES (?, ?)");
        return $stmt->execute([$nombre,$img]);
    }

    // Leer todos los registros: para enlistar datos
    public function leer_todos() {
        $stmt = $this->pdo->query("SELECT * FROM marca WHERE marca_estado = 1");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Leer un registro por ID (para actualizar un registro)
    public function leer_por_id($id) {
        $stmt = $this->pdo->prepare("SELECT * FROM marca WHERE marca_id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Actualizar registro
    public function actualizar($nombre,$img, $id) {
        $stmt = $this->pdo->prepare("UPDATE marca SET marca_nombre = ?, marca_imagen = ? WHERE marca_id = ?");
        return $stmt->execute([$nombre,$img, $id]);
    }

    // Eliminar registro
    public function deshabilitar($id) {
    
    // 1. Reasignar los bienes del registro eliminado a uno por defecto "Desconocido"
    $stmt1 = $this->pdo->prepare("UPDATE bien SET marca_id = 1 WHERE marca_id = ?");
    $stmt1->execute([$id]);

    // 2. Eliminar el registro
    $stmt2 = $this->pdo->prepare("UPDATE marca SET marca_estado = 0 WHERE marca_id = ?");
    return $stmt2->execute([$id]);
    }
}
?>