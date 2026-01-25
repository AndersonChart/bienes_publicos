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

    // Crear desincorporación
    public function crear($fecha, $descripcion, $acta_nombre_archivo, $articulos = []) {
        if (empty($fecha) || empty($descripcion)) {
            throw new Exception("Fecha y descripción son requeridas.");
        }
        if (!is_array($articulos) || count($articulos) === 0) {
            throw new Exception("Debe enviar al menos un artículo.");
        }

        try {
            $this->pdo->beginTransaction();

            // Insertar cabecera
            $stmtCab = $this->pdo->prepare(
                "INSERT INTO ajuste (ajuste_fecha, ajuste_descripcion, ajuste_acta, ajuste_tipo, ajuste_estado)
                 VALUES (?, ?, ?, 0, 1)"
            );
            $stmtCab->execute([$fecha, $descripcion, $acta_nombre_archivo]);
            $ajuste_id = (int)$this->pdo->lastInsertId();

            $stmtCheckSerial = $this->pdo->prepare(
                "SELECT articulo_serial_id, estado_id, articulo_id
                 FROM articulo_serial
                 WHERE articulo_serial_id = ?
                 FOR UPDATE"
            );

            $stmtUpdateSerial = $this->pdo->prepare(
                "UPDATE articulo_serial
                 SET estado_id = 4, articulo_serial_observacion = ?
                 WHERE articulo_serial_id = ? AND estado_id = 1"
            );

            $stmtAjusteArticulo = $this->pdo->prepare(
                "INSERT INTO ajuste_articulo (articulo_serial_id, ajuste_id) VALUES (?, ?)"
            );

            foreach ($articulos as $art) {
                $articuloId = isset($art['articulo_id']) ? (int)$art['articulo_id'] : 0;
                $cantidadTotal = isset($art['cantidad']) ? (int)$art['cantidad'] : 0;
                $nombreArt = $art['articulo_nombre'] ?? 'Artículo ID: ' . $articuloId;

                if ($articuloId <= 0 || $cantidadTotal <= 0) {
                    throw new Exception("Artículo inválido o cantidad no válida para $nombreArt.");
                }

                // Seriales manuales
                $serialesSeleccionados = isset($art['seriales']) && is_array($art['seriales']) ? $art['seriales'] : [];
                $manualIds = [];
                foreach ($serialesSeleccionados as $s) {
                    $serialId = isset($s['id']) ? (int)$s['id'] : 0;
                    $observacion = isset($s['observacion']) ? $s['observacion'] : 'Desincorporación manual';

                    if ($serialId <= 0) {
                        throw new Exception("ID de serial inválido para $nombreArt.");
                    }

                    $stmtCheckSerial->execute([$serialId]);
                    $row = $stmtCheckSerial->fetch(PDO::FETCH_ASSOC);
                    if (!$row) {
                        throw new Exception("El serial con ID {$serialId} no existe (Artículo: $nombreArt).");
                    }
                    if ((int)$row['articulo_id'] !== $articuloId) {
                        throw new Exception("El serial ID {$serialId} no pertenece al artículo $nombreArt.");
                    }
                    if ((int)$row['estado_id'] !== 1) {
                        throw new Exception("El serial ID {$serialId} no está disponible.");
                    }

                    $stmtUpdateSerial->execute([$observacion, $serialId]);
                    if ($stmtUpdateSerial->rowCount() !== 1) {
                        throw new Exception("No se pudo desincorporar el serial ID {$serialId}.");
                    }
                    $stmtAjusteArticulo->execute([$serialId, $ajuste_id]);
                    $manualIds[] = $serialId;
                }

                // Consumo automático (sin serial)
                $procesadosManual = count($manualIds);
                $faltantes = $cantidadTotal - $procesadosManual;

                if ($faltantes > 0) {
                    $notInSql = '';
                    if (!empty($manualIds)) {
                        $placeholders = implode(',', array_fill(0, count($manualIds), '?'));
                        $notInSql = " AND articulo_serial_id NOT IN ($placeholders)";
                    }

                    $sqlBusqueda =
                        "SELECT articulo_serial_id
                         FROM articulo_serial
                         WHERE articulo_id = ? 
                           AND (articulo_serial IS NULL OR TRIM(articulo_serial) = '')
                           AND estado_id = 1" .
                        $notInSql .
                        " ORDER BY articulo_serial_id ASC
                          LIMIT ? FOR UPDATE";

                    $stmtBusqueda = $this->pdo->prepare($sqlBusqueda);

                    $params = [$articuloId];
                    if (!empty($manualIds)) {
                        foreach ($manualIds as $mid) $params[] = $mid;
                    }
                    $params[] = $faltantes;

                    $stmtBusqueda->execute($params);
                    $idsParaConsumir = $stmtBusqueda->fetchAll(PDO::FETCH_COLUMN);

                    if (count($idsParaConsumir) < $faltantes) {
                        throw new Exception("Stock insuficiente sin serial para: $nombreArt. Faltantes: " . ($faltantes - count($idsParaConsumir)));
                    }

                    foreach ($idsParaConsumir as $idConsumir) {
                        $stmtUpdateSerial->execute(['Desincorporación automática', $idConsumir]);
                        if ($stmtUpdateSerial->rowCount() !== 1) {
                            throw new Exception("Error al desincorporar automáticamente el registro ID {$idConsumir}.");
                        }
                        $stmtAjusteArticulo->execute([$idConsumir, $ajuste_id]);
                    }
                }
            }

            $this->pdo->commit();
            return $ajuste_id;
        } catch (Exception $e) {
            if ($this->pdo->inTransaction()) $this->pdo->rollBack();
            error_log("desincorporacion::crear - " . $e->getMessage());
            throw $e;
        }
    }

    // Anular desincorporación
    public function anular($id) {
        $id = (int)$id;
        if ($id <= 0) throw new Exception("ID inválido.");

        try {
            $this->pdo->beginTransaction();

            $stmtCheck = $this->pdo->prepare("SELECT ajuste_estado FROM ajuste WHERE ajuste_id = ? AND ajuste_tipo = 0 FOR UPDATE");
            $stmtCheck->execute([$id]);
            $ajuste = $stmtCheck->fetch(PDO::FETCH_ASSOC);
            if (!$ajuste) throw new Exception("Registro no encontrado.");
            if ((int)$ajuste['ajuste_estado'] === 0) throw new Exception("La desincorporación ya está anulada.");

            $stmtRestaurar = $this->pdo->prepare(
                "UPDATE articulo_serial s
                 JOIN ajuste_articulo aa ON aa.articulo_serial_id = s.articulo_serial_id
                 SET s.estado_id = 1, s.articulo_serial_observacion = CONCAT(COALESCE(s.articulo_serial_observacion,''), ' (restaurado por anulación #', ?, ')')
                 WHERE aa.ajuste_id = ? AND s.estado_id = 4"
            );
            $stmtRestaurar->execute([$id, $id]);

            $stmtCab = $this->pdo->prepare("UPDATE ajuste SET ajuste_estado = 0 WHERE ajuste_id = ?");
            $stmtCab->execute([$id]);

            $this->pdo->commit();
            return true;
        } catch (Exception $e) {
            if ($this->pdo->inTransaction()) $this->pdo->rollBack();
            error_log("desincorporacion::anular - " . $e->getMessage());
            throw $e;
        }
    }

    // Recuperar desincorporación anulada
    public function recuperar($id) {
        $id = (int)$id;
        if ($id <= 0) throw new Exception("ID inválido.");

        try {
            $this->pdo->beginTransaction();

            $stmtCheck = $this->pdo->prepare("SELECT ajuste_estado FROM ajuste WHERE ajuste_id = ? AND ajuste_tipo = 0 FOR UPDATE");
            $stmtCheck->execute([$id]);
            $ajuste = $stmtCheck->fetch(PDO::FETCH_ASSOC);
            if (!$ajuste) throw new Exception("Registro no encontrado.");
            if ((int)$ajuste['ajuste_estado'] !== 0) throw new Exception("La desincorporación no está anulada.");

            $stmtCab = $this->pdo->prepare("UPDATE ajuste SET ajuste_estado = 1 WHERE ajuste_id = ? AND ajuste_tipo = 0");
            $stmtCab->execute([$id]);

            $stmtItems = $this->pdo->prepare(
                "UPDATE articulo_serial s
                 JOIN ajuste_articulo aa ON aa.articulo_serial_id = s.articulo_serial_id
                 SET s.estado_id = 4, s.articulo_serial_observacion = CONCAT(COALESCE(s.articulo_serial_observacion,''), ' (recuperado por ajuste #', ?, ')')
                 WHERE aa.ajuste_id = ? AND s.estado_id = 1"
            );
            $stmtItems->execute([$id, $id]);

            $this->pdo->commit();
            return true;
        } catch (Exception $e) {
            if ($this->pdo->inTransaction()) $this->pdo->rollBack();
            error_log("desincorporacion::recuperar - " . $e->getMessage());
            throw $e;
        }
    }

    // Consultas
    public function leer_por_estado($estado = 1) {
        $stmt = $this->pdo->prepare("SELECT * FROM ajuste WHERE ajuste_tipo = 0 AND ajuste_estado = ? ORDER BY ajuste_id DESC");
        $stmt->execute([(int)$estado]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function leer_por_id($id) {
        $stmt = $this->pdo->prepare("SELECT * FROM ajuste WHERE ajuste_id = ? AND ajuste_tipo = 0");
        $stmt->execute([(int)$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function obtener_stock_articulo($articuloId) {
        $stmt = $this->pdo->prepare("SELECT COUNT(*) as total FROM articulo_serial WHERE articulo_id = ? AND estado_id = 1");
        $stmt->execute([(int)$articuloId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ? (int)$row['total'] : 0;
    }

    public function obtener_stock_seriales($articuloId) {
        $stmt = $this->pdo->prepare("SELECT COUNT(*) as activos FROM articulo_serial WHERE articulo_id = ? AND estado_id = 1 AND articulo_serial IS NOT NULL AND TRIM(articulo_serial) <> ''");
        $stmt->execute([(int)$articuloId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ? (int)$row['activos'] : 0;
    }

    public function leer_seriales_articulo($articuloId) {
        $stmt = $this->pdo->prepare("SELECT articulo_serial_id as id, articulo_serial as serial, articulo_serial_observacion as observacion FROM articulo_serial WHERE articulo_id = ? AND estado_id = 1 AND articulo_serial IS NOT NULL AND TRIM(articulo_serial) <> ''");
        $stmt->execute([(int)$articuloId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function leer_articulos_por_desincorporacion($ajuste_id) {
        $sql = "SELECT a.articulo_codigo, a.articulo_nombre, COUNT(s.articulo_serial_id) AS cantidad,
                GROUP_CONCAT(COALESCE(NULLIF(s.articulo_serial,''),'(sin serial)') SEPARATOR ', ') AS seriales
                FROM ajuste_articulo aa
                INNER JOIN articulo_serial s ON aa.articulo_serial_id = s.articulo_serial_id
                INNER JOIN articulo a ON s.articulo_id = a.articulo_id
                WHERE aa.ajuste_id = ? GROUP BY a.articulo_id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([(int)$ajuste_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}