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
                        m.marca_nombre,
                        --  columnas de stock
                        SUM(CASE WHEN s.estado_id = 1 THEN 1 ELSE 0 END) AS stock_activos,
                        SUM(CASE WHEN s.estado_id = 2 THEN 1 ELSE 0 END) AS stock_asignados,
                        SUM(CASE WHEN s.estado_id = 3 THEN 1 ELSE 0 END) AS stock_mantenimiento,
                        SUM(CASE WHEN s.estado_id != 4 THEN 1 ELSE 0 END) AS stock_total
                    FROM articulo a
                    LEFT JOIN clasificacion cl ON a.clasificacion_id = cl.clasificacion_id
                    LEFT JOIN categoria cat ON cl.categoria_id = cat.categoria_id
                    LEFT JOIN marca m ON a.marca_id = m.marca_id
                    LEFT JOIN articulo_serial s ON a.articulo_id = s.articulo_id
                    WHERE a.articulo_estado = ?";
            
            $params = [$estado];

            if ($categoriaId !== '') {
                $sql .= " AND cl.categoria_id = ?";
                $params[] = (int)$categoriaId;
            }

            if ($clasificacionId !== '') {
                $sql .= " AND a.clasificacion_id = ?";
                $params[] = (int)$clasificacionId;
            }

            $sql .= " GROUP BY a.articulo_id
                    ORDER BY a.articulo_nombre ASC";

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

// Desincorporar artículo (estado lógico)
public function desincorporar($id) {
    try {
        $this->pdo->beginTransaction();

        // Buscar estados de seriales asociados
        $stmt = $this->pdo->prepare("SELECT estado_id FROM articulo_serial WHERE articulo_id = ?");
        $stmt->execute([(int)$id]);
        $seriales = $stmt->fetchAll(PDO::FETCH_COLUMN);

        if (!empty($seriales)) {
            // Validar que todos estén activos (estado 1)
            foreach ($seriales as $estado) {
                if ((int)$estado !== 1) {
                    throw new Exception('No se puede desincorporar: hay seriales comprometidos.');
                }
            }

            // Pasar seriales a estado 4
            $stmtSeriales = $this->pdo->prepare("UPDATE articulo_serial SET estado_id = 4 WHERE articulo_id = ?");
            $stmtSeriales->execute([(int)$id]);
        }

        // Marcar artículo como deshabilitado
        $stmtArt = $this->pdo->prepare("UPDATE articulo SET articulo_estado = 0 WHERE articulo_id = ?");
        $stmtArt->execute([(int)$id]);

        $this->pdo->commit();
        return ['exito' => true, 'mensaje' => 'Artículo deshabilitado correctamente'];
    } catch (Exception $e) {
        $this->pdo->rollBack();
        error_log("[articulo] Error en desincorporar($id): " . $e->getMessage());
        return ['exito' => false, 'mensaje' => $e->getMessage()];
    }
}

