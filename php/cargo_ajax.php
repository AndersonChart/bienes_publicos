<?php
require_once 'cargo.php';
$cargo = new cargo();

function validarCargo($datos, $modo = 'crear', $id = null) {
    $cargo = new cargo();
    $erroresFormato = [];
    $camposObligatorios = [
        'cargo_codigo',
        'cargo_nombre'
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

    // Validación para código
    if (!preg_match('/^[A-Za-z0-9_-]{1,20}$/', $datos['cargo_codigo'])) {
        $erroresFormato['cargo_codigo'] = 'El código debe tener máximo 20 caracteres entre letras, números, guiones o guiones bajos';
    }

    // Validación para nombre (cualquier carácter, máximo 100)
    if (mb_strlen(trim($datos['cargo_nombre'])) > 100) {
        $erroresFormato['cargo_nombre'] = 'El nombre tiene máximo 100 caracteres';
    }

    // Validación para descripción (opcional, cualquier carácter, máximo 200)
    if (trim($datos['cargo_descripcion']) !== '') {
        if (mb_strlen($datos['cargo_descripcion']) > 200) {
            $erroresFormato['cargo_descripcion'] = 'La descripción tiene máximo 200 caracteres';
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
    $original = ($modo === 'actualizar' && $id) ? $cargo->leer_por_id($id) : [];

    // Código duplicado
    if ($modo === 'crear' && $cargo->existeCodigo($datos['cargo_codigo'])) {
        $erroresDuplicados['cargo_codigo'] = 'Código ya registrado';
    }
    if ($modo === 'actualizar' &&
        isset($original['cargo_codigo']) &&
        $datos['cargo_codigo'] !== $original['cargo_codigo'] &&
        $cargo->existeCodigo($datos['cargo_codigo'], $id)) {
        $erroresDuplicados['cargo_codigo'] = 'Código ya registrado';
    }

    // Nombre duplicado
    if ($modo === 'crear' && $cargo->existeNombre($datos['cargo_nombre'])) {
        $erroresDuplicados['cargo_nombre'] = 'Nombre ya registrado';
    }
    if ($modo === 'actualizar' &&
        isset($original['cargo_nombre']) &&
        $datos['cargo_nombre'] !== $original['cargo_nombre'] &&
        $cargo->existeNombre($datos['cargo_nombre'], $id)) {
        $erroresDuplicados['cargo_nombre'] = 'Nombre ya registrado';
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
            $registros = $cargo->leer_por_estado($estado);

            header('Content-Type: application/json');
            echo json_encode(['data' => $registros]);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode([
                'data' => [],
                'error' => true,
                'mensaje' => 'Error al leer cargos',
                'detalle' => $e->getMessage()
            ]);
        }
    break;

    case 'crear':
        try {
            header('Content-Type: application/json');

            $datos = [
                'cargo_codigo'      => $_POST['cargo_codigo'] ?? '',
                'cargo_nombre'      => $_POST['cargo_nombre'] ?? '',
                'cargo_descripcion' => $_POST['cargo_descripcion'] ?? ''
            ];

            // Validación
            $validacion = validarCargo($datos, 'crear');
            if (isset($validacion['error'])) {
                echo json_encode($validacion);
                exit;
            }

            $estado = 1;

            $resultado = $cargo->crear(
                $datos['cargo_codigo'],
                $datos['cargo_nombre'],
                $datos['cargo_descripcion'],
                $estado
            );

            echo json_encode([
                'exito' => true,
                'mensaje' => 'Cargo guardado correctamente',
                'resultado' => $resultado
            ]);
        } catch (Exception $e) {
            echo json_encode([
                'error' => true,
                'mensaje' => 'Error al crear cargo',
                'detalle' => $e->getMessage()
            ]);
        }
    break;

    case 'obtener_cargo':
        header('Content-Type: application/json');

        $id = $_POST['id'] ?? '';
        if (!$id) {
            echo json_encode(['error' => true, 'mensaje' => 'ID no proporcionado']);
            exit;
        }

        $datos = $cargo->leer_por_id($id);
        if ($datos) {
            echo json_encode(['exito' => true, 'cargo' => $datos]);
        } else {
            echo json_encode(['error' => true, 'mensaje' => 'Cargo no encontrado']);
        }
    break;

    case 'actualizar':
        try {
            header('Content-Type: application/json');

            $id = $_POST['cargo_id'] ?? '';
            if (!$id) {
                echo json_encode(['error' => true, 'mensaje' => 'ID de cargo no proporcionado']);
                exit;
            }

            $datos = [
                'cargo_codigo'      => $_POST['cargo_codigo'] ?? '',
                'cargo_nombre'      => $_POST['cargo_nombre'] ?? '',
                'cargo_descripcion' => $_POST['cargo_descripcion'] ?? ''
            ];

            $validacion = validarCargo($datos, 'actualizar', $id);
            if (isset($validacion['error'])) {
                echo json_encode($validacion);
                exit;
            }

            $actual = $cargo->leer_por_id($id);
            if (!$actual) {
                throw new Exception("No se encontró el cargo con ID $id");
            }

            $estado = 1;

            $resultado = $cargo->actualizar(
                $datos['cargo_codigo'],
                $datos['cargo_nombre'],
                $datos['cargo_descripcion'],
                $estado,
                $id
            );

            if (!$resultado) {
                throw new Exception("La actualización falló. Verifica los datos enviados.");
            }

            echo json_encode([
                'exito' => true,
                'mensaje' => 'Cargo actualizado correctamente',
                'resultado' => $resultado
            ]);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode([
                'error' => true,
                'mensaje' => 'Error al actualizar cargo',
                'detalle' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }
    break;

    case 'deshabilitar_cargo':
        header('Content-Type: application/json');
        try {
            $id = $_POST['id'] ?? '';
            if (!$id) {
                throw new Exception('ID no proporcionado');
            }

            $exito = $cargo->desincorporar($id);

            echo json_encode([
                'exito' => $exito,
                'mensaje' => $exito ? 'Cargo deshabilitado correctamente' : 'No se pudo deshabilitar el cargo'
            ]);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode([
                'exito' => false,
                'mensaje' => 'Error al deshabilitar cargo',
                'detalle' => $e->getMessage()
            ]);
        }
    break;

    case 'recuperar_cargo':
        try {
            $exito = $cargo->recuperar($_POST['id']);
            echo json_encode([
                'exito' => $exito,
                'mensaje' => $exito ? 'Cargo recuperado correctamente' : 'Error al recuperar'
            ]);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode([
                'exito' => false,
                'mensaje' => 'Error al recuperar cargo',
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


