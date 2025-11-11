<?php
require_once '../bd/conexion.php';
session_start();

class clasificacion {

    //Estas dos primeras sentencias son obligatorias para conectar el objeto con la base de datos
    private $pdo;

    public function __construct() {
        $this->pdo = Conexion::conectar();
    }

    public function existeCodigo($codigo, $excluirId = null) {
        $sql = "SELECT COUNT(*) FROM clasificacion WHERE clasificacion_codigo = ?";
        $params = [$codigo];

        if ($excluirId !== null) {
            $sql .= " AND clasificacion_id != ?";
            $params[] = $excluirId;
        }

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchColumn() > 0;
    }


    public function existeNombre($nombre, $excluirId = null) {
        $sql = "SELECT COUNT(*) FROM clasificacion WHERE clasificacion_nombre = ?";
        $params = [$nombre];

        if ($excluirId !== null) {
            $sql .= " AND clasificacion_id != ?";
            $params[] = $excluirId;
        }

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchColumn() > 0;
    }


    // Para crear nuevo registro
    public function crear($codigo, $nombre, $categoria, $descripcion, $estado) {
        $stmt = $this->pdo->prepare("INSERT INTO clasificacion (clasificacion_codigo, clasificacion_nombre, categoria_id, clasificacion_descripcion, clasificacion_estado) VALUES (?, ?, ?, ?, ?)");
        return $stmt->execute([$codigo, $nombre, $categoria, $descripcion, $estado]);
    }


    // Para generar las listas
    public function leer_por_estado($estado = 1, $categoriaId = null) {
        $sql = "SELECT c.*, cat.categoria_nombre 
                FROM clasificacion c
                JOIN categoria cat ON c.categoria_id = cat.categoria_id
                WHERE c.clasificacion_estado = ?";
        $params = [$estado];

        if ($categoriaId !== null && $categoriaId !== '') {
            $sql .= " AND c.categoria_id = ?";
            $params[] = $categoriaId;
        }

        $sql .= " ORDER BY c.clasificacion_nombre ASC";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }



    // Leer un registro por ID (para actualizar un registro)
    public function leer_por_id($id) {
        $sql = "SELECT c.*, cat.categoria_nombre
                FROM clasificacion c
                JOIN categoria cat ON c.categoria_id = cat.categoria_id
                WHERE c.clasificacion_id = ?";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }


    // Actualizar registro
    public function actualizar($codigo, $nombre, $categoria, $descripcion, $estado, $id) {
        $stmt = $this->pdo->prepare("UPDATE clasificacion SET clasificacion_codigo = ?, clasificacion_nombre = ?, categoria_id = ?, clasificacion_descripcion = ?, clasificacion_estado = ? WHERE clasificacion_id = ?");
        return $stmt->execute([$codigo, $nombre, $categoria, $descripcion, $estado, $id]);
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