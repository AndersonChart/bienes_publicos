<?php
require_once 'desincorporacion.php';
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

header('Content-Type: application/json; charset=utf-8');

$accion = $_POST['accion'] ?? $_GET['accion'] ?? '';
$resp = ['success' => false, 'message' => 'Acción no encontrada', 'data' => null];

try {
    $model = new desincorporacion();

    switch ($accion) {
        case 'crear':
            $fecha = $_POST['fecha'] ?? null;
            $descripcion = $_POST['descripcion'] ?? null;
            $actaNombre = null;

            // Manejo de archivo 'acta' (opcional, si viene por upload)
            if (isset($_FILES['acta']) && $_FILES['acta']['error'] !== UPLOAD_ERR_NO_FILE) {
                $file = $_FILES['acta'];
                if ($file['error'] !== UPLOAD_ERR_OK) {
                    throw new Exception('Error al subir el acta (code ' . $file['error'] . ').');
                }

                $maxSize = 5 * 1024 * 1024; // 5 MB
                if ($file['size'] > $maxSize) {
                    throw new Exception('El archivo excede el tamaño máximo de 5 MB.');
                }

                $allowedExt = ['pdf','jpg','jpeg','png'];
                $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
                if (!in_array($ext, $allowedExt, true)) {
                    throw new Exception('Tipo de archivo no permitido. Extensiones permitidas: ' . implode(',', $allowedExt));
                }

                $uploadDir = __DIR__ . '/uploads/actas/';
                if (!is_dir($uploadDir) && !mkdir($uploadDir, 0755, true)) {
                    throw new Exception('No se pudo crear el directorio de destino.');
                }

                $safeName = time() . '_' . bin2hex(random_bytes(5)) . '.' . $ext;
                $dest = $uploadDir . $safeName;
                if (!move_uploaded_file($file['tmp_name'], $dest)) {
                    throw new Exception('No se pudo mover el archivo subido.');
                }

                $actaNombre = $safeName;
            } else {
                // Puede venir como nombre ya existente en formulario
                $actaNombre = $_POST['acta'] ?? null;
            }

            $articulos = [];
            if (isset($_POST['articulos'])) {
                $decoded = json_decode($_POST['articulos'], true);
                if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                    $articulos = $decoded;
                } else {
                    throw new Exception('JSON de articulos inválido.');
                }
            }

            $ajusteId = $model->crear($fecha, $descripcion, $actaNombre, $articulos);
            $resp = ['success' => true, 'message' => 'Desincorporación creada', 'data' => ['ajuste_id' => $ajusteId]];
            break;

        case 'anular':
            $id = isset($_POST['id']) ? (int)$_POST['id'] : (isset($_GET['id']) ? (int)$_GET['id'] : 0);
            if ($id <= 0) throw new Exception('ID inválido.');
            $model->anular($id);
            $resp = ['success' => true, 'message' => 'Desincorporación anulada', 'data' => null];
            break;

        case 'recuperar':
            $id = isset($_POST['id']) ? (int)$_POST['id'] : (isset($_GET['id']) ? (int)$_GET['id'] : 0);
            if ($id <= 0) throw new Exception('ID inválido.');
            $model->recuperar($id);
            $resp = ['success' => true, 'message' => 'Desincorporación recuperada', 'data' => null];
            break;

        case 'leer_por_estado':
            $estado = isset($_POST['estado']) ? (int)$_POST['estado'] : (isset($_GET['estado']) ? (int)$_GET['estado'] : 1);
            $rows = $model->leer_por_estado($estado);
            $resp = ['success' => true, 'message' => '', 'data' => $rows];
            break;

        case 'leer_por_id':
            $id = isset($_POST['id']) ? (int)$_POST['id'] : (isset($_GET['id']) ? (int)$_GET['id'] : 0);
            if ($id <= 0) throw new Exception('ID inválido.');
            $row = $model->leer_por_id($id);
            $resp = ['success' => true, 'message' => '', 'data' => $row];
            break;

        case 'leer_seriales_articulo':
            $articuloId = isset($_POST['articulo_id']) ? (int)$_POST['articulo_id'] : (isset($_GET['articulo_id']) ? (int)$_GET['articulo_id'] : 0);
            if ($articuloId <= 0) throw new Exception('ID de artículo inválido.');
            $rows = $model->leer_seriales_articulo($articuloId);
            $resp = ['success' => true, 'message' => '', 'data' => $rows];
            break;

        case 'obtener_stock_articulo':
            $articuloId = isset($_POST['articulo_id']) ? (int)$_POST['articulo_id'] : (isset($_GET['articulo_id']) ? (int)$_GET['articulo_id'] : 0);
            if ($articuloId <= 0) throw new Exception('ID de artículo inválido.');
            $stock = $model->obtener_stock_articulo($articuloId);
            $resp = ['success' => true, 'message' => '', 'data' => ['total' => $stock]];
            break;

        case 'obtener_stock_seriales':
            $articuloId = isset($_POST['articulo_id']) ? (int)$_POST['articulo_id'] : (isset($_GET['articulo_id']) ? (int)$_GET['articulo_id'] : 0);
            if ($articuloId <= 0) throw new Exception('ID de artículo inválido.');
            $stock = $model->obtener_stock_seriales($articuloId);
            $resp = ['success' => true, 'message' => '', 'data' => ['activos' => $stock]];
            break;

        case 'leer_articulos_por_desincorporacion':
            $ajusteId = isset($_POST['ajuste_id']) ? (int)$_POST['ajuste_id'] : (isset($_GET['ajuste_id']) ? (int)$_GET['ajuste_id'] : 0);
            if ($ajusteId <= 0) throw new Exception('ID inválido.');
            $rows = $model->leer_articulos_por_desincorporacion($ajusteId);
            $resp = ['success' => true, 'message' => '', 'data' => $rows];
            break;

        default:
            break;
    }
} catch (Exception $e) {
    error_log('desincorporacion_ajax error: ' . $e->getMessage());
    $resp = ['success' => false, 'message' => $e->getMessage(), 'data' => null];
}

echo json_encode($resp);