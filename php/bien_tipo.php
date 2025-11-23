<?php
require_once '../bd/conexion.php';
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}


class bien_tipo {
    private $pdo;

    public function __construct() {
        try {
            $this->pdo = Conexion::conectar();
            if ($this->pdo) {
                error_log("[bien_tipo] Conexión establecida correctamente");
            }
        } catch (Exception $e) {
            error_log("[bien_tipo] Error al conectar: " . $e->getMessage());
            throw $e;
        }
    }

    // Validación de código único
    public function existeCodigo($codigo, $excluirId = null) {
        try {
            $sql = "SELECT COUNT(*) FROM bien_tipo WHERE bien_tipo_codigo = ?";
            $params = [$codigo];

            if ($excluirId !== null) {
                $sql .= " AND bien_tipo_id != ?";
                $params[] = $excluirId;
            }

            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
            $count = $stmt->fetchColumn();
            error_log("[bien_tipo] existeCodigo($codigo) → $count registros");
            return $count > 0;
        } catch (Exception $e) {
            error_log("[bien_tipo] Error en existeCodigo: " . $e->getMessage());
            throw $e;
        }
    }

    // Crear nuevo bien_tipo
    public function crear($codigo, $nombre, $modelo, $marcaId, $clasificacionId, $descripcion, $estadoId, $imagen) {
        try {
            // Normalizar valores
            $marcaId = !empty($marcaId) ? (int)$marcaId : null;
            $clasificacionId = !empty($clasificacionId) ? (int)$clasificacionId : null;
            $estadoId = (int)$estadoId;

            $sql = "INSERT INTO bien_tipo (
                        bien_tipo_codigo, bien_nombre, bien_modelo, marca_id,
                        clasificacion_id, bien_descripcion,
                        bien_estado, bien_imagen
                    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
            $stmt = $this->pdo->prepare($sql);

            $params = [
                $codigo, $nombre, $modelo, $marcaId,
                $clasificacionId, $descripcion,
                $estadoId, $imagen
            ];

            error_log("[bien_tipo] crear() con parámetros: " . json_encode($params));

            if (!$stmt->execute($params)) {
                $error = $stmt->errorInfo();
                error_log("[bien_tipo] Error SQL al crear: " . implode(" | ", $error));
                throw new Exception("Error SQL: " . $error[2]);
            }

            error_log("[bien_tipo] Registro creado correctamente");
            return true;
        } catch (Exception $e) {
            error_log("[bien_tipo] Excepción en crear: " . $e->getMessage());
            throw $e;
        }
    }

    // Leer bienes por estado lógico y filtros
    public function leer_por_estado($estado = 1, $categoriaId = '', $clasificacionId = '') {
        try {
            $sql = "SELECT 
                        bt.bien_tipo_id,
                        bt.bien_tipo_codigo,
                        bt.bien_nombre,
                        bt.bien_modelo,
                        bt.bien_descripcion,
                        bt.bien_estado,
                        bt.bien_imagen,
                        cl.clasificacion_id,
                        cl.clasificacion_nombre,
                        cat.categoria_id,
                        cat.categoria_nombre,
                        cat.categoria_tipo,
                        bt.marca_id,
                        m.marca_nombre
                    FROM bien_tipo bt
                    LEFT JOIN clasificacion cl 
                        ON bt.clasificacion_id = cl.clasificacion_id
                    LEFT JOIN categoria cat 
                        ON cl.categoria_id = cat.categoria_id
                    LEFT JOIN marca m 
                        ON bt.marca_id = m.marca_id
                    WHERE bt.bien_estado = ?";
            
            $params = [$estado];

            // Filtro por categoría si se envía
            if ($categoriaId !== '') {
                $sql .= " AND cl.categoria_id = ?";
                $params[] = (int)$categoriaId;
            }

            // Filtro por clasificación si se envía
            if ($clasificacionId !== '') {
                $sql .= " AND bt.clasificacion_id = ?";
                $params[] = (int)$clasificacionId;
            }

            $sql .= " ORDER BY bt.bien_nombre ASC";

            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("[bien_tipo] Error en leer_por_estado: " . $e->getMessage());
            throw $e;
        }
    }

