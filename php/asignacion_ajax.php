<?php
require_once 'asignacion.php';
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
header('Content-Type: application/json');
$asignacion = new asignacion();


function validarAsignacion($datos, $modo = 'crear', $id = null) {
    // Incluye cargo_id como obligatorio (visual), aunque no se persista
    $camposObligatorios = ['area_id', 'persona_id', 'cargo_id', 'asignacion_fecha'];

    // Verificar campos obligatorios
    $camposFaltantes = [];
    foreach ($camposObligatorios as $campo) {
        if (!isset($datos[$campo]) || trim((string)$datos[$campo]) === '') {
            $camposFaltantes[] = $campo;
        }
    }
    if (!empty($camposFaltantes)) {
        return [
            'error'   => true,
            'mensaje' => 'Debe rellenar los campos obligatorios',
            'campos'  => $camposFaltantes
        ];
    }

    // Validación de formato de fecha inicio
    $fechaInicio = trim((string)$datos['asignacion_fecha']);
    if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $fechaInicio)) {
        return [
            'error'   => true,
            'mensaje' => 'La fecha de inicio debe tener formato YYYY-MM-DD',
            'campos'  => ['asignacion_fecha']
        ];
    } else {
        // Validación de que la fecha inicio no sea futura
        $hoy = date('Y-m-d');
        if ($fechaInicio > $hoy) {
            return [
                'error'   => true,
                'mensaje' => 'La fecha de inicio no puede ser posterior al día de hoy',
                'campos'  => ['asignacion_fecha']
            ];
        }
    }

    // Validación de fecha fin si existe
    if (!empty($datos['asignacion_fecha_fin'])) {
        $fechaFin = trim((string)$datos['asignacion_fecha_fin']);
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $fechaFin)) {
            return [
                'error'   => true,
                'mensaje' => 'La fecha fin debe tener formato YYYY-MM-DD',
                'campos'  => ['asignacion_fecha_fin']
            ];
        }
        if ($fechaInicio >= $fechaFin) {
            return [
                'error'   => true,
                'mensaje' => 'La fecha de inicio debe ser anterior a la fecha final',
                'campos'  => ['asignacion_fecha','asignacion_fecha_fin']
            ];
        }
    }

    // Validación de descripción
    if (!empty($datos['asignacion_descripcion'])) {
        $descripcion = trim((string)$datos['asignacion_descripcion']);
        if (mb_strlen($descripcion) > 200) {
            return [
                'error'   => true,
                'mensaje' => 'La descripción debe tener máximo 200 caracteres',
                'campos'  => ['asignacion_descripcion']
            ];
        }
    }

    // Normalizar datos
    foreach ($datos as $clave => $valor) {
        if (is_string($valor)) {
            $datos[$clave] = trim($valor);
        }
    }

    return ['valido' => true];
}


