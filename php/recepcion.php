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

    // Crear nueva recepción (por artículo y cantidad, generando N seriales vacíos o informados)
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
                // Preparar sentencias (una vez)
                $stmtSerial = $this->pdo->prepare(
                    "INSERT INTO articulo_serial (articulo_id, articulo_serial, estado_id)
                     VALUES (?, ?, 1)"
                );
                $stmtAjusteArticulo = $this->pdo->prepare(
                    "INSERT INTO ajuste_articulo (articulo_serial_id, ajuste_id)
                     VALUES (?, ?)"
                );

                foreach ($articulos as $articulo) {
                    // Validaciones mínimas defensivas
                    if (!isset($articulo['articulo_id'])) {
                        throw new Exception('articulo_id faltante en payload');
                    }
                    $articuloId = (int)$articulo['articulo_id'];

                    // Si viene cantidad, úsala para generar placeholders cuando falten seriales
                    $cantidad   = isset($articulo['cantidad']) ? (int)$articulo['cantidad'] : 0;
                    $seriales   = isset($articulo['seriales']) && is_array($articulo['seriales'])
                        ? $articulo['seriales']
                        : [];

                    // Normalizar: si cantidad > len(seriales), completar con NULLs
                    if ($cantidad > 0) {
                        $faltantes = max(0, $cantidad - count($seriales));
                        if ($faltantes > 0) {
                            $seriales = array_merge($seriales, array_fill(0, $faltantes, null));
                        }
                    }

                    // Si no hay cantidad pero hay seriales, usa el largo de seriales
                    if ($cantidad <= 0 && count($seriales) > 0) {
                        $cantidad = count($seriales);
                    }

                    // Si no hay nada que insertar, continuar
                    if ($cantidad <= 0) {
                        continue;
                    }

                    // Insertar N registros en articulo_serial y vincular a ajuste
                    foreach ($seriales as $serial) {
                        // Permitir NULL o string (trim para vacíos)
                        $valorSerial = (is_string($serial) && trim($serial) !== '') ? trim($serial) : null;

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

    // Listar recepciones (ajustes tipo entrada)
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

    // Anular recepción
    public function anular($id) {
        $stmt = $this->pdo->prepare(
            "UPDATE ajuste SET ajuste_estado = 0
             WHERE ajuste_id = ? AND ajuste_tipo = 1"
        );
        return $stmt->execute([(int)$id]);
    }

    // Listar artículos disponibles para recepción (catálogo base por artículo)
    public function leer_articulos_disponibles($estado = 1, $categoriaId = '', $clasificacionId = '') {
        $sql = "SELECT
                    a.articulo_id,
                    a.articulo_codigo,
                    a.articulo_nombre,
                    a.articulo_imagen,
                    cl.clasificacion_nombre,
                    cat.categoria_nombre
                FROM articulo a
                LEFT JOIN clasificacion cl ON a.clasificacion_id = cl.clasificacion_id
                LEFT JOIN categoria cat ON cl.categoria_id = cat.categoria_id
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

    // Listar artículos asociados a una recepción (seriales creados en ese ajuste)
    public function leer_articulos_por_recepcion($ajuste_id) {
        $sql = "SELECT
                    aa.ajuste_articulo_id,
                    aa.articulo_serial_id,
                    a.articulo_codigo,
                    a.articulo_nombre,
                    s.articulo_serial
                FROM ajuste_articulo aa
                INNER JOIN articulo_serial s ON aa.articulo_serial_id = s.articulo_serial_id
                INNER JOIN articulo a ON s.articulo_id = a.articulo_id
                WHERE aa.ajuste_id = ?
                ORDER BY aa.ajuste_articulo_id ASC";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([(int)$ajuste_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>
