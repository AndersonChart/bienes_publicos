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
    

    // Crear nueva desincorporación
    public function crear($fecha, $descripcion, $nombreOrig, $nombreSist, $articulos = []) {
        try {
            $this->pdo->beginTransaction();

            // 1. Insertar cabecera (Tipo 0 = Salida/Desincorporación)
            // Se incluyen ajuste_nombre_original y ajuste_nombre_sistema
            $stmtCab = $this->pdo->prepare(
                "INSERT INTO ajuste (ajuste_fecha, ajuste_descripcion, ajuste_nombre_original, ajuste_nombre_sistema, ajuste_tipo, ajuste_estado)
                VALUES (?, ?, ?, ?, 0, 1)"
            );
            $stmtCab->execute([$fecha, $descripcion, $nombreOrig, $nombreSist]);
            $ajuste_id = $this->pdo->lastInsertId();

            // 2. Procesar artículos vinculados
            if (!empty($articulos)) {
                $stmtDetalle = $this->pdo->prepare(
                    "INSERT INTO ajuste_articulo (articulo_serial_id, ajuste_id) VALUES (?, ?)"
                );
                $stmtUpdateSerial = $this->pdo->prepare(
                    "UPDATE articulo_serial SET estado_id = 4 WHERE articulo_serial_id = ?"
                );

                foreach ($articulos as $art) {
                    if (isset($art['seriales']) && is_array($art['seriales'])) {
                        foreach ($art['seriales'] as $serial) {
                            // Extraemos el ID del serial (funciona si es objeto {id:X} o valor directo X)
                            $serialId = is_array($serial) ? $serial['id'] : $serial;

                            // Insertar vínculo en la tabla intermedia
                            $stmtDetalle->execute([$serialId, $ajuste_id]);
                            
                            // Actualizar estado del serial a 'Desincorporado' (ID 4)
                            $stmtUpdateSerial->execute([$serialId]);
                        }
                    }
                }
            }

            $this->pdo->commit();
            return $ajuste_id;

        } catch (Exception $e) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }
            throw $e;
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

    // Mostrar seriales de un artículo: activos (estado 1)
    public function leer_seriales_articulo($articuloId) {
        try {
            $sql = "SELECT s.articulo_serial_id AS id,
                            -- Si el serial es nulo o vacío, mostramos un texto descriptivo
                            COALESCE(NULLIF(TRIM(s.articulo_serial), ''), 'SIN SERIAL') AS serial,
                            s.articulo_serial_observacion AS observacion,
                            s.estado_id AS estado
                    FROM articulo_serial s
                    WHERE s.articulo_id = ?
                    AND s.estado_id = 1"; // Solo activos

            $params = [(int)$articuloId];

            // EXPLICACIÓN DEL ORDEN:
            // 1. Usamos una expresión lógica: si el serial está vacío o es nulo, le damos prioridad.
            // 2. Luego ordenamos por el ID.
            $sql .= " ORDER BY 
                        CASE 
                            WHEN s.articulo_serial IS NULL OR TRIM(s.articulo_serial) = '' THEN 0 
                            ELSE 1 
                        END ASC, 
                        s.articulo_serial_id ASC";

            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            throw $e;
        }
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