<?php
require_once '../bd/conexion.php'; // Ajusta la ruta si la incluyes desde otro nivel

class asignacion {
    private $pdo;
    // id de estado para marcar un articulo serial como Asignado (según tus datos por defecto)
    const SERIAL_ASIGNADO_ESTADO_ID = 2;

    public function __construct() {
        try {
            $this->pdo = Conexion::conectar();
        } catch (Exception $e) {
            throw $e;
        }
    }

    /**
     * Crear asignación (nota) y asociar seriales.
     * $serialIds = array de articulo_serial_id
     */
    public function crear($areaId, $personaId, $fecha, $fechaFin, $serialIds = []) {
        try {
            $this->pdo->beginTransaction();

            $stmt = $this->pdo->prepare("INSERT INTO asignacion (area_id, persona_id, asignacion_fecha, asignacion_fecha_fin, asignacion_estado) VALUES (?, ?, ?, ?, 1)");
            $stmt->execute([(int)$areaId, (int)$personaId, $fecha, $fechaFin ?: null]);
            $asignacionId = (int)$this->pdo->lastInsertId();

            if (!empty($serialIds)) {
                $insStmt = $this->pdo->prepare("INSERT INTO asignacion_articulo (articulo_serial_id, asignacion_id) VALUES (?, ?)");
                $updStmt = $this->pdo->prepare("UPDATE articulo_serial SET estado_id = ? WHERE articulo_serial_id = ?");

                foreach ($serialIds as $serialId) {
                    $insStmt->execute([(int)$serialId, $asignacionId]);
                    $updStmt->execute([self::SERIAL_ASIGNADO_ESTADO_ID, (int)$serialId]);
                }
            }

            $this->pdo->commit();
            return $asignacionId;
        } catch (Exception $e) {
            $this->pdo->rollBack();
            throw $e;
        }
    }

    /** Obtener asignaciones para datatable (filtros opcionales) */
    public function obtener_todos($estado = 1, $areaId = '', $personaId = '') {
        $sql = "SELECT a.asignacion_id, a.area_id, ar.area_nombre, a.persona_id, CONCAT(p.persona_nombre,' ',p.persona_apellido) as persona_nombre,
                       a.asignacion_fecha, a.asignacion_fecha_fin, a.asignacion_estado,
                       (SELECT COUNT(*) FROM asignacion_articulo aa WHERE aa.asignacion_id = a.asignacion_id) AS cantidad_articulos
                FROM asignacion a
                LEFT JOIN area ar ON a.area_id = ar.area_id
                LEFT JOIN persona p ON a.persona_id = p.persona_id
                WHERE a.asignacion_estado = ?";

        $params = [(int)$estado];

        if ($areaId !== '') {
            $sql .= " AND a.area_id = ?";
            $params[] = (int)$areaId;
        }
        if ($personaId !== '') {
            $sql .= " AND a.persona_id = ?";
            $params[] = (int)$personaId;
        }

        $sql .= " ORDER BY a.asignacion_fecha DESC, a.asignacion_id DESC";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /** Obtener por id con artículos seriales */
    public function leer_por_id($id) {
        $stmt = $this->pdo->prepare("SELECT a.*, ar.area_nombre, CONCAT(p.persona_nombre,' ',p.persona_apellido) as persona_nombre FROM asignacion a
                                     LEFT JOIN area ar ON a.area_id = ar.area_id
                                     LEFT JOIN persona p ON a.persona_id = p.persona_id
                                     WHERE a.asignacion_id = ?");
        $stmt->execute([(int)$id]);
        $asig = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$asig) return null;

        // artículos seriales
        $stmt2 = $this->pdo->prepare("SELECT s.articulo_serial_id, s.articulo_serial, s.estado_id, t.articulo_nombre, t.articulo_modelo, m.marca_nombre
                                      FROM asignacion_articulo aa
                                      JOIN articulo_serial s ON aa.articulo_serial_id = s.articulo_serial_id
                                      JOIN articulo t ON s.articulo_id = t.articulo_id
                                      LEFT JOIN marca m ON t.marca_id = m.marca_id
                                      WHERE aa.asignacion_id = ?");
        $stmt2->execute([(int)$id]);
        $asig['articulos'] = $stmt2->fetchAll(PDO::FETCH_ASSOC);

        return $asig;
    }

    /** Deshabilitar (estado lógico) una asignación */
    public function deshabilitar($id) {
        $stmt = $this->pdo->prepare("UPDATE asignacion SET asignacion_estado = 0 WHERE asignacion_id = ?");
        return $stmt->execute([(int)$id]);
    }

    /** Recuperar asignación (si tu flujo lo permite) */
    public function recuperar($id) {
        $stmt = $this->pdo->prepare("UPDATE asignacion SET asignacion_estado = 1 WHERE asignacion_id = ?");
        return $stmt->execute([(int)$id]);
    }

    /** Finalizar: marcar asignacion_fecha_fin y opcionalmente devolver seriales a Disponible (estado 1) */
    public function finalizar($id, $fechaFin = null, $devolverSeriales = false) {
        try {
            $this->pdo->beginTransaction();
            $stmt = $this->pdo->prepare("UPDATE asignacion SET asignacion_fecha_fin = ?, asignacion_estado = 0 WHERE asignacion_id = ?");
            $stmt->execute([$fechaFin ?: date('Y-m-d'), (int)$id]);

            if ($devolverSeriales) {
                // poner seriales a Disponible (estado = 1)
                $sel = $this->pdo->prepare("SELECT articulo_serial_id FROM asignacion_articulo WHERE asignacion_id = ?");
                $sel->execute([(int)$id]);
                $seriales = $sel->fetchAll(PDO::FETCH_COLUMN);

                if (!empty($seriales)) {
                    $upd = $this->pdo->prepare("UPDATE articulo_serial SET estado_id = 1 WHERE articulo_serial_id = ?");
                    foreach ($seriales as $s) {
                        $upd->execute([(int)$s]);
                    }
                }
            }

            $this->pdo->commit();
            return true;
        } catch (Exception $e) {
            $this->pdo->rollBack();
            throw $e;
        }
    }

    /** Buscar seriales disponibles por texto (serial o nombre de artículo) */
    public function buscar_seriales_disponibles($texto, $limit = 50) {
        $texto = "%".str_replace('%','\\%',$texto)."%";
        $sql = "SELECT s.articulo_serial_id, s.articulo_serial, s.estado_id, a.articulo_nombre, a.articulo_modelo, m.marca_nombre
                FROM articulo_serial s
                JOIN articulo a ON s.articulo_id = a.articulo_id
                LEFT JOIN marca m ON a.marca_id = m.marca_id
                WHERE s.estado_id = 1 AND (s.articulo_serial LIKE ? OR a.articulo_nombre LIKE ?)
                LIMIT ?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$texto, $texto, (int)$limit]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>
