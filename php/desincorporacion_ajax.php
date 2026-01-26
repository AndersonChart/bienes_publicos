<?php
require_once 'desincorporacion.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

header('Content-Type: application/json');

$desincorporacion = new desincorporacion();
$accion    = $_POST['accion'] ?? '';
/*
function validardesincorporacion($datos, $modo = 'crear', $id = null) {
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
            'error'   => true,
            'mensaje' => 'Debe indicar la fecha de la recepción',
            'campos'  => $camposFaltantes
        ];
    }

    // Validación de formato de fecha (YYYY-MM-DD)
    $fecha = trim($datos['ajuste_fecha']);
    if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $fecha)) {
        return [
            'error'   => true,
            'mensaje' => 'La fecha de la recepción debe tener formato YYYY-MM-DD',
            'campos'  => ['ajuste_fecha']
        ];
    } else {
        // Validación de que la fecha no sea futura
        $hoy = date('Y-m-d');
        if ($fecha > $hoy) {
            return [
                'error'   => true,
                'mensaje' => 'La fecha de la recepción no puede ser posterior al día de hoy',
                'campos'  => ['ajuste_fecha']
            ];
        }
    }

    // Validación de artículos vacíos
    if (!is_array($datos['articulos']) || count($datos['articulos']) === 0) {
        return [
            'error'   => true,
            'mensaje' => 'Debe ingresar al menos un artículo con cantidad para la recepción',
            'campos'  => ['articulos']
        ];
    }

    // Validación de seriales únicos
    $todosSeriales = [];
    foreach ($datos['articulos'] as $idx => $art) {
        if (!isset($art['articulo_id'])) {
            return [
                'error'   => true,
                'mensaje' => "Falta el identificador del artículo en la recepción",
                'campos'  => ["articulos[$idx][articulo_id]"]
            ];
        }

        $seriales = isset($art['seriales']) && is_array($art['seriales']) ? $art['seriales'] : [];
        $seriales = array_map(fn($s) => is_string($s) ? trim($s) : '', $seriales);

        // Duplicados dentro del mismo artículo
        $noVacios = array_values(array_filter($seriales, fn($s) => $s !== ''));
        if (count($noVacios) !== count(array_unique($noVacios))) {
            return [
                'error'   => true,
                'mensaje' => "Hay seriales repetidos dentro del mismo artículo",
                'campos'  => ["articulos[$idx][seriales]"]
            ];
        }

        $todosSeriales = array_merge($todosSeriales, $noVacios);
    }

    // Duplicados entre artículos
    if (count($todosSeriales) !== count(array_unique($todosSeriales))) {
        return [
            'error'   => true,
            'mensaje' => 'Hay seriales repetidos entre distintos artículos de la recepción',
            'campos'  => ['articulos']
        ];
    }

    // Validación contra BD (ignora estado 4)
    if (!empty($todosSeriales)) {
        global $desincorporacion;
        $repetidosBD = $desincorporacion->validar_seriales($todosSeriales);
        if (!empty($repetidosBD)) {
            return [
                'error'   => true,
                'mensaje' => 'Los siguientes seriales ya existen en el inventario: ' . implode(', ', $repetidosBD),
                'campos'  => ['articulos']
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
*/
// Router de acciones
switch ($accion) {
    //Leer desincorporaciones
    case 'leer_todos':
        try {
            $estado    = isset($_POST['estado']) ? intval($_POST['estado']) : 1;
            $registros = $desincorporacion->leer_por_estado($estado);

            $data = array_map(function ($row) {
                return [
                    'desincorporacion_id'          => $row['ajuste_id'],
                    'desincorporacion_fecha'       => $row['ajuste_fecha'],
                    'desincorporacion_descripcion' => $row['ajuste_descripcion'],
                    'desincorporacion_estado'      => $row['ajuste_estado']
                ];
            }, $registros);

            echo json_encode(['data' => $data]);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode([
                'data'    => [],
                'error'   => true,
                'mensaje' => 'Error al leer las desincorporaciones registradas',
                'detalle' => $e->getMessage()
            ]);
        }
    break;

    // Listar artículos disponibles para desincorporación
    case 'listar_articulos_desincorporacion':
        try {
            $categoriaId     = $_POST['categoria_id'] ?? '';
            $clasificacionId = $_POST['clasificacion_id'] ?? '';

            $registros = $desincorporacion->leer_articulos_disponibles(1, $categoriaId, $clasificacionId);

            // Si no hay registros, enviar array vacío pero con estructura DataTables
            if (!$registros) {
                echo json_encode(['data' => []]);
                exit;
            }

            $data = array_map(function ($row) use ($desincorporacion) {
                return [
                    'articulo_id'               => $row['articulo_id'],
                    'articulo_codigo'           => $row['articulo_codigo'],
                    'articulo_nombre'           => $row['articulo_nombre'],
                    'articulo_modelo'           => $row['articulo_modelo'] ?? '',
                    'articulo_imagen'           => $row['articulo_imagen'] ?? '',
                    'clasificacion_nombre'      => $row['clasificacion_nombre'],
                    'categoria_nombre'          => $row['categoria_nombre'],
                    'stock_disponible'          => (int)$desincorporacion->obtener_stock_articulo($row['articulo_id']), 
                    'stock_disponible_seriales' => (int)$desincorporacion->obtener_stock_articulo_seriales($row['articulo_id'])
                ];
            }, $registros);

            echo json_encode(['data' => $data]);
        } catch (Exception $e) {
            echo json_encode(['data' => [], 'error' => true, 'detalle' => $e->getMessage()]);
        }
    break;

    default:
        http_response_code(400);
        echo json_encode([
            'error'   => true,
            'mensaje' => 'Acción no reconocida en el proceso de desincorporación'
        ]);
    break;
}
