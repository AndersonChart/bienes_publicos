<?php
require_once '../bd/conexion.php';
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}


class articulo {
    private $pdo;

    public function __construct() {
        try {
            $this->pdo = Conexion::conectar();
            if ($this->pdo) {
                error_log("[articulo] Conexión establecida correctamente");
            }
        } catch (Exception $e) {
            error_log("[articulo] Error al conectar: " . $e->getMessage());
            throw $e;
        }
    }

    // Validación de código único
    public function existeCodigo($codigo, $excluirId = null) {
        try {
            $sql = "SELECT COUNT(*) FROM articulo WHERE articulo_codigo = ?";
            $params = [$codigo];

            if ($excluirId !== null) {
                $sql .= " AND articulo_id != ?";
                $params[] = $excluirId;
            }

            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
            $count = $stmt->fetchColumn();
            error_log("[articulo] existeCodigo($codigo) → $count registros");
            return $count > 0;
        } catch (Exception $e) {
            error_log("[articulo] Error en existeCodigo: " . $e->getMessage());
            throw $e;
        }
    }

    // Crear nuevo articulo
    public function crear($codigo, $nombre, $modelo, $marcaId, $clasificacionId, $descripcion, $estadoId, $imagen) {
        try {
            // Normalizar valores
            $marcaId = !empty($marcaId) ? (int)$marcaId : null;
            $clasificacionId = !empty($clasificacionId) ? (int)$clasificacionId : null;
            $estadoId = (int)$estadoId;

            $sql = "INSERT INTO articulo (
                        articulo_codigo, articulo_nombre, articulo_modelo, marca_id,
                        clasificacion_id, articulo_descripcion,
                        articulo_estado, articulo_imagen
                    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
            $stmt = $this->pdo->prepare($sql);

            $params = [
                $codigo, $nombre, $modelo, $marcaId,
                $clasificacionId, $descripcion,
                $estadoId, $imagen
            ];

            error_log("[articulo] crear() con parámetros: " . json_encode($params));

            if (!$stmt->execute($params)) {
                $error = $stmt->errorInfo();
                error_log("[articulo] Error SQL al crear: " . implode(" | ", $error));
                throw new Exception("Error SQL: " . $error[2]);
            }

            error_log("[articulo] Registro creado correctamente");
            return true;
        } catch (Exception $e) {
            error_log("[articulo] Excepción en crear: " . $e->getMessage());
            throw $e;
        }
    }

    // Leer articulos por estado lógico y filtros
    public function leer_por_estado($estado = 1, $categoriaId = '', $clasificacionId = '') {
        try {
            $sql = "SELECT 
                        a.articulo_id,
                        a.articulo_codigo,
                        a.articulo_nombre,
                        a.articulo_modelo,
                        a.articulo_descripcion,
                        a.articulo_estado,
                        a.articulo_imagen,
                        cl.clasificacion_id,
                        cl.clasificacion_nombre,
                        cat.categoria_id,
                        cat.categoria_nombre,
                        cat.categoria_tipo,
                        a.marca_id,
                        m.marca_nombre
                    FROM articulo a
                    LEFT JOIN clasificacion cl 
                        ON a.clasificacion_id = cl.clasificacion_id
                    LEFT JOIN categoria cat 
                        ON cl.categoria_id = cat.categoria_id
                    LEFT JOIN marca m 
                        ON a.marca_id = m.marca_id
                    WHERE a.articulo_estado = ?";
            
            $params = [$estado];

            // Filtro por categoría si se envía
            if ($categoriaId !== '') {
                $sql .= " AND cl.categoria_id = ?";
                $params[] = (int)$categoriaId;
            }

            // Filtro por clasificación si se envía
            if ($clasificacionId !== '') {
                $sql .= " AND a.clasificacion_id = ?";
                $params[] = (int)$clasificacionId;
            }

            $sql .= " ORDER BY a.articulo_nombre ASC";

            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("[articulo] Error en leer_por_estado: " . $e->getMessage());
            throw $e;
        }
    }

