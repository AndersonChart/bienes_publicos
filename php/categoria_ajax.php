<?php
require_once 'categoria.php';
$categoria = new categoria();

function validarCategoria($datos, $modo = 'crear', $id = null) {
    $categoria = new categoria();
    $erroresFormato = [];
    $camposObligatorios = [
        'categoria_codigo',
        'categoria_nombre',
        'categoria_tipo'
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

    // Validación para código
    if (!preg_match('/^[A-Za-z0-9_-]{1,20}$/', $datos['categoria_codigo'])) {
        $erroresFormato['categoria_codigo'] = 'El código debe tener máximo 20 caracteres entre letras, números, guiones o guiones bajos';
    }

    // Validación para nombre
    if (!preg_match('/^[A-Za-zÁÉÍÓÚáéíóúÑñ0-9\s]{1,100}$/u', $datos['categoria_nombre'])) {
        $erroresFormato['categoria_nombre'] = 'El nombre tiene máximo 100 caracteres';
    }

    // Validación para descripción (opcional)
    if (trim($datos['categoria_descripcion']) !== '') {
        if (!preg_match('/^[A-Za-zÁÉÍÓÚáéíóúÑñ0-9\s]{1,200}$/u', $datos['categoria_descripcion'])) {
            $erroresFormato['categoria_descripcion'] = 'La descripción tiene máximo 200 caracteres';
        }
    }

    // Validación para tipo (solo 0 o 1)
    $datos['categoria_tipo'] = isset($datos['categoria_tipo']) ? intval($datos['categoria_tipo']) : null;

    if (!in_array($datos['categoria_tipo'], [0, 1], true)) {
        $erroresFormato['categoria_tipo'] = 'El tipo de categoría debe ser 0 (Básico) o 1 (Completo)';
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

    // Validación de duplicados
    $erroresDuplicados = [];
    $original = ($modo === 'actualizar' && $id) ? $categoria->leer_por_id($id) : [];

    // Código duplicado
    if ($modo === 'crear' && $categoria->existeCodigo($datos['categoria_codigo'])) {
        $erroresDuplicados['categoria_codigo'] = 'Código ya registrado';
    }
    if ($modo === 'actualizar' &&
        isset($original['categoria_codigo']) &&
        $datos['categoria_codigo'] !== $original['categoria_codigo'] &&
        $categoria->existeCodigo($datos['categoria_codigo'], $id)) {
        $erroresDuplicados['categoria_codigo'] = 'Código ya registrado';
    }

    // Nombre duplicado
    if ($modo === 'crear' && $categoria->existeNombre($datos['categoria_nombre'])) {
        $erroresDuplicados['categoria_nombre'] = 'Nombre ya registrado';
    }
    if ($modo === 'actualizar' &&
        isset($original['categoria_nombre']) &&
        $datos['categoria_nombre'] !== $original['categoria_nombre'] &&
        $categoria->existeNombre($datos['categoria_nombre'], $id)) {
        $erroresDuplicados['categoria_nombre'] = 'Nombre ya registrado';
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
    case 'leer_todas':
        try {
            $estado = isset($_POST['estado']) ? intval($_POST['estado']) : 1;
            $tipo = isset($_POST['categoria_tipo']) && $_POST['categoria_tipo'] !== '' 
                ? intval($_POST['categoria_tipo']) 
                : null;

            if ($tipo === null) {
                // Traer todas las categorías sin filtrar por tipo
                $registros = $categoria->leer_por_estado($estado);
            } else {
                // Traer filtrando por tipo
                $registros = $categoria->leer_por_estado_y_tipo($estado, $tipo);
            }

            header('Content-Type: application/json');
            echo json_encode(['data' => $registros]);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode([
                'data' => [],
                'error' => true,
                'mensaje' => 'Error al leer categorías',
                'detalle' => $e->getMessage()
            ]);
        }
    break;


    case 'crear':
        try {
            header('Content-Type: application/json');

            $datos = [
                'categoria_codigo'      => $_POST['categoria_codigo'] ?? '',
                'categoria_nombre'      => $_POST['categoria_nombre'] ?? '',
                'categoria_tipo' => $_POST['categoria_tipo'] ?? '',
                'categoria_descripcion' => $_POST['categoria_descripcion'] ?? ''
            ];

            // Aquí luego conectamos con validarCategoria()
            $validacion = validarCategoria($datos, 'crear');
            if (isset($validacion['error'])) {
                echo json_encode($validacion);
                exit;
            }

            $estado = 1;
            $datos['categoria_tipo'] = intval($_POST['categoria_tipo'] ?? 0);

            $resultado = $categoria->crear(
                $datos['categoria_codigo'],
                $datos['categoria_nombre'],
                $datos['categoria_tipo'],
                $datos['categoria_descripcion'],
                $estado
            );

            echo json_encode([
                'exito' => true,
                'mensaje' => 'Categoría guardada correctamente',
                'resultado' => $resultado
            ]);
        } catch (Exception $e) {
            echo json_encode([
                'error' => true,
                'mensaje' => 'Error al crear categoría',
                'detalle' => $e->getMessage()
            ]);
        }
    break;

    case 'obtener_categoria':
        header('Content-Type: application/json');

        $id = $_POST['id'] ?? '';
        if (!$id) {
            echo json_encode(['error' => true, 'mensaje' => 'ID no proporcionado']);
            exit;
        }

        $datos = $categoria->leer_por_id($id);
        if ($datos) {
            echo json_encode(['exito' => true, 'categoria' => $datos]);
        } else {
            echo json_encode(['error' => true, 'mensaje' => 'Categoría no encontrada']);
        }
    break;

    case 'actualizar':
        try {
            header('Content-Type: application/json');

            $id = $_POST['categoria_id'] ?? '';
            if (!$id) {
                echo json_encode(['error' => true, 'mensaje' => 'ID de categoría no proporcionado']);
                exit;
            }

            $datos = [
                'categoria_codigo'      => $_POST['categoria_codigo'] ?? '',
                'categoria_nombre'      => $_POST['categoria_nombre'] ?? '',
                'categoria_tipo' => $_POST['categoria_tipo'] ?? '',
                'categoria_descripcion' => $_POST['categoria_descripcion'] ?? ''
            ];

            $validacion = validarCategoria($datos, 'actualizar', $id);
            if (isset($validacion['error'])) {
                echo json_encode($validacion);
                exit;
            }

            $actual = $categoria->leer_por_id($id);
            if (!$actual) {
                throw new Exception("No se encontró la categoría con ID $id");
            }

            $estado = 1;
            $datos['categoria_tipo'] = intval($_POST['categoria_tipo'] ?? 0);

            $resultado = $categoria->actualizar(
                $datos['categoria_codigo'],
                $datos['categoria_nombre'],
                $datos['categoria_tipo'],
                $datos['categoria_descripcion'],
                $estado,
                $id
            );

            if (!$resultado) {
                throw new Exception("La actualización falló. Verifica los datos enviados.");
            }

            echo json_encode([
                'exito' => true,
                'mensaje' => 'Categoría actualizada correctamente',
                'resultado' => $resultado
            ]);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode([
                'error' => true,
                'mensaje' => 'Error al actualizar categoría',
                'detalle' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }
    break;

    case 'deshabilitar_categoria':
        header('Content-Type: application/json');
        try {
            $id = $_POST['id'] ?? '';
            if (!$id) {
                throw new Exception('ID no proporcionado');
            }

            $exito = $categoria->desincorporar($id);

            echo json_encode([
                'exito' => $exito,
                'mensaje' => $exito ? 'Categoría deshabilitada correctamente' : 'No se pudo deshabilitar la categoría'
            ]);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode([
                'exito' => false,
                'mensaje' => 'Error al deshabilitar categoría',
                'detalle' => $e->getMessage()
            ]);
        }
    break;

    case 'recuperar_categoria':
        try {
            $exito = $categoria->recuperar($_POST['id']);
            echo json_encode([
                'exito' => $exito,
                'mensaje' => $exito ? 'Categoría recuperada correctamente' : 'Error al recuperar'
            ]);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode([
                'exito' => false,
                'mensaje' => 'Error al recuperar categoría',
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

