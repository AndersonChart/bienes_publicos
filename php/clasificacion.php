<?php
require_once '../bd/conexion.php';
session_start();

class clasificacion {

    //Estas dos primeras sentencias son obligatorias para conectar el objeto con la base de datos
    private $pdo;

    public function __construct() {
        $this->pdo = Conexion::conectar();
    }

    public function existeNombre($nombre) {
    $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM clasificacion WHERE clasificacion_nombre = ?");
    $stmt->execute([$nombre]);
    return $stmt->fetchColumn() > 0;
    }

    // Para crear nuevo registro
    public function crear($nombre,$categoria,$descripcion,$estado) {
        $stmt = $this->pdo->prepare("INSERT INTO clasificacion (clasificacion_nombre, categoria_id, clasificacion_descripcion, clasificacion_estado) VALUES (?, ?, ?, ?)");
        return $stmt->execute([$nombre,$categoria,$descripcion,$estado]);
    }

    // Para generar las listas
    public function leer_por_estado($estado = 1) {
        $stmt = $this->pdo->prepare("SELECT * FROM clasificacion WHERE clasificacion_estado = ?");
        $stmt->execute([$estado]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Leer un registro por ID (para actualizar un registro)
    public function leer_por_id($id) {
        $stmt = $this->pdo->prepare("SELECT * FROM clasificacion WHERE clasificacion_id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Actualizar registro
    public function actualizar($nombre,$categoria,$descripcion,$estado,$id) {
        error_log("Actualizando clasificacion ID $id con datos: " . json_encode(func_get_args()));
        $stmt = $this->pdo->prepare("UPDATE clasificacion SET clasificacion_nombre = ?, categoria_id = ?, clasificacion_descripcion = ?, clasificacion_estado = ? WHERE clasificacion_id = ?");
        return $stmt->execute([$nombre,$categoria,$descripcion,$estado,$id]);
    }

    // Desincorporar registro
    public function desincorporar($id) {
        $stmt = $this->pdo->prepare("UPDATE clasificacion SET clasificacion_estado = 0 WHERE clasificacion_id = ?");
        return $stmt->execute([$id]);
    }

    // Recuperar registro
    public function recuperar($id) {
        $stmt = $this->pdo->prepare("UPDATE clasificacion SET clasificacion_estado = 1 WHERE clasificacion_id = ?");
        return $stmt->execute([$id]);
    }
}
?>