// Recuperar artículo
public function recuperar($id) {
    try {
        $this->pdo->beginTransaction();

        // Buscar seriales desincorporados del artículo
        $stmt = $this->pdo->prepare(
            "SELECT articulo_serial 
            FROM articulo_serial 
            WHERE articulo_id = ? AND estado_id = 4"
        );
        $stmt->execute([(int)$id]);
        $seriales = $stmt->fetchAll(PDO::FETCH_COLUMN);

        // Filtrar seriales válidos (no vacíos)
        $serialesValidos = array_filter(array_map('trim', $seriales), function($s) {
            return $s !== '' && $s !== null;
        });

        // Validar duplicados solo si hay seriales válidos
        if (!empty($serialesValidos)) {
            $placeholders = implode(',', array_fill(0, count($serialesValidos), '?'));
            $sql = "SELECT articulo_serial 
                    FROM articulo_serial 
                    WHERE articulo_serial IN ($placeholders) AND estado_id <> 4";
            $stmtDup = $this->pdo->prepare($sql);
            $stmtDup->execute($serialesValidos);
            $repetidos = $stmtDup->fetchAll(PDO::FETCH_COLUMN);

            if (!empty($repetidos)) {
                throw new Exception('No se puede recuperar: seriales repetidos (' . implode(', ', $repetidos) . ')');
            }
        }

        // Marcar artículo como habilitado
        $stmtArt = $this->pdo->prepare("UPDATE articulo SET articulo_estado = 1 WHERE articulo_id = ?");
        $stmtArt->execute([(int)$id]);

        // Reactivar seriales (si existen)
        $stmtSeriales = $this->pdo->prepare(
            "UPDATE articulo_serial SET estado_id = 1 WHERE articulo_id = ? AND estado_id = 4"
        );
        $stmtSeriales->execute([(int)$id]);

        $this->pdo->commit();
        return ['exito' => true, 'mensaje' => 'Artículo recuperado correctamente'];
    } catch (Exception $e) {
        $this->pdo->rollBack();
        error_log("[articulo] Error en recuperar($id): " . $e->getMessage());
        return ['exito' => false, 'mensaje' => $e->getMessage()];
    }
}

    //METODOS PARA LOS SERIALES

        // 1. Validar serial duplicado en BD (ignora desincorporados estado_id = 4)
    public function existeSerial($serial, $excluirId = null) {
        try {
            $sql = "SELECT COUNT(*) FROM articulo_serial 
                    WHERE articulo_serial = ? AND estado_id != 4";
            $params = [$serial];

            if ($excluirId !== null) {
                $sql .= " AND articulo_serial_id != ?";
                $params[] = $excluirId;
            }

            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchColumn() > 0;
        } catch (Exception $e) {
            throw $e;
        }
    }

    // 2. Mostrar todos los seriales por artículo (excepto desincorporados)
    public function leer_seriales_articulo($articuloId) {
        try {
            $sql = "SELECT articulo_serial_id AS id,
                            articulo_serial AS serial,
                            articulo_serial_observacion AS observacion,
                            estado_id AS estado
                    FROM articulo_serial
                    WHERE articulo_id = ? AND estado_id != 4
                    ORDER BY articulo_serial_id ASC";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([(int)$articuloId]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            throw $e;
        }
    }

    // 3. Actualizar seriales (buffer → BD)
    public function actualizar_seriales($articuloId, $seriales) {
        try {
            $this->pdo->beginTransaction();

            foreach ($seriales as $s) {
                $id = (int)$s['id'];
                $serial = trim($s['serial']);
                $observacion = trim($s['observacion']);
                $estado = (int)$s['estado'];

                // Validar duplicado en BD
                if ($serial !== '' && $this->existeSerial($serial, $id)) {
                    throw new Exception("El serial {$serial} ya existe en el inventario.");
                }

                $sql = "UPDATE articulo_serial
                        SET articulo_serial = ?, 
                            articulo_serial_observacion = ?, 
                            estado_id = ?
                        WHERE articulo_serial_id = ? AND articulo_id = ?";
                $stmt = $this->pdo->prepare($sql);
                $stmt->execute([$serial, $observacion, $estado, $id, $articuloId]);
            }

            $this->pdo->commit();
            return ['exito' => true];
        } catch (Exception $e) {
            $this->pdo->rollBack();
            return ['exito' => false, 'mensaje' => $e->getMessage()];
        }
    }

    // 4. Stock por estado (activos=1, asignados=2, mantenimiento=3, total excluye desincorporados=4)
    public function obtener_stock_articulo($articuloId) {
        try {
            $sql = "SELECT 
                        SUM(CASE WHEN estado_id = 1 THEN 1 ELSE 0 END) AS activos,
                        SUM(CASE WHEN estado_id = 2 THEN 1 ELSE 0 END) AS asignados,
                        SUM(CASE WHEN estado_id = 3 THEN 1 ELSE 0 END) AS mantenimiento,
                        SUM(CASE WHEN estado_id != 4 THEN 1 ELSE 0 END) AS total
                    FROM articulo_serial
                    WHERE articulo_id = ?";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([(int)$articuloId]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            throw $e;
        }
    }

}

?>
