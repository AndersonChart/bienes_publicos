<?php
require_once 'recepcion.php';
$recepcion = new recepcion();

function validarAjuste($datos, $modo = 'crear', $id = null) {
    $erroresFormato = [];
    $camposObligatorios = ['ajuste_fecha'];

    // Verificar campos obligatorios
    $camposFaltantes = [];
    foreach ($camposObligatorios as $campo) {
        if (!isset($datos[$campo]) || trim($datos[$campo]) === '') {
            $camposFaltantes[] = $campo;
        }
    }

    if (!empty($camposFaltantes)) {
        return [
            'error' => true,
            'mensaje' => 'Debe rellenar la fecha del ajuste',
            'campos' => $camposFaltantes
        ];
    }

    // Validación de formato de fecha (YYYY-MM-DD)
    $fecha = trim($datos['ajuste_fecha']);
    if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $fecha)) {
        return [
            'error' => true,
            'mensaje' => 'La fecha debe tener formato YYYY-MM-DD',
            'campos' => ['ajuste_fecha']
        ];
    } else {
        // Validación de que la fecha no sea futura
        $hoy = date('Y-m-d');
        if ($fecha > $hoy) {
            return [
                'error' => true,
                'mensaje' => 'La fecha no puede ser posterior al día de hoy',
                'campos' => ['ajuste_fecha']
            ];
        }
    }

    // Validación de artículos vacíos
    if (empty($datos['articulos'])) {
        return [
            'error' => true,
            'mensaje' => 'Debe ingresar al menos un artículo',
            'campos' => ['articulos']
        ];
    }

    // Normalizar datos (trim)
    foreach ($datos as $clave => $valor) {
        if (is_string($valor)) {
            $datos[$clave] = trim($valor);
        }
    }

    return ['valido' => true];
}


$accion = $_POST['accion'] ?? '';