    // Leer bien_tipo por ID con su clasificación y categoría
    public function leer_por_id($id) {
        try {
            $sql = "SELECT 
                        bt.bien_tipo_id, 
                        bt.bien_tipo_codigo, 
                        bt.bien_nombre, 
                        bt.bien_modelo,
                        bt.bien_descripcion, 
                        bt.bien_estado, 
                        bt.bien_imagen,
                        bt.clasificacion_id, 
                        cl.clasificacion_nombre,
                        cat.categoria_id, 
                        cat.categoria_nombre, 
                        cat.categoria_tipo,
                        bt.marca_id, 
                        m.marca_nombre
                    FROM bien_tipo bt
                    LEFT JOIN clasificacion cl 
                        ON bt.clasificacion_id = cl.clasificacion_id
                    LEFT JOIN categoria cat 
                        ON cl.categoria_id = cat.categoria_id
                    LEFT JOIN marca m 
                        ON bt.marca_id = m.marca_id
                    WHERE bt.bien_tipo_id = ?";
            
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([(int)$id]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);

            error_log("[bien_tipo] leer_por_id($id) → " . json_encode($result));
            return $result;
        } catch (Exception $e) {
            error_log("[bien_tipo] Error en leer_por_id: " . $e->getMessage());
            throw $e;
        }
    }





    // Actualizar bien_tipo
    public function actualizar($codigo, $nombre, $modelo, $marcaId, $clasificacionId, $descripcion, $estadoId, $imagen, $id) {
        try {
            // Normalizar valores
            $marcaId = !empty($marcaId) ? (int)$marcaId : null;
            $clasificacionId = !empty($clasificacionId) ? (int)$clasificacionId : null;
            $estadoId = (int)$estadoId;

            $sql = "UPDATE bien_tipo SET
                        bien_tipo_codigo = ?, bien_nombre = ?, bien_modelo = ?, marca_id = ?,
                        clasificacion_id = ?, bien_descripcion = ?,
                        bien_estado = ?, bien_imagen = ?
                    WHERE bien_tipo_id = ?";
            $stmt = $this->pdo->prepare($sql);

            $params = [
                $codigo, $nombre, $modelo, $marcaId,
                $clasificacionId, $descripcion,
                $estadoId, $imagen, (int)$id
            ];

            error_log("[bien_tipo] actualizar() con parámetros: " . json_encode($params));

            if (!$stmt->execute($params)) {
                $error = $stmt->errorInfo();
                error_log("[bien_tipo] Error SQL al actualizar: " . implode(" | ", $error));
                throw new Exception("Error SQL: " . $error[2]);
            }

            error_log("[bien_tipo] Registro actualizado correctamente");
            return true;
        } catch (Exception $e) {
            error_log("[bien_tipo] Excepción en actualizar: " . $e->getMessage());
            throw $e;
        }
    }

    // Desincorporar bien_tipo (estado lógico)
    public function desincorporar($id) {
        try {
            $stmt = $this->pdo->prepare("UPDATE bien_tipo SET bien_estado = 0 WHERE bien_tipo_id = ?");
            $ok = $stmt->execute([(int)$id]);
            error_log("[bien_tipo] desincorporar($id) → " . ($ok ? "OK" : "Fallo"));
            return $ok;
        } catch (Exception $e) {
            error_log("[bien_tipo] Error en desincorporar: " . $e->getMessage());
            throw $e;
        }
    }

    // Recuperar bien_tipo
    public function recuperar($id) {
        try {
            $stmt = $this->pdo->prepare("UPDATE bien_tipo SET bien_estado = 1 WHERE bien_tipo_id = ?");
            $ok = $stmt->execute([(int)$id]);
            error_log("[bien_tipo] recuperar($id) → " . ($ok ? "OK" : "Fallo"));
            return $ok;
        } catch (Exception $e) {
            error_log("[bien_tipo] Error en recuperar: " . $e->getMessage());
            throw $e;
        }
    }
}

?>
