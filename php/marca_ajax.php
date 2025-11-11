<?php

require_once 'marca.php';
$marca = new marca();

function validarMarca($datos, $modo = 'crear', $id = null) {
    $marca = new marca();
    $erroresFormato = [];

    // Campos obligatorios
    $camposObligatorios = ['marca_codigo', 'marca_nombre'];
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
    if (!preg_match('/^[A-Za-z0-9_-]{1,20}$/', $datos['marca_codigo'])) {
        $erroresFormato['marca_codigo'] = 'El código debe tener máximo 20 caracteres entre letras, números, guiones o guiones bajos';
    }

    if (!preg_match('/^[A-Za-zÁÉÍÓÚáéíóúÑñ0-9\s]{1,100}$/u', $datos['marca_nombre'])) {
        $erroresFormato['marca_nombre'] = 'El nombre tiene máximo 100 caracteres';
    }

    // Validación de imagen (solo si se sube)
    if (isset($_FILES['marca_imagen']) && $_FILES['marca_imagen']['error'] === UPLOAD_ERR_OK) {
        $extension = strtolower(pathinfo($_FILES['marca_imagen']['name'], PATHINFO_EXTENSION));
        $peso = $_FILES['marca_imagen']['size'];

        if (!in_array($extension, ['jpg', 'jpeg', 'png'])) {
            $erroresFormato['marca_imagen'] = 'Formato de imagen no permitido (solo JPG, JPEG o PNG)';
        } elseif ($peso > 3 * 1024 * 1024) {
            $erroresFormato['marca_imagen'] = 'La imagen supera el tamaño máximo de 3MB';
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
    $original = ($modo === 'actualizar' && $id) ? $marca->leer_por_id($id) : [];

    if ($modo === 'crear' && $marca->existeCodigo($datos['marca_codigo'])) {
        $erroresDuplicados['marca_codigo'] = 'Código ya registrado';
    }

    if ($modo === 'actualizar' &&
        isset($original['marca_codigo']) &&
        $datos['marca_codigo'] !== $original['marca_codigo'] &&
        $marca->existeCodigo($datos['marca_codigo'], $id)) {
        $erroresDuplicados['marca_codigo'] = 'Código ya registrado';
    }

    if ($modo === 'crear' && $marca->existeNombre($datos['marca_nombre'])) {
        $erroresDuplicados['marca_nombre'] = 'Nombre ya registrado';
    }

    if ($modo === 'actualizar' &&
        isset($original['marca_nombre']) &&
        $datos['marca_nombre'] !== $original['marca_nombre'] &&
        $marca->existeNombre($datos['marca_nombre'], $id)) {
        $erroresDuplicados['marca_nombre'] = 'Nombre ya registrado';
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
            $registros = $marca->leer_por_estado($estado);
            echo json_encode(['data' => $registros]);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode([
                'data' => [],
                'error' => true,
                'mensaje' => 'Error al leer marcas',
                'detalle' => $e->getMessage()
            ]);
        }
    break;

    case 'crear':
        try {
            header('Content-Type: application/json');

            $datos = [
                'marca_codigo' => $_POST['marca_codigo'] ?? '',
                'marca_nombre' => $_POST['marca_nombre'] ?? '',
                'marca_imagen' => ''
            ];

            $validacion = validarMarca($datos, 'crear');
            if (isset($validacion['error'])) {
                echo json_encode($validacion);
                exit;
            }

            if (isset($_FILES['marca_imagen']) && $_FILES['marca_imagen']['error'] === UPLOAD_ERR_OK) {
                $nombreLimpio = preg_replace('/[^a-zA-Z0-9_-]/', '_', strtolower($datos['marca_nombre']));
                $extension = pathinfo($_FILES['marca_imagen']['name'], PATHINFO_EXTENSION);
                $nombreArchivo = $nombreLimpio . '_' . uniqid() . '.' . $extension;
                $directorio = '../img/marcas/';
                if (!is_dir($directorio)) mkdir($directorio, 0755, true);
                $rutaRelativa = 'img/marcas/' . $nombreArchivo;
                move_uploaded_file($_FILES['marca_imagen']['tmp_name'], $directorio . $nombreArchivo);
                $datos['marca_imagen'] = $rutaRelativa;
            }

            $estado = 1;
            $resultado = $marca->crear($datos['marca_codigo'], $datos['marca_nombre'], $datos['marca_imagen'], $estado);

            echo json_encode([
                'exito' => true,
                'mensaje' => 'Marca registrada correctamente',
                'resultado' => $resultado
            ]);
        } catch (Exception $e) {
            echo json_encode([
                'error' => true,
                'mensaje' => 'Error al crear marca',
                'detalle' => $e->getMessage()
            ]);
        }
    break;

    case 'obtener_marca':
        header('Content-Type: application/json');
        $id = $_POST['id'] ?? '';
        if (!$id) {
            echo json_encode(['error' => true, 'mensaje' => 'ID no proporcionado']);
            exit;
        }
        $datos = $marca->leer_por_id($id);
        echo json_encode($datos ? ['exito' => true, 'marca' => $datos] : ['error' => true, 'mensaje' => 'Marca no encontrada']);
    break;

    case 'actualizar':
        try {
            header('Content-Type: application/json');
            $id = $_POST['marca_id'] ?? '';
            if (!$id) {
                echo json_encode(['error' => true, 'mensaje' => 'ID de marca no proporcionado']);
                exit;
            }

            $datos = [
                'marca_codigo' => $_POST['marca_codigo'] ?? '',
                'marca_nombre' => $_POST['marca_nombre'] ?? '',
                'marca_imagen' => ''
            ];

            $validacion = validarMarca($datos, 'actualizar', $id);
            if (isset($validacion['error'])) {
                echo json_encode($validacion);
                exit;
            }

            $actual = $marca->leer_por_id($id);
            if (!$actual) throw new Exception("No se encontró la marca con ID $id");

            if (isset($_FILES['marca_imagen']) && $_FILES['marca_imagen']['error'] === UPLOAD_ERR_OK) {
                if (!empty($actual['marca_imagen'])) {
                    $rutaAnterior = '../' . $actual['marca_imagen'];
                    if (file_exists($rutaAnterior)) unlink($rutaAnterior);
                }
                $nombreLimpio = preg_replace('/[^a-zA-Z0-9_-]/', '_', strtolower($datos['marca_nombre']));
                $extension = pathinfo($_FILES['marca_imagen']['name'], PATHINFO_EXTENSION);
                $nombreArchivo = $nombreLimpio . '_' . uniqid() . '.' . $extension;
                $directorio = '../img/marcas/';
                if (!is_dir($directorio)) mkdir($directorio, 0755, true);
                $rutaRelativa = 'img/marcas/' . $nombreArchivo;
                move_uploaded_file($_FILES['marca_imagen']['tmp_name'], $directorio . $nombreArchivo);
                $datos['marca_imagen'] = $rutaRelativa;
            } else {
                $datos['marca_imagen'] = $actual['marca_imagen'];
            }

            $estado = 1;
            $resultado = $marca->actualizar($datos['marca_codigo'], $datos['marca_nombre'], $datos['marca_imagen'], $estado, $id);

            echo json_encode([
                'exito' => true,
                'mensaje' => 'Marca actualizada correctamente',
                'resultado' => $resultado
            ]);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode([
                'error' => true,
                'mensaje' => 'Error al actualizar marca',
                'detalle' => $e->getMessage()
            ]);
        }
    break;

    case 'deshabilitar_marca':
        try {
            $id = $_POST['id'] ?? '';
            if (!$id) throw new Exception('ID no proporcionado');
            $exito = $marca->desincorporar($id);
            echo json_encode([
                'exito' => $exito,
                'mensaje' => $exito ? 'Marca deshabilitada correctamente' : 'No se pudo deshabilitar la marca'
            ]);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode([
                'exito' => false,
                'mensaje' => 'Error al deshabilitar marca',
                'detalle' => $e->getMessage()
            ]);
        }
    break;

    case 'recuperar_marca':
        try {
            $id = $_POST['id'] ?? '';
            $exito = $marca->recuperar($id);
            echo json_encode([
                'exito' => $exito,
                'mensaje' => $exito ? 'Marca recuperada correctamente' : 'Error al recuperar'
            ]);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode([
                'exito' => false,
                'mensaje' => 'Error al recuperar marca',
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