switch ($accion) {
    case 'leer_todos':
        try {
            header('Content-Type: application/json');
            $estado = isset($_POST['estado']) ? intval($_POST['estado']) : 1;
            $registros = $recepcion->leer_por_estado($estado);

            // Mapear a los nombres que espera el frontend
            $data = array_map(function($row) {
                return [
                    'recepcion_id'          => $row['ajuste_id'],
                    'recepcion_fecha'       => $row['ajuste_fecha'],
                    'recepcion_descripcion' => $row['ajuste_descripcion']
                ];
            }, $registros);

            echo json_encode(['data' => $data]);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode([
                'data' => [],
                'error' => true,
                'mensaje' => 'Error al leer recepciones',
                'detalle' => $e->getMessage()
            ]);
        }
    break;

    case 'crear':
        try {
            header('Content-Type: application/json');

            $articulos = isset($_POST['articulos']) ? json_decode($_POST['articulos'], true) : [];

            $datos = [
                'ajuste_fecha'       => $_POST['ajuste_fecha'] ?? '',
                'ajuste_descripcion' => $_POST['ajuste_descripcion'] ?? '',
                'ajuste_tipo'        => $_POST['ajuste_tipo'] ?? 1,
                'articulos'          => $articulos
            ];

            if (empty($datos['ajuste_fecha'])) {
                echo json_encode([
                    'error' => true,
                    'mensaje' => 'Debe ingresar la fecha del ajuste',
                    'campos' => ['ajuste_fecha']
                ]);
                exit;
            }

            if (empty($datos['articulos'])) {
                echo json_encode([
                    'error' => true,
                    'mensaje' => 'Debe ingresar al menos un artículo con cantidad',
                    'campos' => ['articulos']
                ]);
                exit;
            }

            $resultado = $recepcion->crear(
                $datos['ajuste_fecha'],
                $datos['ajuste_descripcion'],
                $datos['articulos']
            );

            echo json_encode([
                'exito' => true,
                'mensaje' => 'Recepción creada correctamente',
                'ajuste_id' => $resultado
            ]);
        } catch (Exception $e) {
            echo json_encode([
                'error' => true,
                'mensaje' => 'Error al crear recepción',
                'detalle' => $e->getMessage()
            ]);
        }
    break;

    case 'listar_articulos_recepcion':
        try {
            header('Content-Type: application/json');
            $categoriaId = $_POST['categoria_id'] ?? '';
            $clasificacionId = $_POST['clasificacion_id'] ?? '';

            $registros = $recepcion->leer_articulos_disponibles(1, $categoriaId, $clasificacionId);

            $data = array_map(function($row) {
                return [
                    'articulo_id'          => $row['articulo_id'],
                    'articulo_codigo'      => $row['articulo_codigo'],
                    'articulo_nombre'      => $row['articulo_nombre'],
                    'articulo_imagen'      => $row['articulo_imagen'],
                    'clasificacion_nombre' => $row['clasificacion_nombre'],
                    'categoria_nombre'     => $row['categoria_nombre']
                ];
            }, $registros);

            echo json_encode(['data' => $data]);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode([
                'data' => [],
                'error' => true,
                'mensaje' => 'Error al listar artículos disponibles',
                'detalle' => $e->getMessage()
            ]);
        }
    break;

    case 'obtener_recepcion': // corregido: antes tenías 'obtener_ajuste'
        header('Content-Type: application/json');
        $id = $_POST['id'] ?? '';
        if (!$id) {
            echo json_encode(['error' => true, 'mensaje' => 'ID no proporcionado']);
            exit;
        }

        $datos = $recepcion->leer_por_id($id);
        if ($datos) {
            $map = [
                'recepcion_id'          => $datos['ajuste_id'],
                'recepcion_fecha'       => $datos['ajuste_fecha'],
                'recepcion_descripcion' => $datos['ajuste_descripcion'],
                'recepcion_tipo'        => $datos['ajuste_tipo']
            ];
            echo json_encode(['exito' => true, 'recepcion' => $map]);
        } else {
            echo json_encode(['error' => true, 'mensaje' => 'Recepción no encontrada']);
        }
    break;

    case 'anular':
        header('Content-Type: application/json');
        try {
            $id = $_POST['id'] ?? '';
            if (!$id) {
                throw new Exception('ID no proporcionado');
            }

            $exito = $recepcion->anular($id);

            echo json_encode([
                'exito' => $exito,
                'mensaje' => $exito ? 'Recepción anulada correctamente' : 'No se pudo anular la recepción'
            ]);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode([
                'exito' => false,
                'mensaje' => 'Error al anular recepción',
                'detalle' => $e->getMessage()
            ]);
        }
    break;
    case 'listar_articulos_recepcion':
        try {
            header('Content-Type: application/json');
            $categoriaId = $_POST['categoria_id'] ?? '';
            $clasificacionId = $_POST['clasificacion_id'] ?? '';

            // Estado = 1 significa activos/disponibles
            $registros = $recepcion->leer_articulos_disponibles(1, $categoriaId, $clasificacionId);

            echo json_encode(['data' => $registros]);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode([
                'data' => [],
                'error' => true,
                'mensaje' => 'Error al listar artículos disponibles',
                'detalle' => $e->getMessage()
            ]);
        }
    break;


    case 'listar_articulos_por_ajuste':
        try {
            header('Content-Type: application/json');
            $id = $_POST['id'] ?? '';
            if (!$id) {
                echo json_encode(['data' => [], 'error' => true, 'mensaje' => 'ID no proporcionado']);
                exit;
            }
            $registros = $recepcion->leer_articulos_por_ajuste($id);
            echo json_encode(['data' => $registros]);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode([
                'data' => [],
                'error' => true,
                'mensaje' => 'Error al listar artículos de la recepción',
                'detalle' => $e->getMessage()
            ]);
        }
    break;

    default:
        echo json_encode([
            'error' => true,
            'mensaje' => 'Acción no válida'
        ]);
    break;
}
?>
