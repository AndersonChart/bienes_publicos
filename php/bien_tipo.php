<?php
require_once '../bd/conexion.php';
session_start();

class bien_tipo {
    private $pdo;

    public function __construct() {
        $this->pdo = Conexion::conectar();
    }

    // Validación de código único
    public function existeCodigo($codigo, $excluirId = null) {
        $sql = "SELECT COUNT(*) FROM bien WHERE bien_codigo = ?";
        $params = [$codigo];

        if ($excluirId !== null) {
            $sql .= " AND bien_id != ?";
            $params[] = $excluirId;
        }

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchColumn() > 0;
    }

    // Crear nuevo bien
    public function crear($codigo, $nombre, $modelo, $marcaId, $categoriaId, $clasificacionId, $descripcion, $estadoId, $imagen) {
        $sql = "INSERT INTO bien (
                    bien_codigo, bien_nombre, bien_modelo, marca_id,
                    categoria_id, clasificacion_id, bien_descripcion,
                    estado_id, bien_imagen
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([
            $codigo, $nombre, $modelo, $marcaId,
            $categoriaId, $clasificacionId, $descripcion,
            $estadoId, $imagen
        ]);
    }

    // Leer bienes por estado lógico
    public function leer_por_estado($estado = 1, $categoriaId = null, $clasificacionId = null) {
        $sql = "SELECT * FROM bien WHERE estado_id = ?";
        $params = [$estado];

        if ($categoriaId !== null && $categoriaId !== '') {
            $sql .= " AND categoria_id = ?";
            $params[] = $categoriaId;
        }

        if ($clasificacionId !== null && $clasificacionId !== '') {
            $sql .= " AND clasificacion_id = ?";
            $params[] = $clasificacionId;
        }

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }


    // Leer bien por ID
    public function leer_por_id($id) {
        $stmt = $this->pdo->prepare("SELECT * FROM bien WHERE bien_id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Actualizar bien
    public function actualizar($codigo, $nombre, $modelo, $marcaId, $categoriaId, $clasificacionId, $descripcion, $estadoId, $imagen, $id) {
        $sql = "UPDATE bien SET
                    bien_codigo = ?, bien_nombre = ?, bien_modelo = ?, marca_id = ?,
                    categoria_id = ?, clasificacion_id = ?, bien_descripcion = ?,
                    estado_id = ?, bien_imagen = ?
                WHERE bien_id = ?";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([
            $codigo, $nombre, $modelo, $marcaId,
            $categoriaId, $clasificacionId, $descripcion,
            $estadoId, $imagen, $id
        ]);
    }

    // Desincorporar bien (estado lógico)
    public function desincorporar($id) {
        $stmt = $this->pdo->prepare("UPDATE bien SET estado_id = 0 WHERE bien_id = ?");
        return $stmt->execute([$id]);
    }

    // Recuperar bien
    public function recuperar($id) {
        $stmt = $this->pdo->prepare("UPDATE bien SET estado_id = 1 WHERE bien_id = ?");
        return $stmt->execute([$id]);
    }
}
?>