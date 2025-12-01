<?php
require_once 'recepcion.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

header('Content-Type: application/json');

$recepcion = new recepcion();
$accion    = $_POST['accion'] ?? '';

function validarRecepcion($datos, $modo = 'crear', $id = null) {
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
        global $recepcion;
        $repetidosBD = $recepcion->validar_seriales($todosSeriales);
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

// Router de acciones
switch ($accion) {
    case 'leer_todos':
        try {
            $estado    = isset($_POST['estado']) ? intval($_POST['estado']) : 1;
            $registros = $recepcion->leer_por_estado($estado);

            $data = array_map(function ($row) {
                return [
                    'recepcion_id'          => $row['ajuste_id'],
                    'recepcion_fecha'       => $row['ajuste_fecha'],
                    'recepcion_descripcion' => $row['ajuste_descripcion'],
                    'recepcion_estado'      => $row['ajuste_estado'] // <-- necesario
                ];
            }, $registros);

            echo json_encode(['data' => $data]);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode([
                'data'    => [],
                'error'   => true,
                'mensaje' => 'Error al leer las recepciones registradas',
                'detalle' => $e->getMessage()
            ]);
        }
        break;

    case 'crear':
        try {
            // Decodificar artículos enviados como JSON
            $articulos = [];
            if (isset($_POST['articulos'])) {
                $decoded = json_decode($_POST['articulos'], true);
                if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                    $articulos = $decoded;
                }
            }

            $datos = [
                'ajuste_fecha'       => $_POST['ajuste_fecha'] ?? '',
                'ajuste_descripcion' => $_POST['ajuste_descripcion'] ?? '',
                'ajuste_tipo'        => (int)($_POST['ajuste_tipo'] ?? 1),
                'articulos'          => $articulos
            ];

            //  Validación integral
            $validacion = validarRecepcion($datos, 'crear');
            if (isset($validacion['error'])) {
                echo json_encode($validacion);
                exit;
            }

            // Crear recepción
            $recepcionId = $recepcion->crear(
                $datos['ajuste_fecha'],
                $datos['ajuste_descripcion'],
                $datos['articulos']
            );

            echo json_encode([
                'exito'        => true,
                'mensaje'      => 'La recepción fue registrada correctamente',
                'recepcion_id' => $recepcionId
            ]);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode([
                'error'   => true,
                'mensaje' => 'Error al registrar la recepción',
                'detalle' => $e->getMessage()
            ]);
        }
        break;

    case 'listar_articulos_recepcion':
        try {
            $categoriaId     = $_POST['categoria_id'] ?? '';
            $clasificacionId = $_POST['clasificacion_id'] ?? '';

            $registros = $recepcion->leer_articulos_disponibles(1, $categoriaId, $clasificacionId);

            $data = array_map(function ($row) {
                return [
                    'articulo_id'          => $row['articulo_id'],
                    'articulo_codigo'      => $row['articulo_codigo'],
                    'articulo_nombre'      => $row['articulo_nombre'],
                    'articulo_modelo'      => $row['articulo_modelo'] ?? '',
                    'marca_nombre'         => $row['marca_nombre'] ?? '',
                    'articulo_descripcion' => $row['articulo_descripcion'] ?? '',
                    'articulo_imagen'      => $row['articulo_imagen'],
                    'clasificacion_nombre' => $row['clasificacion_nombre'],
                    'categoria_nombre'     => $row['categoria_nombre']
                ];
            }, $registros);

            echo json_encode(['data' => $data]);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode([
                'data'    => [],
                'error'   => true,
                'mensaje' => 'Error al listar los artículos disponibles para recepción',
                'detalle' => $e->getMessage()
            ]);
        }
        break;

        case 'obtener_recepcion':
        try {
            $id = $_POST['id'] ?? '';
            if (!$id) {
                echo json_encode([
                    'error'   => true,
                    'mensaje' => 'No se proporcionó el identificador de la recepción'
                ]);
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
                echo json_encode([
                    'exito'     => true,
                    'recepcion' => $map
                ]);
            } else {
                echo json_encode([
                    'error'   => true,
                    'mensaje' => 'No se encontró la recepción solicitada'
                ]);
            }
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode([
                'error'   => true,
                'mensaje' => 'Ocurrió un error al obtener la recepción',
                'detalle' => $e->getMessage()
            ]);
        }
        break;


    case 'anular':
    try {
        $id = $_POST['id'] ?? '';
        if (!$id) {
            throw new Exception('No se proporcionó el identificador de la recepción');
        }
        $exito = $recepcion->anular($id);

        if ($exito) {
            echo json_encode([
                'exito'   => true,
                'mensaje' => 'La recepción fue anulada correctamente'
            ]);
        } else {
            echo json_encode([
                'exito'   => false,
                'mensaje' => 'No se puede anular la recepción'
            ]);
        }
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode([
            'exito'   => false,
            'mensaje' => 'No se puede anular la recepción'
        ]);
    }
    break;

    case 'recuperar':
    try {
        $id = $_POST['id'] ?? '';
        if (!$id) {
            throw new Exception('No se proporcionó el identificador de la recepción');
        }

        $exito = $recepcion->recuperar($id);

        if ($exito) {
            echo json_encode([
                'exito'   => true,
                'mensaje' => 'La recepción fue recuperada correctamente'
            ]);
        } else {
            echo json_encode([
                'exito'   => false,
                'mensaje' => 'No se pudo recuperar la recepción: seriales comprometidos o duplicados'
            ]);
        }
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode([
            'exito'   => false,
            'mensaje' => 'Error al recuperar la recepción',
            'detalle' => $e->getMessage()
        ]);
    }
    break;


    case 'listar_articulos_por_ajuste':
        try {
            $id = $_POST['id'] ?? '';
            if (!$id) {
                echo json_encode(['data' => [], 'error' => true, 'mensaje' => 'No se proporcionó el identificador de la recepción']);
                exit;
            }
            $registros = $recepcion->leer_articulos_por_recepcion($id);
            echo json_encode(['data' => $registros]);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode([
                'data'    => [],
                'error'   => true,
                'mensaje' => 'Error al listar los artículos asociados a la recepción',
                'detalle' => $e->getMessage()
            ]);
        }
        break;

    case 'obtener_articulo':
        try {
            $id = $_POST['id'] ?? '';
            if (!$id) {
                echo json_encode(['error' => true, 'mensaje' => 'No se proporcionó el identificador del artículo']);
                exit;
            }
            $articulo = $recepcion->leer_articulo_por_id($id);
            if ($articulo) {
                echo json_encode(['exito' => true, 'articulo' => $articulo]);
            } else {
                echo json_encode(['error' => true, 'mensaje' => 'No se encontró el artículo solicitado']);
            }
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode([
                'error'   => true,
                'mensaje' => 'Ocurrió un error al obtener el artículo',
                'detalle' => $e->getMessage()
            ]);
        }
        break;

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

            // Validación contra BD (ignora estado_id = 4)
            $repetidos = $recepcion->validar_seriales($seriales);
            echo json_encode(['exito' => true, 'repetidos' => $repetidos]);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode([
                'error'   => true,
                'mensaje' => 'Ocurrió un error al validar los seriales',
                'detalle' => $e->getMessage()
            ]);
        }
        break;


    default:
        http_response_code(400);
        echo json_encode([
            'error'   => true,
            'mensaje' => 'Acción no reconocida en el proceso de recepción'
        ]);
        break;
}
