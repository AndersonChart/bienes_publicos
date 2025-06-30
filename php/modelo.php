<?php
require_once 'bd/conexion.php';

class modelo {

    //Estas dos primeras sentencias son obligatorias para conectar el objeto con la base de datos
    private $pdo;

    public function __construct() {
        $this->pdo = Conexion::conectar();
    }

    // Para crear nuevo registro
    public function crear($nombre,$marca) {
        $stmt = $this->pdo->prepare("INSERT INTO modelo (modelo_nombre, marca_id) VALUES (?, ?)");
        return $stmt->execute([$nombre,$marca]);
    }

    // Leer todos los registros: para enlistar datos, añadido la busqueda de marca_nombre
    public function leer_todos() {
        $stmt = $this->pdo->query(
            "SELECT modelo.*, marca.marca_nombre 
            FROM modelo 
            INNER JOIN marca ON modelo.marca_id = marca.marca_id 
            WHERE modelo.modelo_estado = 1"
    );
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Leer un registro por ID (para actualizar un registro)
    public function leer_por_id($id) {
        $stmt = $this->pdo->prepare("SELECT * FROM modelo WHERE modelo_id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Actualizar registro
    public function actualizar($nombre,$marca, $id) {
        $stmt = $this->pdo->prepare("UPDATE modelo SET modelo_nombre = ?, marca_id = ? WHERE modelo_id = ?");
        return $stmt->execute([$nombre,$marca, $id]);
    }

    // Desincorporar registro
    public function deshabilitar($id) {

    // 1. Reasignar los bienes del registro eliminado a uno por defecto "Desconocido"
    $stmt1 = $this->pdo->prepare("UPDATE bien SET modelo_id = 1 WHERE modelo_id = ?");
    $stmt1->execute([$id]);

    // 2. Desincorporar el registro
    $stmt2 = $this->pdo->prepare("UPDATE modelo SET modelo_estado = 0 WHERE modelo_id = ?");
    return $stmt2->execute([$id]);
    }

    //Funciones adicionales:

    //Buscar nombre de marca por modelo registrado

    public function nombre_marca($id) {
        $stmt = $this->pdo->prepare(
            "SELECT marca.marca_nombre FROM modelo INNER JOIN marca ON modelo.marca_id = marca.marca_id WHERE modelo.modelo_id = ?"
        );
        $stmt->execute([$id]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result ? $result['marca_nombre'] : null;
    }

}
?>