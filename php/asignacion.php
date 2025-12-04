<?php
require_once '../bd/conexion.php';
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

class asignacion {
    private $pdo;

    public function __construct() {
        $this->pdo = Conexion::conectar();
    }

    // Crear nueva asignación
    public function crear($areaId, $personaId, $fecha, $descripcion, $seriales = [], $fechaFin = null) {
        try {
            $this->pdo->beginTransaction();

            // Insertar cabecera de asignación
            $stmtCab = $this->pdo->prepare(
                "INSERT INTO asignacion (area_id, persona_id, asignacion_fecha, asignacion_fecha_fin, asignacion_descripcion, asignacion_estado)
                VALUES (?, ?, ?, ?, ?, 1)"
            );
            $stmtCab->execute([$areaId, $personaId, $fecha, $fechaFin, $descripcion]);
            $asignacion_id = $this->pdo->lastInsertId();

            if (!empty($seriales)) {
                // Insertar vínculo y actualizar estado
                $stmtDetalle = $this->pdo->prepare(
                    "INSERT INTO asignacion_articulo (articulo_serial_id, asignacion_id) VALUES (?, ?)"
                );
                $stmtUpdateSerial = $this->pdo->prepare(
                    "UPDATE articulo_serial SET estado_id = 2 WHERE articulo_serial_id = ?"
                );

                foreach ($seriales as $serialId) {
                    $stmtDetalle->execute([$serialId, $asignacion_id]);
                    $stmtUpdateSerial->execute([$serialId]);
                }
            }

            $this->pdo->commit();
            return $asignacion_id;
        } catch (Exception $e) {
            $this->pdo->rollBack();
            throw $e;
        }
    }

    public function reasignar($id, $areaId, $personaId, $fechaInicio, $descripcion, $seriales = [], $fechaFin = null) {
    try {
        $this->pdo->beginTransaction();

        // Actualizar cabecera
        $stmtCab = $this->pdo->prepare(
            "UPDATE asignacion 
            SET area_id = ?, persona_id = ?, asignacion_fecha = ?, asignacion_fecha_fin = ?, asignacion_descripcion = ?
            WHERE asignacion_id = ?"
        );
        $stmtCab->execute([$areaId, $personaId, $fechaInicio, $fechaFin, $descripcion, (int)$id]);

        // Seriales previos
        $stmtSel = $this->pdo->prepare("SELECT articulo_serial_id FROM asignacion_articulo WHERE asignacion_id = ?");
        $stmtSel->execute([(int)$id]);
        $serialesPrevios = $stmtSel->fetchAll(PDO::FETCH_COLUMN);

        $serialesNuevos   = array_map('intval', $seriales);
        $serialesPrevios  = array_map('intval', $serialesPrevios);

        // Liberar los que ya no están
        $serialesLiberar = array_diff($serialesPrevios, $serialesNuevos);
        if (!empty($serialesLiberar)) {
            $stmtDel = $this->pdo->prepare("DELETE FROM asignacion_articulo WHERE asignacion_id = ? AND articulo_serial_id = ?");
            $stmtUpdateLibre = $this->pdo->prepare("UPDATE articulo_serial SET estado_id = 1 WHERE articulo_serial_id = ?");
            foreach ($serialesLiberar as $serialId) {
                $stmtDel->execute([(int)$id, $serialId]);
                $stmtUpdateLibre->execute([$serialId]);
            }
        }

        // Agregar los nuevos
        $serialesAgregar = array_diff($serialesNuevos, $serialesPrevios);
        if (!empty($serialesAgregar)) {
            $stmtIns = $this->pdo->prepare("INSERT INTO asignacion_articulo (articulo_serial_id, asignacion_id) VALUES (?, ?)");
            $stmtUpdateAsignado = $this->pdo->prepare("UPDATE articulo_serial SET estado_id = 2 WHERE articulo_serial_id = ?");
            foreach ($serialesAgregar as $serialId) {
                $stmtIns->execute([$serialId, (int)$id]);
                $stmtUpdateAsignado->execute([$serialId]);
            }
        }

        $this->pdo->commit();
        return true;
    } catch (Exception $e) {
        $this->pdo->rollBack();
        throw $e;
    }
}



// Listar asignaciones por estado (y opcionalmente filtros)
public function leer_por_estado($estado = 1, $cargoId = '', $personaId = '', $areaId = '') {
    try {
        $sql = "SELECT 
                    a.asignacion_id,
                    a.asignacion_fecha,
                    a.asignacion_fecha_fin,
                    a.asignacion_descripcion,
                    a.asignacion_estado,
                    p.persona_nombre,
                    p.persona_apellido,
                    c.cargo_nombre,
                    ar.area_nombre
                FROM asignacion a
                INNER JOIN persona p ON a.persona_id = p.persona_id
                INNER JOIN cargo c ON p.cargo_id = c.cargo_id
                INNER JOIN area ar ON a.area_id = ar.area_id
                WHERE a.asignacion_estado = ?";
        
        $params = [(int)$estado];

        // Filtros opcionales
        if ($cargoId !== '') {
            $sql .= " AND c.cargo_id = ?";
            $params[] = (int)$cargoId;
        }

        if ($personaId !== '') {
            $sql .= " AND a.persona_id = ?";
            $params[] = (int)$personaId;
        }

        if ($areaId !== '') {
            $sql .= " AND a.area_id = ?";
            $params[] = (int)$areaId;
        }

        $sql .= " ORDER BY a.asignacion_fecha DESC, a.asignacion_id DESC";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        error_log("[asignacion] Error en leer_por_estado: " . $e->getMessage());
        throw $e;
    }
}




