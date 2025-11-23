<?php
require_once '../bd/conexion.php';
session_start();

class categoria {

    private $pdo;

    public function __construct() {
        $this->pdo = Conexion::conectar();
    }

    // Verificar duplicados por código
    public function existeCodigo($codigo, $excluirId = null) {
        $sql = "SELECT COUNT(*) FROM categoria WHERE categoria_codigo = ?";
        $params = [$codigo];

        if ($excluirId !== null) {
            $sql .= " AND categoria_id != ?";
            $params[] = $excluirId;
        }

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchColumn() > 0;
    }

    // Verificar duplicados por nombre
    public function existeNombre($nombre, $excluirId = null) {
        $sql = "SELECT COUNT(*) FROM categoria WHERE categoria_nombre = ?";
        $params = [$nombre];

        if ($excluirId !== null) {
            $sql .= " AND categoria_id != ?";
            $params[] = $excluirId;
        }

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchColumn() > 0;
    }

    // Crear nuevo registro
    public function crear($codigo, $nombre, $tipo, $descripcion, $estado) {
        $stmt = $this->pdo->prepare(
            "INSERT INTO categoria (categoria_codigo, categoria_nombre, categoria_tipo, categoria_descripcion, categoria_estado) 
            VALUES (?, ?, ?, ?, ?)"
        );
        return $stmt->execute([$codigo, $nombre, $tipo, $descripcion, $estado]);
    }

    // Listar por estado (sin filtro de tipo)
    public function leer_por_estado($estado = 1) {
        $sql = "SELECT * FROM categoria WHERE categoria_estado = ? ORDER BY categoria_nombre ASC";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$estado]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Listar por estado y tipo (para el filtro)
    public function leer_por_estado_y_tipo($estado = 1, $tipo) {
        $sql = "SELECT * FROM categoria WHERE categoria_estado = ? AND categoria_tipo = ? ORDER BY categoria_nombre ASC";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$estado, $tipo]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Leer un registro por ID
    public function leer_por_id($id) {
        $sql = "SELECT * FROM categoria WHERE categoria_id = ?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Actualizar registro de categoría y sincronizar bienes
    public function actualizar($codigo, $nombre, $tipo, $descripcion, $estado, $id) {
        try {
            $this->pdo->beginTransaction();

            // Actualizar categoría
            $stmt = $this->pdo->prepare(
                "UPDATE categoria 
                    SET categoria_codigo = ?, categoria_nombre = ?, categoria_tipo = ?, categoria_descripcion = ?, categoria_estado = ? 
                WHERE categoria_id = ?"
            );
            $stmt->execute([$codigo, $nombre, $tipo, $descripcion, $estado, $id]);

            // Si cambia a básico (0), limpiar modelo y marca de todos los bienes ligados
            if ((int)$tipo === 0) {
                $sqlBienes = "UPDATE bien_tipo 
                            SET bien_modelo = '', marca_id = NULL 
                            WHERE clasificacion_id IN (
                                SELECT clasificacion_id FROM clasificacion WHERE categoria_id = ?
                            )";
                $stmtBienes = $this->pdo->prepare($sqlBienes);
                $stmtBienes->execute([$id]);
            }

            $this->pdo->commit();
            return true;
        } catch (Exception $e) {
            $this->pdo->rollBack();
            error_log("[categoria] Error en actualizar: " . $e->getMessage());
            throw $e;
        }
    }


    // Desincorporar registro
    public function desincorporar($id) {
        $stmt = $this->pdo->prepare("UPDATE categoria SET categoria_estado = 0 WHERE categoria_id = ?");
        return $stmt->execute([$id]);
    }

    // Recuperar registro
    public function recuperar($id) {
        $stmt = $this->pdo->prepare("UPDATE categoria SET categoria_estado = 1 WHERE categoria_id = ?");
        return $stmt->execute([$id]);
    }
}
?>
