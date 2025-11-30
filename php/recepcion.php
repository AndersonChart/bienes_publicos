<?php
require_once '../bd/conexion.php';
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

class recepcion {
    private $pdo;

    public function __construct() {
        $this->pdo = Conexion::conectar();
    }

    // Crear nueva recepción
    public function crear($fecha, $descripcion, $articulos = []) {
        try {
            $this->pdo->beginTransaction();

            // Insertar cabecera del ajuste (tipo 1 = Entrada)
            $stmtCab = $this->pdo->prepare(
                "INSERT INTO ajuste (ajuste_fecha, ajuste_descripcion, ajuste_tipo, ajuste_estado)
                VALUES (?, ?, 1, 1)"
            );
            $stmtCab->execute([$fecha, $descripcion]);
            $ajuste_id = $this->pdo->lastInsertId();

            if (!empty($articulos)) {
                $stmtSerial = $this->pdo->prepare(
                    "INSERT INTO articulo_serial (articulo_id, articulo_serial, estado_id)
                    VALUES (?, ?, 1)"
                );
                $stmtAjusteArticulo = $this->pdo->prepare(
                    "INSERT INTO ajuste_articulo (articulo_serial_id, ajuste_id)
                    VALUES (?, ?)"
                );

                // Validar duplicados entre artículos en el mismo payload
                $todosSeriales = [];
                foreach ($articulos as $articulo) {
                    if (isset($articulo['seriales']) && is_array($articulo['seriales'])) {
                        foreach ($articulo['seriales'] as $s) {
                            $val = is_string($s) ? trim($s) : '';
                            if ($val !== '') {
                                if (in_array($val, $todosSeriales)) {
                                    throw new Exception("El serial {$val} está repetido en distintos artículos de la recepción.");
                                }
                                $todosSeriales[] = $val;
                            }
                        }
                    }
                }

                foreach ($articulos as $articulo) {
                    if (!isset($articulo['articulo_id'])) {
                        throw new Exception('articulo_id faltante en payload');
                    }
                    $articuloId = (int)$articulo['articulo_id'];
                    $cantidad   = isset($articulo['cantidad']) ? (int)$articulo['cantidad'] : 0;
                    $seriales   = isset($articulo['seriales']) && is_array($articulo['seriales'])
                        ? $articulo['seriales']
                        : [];

                    if ($cantidad > 0) {
                        $faltantes = max(0, $cantidad - count($seriales));
                        if ($faltantes > 0) {
                            $seriales = array_merge($seriales, array_fill(0, $faltantes, null));
                        }
                    }
                    if ($cantidad <= 0 && count($seriales) > 0) {
                        $cantidad = count($seriales);
                    }
                    if ($cantidad <= 0) {
                        continue;
                    }

                    foreach ($seriales as $serial) {
                        $valorSerial = (is_string($serial) && trim($serial) !== '') ? trim($serial) : '';

                        // Validar duplicados en BD (ignora estado 4)
                        if ($valorSerial !== '' && $this->existe_serial($valorSerial)) {
                            throw new Exception("El serial {$valorSerial} ya existe en el inventario.");
                        }

                        $stmtSerial->execute([$articuloId, $valorSerial]);
                        $serialId = $this->pdo->lastInsertId();
                        $stmtAjusteArticulo->execute([$serialId, $ajuste_id]);
                    }
                }
            }

            $this->pdo->commit();
            return $ajuste_id;

        } catch (Exception $e) {
            $this->pdo->rollBack();
            throw $e;
        }
    }

    // Validar si un serial ya existe en la BD (excepto estado 4)
    public function existe_serial($serial) {
        $stmt = $this->pdo->prepare(
            "SELECT COUNT(*) 
            FROM articulo_serial 
            WHERE articulo_serial = ? 
            AND estado_id <> 4"
        );
        $stmt->execute([$serial]);
        return $stmt->fetchColumn() > 0;
    }

    // Validar lista de seriales y devolver los repetidos (excepto estado 4)
    public function validar_seriales($seriales = []) {
        $repetidos = [];
        if (!empty($seriales)) {
            $placeholders = implode(',', array_fill(0, count($seriales), '?'));
            $stmt = $this->pdo->prepare(
                "SELECT articulo_serial 
                FROM articulo_serial 
                WHERE articulo_serial IN ($placeholders)
                AND estado_id <> 4"
            );
            $stmt->execute($seriales);
            $repetidos = $stmt->fetchAll(PDO::FETCH_COLUMN);
        }
        return $repetidos;
    }