    // Leer asignación por ID
    public function leer_por_id($id) {
        $sql = "SELECT a.asignacion_id,
                    a.asignacion_fecha,
                    a.asignacion_fecha_fin,
                    a.asignacion_descripcion,
                    a.asignacion_estado,
                    a.area_id,
                    a.persona_id,
                    p.persona_nombre,
                    p.persona_apellido,
                    c.cargo_id,
                    c.cargo_nombre,
                    ar.area_nombre
                FROM asignacion a
                INNER JOIN persona p ON a.persona_id = p.persona_id
                INNER JOIN cargo c ON p.cargo_id = c.cargo_id
                INNER JOIN area ar ON a.area_id = ar.area_id
                WHERE a.asignacion_id = ?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([(int)$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }




    public function anular($id) {
        try {
            $this->pdo->beginTransaction();

            // Marcar cabecera como anulada
            $stmtCab = $this->pdo->prepare(
                "UPDATE asignacion SET asignacion_estado = 0 WHERE asignacion_id = ?"
            );
            $stmtCab->execute([(int)$id]);

            // Seriales vinculados vuelven a estado 1 (activo)
            $stmtSeriales = $this->pdo->prepare(
                "UPDATE articulo_serial SET estado_id = 1
                WHERE articulo_serial_id IN (
                    SELECT articulo_serial_id FROM asignacion_articulo WHERE asignacion_id = ?
                )"
            );
            $stmtSeriales->execute([(int)$id]);

            $this->pdo->commit();
            return true;
        } catch (Exception $e) {
            $this->pdo->rollBack();
            return false;
        }
    }


    public function recuperar($id) {
        try {
            $this->pdo->beginTransaction();

            // Buscar seriales asociados
            $stmt = $this->pdo->prepare(
                "SELECT s.articulo_serial_id, s.estado_id
                FROM asignacion_articulo aa
                INNER JOIN articulo_serial s ON aa.articulo_serial_id = s.articulo_serial_id
                WHERE aa.asignacion_id = ?"
            );
            $stmt->execute([(int)$id]);
            $seriales = $stmt->fetchAll(PDO::FETCH_ASSOC);

            if (empty($seriales)) {
                throw new Exception('No hay seriales asociados a la asignación');
            }

            // Validar que todos estén activos
            foreach ($seriales as $s) {
                if ((int)$s['estado_id'] !== 1) {
                    throw new Exception('No se puede recuperar: algunos seriales están comprometidos');
                }
            }

            // Reactivar cabecera
            $stmtCab = $this->pdo->prepare(
                "UPDATE asignacion SET asignacion_estado = 1 WHERE asignacion_id = ?"
            );
            $stmtCab->execute([(int)$id]);

            // Marcar seriales como asignados nuevamente
            $stmtSeriales = $this->pdo->prepare(
                "UPDATE articulo_serial SET estado_id = 2
                WHERE articulo_serial_id IN (
                    SELECT articulo_serial_id FROM asignacion_articulo WHERE asignacion_id = ?
                )"
            );
            $stmtSeriales->execute([(int)$id]);

            $this->pdo->commit();
            return true;
        } catch (Exception $e) {
            $this->pdo->rollBack();
            return false;
        }
    }



    // Listar artículos disponibles
    public function leer_articulos_disponibles($estado = 1, $categoriaId = '', $clasificacionId = '') {
        $sql = "SELECT
                    a.articulo_id,
                    a.articulo_codigo,
                    a.articulo_nombre,
                    a.articulo_modelo,
                    a.articulo_descripcion,
                    a.articulo_imagen,
                    cl.clasificacion_nombre,
                    cat.categoria_nombre,
                    cat.categoria_tipo,
                    m.marca_nombre
                FROM articulo a
                LEFT JOIN clasificacion cl ON a.clasificacion_id = cl.clasificacion_id
                LEFT JOIN categoria cat ON cl.categoria_id = cat.categoria_id
                LEFT JOIN marca m ON a.marca_id = m.marca_id
                WHERE a.articulo_estado = ?";
        $params = [(int)$estado];

        if ($categoriaId !== '') {
            $sql .= " AND cl.categoria_id = ?";
            $params[] = (int)$categoriaId;
        }
        if ($clasificacionId !== '') {
            $sql .= " AND a.clasificacion_id = ?";
            $params[] = (int)$clasificacionId;
        }

        $sql .= " ORDER BY a.articulo_nombre ASC, a.articulo_codigo ASC";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }


    // Leer detalle completo de un artículo
    public function leer_articulo_por_id($id) {
        $sql = "SELECT a.*, cl.clasificacion_nombre, cat.categoria_nombre, cat.categoria_tipo, m.marca_nombre
                FROM articulo a
                LEFT JOIN clasificacion cl ON a.clasificacion_id = cl.clasificacion_id
                LEFT JOIN categoria cat ON cl.categoria_id = cat.categoria_id
                LEFT JOIN marca m ON a.marca_id = m.marca_id
                WHERE a.articulo_id = ?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([(int)$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Mostrar seriales de un artículo: activos (estado 1) + los asignados a la misma asignación (estado 2)
    public function leer_seriales_articulo($articuloId, $asignacionId = null) {
        try {
            $sql = "SELECT s.articulo_serial_id AS id,
                        s.articulo_serial AS serial,
                        s.articulo_serial_observacion AS observacion,
                        s.estado_id AS estado
                    FROM articulo_serial s
                    WHERE s.articulo_id = ?
                    AND s.articulo_serial IS NOT NULL
                    AND TRIM(s.articulo_serial) <> ''";

            $params = [(int)$articuloId];

            if ($asignacionId) {
                // incluir activos o vinculados a esta asignación
                $sql .= " AND (s.estado_id = 1 OR s.articulo_serial_id IN (
                                SELECT aa.articulo_serial_id
                                FROM asignacion_articulo aa
                                WHERE aa.asignacion_id = ?
                            ))";
                $params[] = (int)$asignacionId;
            } else {
                $sql .= " AND s.estado_id = 1";
            }

            $sql .= " ORDER BY s.articulo_serial_id ASC";

            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            throw $e;
        }
    }



    // Stock disponible por artículo (solo activos con código de serial)
    public function obtener_stock_articulo($articuloId) {
        try {
            $sql = "SELECT 
                        SUM(CASE WHEN estado_id = 1 
                                AND articulo_serial IS NOT NULL 
                                AND TRIM(articulo_serial) <> '' 
                                THEN 1 ELSE 0 END) AS activos
                    FROM articulo_serial
                    WHERE articulo_id = ?";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([(int)$articuloId]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            throw $e;
        }
    }

    // Listar artículos asociados a una recepción
    public function leer_articulos_por_asignacion($asignacionId) {
        $sql = "SELECT a.articulo_id, a.articulo_codigo, a.articulo_nombre
                FROM asignacion_articulo aa
                INNER JOIN articulo_serial s ON s.articulo_serial_id = aa.articulo_serial_id
                INNER JOIN articulo a ON a.articulo_id = s.articulo_id
                WHERE aa.asignacion_id = ?
                GROUP BY a.articulo_id, a.articulo_codigo, a.articulo_nombre";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([(int)$asignacionId]);
        $articulos = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Por cada artículo, traer sus seriales vinculados a esta asignación con IDs
        $stmtSeriales = $this->pdo->prepare(
            "SELECT s.articulo_serial_id AS id, s.articulo_serial AS serial
            FROM asignacion_articulo aa
            INNER JOIN articulo_serial s ON s.articulo_serial_id = aa.articulo_serial_id
            WHERE aa.asignacion_id = ? AND s.articulo_id = ?
            ORDER BY s.articulo_serial_id ASC"
        );

        foreach ($articulos as &$row) {
            $stmtSeriales->execute([(int)$asignacionId, (int)$row['articulo_id']]);
            $row['seriales'] = $stmtSeriales->fetchAll(PDO::FETCH_ASSOC); // array [{id, serial}]
        }
        return $articulos;
    }

    public function leer_seriales_asignados($asignacionId) {
    $stmt = $this->pdo->prepare(
        "SELECT articulo_serial_id FROM asignacion_articulo WHERE asignacion_id = ?"
    );
    $stmt->execute([(int)$asignacionId]);
    return $stmt->fetchAll(PDO::FETCH_COLUMN, 0); // array de IDs
}


}
?>