$accion    = $_POST['accion'] ?? '';
// Router de acciones para Asignación
switch ($accion) {

    // Listar todas las asignaciones por estado
    case 'leer_todos':
    try {
        $estado    = isset($_POST['estado']) ? intval($_POST['estado']) : 1;
        $cargoId   = $_POST['cargo_id'] ?? '';
        $personaId = $_POST['persona_id'] ?? '';
        $areaId    = $_POST['area_id'] ?? '';

        $registros = $asignacion->leer_por_estado($estado, $cargoId, $personaId, $areaId);

        $data = array_map(function ($row) {
            return [
                'asignacion_id'        => $row['asignacion_id'],
                'asignacion_fecha'     => $row['asignacion_fecha'],
                'asignacion_fecha_fin' => $row['asignacion_fecha_fin'],
                'asignacion_estado'    => $row['asignacion_estado'],
                'area_nombre'          => $row['area_nombre'],
                'persona_nombre'       => $row['persona_nombre'].' '.$row['persona_apellido'],
                'cargo_nombre'         => $row['cargo_nombre']
            ];
        }, $registros);

        echo json_encode(['data' => $data]);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode([
            'data'    => [],
            'error'   => true,
            'mensaje' => 'Error al leer las asignaciones registradas',
            'detalle' => $e->getMessage()
        ]);
    }
    break;

    // Crear nueva asignación
    case 'crear':
    try {
        $seriales = [];
        if (isset($_POST['seriales'])) {
            $decoded = json_decode($_POST['seriales'], true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                // Aquí deben ser IDs de articulo_serial
                $seriales = array_values(array_filter($decoded, fn($s) => trim($s) !== ''));
            }
        }

        $areaId      = $_POST['area_id'] ?? '';
        $personaId   = $_POST['persona_id'] ?? '';
        $cargoId     = $_POST['cargo_id'] ?? '';
        $fechaInicio = $_POST['asignacion_fecha'] ?? '';
        $fechaFin    = $_POST['asignacion_fecha_fin'] ?? '';
        $descripcion = $_POST['asignacion_descripcion'] ?? '';

        $datos = [
            'area_id'                => $areaId,
            'persona_id'             => $personaId,
            'cargo_id'               => $cargoId,
            'asignacion_fecha'       => $fechaInicio,
            'asignacion_fecha_fin'   => $fechaFin,
            'asignacion_descripcion' => $descripcion
        ];
        $valid = validarAsignacion($datos, 'crear');
        if (!empty($valid['error'])) {
            echo json_encode($valid);
            exit;
        }

        if (empty($seriales)) {
            echo json_encode([
                'error'   => true,
                'mensaje' => 'Debe seleccionar al menos un serial',
                'campos'  => ['seriales']
            ]);
            exit;
        }

        $asignacionId = $asignacion->crear(
            $areaId,
            $personaId,
            $fechaInicio,
            $descripcion,
            $seriales,
            $fechaFin
        );

        echo json_encode([
            'exito'         => true,
            'mensaje'       => 'La asignación fue registrada correctamente',
            'asignacion_id' => $asignacionId
        ]);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode([
            'error'   => true,
            'mensaje' => 'Error al registrar la asignación',
            'detalle' => $e->getMessage()
        ]);
    }
    break;



    // Listar artículos disponibles para asignación
    case 'listar_articulos_asignacion':
        try {
            $categoriaId     = $_POST['categoria_id'] ?? '';
            $clasificacionId = $_POST['clasificacion_id'] ?? '';

            // Traer artículos disponibles
            $registros = $asignacion->leer_articulos_disponibles(1, $categoriaId, $clasificacionId);

            $data = array_map(function ($row) use ($asignacion) {
                // Calcular stock disponible por artículo
                $stock = $asignacion->obtener_stock_articulo($row['articulo_id']);
                $stockDisponible = $stock['activos'] ?? 0;

                return [
                    'articulo_id'          => $row['articulo_id'],
                    'articulo_codigo'      => $row['articulo_codigo'],
                    'articulo_nombre'      => $row['articulo_nombre'],
                    'articulo_modelo'      => $row['articulo_modelo'] ?? '',
                    'marca_nombre'         => $row['marca_nombre'] ?? '',
                    'articulo_descripcion' => $row['articulo_descripcion'] ?? '',
                    'articulo_imagen'      => $row['articulo_imagen'],
                    'clasificacion_nombre' => $row['clasificacion_nombre'],
                    'categoria_nombre'     => $row['categoria_nombre'],
                    'stock_disponible'     => $stockDisponible
                ];
            }, $registros);

            echo json_encode(['data' => $data]);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode([
                'data'    => [],
                'error'   => true,
                'mensaje' => 'Error al listar los artículos disponibles para asignación',
                'detalle' => $e->getMessage()
            ]);
        }
        break;


    // Obtener detalle de una asignación
    case 'obtener_asignacion':
        try {
            $id = $_POST['id'] ?? '';
            if (!$id) {
                echo json_encode(['error' => true, 'mensaje' => 'No se proporcionó el identificador de la asignación']);
                exit;
            }

            $datos = $asignacion->leer_por_id($id);
            if ($datos) {
                echo json_encode(['exito' => true, 'asignacion' => $datos]);
            } else {
                echo json_encode(['error' => true, 'mensaje' => 'No se encontró la asignación solicitada']);
            }
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => true, 'mensaje' => 'Error al obtener la asignación', 'detalle' => $e->getMessage()]);
        }
        break;

    // Anular asignación
    case 'anular':
        try {
            $id = $_POST['id'] ?? '';
            if (!$id) throw new Exception('No se proporcionó el identificador de la asignación');

            $exito = $asignacion->anular($id);
            echo json_encode([
                'exito'   => $exito,
                'mensaje' => $exito ? 'La asignación fue anulada correctamente' : 'No se puede anular la asignación'
            ]);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['exito' => false, 'mensaje' => 'Error al anular la asignación']);
        }
        break;

    // Recuperar asignación
    case 'recuperar':
        try {
            $id = $_POST['id'] ?? '';
            if (!$id) throw new Exception('No se proporcionó el identificador de la asignación');

            $exito = $asignacion->recuperar($id);
            echo json_encode([
                'exito'   => $exito,
                'mensaje' => $exito ? 'La asignación fue recuperada correctamente' : 'No se pudo recuperar la asignación: seriales comprometidos'
            ]);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['exito' => false, 'mensaje' => 'Error al recuperar la asignación']);
        }
        break;

    // Listar artículos vinculados a una asignación (resumen)
    case 'listar_articulos_por_asignacion':
        try {
            $id = $_POST['id'] ?? '';
            if (!$id) {
                echo json_encode(['data' => [], 'error' => true, 'mensaje' => 'No se proporcionó el identificador de la asignación']);
                exit;
            }
            $registros = $asignacion->leer_articulos_por_asignacion($id);
            echo json_encode(['data' => $registros]);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['data' => [], 'error' => true, 'mensaje' => 'Error al listar los artículos asociados a la asignación', 'detalle' => $e->getMessage()]);
        }
        break;

    // Listar seriales disponibles de un artículo
    case 'leer_seriales_articulo':
        try {
            $id = $_POST['id'] ?? '';
            if (!$id) {
                echo json_encode(['data' => [], 'error' => true, 'mensaje' => 'No se proporcionó el identificador del artículo']);
                exit;
            }
            $registros = $asignacion->leer_seriales_articulo($id);
            echo json_encode(['data' => $registros]);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode([
                'data'    => [],
                'error'   => true,
                'mensaje' => 'Error al listar los seriales del artículo',
                'detalle' => $e->getMessage()
            ]);
        }
        break;


    // Obtener detalle de un artículo
    case 'obtener_articulo':
        try {
            $id = $_POST['id'] ?? '';
            if (!$id) {
                echo json_encode(['error' => true, 'mensaje' => 'No se proporcionó el identificador del artículo']);
                exit;
            }
            $articulo = $asignacion->leer_articulo_por_id($id);
            echo json_encode($articulo ? ['exito' => true, 'articulo' => $articulo] : ['error' => true, 'mensaje' => 'No se encontró el artículo solicitado']);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => true, 'mensaje' => 'Error al obtener el artículo', 'detalle' => $e->getMessage()]);
        }
        break;

    // Validar seriales
    case 'validar_seriales':
        try {
            $seriales = [];
            if (isset($_POST['seriales'])) {
                $decoded = json_decode($_POST['seriales'], true);
                if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                    $seriales = array_values(array_filter(array_map(function ($s) {
                        return is_string($s) ? trim($s) : '';
                    }, $decoded), fn($s) => $s !== '' ));
                }
            }

            if (empty($seriales)) {
                echo json_encode(['exito' => true, 'repetidos' => []]);
                exit;
            }

            $repetidos = $asignacion->validar_seriales($seriales);
            echo json_encode(['exito' => true, 'repetidos' => $repetidos]);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => true, 'mensaje' => 'Error al validar los seriales', 'detalle' => $e->getMessage()]);
        }
        break;

        default:
        http_response_code(400);
        echo json_encode([
            'error'   => true,
            'mensaje' => 'Acción no reconocida en el proceso de asignación'
        ]);
        break;
}
