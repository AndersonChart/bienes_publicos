<?php

require_once 'clasificacion.php';
$clasificacion = new clasificacion();

function validarclasificacion($datos, $modo = 'crear', $id = null) {
    $clasificacion = new clasificacion();
    $erroresFormato = [];
    $camposObligatorios = [
        'clasificacion_codigo',
        'clasificacion_nombre',
        'categoria_id'
    ];

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

    // Validación para codigo:
    if (!preg_match('/^[A-Za-z0-9_-]{1,20}$/', $datos['clasificacion_codigo'])) {
        $erroresFormato['clasificacion_codigo'] = 'El código debe tener máximo 20 caracteres entre letras, números, guiones o guiones bajos';
    }

    // Validación para nombre: letras, espacios y dígitos, hasta 100 caracteres
    if (!preg_match('/^[A-Za-zÁÉÍÓÚáéíóúÑñ0-9\s]{1,100}$/u', $datos['clasificacion_nombre'])) {
        $erroresFormato['clasificacion_nombre'] = 'El nombre tiene máximo 100 caracteres';
    }

    // Validación para descripción: letras, espacios y dígitos, hasta 200 caracteres
    if (trim($datos['clasificacion_descripcion']) !== '') {
        if (!preg_match('/^[A-Za-zÁÉÍÓÚáéíóúÑñ0-9\s]{1,200}$/u', $datos['clasificacion_descripcion'])) {
            $erroresFormato['clasificacion_descripcion'] = 'La descripción tiene máximo 200 caracteres';
        }
    }


    if (!empty($erroresFormato)) {
        $primerCampo = array_key_first($erroresFormato);
        return [
            'error' => true,
            'mensaje' => $erroresFormato[$primerCampo],
            'errores' => [$primerCampo => $erroresFormato[$primerCampo]],
            'campos' => [$primerCampo]
        ];
    }

    foreach ($datos as $clave => $valor) {
        $datos[$clave] = trim($valor);
    }

    // Validación de duplicados (solo en modo crear o si cambió el dato)
    $erroresDuplicados = [];

    $original = ($modo === 'actualizar' && $id) ? $clasificacion->leer_por_id($id) : [];

    // Validar código duplicado
    if ($modo === 'crear' && $clasificacion->existeCodigo($datos['clasificacion_codigo'])) {
        $erroresDuplicados['clasificacion_codigo'] = 'Código ya registrado';
    }

    if ($modo === 'actualizar' &&
        isset($original['clasificacion_codigo']) &&
        $datos['clasificacion_codigo'] !== $original['clasificacion_codigo'] &&
        $clasificacion->existeCodigo($datos['clasificacion_codigo'], $id)) {
        $erroresDuplicados['clasificacion_codigo'] = 'Código ya registrado';
    }

    // Validar nombre duplicado
    if ($modo === 'crear' && $clasificacion->existeNombre($datos['clasificacion_nombre'])) {
        $erroresDuplicados['clasificacion_nombre'] = 'Nombre ya registrado';
    }

    if ($modo === 'actualizar' &&
        isset($original['clasificacion_nombre']) &&
        $datos['clasificacion_nombre'] !== $original['clasificacion_nombre'] &&
        $clasificacion->existeNombre($datos['clasificacion_nombre'], $id)) {
        $erroresDuplicados['clasificacion_nombre'] = 'Nombre ya registrado';
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

// Verifica que se haya enviado una acción
$accion = isset($_POST['accion']) ? $_POST['accion'] : '';

switch ($accion) {
    case 'leer_todos':
        try {
            $estado = isset($_POST['estado']) ? intval($_POST['estado']) : 1;
            $registros = $clasificacion->leer_por_estado($estado);
            echo json_encode(['data' => $registros]);
        } catch (Exception $e) {
            http_response_code(500); // opcional: marca error HTTP
            echo json_encode([
                'data' => [],
                'error' => true,
                'mensaje' => 'Error al leer clasificaciones',
                'detalle' => $e->getMessage()
            ]);
        }
    break;

    
    case 'crear':
        try {
            header('Content-Type: application/json');

            // Recoger datos del formulario
            $datos = [
                'clasificacion_codigo'       => $_POST['clasificacion_codigo'] ?? '',
                'clasificacion_nombre'       => $_POST['clasificacion_nombre'] ?? '',
                'clasificacion_descripcion'  => $_POST['clasificacion_descripcion'] ?? '',
                'categoria_id'               => $_POST['categoria_id'] ?? ''
            ];

            $validacion = validarclasificacion($datos, 'crear');
            if (isset($validacion['error'])) {
                echo json_encode($validacion);
                exit;
            }

            $estado = 1;
            $resultado = $clasificacion->crear(
                $datos['clasificacion_codigo'],
                $datos['clasificacion_nombre'],
                $datos['categoria_id'],
                $datos['clasificacion_descripcion'],
                $estado
            );

            echo json_encode([
                'exito' => true,
                'mensaje' => 'clasificación guardada correctamente',
                'resultado' => $resultado
            ]);
        } catch (Exception $e) {
            echo json_encode([
                'error' => true,
                'mensaje' => 'Error al crear clasificación',
                'detalle' => $e->getMessage()
            ]);
        }
    break;
    
    case 'obtener_clasificacion':
        header('Content-Type: application/json');

        $id = $_POST['id'] ?? '';
        if (!$id) {
            echo json_encode(['error' => true, 'mensaje' => 'ID no proporcionado']);
            exit;
        }

        $datos = $clasificacion->leer_por_id($id);
        if ($datos) {
            echo json_encode(['exito' => true, 'clasificacion' => $datos]);
        } else {
            echo json_encode(['error' => true, 'mensaje' => 'clasificacion no encontrado']);
        }
    break;


    case 'actualizar':
        try {
            header('Content-Type: application/json');

            $id = $_POST['clasificacion_id'] ?? '';
            if (!$id) {
                echo json_encode(['error' => true, 'mensaje' => 'ID de clasificación no proporcionado']);
                exit;
            }

            $datos = [
                'clasificacion_codigo'       => $_POST['clasificacion_codigo'] ?? '',
                'clasificacion_nombre'       => $_POST['clasificacion_nombre'] ?? '',
                'clasificacion_descripcion'  => $_POST['clasificacion_descripcion'] ?? '',
                'categoria_id'               => $_POST['categoria_id'] ?? ''
            ];

            $validacion = validarclasificacion($datos, 'actualizar', $id);
            if (isset($validacion['error'])) {
                echo json_encode($validacion);
                exit;
            }

            $actual = $clasificacion->leer_por_id($id);
            if (!$actual) {
                throw new Exception("No se encontró la clasificación con ID $id");
            }

            $estado = 1;
            $resultado = $clasificacion->actualizar(
                $datos['clasificacion_codigo'],
                $datos['clasificacion_nombre'],
                $datos['categoria_id'],
                $datos['clasificacion_descripcion'],
                $estado,
                $id
            );

            if (!$resultado) {
                throw new Exception("La actualización falló. Verifica los datos enviados.");
            }

            echo json_encode([
                'exito' => true,
                'mensaje' => 'clasificacion actualizado correctamente',
                'resultado' => $resultado
            ]);
        } catch (Exception $e) {
            http_response_code(500); // Marca el error como interno
            echo json_encode([
                'error' => true,
                'mensaje' => 'Error al actualizar clasificacion',
                'detalle' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }

    break;


    case 'deshabilitar_clasificacion':
    header('Content-Type: application/json');

    try {
        $id = $_POST['id'] ?? '';
        if (!$id) {
            throw new Exception('ID no proporcionado');
        }

        $exito = $clasificacion->desincorporar($id);

        echo json_encode([
            'exito' => $exito,
            'mensaje' => $exito ? 'clasificacion deshabilitado correctamente' : 'No se pudo deshabilitar el clasificacion'
        ]);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode([
            'exito' => false,
            'mensaje' => 'Error al deshabilitar clasificacion',
            'detalle' => $e->getMessage()
        ]);
    }
    break;


    case 'recuperar_clasificacion':
        try {
            $exito = $clasificacion->recuperar($_POST['id']);
            echo json_encode([
                'exito' => $exito,
                'mensaje' => $exito ? 'clasificacion recuperado correctamente' : 'Error al recuperar'
            ]);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode([
                'exito' => false,
                'mensaje' => 'Error al recuperar clasificacion',
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