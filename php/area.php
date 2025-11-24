<?php
require_once '../bd/conexion.php';
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

class area {

    private $pdo;

    public function __construct() {
        $this->pdo = Conexion::conectar();
    }

    // Verificar duplicados por código
    public function existeCodigo($codigo, $excluirId = null) {
        $sql = "SELECT COUNT(*) FROM area WHERE area_codigo = ?";
        $params = [$codigo];

        if ($excluirId !== null) {
            $sql .= " AND area_id != ?";
            $params[] = $excluirId;
        }

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchColumn() > 0;
    }

    // Verificar duplicados por nombre
    public function existeNombre($nombre, $excluirId = null) {
        $sql = "SELECT COUNT(*) FROM area WHERE area_nombre = ?";
        $params = [$nombre];

        if ($excluirId !== null) {
            $sql .= " AND area_id != ?";
            $params[] = $excluirId;
        }

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchColumn() > 0;
    }

    // Crear nuevo registro
    public function crear($codigo, $nombre, $descripcion, $estado) {
        $stmt = $this->pdo->prepare(
            "INSERT INTO area (area_codigo, area_nombre, area_descripcion, area_estado) 
            VALUES (?, ?, ?, ?)"
        );
        return $stmt->execute([$codigo, $nombre, $descripcion, $estado]);
    }

    // Listar por estado
    public function leer_por_estado($estado = 1) {
        $sql = "SELECT * FROM area WHERE area_estado = ? ORDER BY area_nombre ASC";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$estado]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Leer un registro por ID
    public function leer_por_id($id) {
        $sql = "SELECT * FROM area WHERE area_id = ?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Actualizar registro
    public function actualizar($codigo, $nombre, $descripcion, $estado, $id) {
        try {
            $stmt = $this->pdo->prepare(
                "UPDATE area 
                    SET area_codigo = ?, area_nombre = ?, area_descripcion = ?, area_estado = ? 
                WHERE area_id = ?"
            );
            return $stmt->execute([$codigo, $nombre, $descripcion, $estado, $id]);
        } catch (Exception $e) {
            error_log("[area] Error en actualizar: " . $e->getMessage());
            throw $e;
        }
    }

    // Desincorporar registro
    public function desincorporar($id) {
        $stmt = $this->pdo->prepare("UPDATE area SET area_estado = 0 WHERE area_id = ?");
        return $stmt->execute([$id]);
    }

    // Recuperar registro
    public function recuperar($id) {
        $stmt = $this->pdo->prepare("UPDATE area SET area_estado = 1 WHERE area_id = ?");
        return $stmt->execute([$id]);
    }
}
?>
