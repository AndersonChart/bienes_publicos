<?php
require_once 'bd/conexion.php';

class bien {

    //Estas dos primeras sentencias son obligatorias para conectar el objeto con la base de datos
    private $pdo;

    public function __construct() {
        $this->pdo = Conexion::conectar();
    }

    // Para crear nuevo registro
    public function crear($serie,$nombre,$descripcion,$categoria,$add,$marca,$modelo,$estado,$imagen,$acta) {
        $stmt = $this->pdo->prepare("INSERT INTO bien (bien_serie, bien_nombre, bien_descripcion, categoria_id, fecha_add, marca_id, modelo_id, estado_id, bien_imagen, bien_acta) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        return $stmt->execute([$serie,$nombre,$descripcion,$categoria,$add,$marca,$modelo,$estado,$imagen,$acta]);
    }

    // Leer todos los registros: para enlistar datos
    public function leer_todos() {
        $stmt = $this->pdo->query("SELECT * FROM bien WHERE estado_id != 4");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Leer un registro por ID (para actualizar un registro)
    public function leer_por_id($id) {
        $stmt = $this->pdo->prepare("SELECT * FROM bien WHERE bien_id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Actualizar registro
    public function actualizar($serie,$nombre,$descripcion,$categoria,$add,$marca,$modelo,$estado,$imagen,$acta, $id) {
        $stmt = $this->pdo->prepare("UPDATE bien SET bien_serie = ?, bien_nombre = ?, bien_descripcion = ?, categoria_id = ?, fecha_add = ?, marca_id = ?, modelo_id = ?, estado_id = ?, bien_imagen = ?, bien_acta = ? WHERE bien_id = ?");
        return $stmt->execute([$serie,$nombre,$descripcion,$categoria,$add,$marca,$modelo,$estado,$imagen,$acta, $id]);
    }

    // Desincorporar registro
    public function desincorporar($id) {
        $stmt = $this->pdo->prepare("UPDATE bien SET estado_id = 4 WHERE bien_id = ?");
        return $stmt->execute([$id]);
    }
}
?>