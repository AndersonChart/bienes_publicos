<?php

require_once 'bien_tipo.php';
$bien_tipo = new bien_tipo();

function validarBienTipo($datos, $modo = 'crear', $id = null) {
    $bien_tipo = new bien_tipo();
    $erroresFormato = [];

    // Campos obligatorios
    $camposObligatorios = ['bien_tipo_codigo', 'bien_nombre', 'categoria_id', 'clasificacion_id'];
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

    // Validación de formato
    if (!preg_match('/^[A-Za-z0-9_-]{1,20}$/', $datos['bien_tipo_codigo'])) {
        $erroresFormato['bien_tipo_codigo'] = 'El código debe tener máximo 20 caracteres entre letras, números, guiones o guiones bajos';
    }

    if (!preg_match('/^[A-Za-zÁÉÍÓÚáéíóúÑñ0-9\s]{1,100}$/u', $datos['bien_nombre'])) {
        $erroresFormato['bien_nombre'] = 'El nombre tiene máximo 100 caracteres';
    }

    // Validación de imagen (solo si se sube)
    if (isset($_FILES['bien_imagen']) && $_FILES['bien_imagen']['error'] === UPLOAD_ERR_OK) {
        $extension = strtolower(pathinfo($_FILES['bien_imagen']['name'], PATHINFO_EXTENSION));
        $peso = $_FILES['bien_imagen']['size'];

        if (!in_array($extension, ['jpg', 'jpeg', 'png'])) {
            $erroresFormato['bien_imagen'] = 'Formato de imagen no permitido (solo JPG, JPEG o PNG)';
        } elseif ($peso > 3 * 1024 * 1024) {
            $erroresFormato['bien_imagen'] = 'La imagen supera el tamaño máximo de 3MB';
        }
    }

    if (!empty($erroresFormato)) {
        $primerCampo = array_key_first($erroresFormato);
        return [
            'error' => true,
            'mensaje' => $erroresFormato[$primerCampo],
            'errores' => $erroresFormato,
            'campos' => [$primerCampo]
        ];
    }

    // Sanitizar datos
    foreach ($datos as $clave => $valor) {
        $datos[$clave] = trim($valor);
    }

    // Validación de duplicados
    $erroresDuplicados = [];
    $original = ($modo === 'actualizar' && $id) ? $bien_tipo->leer_por_id($id) : [];

    if ($modo === 'crear' && $bien_tipo->existeCodigo($datos['bien_tipo_codigo'])) {
        $erroresDuplicados['bien_tipo_codigo'] = 'Código ya registrado';
    }

    if ($modo === 'actualizar' &&
        isset($original['bien_tipo_codigo']) &&
        $datos['bien_tipo_codigo'] !== $original['bien_tipo_codigo'] &&
        $bien_tipo->existeCodigo($datos['bien_tipo_codigo'], $id)) {
        $erroresDuplicados['bien_tipo_codigo'] = 'Código ya registrado';
    }

    if ($modo === 'crear' && $bien_tipo->existeNombre($datos['bien_nombre'])) {
        $erroresDuplicados['bien_nombre'] = 'Nombre ya registrado';
    }

    if ($modo === 'actualizar' &&
        isset($original['bien_nombre']) &&
        $datos['bien_nombre'] !== $original['bien_nombre'] &&
        $bien_tipo->existeNombre($datos['bien_nombre'], $id)) {
        $erroresDuplicados['bien_nombre'] = 'Nombre ya registrado';
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
$accion = $_POST['accion'] ?? '';

switch ($accion) {
    case 'leer_todos':
        try {
            $estado = isset($_POST['estado']) ? intval($_POST['estado']) : 1;
            $categoriaId = $_POST['categoria_id'] ?? null;
            $clasificacionId = $_POST['clasificacion_id'] ?? null;

            $registros = $bien_tipo->leer_por_estado($estado, $categoriaId, $clasificacionId);
            echo json_encode(['data' => $registros]);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode([
                'data' => [],
                'error' => true,
                'mensaje' => 'Error al leer bienes',
                'detalle' => $e->getMessage()
            ]);
        }
    break;

    case 'crear':
        try {
            header('Content-Type: application/json');

            $datos = [
                'bien_tipo_codigo' => $_POST['bien_tipo_codigo'] ?? '',
                'categoria_id' => $_POST['categoria_id'] ?? '',
                'clasificacion_id' => $_POST['clasificacion_id'] ?? '',
                'bien_nombre' => $_POST['bien_nombre'] ?? '',
                'bien_modelo' => $_POST['bien_modelo'] ?? '',
                'marca_id' => $_POST['marca_id'] ?? '',
                'bien_descripcion' => $_POST['bien_descripcion'] ?? '',
                'bien_estado' => 1,
                'bien_imagen' => ''
            ];

            $validacion = validarBienTipo($datos, 'crear');
            if (isset($validacion['error'])) {
                echo json_encode($validacion);
                exit;
            }

            if (isset($_FILES['bien_imagen']) && $_FILES['bien_imagen']['error'] === UPLOAD_ERR_OK) {
                $nombreLimpio = preg_replace('/[^a-zA-Z0-9_-]/', '_', strtolower($datos['bien_nombre']));
                $extension = pathinfo($_FILES['bien_imagen']['name'], PATHINFO_EXTENSION);
                $nombreArchivo = $nombreLimpio . '_' . uniqid() . '.' . $extension;
                $directorio = '../img/bienes/';
                if (!is_dir($directorio)) mkdir($directorio, 0755, true);
                $rutaRelativa = 'img/bienes/' . $nombreArchivo;
                move_uploaded_file($_FILES['bien_imagen']['tmp_name'], $directorio . $nombreArchivo);
                $datos['bien_imagen'] = $rutaRelativa;
            }

            $resultado = $bien_tipo->crear(
                $datos['bien_tipo_codigo'], $datos['bien_nombre'], $datos['bien_modelo'],
                $datos['marca_id'], $datos['categoria_id'], $datos['clasificacion_id'],
                $datos['bien_descripcion'], $datos['bien_estado'], $datos['bien_imagen']
            );

            echo json_encode([
                'exito' => true,
                'mensaje' => 'Bien registrado correctamente',
                'resultado' => $resultado
            ]);
        } catch (Exception $e) {
            echo json_encode([
                'error' => true,
                'mensaje' => 'Error al crear bien',
                'detalle' => $e->getMessage()
            ]);
        }
    break;

    case 'obtener_bien':
        header('Content-Type: application/json');
        $id = $_POST['id'] ?? '';
        if (!$id) {
            echo json_encode(['error' => true, 'mensaje' => 'ID no proporcionado']);
            exit;
        }
        $datos = $bien_tipo->leer_por_id($id);
        echo json_encode($datos ? ['exito' => true, 'bien' => $datos] : ['error' => true, 'mensaje' => 'Bien no encontrado']);
    break;

    case 'actualizar':
        try {
            header('Content-Type: application/json');
            $id = $_POST['bien_tipo_id'] ?? '';
            if (!$id) {
                echo json_encode(['error' => true, 'mensaje' => 'ID de bien no proporcionado']);
                exit;
            }

            $datos = [
                'bien_tipo_codigo' => $_POST['bien_tipo_codigo'] ?? '',
                'categoria_id' => $_POST['categoria_id'] ?? '',
                'clasificacion_id' => $_POST['clasificacion_id'] ?? '',
                'bien_nombre' => $_POST['bien_nombre'] ?? '',
                'bien_modelo' => $_POST['bien_modelo'] ?? '',
                'marca_id' => $_POST['marca_id'] ?? '',
                'bien_descripcion' => $_POST['bien_descripcion'] ?? '',
                'bien_estado' => 1,
                'bien_imagen' => ''
            ];

            $validacion = validarBienTipo($datos, 'actualizar', $id);
            if (isset($validacion['error'])) {
                echo json_encode($validacion);
                exit;
            }

            $actual = $bien_tipo->leer_por_id($id);
            if (!$actual) throw new Exception("No se encontró el bien con ID $id");

            if (isset($_FILES['bien_imagen']) && $_FILES['bien_imagen']['error'] === UPLOAD_ERR_OK) {
                if (!empty($actual['bien_imagen'])) {
                    $rutaAnterior = '../' . $actual['bien_imagen'];
                    if (file_exists($rutaAnterior)) unlink($rutaAnterior);
                }
                $nombreLimpio = preg_replace('/[^a-zA-Z0-9_-]/', '_', strtolower($datos['bien_nombre']));
                $extension = pathinfo($_FILES['bien_imagen']['name'], PATHINFO_EXTENSION);
                $nombreArchivo = $nombreLimpio . '_' . uniqid() . '.' . $extension;
                $directorio = '../img/bienes/';
                if (!is_dir($directorio)) mkdir($directorio, 0755, true);
                $rutaRelativa = 'img/bienes/' . $nombreArchivo;
                move_uploaded_file($_FILES['bien_imagen']['tmp_name'], $directorio . $nombreArchivo);
                $datos['bien_imagen'] = $rutaRelativa;
            } else {
                $datos['bien_imagen'] = $actual['bien_imagen'];
            }

            $resultado = $bien_tipo->actualizar(
                $datos['bien_tipo_codigo'], $datos['bien_nombre'], $datos['bien_modelo'],
                $datos['marca_id'], $datos['categoria_id'], $datos['clasificacion_id'],
                $datos['bien_descripcion'], $datos['bien_estado'], $datos['bien_imagen'], $id
            );

            echo json_encode([
                'exito' => true,
                'mensaje' => 'Bien actualizado correctamente',
                'resultado' => $resultado
            ]);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode([
                'error' => true,
                'mensaje' => 'Error al actualizar bien',
                'detalle' => $e->getMessage()
            ]);
        }
    break;

    case 'deshabilitar_bien':
        try {
            $id = $_POST['id'] ?? '';
            if (!$id) throw new Exception('ID no proporcionado');
            $exito = $bien_tipo->desincorporar($id);
            echo json_encode([
                'exito' => $exito,
                'mensaje' => $exito ? 'Bien deshabilitado correctamente' : 'No se pudo deshabilitar el bien'
            ]);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode([
                'exito' => false,
                'mensaje' => 'Error al deshabilitar bien',
                'detalle' => $e->getMessage()
            ]);
        }
    break;

    case 'recuperar_bien':
        try {
            $id = $_POST['id'] ?? '';
            $exito = $bien_tipo->recuperar($id);
            echo json_encode([
                'exito' => $exito,
                'mensaje' => $exito ? 'Bien recuperado correctamente' : 'Error al recuperar'
            ]);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode([
                'exito' => false,
                'mensaje' => 'Error al recuperar bien',
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
