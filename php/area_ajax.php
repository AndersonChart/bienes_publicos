<?php
require_once 'area.php';
$area = new area();

function validarArea($datos, $modo = 'crear', $id = null) {
    $area = new area();
    $erroresFormato = [];
    $camposObligatorios = [
        'area_codigo',
        'area_nombre'
    ];

    // Verificar campos obligatorios
    $camposFaltantes = [];
    foreach ($camposObligatorios as $campo) {
        if (trim($datos[$campo]) === '') {
            $camposFaltantes[] = $campo;
        }
    }

    if (!empty($camposFaltantes)) {
        return [
            'error' => true,
            'mensaje' => 'Rellene los campos obligatorios',
            'campos' => $camposFaltantes
        ];
    }

    // Validación para código (máx 20, letras, números, guiones y guiones bajos)
    if (!preg_match('/^[A-Za-z0-9_-]{1,20}$/', $datos['area_codigo'])) {
        $erroresFormato['area_codigo'] = 'El código debe tener máximo 20 caracteres entre letras, números, guiones o guiones bajos';
    }

    // Validación para nombre (cualquier carácter, máximo 100)
    if (mb_strlen(trim($datos['area_nombre'])) > 100) {
        $erroresFormato['area_nombre'] = 'El nombre tiene máximo 100 caracteres';
    }

    // Validación para descripción (opcional, cualquier carácter, máximo 200)
    if (trim($datos['area_descripcion']) !== '') {
        if (mb_strlen($datos['area_descripcion']) > 200) {
            $erroresFormato['area_descripcion'] = 'La descripción tiene máximo 200 caracteres';
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

    // Normalizar valores
    foreach ($datos as $clave => $valor) {
        $datos[$clave] = trim($valor);
    }

    // Validación de duplicados
    $erroresDuplicados = [];
    $original = ($modo === 'actualizar' && $id) ? $area->leer_por_id($id) : [];

    // Código duplicado
    if ($modo === 'crear' && $area->existeCodigo($datos['area_codigo'])) {
        $erroresDuplicados['area_codigo'] = 'Código ya registrado';
    }
    if ($modo === 'actualizar' &&
        isset($original['area_codigo']) &&
        $datos['area_codigo'] !== $original['area_codigo'] &&
        $area->existeCodigo($datos['area_codigo'], $id)) {
        $erroresDuplicados['area_codigo'] = 'Código ya registrado';
    }

    // Nombre duplicado
    if ($modo === 'crear' && $area->existeNombre($datos['area_nombre'])) {
        $erroresDuplicados['area_nombre'] = 'Nombre ya registrado';
    }
    if ($modo === 'actualizar' &&
        isset($original['area_nombre']) &&
        $datos['area_nombre'] !== $original['area_nombre'] &&
        $area->existeNombre($datos['area_nombre'], $id)) {
        $erroresDuplicados['area_nombre'] = 'Nombre ya registrado';
    }

    if (!empty($erroresDuplicados)) {
        $primerCampo = array_key_first($erroresDuplicados);
        return [
            'error' => true,
            'mensaje' => $erroresDuplicados[$primerCampo],
            'errores' => $erroresDuplicados,
            'campos' => [$primerCampo]
        ];
    }

    return ['valido' => true];
}

$accion = $_POST['accion'] ?? '';

switch ($accion) {
    case 'leer_todos':
        try {
            $estado = isset($_POST['estado']) ? intval($_POST['estado']) : 1;
            $registros = $area->leer_por_estado($estado);

            header('Content-Type: application/json');
            echo json_encode(['data' => $registros]);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode([
                'data' => [],
                'error' => true,
                'mensaje' => 'Error al leer áreas',
                'detalle' => $e->getMessage()
            ]);
        }
    break;

    case 'crear':
        try {
            header('Content-Type: application/json');

            $datos = [
                'area_codigo'      => $_POST['area_codigo'] ?? '',
                'area_nombre'      => $_POST['area_nombre'] ?? '',
                'area_descripcion' => $_POST['area_descripcion'] ?? ''
            ];

            // Validación
            $validacion = validarArea($datos, 'crear');
            if (isset($validacion['error'])) {
                echo json_encode($validacion);
                exit;
            }

            $estado = 1;

            $resultado = $area->crear(
                $datos['area_codigo'],
                $datos['area_nombre'],
                $datos['area_descripcion'],
                $estado
            );

            echo json_encode([
                'exito' => true,
                'mensaje' => 'Área guardada correctamente',
                'resultado' => $resultado
            ]);
        } catch (Exception $e) {
            echo json_encode([
                'error' => true,
                'mensaje' => 'Error al crear área',
                'detalle' => $e->getMessage()
            ]);
        }
    break;

    case 'obtener_area':
        header('Content-Type: application/json');

        $id = $_POST['id'] ?? '';
        if (!$id) {
            echo json_encode(['error' => true, 'mensaje' => 'ID no proporcionado']);
            exit;
        }

        $datos = $area->leer_por_id($id);
        if ($datos) {
            echo json_encode(['exito' => true, 'area' => $datos]);
        } else {
            echo json_encode(['error' => true, 'mensaje' => 'Área no encontrada']);
        }
    break;

    case 'actualizar':
        try {
            header('Content-Type: application/json');

            $id = $_POST['area_id'] ?? '';
            if (!$id) {
                echo json_encode(['error' => true, 'mensaje' => 'ID de área no proporcionado']);
                exit;
            }

            $datos = [
                'area_codigo'      => $_POST['area_codigo'] ?? '',
                'area_nombre'      => $_POST['area_nombre'] ?? '',
                'area_descripcion' => $_POST['area_descripcion'] ?? ''
            ];

            $validacion = validarArea($datos, 'actualizar', $id);
            if (isset($validacion['error'])) {
                echo json_encode($validacion);
                exit;
            }

            $actual = $area->leer_por_id($id);
            if (!$actual) {
                throw new Exception("No se encontró el área con ID $id");
            }

            $estado = 1;

            $resultado = $area->actualizar(
                $datos['area_codigo'],
                $datos['area_nombre'],
                $datos['area_descripcion'],
                $estado,
                $id
            );

            if (!$resultado) {
                throw new Exception("La actualización falló. Verifica los datos enviados.");
            }

            echo json_encode([
                'exito' => true,
                'mensaje' => 'Área actualizada correctamente',
                'resultado' => $resultado
            ]);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode([
                'error' => true,
                'mensaje' => 'Error al actualizar área',
                'detalle' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }
    break;

    case 'deshabilitar_area':
        header('Content-Type: application/json');
        try {
            $id = $_POST['id'] ?? '';
            if (!$id) {
                throw new Exception('ID no proporcionado');
            }

            $exito = $area->desincorporar($id);

            echo json_encode([
                'exito' => $exito,
                'mensaje' => $exito ? 'Área deshabilitada correctamente' : 'No se pudo deshabilitar el área'
            ]);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode([
                'exito' => false,
                'mensaje' => 'Error al deshabilitar área',
                'detalle' => $e->getMessage()
            ]);
        }
    break;

    case 'recuperar_area':
        try {
            $exito = $area->recuperar($_POST['id']);
            echo json_encode([
                'exito' => $exito,
                'mensaje' => $exito ? 'Área recuperada correctamente' : 'Error al recuperar'
            ]);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode([
                'exito' => false,
                'mensaje' => 'Error al recuperar área',
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
