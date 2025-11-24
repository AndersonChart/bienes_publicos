<?php
require_once '../bd/conexion.php';
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

class cargo {

    private $pdo;

    public function __construct() {
        $this->pdo = Conexion::conectar();
    }

    // Verificar duplicados por código
    public function existeCodigo($codigo, $excluirId = null) {
        $sql = "SELECT COUNT(*) FROM cargo WHERE cargo_codigo = ?";
        $params = [$codigo];

        if ($excluirId !== null) {
            $sql .= " AND cargo_id != ?";
            $params[] = $excluirId;
        }

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchColumn() > 0;
    }

    // Verificar duplicados por nombre
    public function existeNombre($nombre, $excluirId = null) {
        $sql = "SELECT COUNT(*) FROM cargo WHERE cargo_nombre = ?";
        $params = [$nombre];

        if ($excluirId !== null) {
            $sql .= " AND cargo_id != ?";
            $params[] = $excluirId;
        }

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchColumn() > 0;
    }

    // Crear nuevo registro
    public function crear($codigo, $nombre, $descripcion, $estado) {
        $stmt = $this->pdo->prepare(
            "INSERT INTO cargo (cargo_codigo, cargo_nombre, cargo_descripcion, cargo_estado) 
            VALUES (?, ?, ?, ?)"
        );
        return $stmt->execute([$codigo, $nombre, $descripcion, $estado]);
    }

    // Listar por estado
    public function leer_por_estado($estado = 1) {
        $sql = "SELECT * FROM cargo WHERE cargo_estado = ? ORDER BY cargo_nombre ASC";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$estado]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Leer un registro por ID
    public function leer_por_id($id) {
        $sql = "SELECT * FROM cargo WHERE cargo_id = ?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Actualizar registro
    public function actualizar($codigo, $nombre, $descripcion, $estado, $id) {
        try {
            $stmt = $this->pdo->prepare(
                "UPDATE cargo 
                    SET cargo_codigo = ?, cargo_nombre = ?, cargo_descripcion = ?, cargo_estado = ? 
                WHERE cargo_id = ?"
            );
            return $stmt->execute([$codigo, $nombre, $descripcion, $estado, $id]);
        } catch (Exception $e) {
            error_log("[cargo] Error en actualizar: " . $e->getMessage());
            throw $e;
        }
    }

    // Desincorporar registro
    public function desincorporar($id) {
        $stmt = $this->pdo->prepare("UPDATE cargo SET cargo_estado = 0 WHERE cargo_id = ?");
        return $stmt->execute([$id]);
    }

    // Recuperar registro
    public function recuperar($id) {
        $stmt = $this->pdo->prepare("UPDATE cargo SET cargo_estado = 1 WHERE cargo_id = ?");
        return $stmt->execute([$id]);
    }
}
?>
