<?php
require_once '../bd/conexion.php';
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

class desincorporacion {
    private $pdo;

    public function __construct() {
        $this->pdo = Conexion::conectar();
        $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }

    // Listar desincorporaciones
    public function leer_por_estado($estado = 1) {
        $sql = "SELECT ajuste_id, ajuste_fecha, ajuste_descripcion, ajuste_tipo, ajuste_estado
                FROM ajuste
                WHERE ajuste_tipo = 0 AND ajuste_estado = ?
                ORDER BY ajuste_fecha DESC, ajuste_id DESC";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([(int)$estado]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Leer desincorporación por ID
    public function leer_por_id($id) {
        $sql = "SELECT ajuste_id, ajuste_fecha, ajuste_descripcion, ajuste_tipo, ajuste_estado
                FROM ajuste
                WHERE ajuste_id = ? AND ajuste_tipo = 0";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([(int)$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
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

    // Stock disponible por artículo (solo activos)
    public function obtener_stock_articulo($articuloId) {
        try {
            $sql = "SELECT 
                        SUM(CASE WHEN estado_id = 1 THEN 1 ELSE 0 END) AS activos
                    FROM articulo_serial
                    WHERE articulo_id = ?";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([(int)$articuloId]);
            $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
            
            // Retornar 0 si es NULL (por si no hay registros)
            return $resultado['activos'] ? (int)$resultado['activos'] : 0;
        } catch (Exception $e) {
            throw $e;
        }
    }

    
    // Stock disponible por artículo (solo activos con código de serial)
    public function obtener_stock_articulo_seriales($articuloId) {
        try {
            $sql = "SELECT 
                        SUM(CASE 
                            WHEN estado_id = 1 
                            AND articulo_serial IS NOT NULL 
                            AND TRIM(articulo_serial) <> '' 
                            THEN 1 ELSE 0 END) AS activos
                    FROM articulo_serial
                    WHERE articulo_id = ?";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([(int)$articuloId]);
            $resultado = $stmt->fetch(PDO::FETCH_ASSOC);

            return $resultado['activos'] ? (int)$resultado['activos'] : 0;
        } catch (Exception $e) {
            throw $e;
        }
    }
}