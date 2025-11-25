<?php
require_once 'recepcion.php';
$recepcion = new recepcion();

function validarRecepcion($datos, $modo = 'crear', $id = null) {
    $recepcion = new recepcion();
    $erroresFormato = [];
    $camposObligatorios = ['ajuste_fecha'];

    // Verificar campo obligatorio
    $camposFaltantes = [];
    foreach ($camposObligatorios as $campo) {
        if (!isset($datos[$campo]) || trim($datos[$campo]) === '') {
            $camposFaltantes[] = $campo;
        }
    }

    if (!empty($camposFaltantes)) {
        return [
            'error' => true,
            'mensaje' => 'Debe rellenar la fecha de recepción',
            'campos' => $camposFaltantes
        ];
    }

    // Validación de formato de fecha (YYYY-MM-DD)
    $fecha = trim($datos['ajuste_fecha']);
    if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $fecha)) {
        $erroresFormato['ajuste_fecha'] = 'La fecha debe tener formato YYYY-MM-DD';
    } else {
        // Validación de que la fecha no sea futura
        $hoy = date('Y-m-d');
        if ($fecha > $hoy) {
            $erroresFormato['ajuste_fecha'] = 'La fecha no puede ser posterior al día de hoy';
        }
    }

    // Si hay errores de formato
    if (!empty($erroresFormato)) {
        $primerCampo = array_key_first($erroresFormato);
        return [
            'error' => true,
            'mensaje' => $erroresFormato[$primerCampo],
            'errores' => [$primerCampo => $erroresFormato[$primerCampo]],
            'campos' => [$primerCampo]
        ];
    }

    // Normalizar datos (trim)
    foreach ($datos as $clave => $valor) {
        $datos[$clave] = trim($valor);
    }

    return ['valido' => true];
}

$accion = $_POST['accion'] ?? '';

switch ($accion) {
    case 'leer_todos':
        try {
            $estado = isset($_POST['estado']) ? intval($_POST['estado']) : 1;
            $registros = $recepcion->leer_por_estado($estado);

            // Mapear a los alias que espera el DataTable
            $data = array_map(function($row) {
                return [
                    'recepcion_id'          => $row['ajuste_id'],
                    'recepcion_fecha'       => $row['ajuste_fecha'],
                    'recepcion_descripcion' => $row['ajuste_descripcion']
                ];
            }, $registros);

            header('Content-Type: application/json');
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

            $datos = [
                'ajuste_fecha'       => $_POST['ajuste_fecha'] ?? '',
                'ajuste_descripcion' => $_POST['ajuste_descripcion'] ?? ''
            ];

            // Validación
            $validacion = validarRecepcion($datos, 'crear');
            if (isset($validacion['error'])) {
                echo json_encode($validacion);
                exit;
            }

            $resultado = $recepcion->crear($datos['ajuste_fecha'], $datos['ajuste_descripcion']);

            echo json_encode([
                'exito' => true,
                'mensaje' => 'Recepción guardada correctamente',
                'resultado' => $resultado
            ]);
        } catch (Exception $e) {
            echo json_encode([
                'error' => true,
                'mensaje' => 'Error al crear recepción',
                'detalle' => $e->getMessage()
            ]);
        }
    break;

    case 'obtener_recepcion':
        header('Content-Type: application/json');

        $id = $_POST['id'] ?? '';
        if (!$id) {
            echo json_encode(['error' => true, 'mensaje' => 'ID no proporcionado']);
            exit;
        }

        $datos = $recepcion->leer_por_id($id);
        if ($datos) {
            // Mapear alias para frontend
            $map = [
                'recepcion_id'          => $datos['ajuste_id'],
                'recepcion_fecha'       => $datos['ajuste_fecha'],
                'recepcion_descripcion' => $datos['ajuste_descripcion']
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

    default:
        echo json_encode([
            'error' => true,
            'mensaje' => 'Acción no válida'
        ]);
    break;
}
?>