    // Listar recepciones
    public function leer_por_estado($estado = 1) {
        $sql = "SELECT ajuste_id, ajuste_fecha, ajuste_descripcion, ajuste_tipo, ajuste_estado
                FROM ajuste
                WHERE ajuste_tipo = 1 AND ajuste_estado = ?
                ORDER BY ajuste_fecha DESC, ajuste_id DESC";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([(int)$estado]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Leer recepción por ID
    public function leer_por_id($id) {
        $sql = "SELECT ajuste_id, ajuste_fecha, ajuste_descripcion, ajuste_tipo, ajuste_estado
                FROM ajuste
                WHERE ajuste_id = ? AND ajuste_tipo = 1";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([(int)$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function anular($id) {
        try {
            $this->pdo->beginTransaction();

            // Buscar seriales asociados a la recepción
            $stmt = $this->pdo->prepare(
                "SELECT s.articulo_serial_id, s.estado_id
                FROM ajuste_articulo aa
                INNER JOIN articulo_serial s ON aa.articulo_serial_id = s.articulo_serial_id
                WHERE aa.ajuste_id = ?"
            );
            $stmt->execute([(int)$id]);
            $seriales = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Validar que todos estén en estado 1
            foreach ($seriales as $s) {
                if ((int)$s['estado_id'] !== 1) {
                    // Si alguno no está en estado 1, abortar
                    throw new Exception('Seriales comprometidos, no se puede anular.');
                }
            }

            // Marcar cabecera como anulada
            $stmtCab = $this->pdo->prepare(
                "UPDATE ajuste SET ajuste_estado = 0
                WHERE ajuste_id = ? AND ajuste_tipo = 1"
            );
            $stmtCab->execute([(int)$id]);

            // Marcar todos los seriales asociados como estado 4
            $stmtSeriales = $this->pdo->prepare(
                "UPDATE articulo_serial
                SET estado_id = 4
                WHERE articulo_serial_id IN (
                    SELECT articulo_serial_id FROM ajuste_articulo WHERE ajuste_id = ?
                )"
            );
            $stmtSeriales->execute([(int)$id]);

            $this->pdo->commit();
            return true;
        } catch (Exception $e) {
            $this->pdo->rollBack();
            return false; // devolvemos false para que el router muestre el modal de error
        }
    }

    public function recuperar($id) {
        try {
            $this->pdo->beginTransaction();

            // Buscar seriales asociados a la recepción
            $stmt = $this->pdo->prepare(
                "SELECT s.articulo_serial_id, s.articulo_serial, s.estado_id
                FROM ajuste_articulo aa
                INNER JOIN articulo_serial s ON aa.articulo_serial_id = s.articulo_serial_id
                WHERE aa.ajuste_id = ?"
            );
            $stmt->execute([(int)$id]);
            $seriales = $stmt->fetchAll(PDO::FETCH_ASSOC);

            if (empty($seriales)) {
                throw new Exception('No hay seriales asociados a la recepción');
            }

            // Validar que ninguno esté ya en inventario (estado distinto de 4)
            foreach ($seriales as $s) {
                if ((int)$s['estado_id'] !== 4) {
                    throw new Exception('No se puede recuperar: algunos seriales ya están en inventario');
                }
            }

            // Validar duplicados en BD (ignora estado 4)
            $listaSeriales = array_filter(array_map(fn($s) => trim($s['articulo_serial']), $seriales));
            if (!empty($listaSeriales)) {
                $repetidos = $this->validar_seriales($listaSeriales);
                if (!empty($repetidos)) {
                    throw new Exception('No se puede recuperar: existen seriales repetidos en inventario');
                }
            }

            // Marcar cabecera como vigente
            $stmtCab = $this->pdo->prepare(
                "UPDATE ajuste SET ajuste_estado = 1
                WHERE ajuste_id = ? AND ajuste_tipo = 1"
            );
            $stmtCab->execute([(int)$id]);

            // Marcar todos los seriales asociados como estado 1 (activo)
            $stmtSeriales = $this->pdo->prepare(
                "UPDATE articulo_serial
                SET estado_id = 1
                WHERE articulo_serial_id IN (
                    SELECT articulo_serial_id FROM ajuste_articulo WHERE ajuste_id = ?
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
    // Listar artículos asociados a una recepción
    public function leer_articulos_por_recepcion($ajuste_id) {
        $sql = "SELECT 
                    a.articulo_codigo,
                    a.articulo_nombre,
                    COUNT(s.articulo_serial_id) AS cantidad,
                    GROUP_CONCAT(COALESCE(NULLIF(s.articulo_serial,''),'(sin serial)') ORDER BY s.articulo_serial_id SEPARATOR ', ') AS seriales
                FROM ajuste_articulo aa
                INNER JOIN articulo_serial s ON aa.articulo_serial_id = s.articulo_serial_id
                INNER JOIN articulo a ON s.articulo_id = a.articulo_id
                WHERE aa.ajuste_id = ?
                GROUP BY a.articulo_codigo, a.articulo_nombre
                ORDER BY a.articulo_nombre ASC";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([(int)$ajuste_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>