    // Leer articulo por ID con su clasificación y categoría
    public function leer_por_id($id) {
        try {
            $sql = "SELECT 
                        a.articulo_id, 
                        a.articulo_codigo, 
                        a.articulo_nombre, 
                        a.articulo_modelo,
                        a.articulo_descripcion, 
                        a.articulo_estado, 
                        a.articulo_imagen,
                        a.clasificacion_id, 
                        cl.clasificacion_nombre,
                        cat.categoria_id, 
                        cat.categoria_nombre, 
                        cat.categoria_tipo,
                        a.marca_id, 
                        m.marca_nombre
                    FROM articulo a
                    LEFT JOIN clasificacion cl 
                        ON a.clasificacion_id = cl.clasificacion_id
                    LEFT JOIN categoria cat 
                        ON cl.categoria_id = cat.categoria_id
                    LEFT JOIN marca m 
                        ON a.marca_id = m.marca_id
                    WHERE a.articulo_id = ?";
            
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([(int)$id]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);

            error_log("[articulo] leer_por_id($id) → " . json_encode($result));
            return $result;
        } catch (Exception $e) {
            error_log("[articulo] Error en leer_por_id: " . $e->getMessage());
            throw $e;
        }
    }





    // Actualizar articulo
    public function actualizar($codigo, $nombre, $modelo, $marcaId, $clasificacionId, $descripcion, $estadoId, $imagen, $id) {
        try {
            // Normalizar valores
            $marcaId = !empty($marcaId) ? (int)$marcaId : null;
            $clasificacionId = !empty($clasificacionId) ? (int)$clasificacionId : null;
            $estadoId = (int)$estadoId;

            $sql = "UPDATE articulo SET
                        articulo_codigo = ?, articulo_nombre = ?, articulo_modelo = ?, marca_id = ?,
                        clasificacion_id = ?, articulo_descripcion = ?,
                        articulo_estado = ?, articulo_imagen = ?
                    WHERE articulo_id = ?";
            $stmt = $this->pdo->prepare($sql);

            $params = [
                $codigo, $nombre, $modelo, $marcaId,
                $clasificacionId, $descripcion,
                $estadoId, $imagen, (int)$id
            ];

            error_log("[articulo] actualizar() con parámetros: " . json_encode($params));

            if (!$stmt->execute($params)) {
                $error = $stmt->errorInfo();
                error_log("[articulo] Error SQL al actualizar: " . implode(" | ", $error));
                throw new Exception("Error SQL: " . $error[2]);
            }

            error_log("[articulo] Registro actualizado correctamente");
            return true;
        } catch (Exception $e) {
            error_log("[articulo] Excepción en actualizar: " . $e->getMessage());
            throw $e;
        }
    }

    // Desincorporar articulo (estado lógico)
    public function desincorporar($id) {
        try {
            $stmt = $this->pdo->prepare("UPDATE articulo SET articulo_estado = 0 WHERE articulo_id = ?");
            $ok = $stmt->execute([(int)$id]);
            error_log("[articulo] desincorporar($id) → " . ($ok ? "OK" : "Fallo"));
            return $ok;
        } catch (Exception $e) {
            error_log("[articulo] Error en desincorporar: " . $e->getMessage());
            throw $e;
        }
    }

    // Recuperar articulo
    public function recuperar($id) {
        try {
            $stmt = $this->pdo->prepare("UPDATE articulo SET articulo_estado = 1 WHERE articulo_id = ?");
            $ok = $stmt->execute([(int)$id]);
            error_log("[articulo] recuperar($id) → " . ($ok ? "OK" : "Fallo"));
            return $ok;
        } catch (Exception $e) {
            error_log("[articulo] Error en recuperar: " . $e->getMessage());
            throw $e;
        }
    }
}

?>
