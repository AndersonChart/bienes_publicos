<?php

require_once 'articulo.php';
require_once 'clasificacion.php';
$articulo = new articulo();

function validarArticulo($datos, $modo = 'crear', $id = null) {
    $articulo = new articulo();
    $clasificacion = new clasificacion();
    $erroresFormato = [];
    $camposFaltantes = [];

    // Campos obligatorios comunes
    if (trim((string)$datos['articulo_codigo']) === '') {
        $camposFaltantes[] = 'articulo_codigo';
    }
    if (trim((string)$datos['articulo_nombre']) === '') {
        $camposFaltantes[] = 'articulo_nombre';
    }
    if (empty($datos['clasificacion_id']) || !ctype_digit((string)$datos['clasificacion_id'])) {
        $camposFaltantes[] = 'clasificacion_id';
    }

    // Obtener categoria_tipo desde la BD según la clasificación
    $categoriaTipo = null;
    if (!empty($datos['clasificacion_id']) && ctype_digit((string)$datos['clasificacion_id'])) {
        $categoriaTipo = $clasificacion->obtenerCategoriaTipo((int)$datos['clasificacion_id']);
    }

    // Validación condicional: si la categoría es de tipo completo (1)
    if ((int)$categoriaTipo === 1) {
        if (trim((string)$datos['articulo_modelo']) === '') {
            $camposFaltantes[] = 'articulo_modelo';
        }
        if (empty($datos['marca_id']) || !ctype_digit((string)$datos['marca_id'])) {
            $camposFaltantes[] = 'marca_id';
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
    if (!preg_match('/^[A-Za-z0-9_-]{1,20}$/', $datos['articulo_codigo'])) {
        $erroresFormato['articulo_codigo'] = 'El código debe tener máximo 20 caracteres entre letras, números, guiones o guiones bajos';
    }

    // Validación para nombre (cualquier carácter, máximo 100)
    if (mb_strlen(trim($datos['articulo_nombre'])) > 100) {
        $erroresFormato['articulo_nombre'] = 'El nombre debe tener máximo 100 caracteres';
    }

    // Validación para modelo (opcional, cualquier carácter, máximo 100)
    if (trim((string)$datos['articulo_modelo']) !== '') {
        if (mb_strlen($datos['articulo_modelo']) > 100) {
            $erroresFormato['articulo_modelo'] = 'El modelo debe tener máximo 100 caracteres';
        }
    }

    // Validación de imagen
    if (isset($_FILES['articulo_imagen']) && $_FILES['articulo_imagen']['error'] === UPLOAD_ERR_OK) {
        $extension = strtolower(pathinfo($_FILES['articulo_imagen']['name'], PATHINFO_EXTENSION));
        $peso = $_FILES['articulo_imagen']['size'];

        if (!in_array($extension, ['jpg', 'jpeg', 'png'])) {
            $erroresFormato['articulo_imagen'] = 'Formato de imagen no permitido (solo JPG, JPEG o PNG)';
        } elseif ($peso > 3 * 1024 * 1024) {
            $erroresFormato['articulo_imagen'] = 'La imagen supera el tamaño máximo de 3MB';
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
        $datos[$clave] = trim((string)$valor);
    }

    // Validación de duplicados
    $erroresDuplicados = [];
    $original = ($modo === 'actualizar' && $id) ? $articulo->leer_por_id($id) : [];

    if ($modo === 'crear' && $articulo->existeCodigo($datos['articulo_codigo'])) {
        $erroresDuplicados['articulo_codigo'] = 'Código ya registrado';
    }

    if ($modo === 'actualizar' &&
        isset($original['articulo_codigo']) &&
        $datos['articulo_codigo'] !== $original['articulo_codigo'] &&
        $articulo->existeCodigo($datos['articulo_codigo'], $id)) {
        $erroresDuplicados['articulo_codigo'] = 'Código ya registrado';
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
            $categoriaId = $_POST['categoria_id'] ?? '';
            $clasificacionId = $_POST['clasificacion_id'] ?? '';

            $registros = $articulo->leer_por_estado($estado, $categoriaId, $clasificacionId);
            echo json_encode(['data' => $registros]);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode([
                'data' => [],
                'error' => true,
                'mensaje' => 'Error al leer artículos',
                'detalle' => $e->getMessage()
            ]);
        }
    break;

    case 'crear':
        try {
            header('Content-Type: application/json');
            $datos = [
                'articulo_codigo'   => $_POST['articulo_codigo'] ?? '',
                'clasificacion_id'  => $_POST['clasificacion_id'] ?? '',
                'articulo_nombre'   => $_POST['articulo_nombre'] ?? '',
                'articulo_descripcion' => $_POST['articulo_descripcion'] ?? '',
                'articulo_estado'   => 1,
                'articulo_imagen'   => ''
            ];

            $clasificacionObj = new clasificacion();
            $clasificacion = $clasificacionObj->leer_por_id($datos['clasificacion_id']);
            $categoriaTipo = $clasificacion ? intval($clasificacion['categoria_tipo']) : 0;
            $datos['categoria_tipo'] = $categoriaTipo;

            if ($categoriaTipo === 1) {
                $datos['articulo_modelo'] = $_POST['articulo_modelo'] ?? '';
                $datos['marca_id']        = !empty($_POST['marca_id']) ? $_POST['marca_id'] : null;
            } else {
                $datos['articulo_modelo'] = '';
                $datos['marca_id']        = null;
            }

            $validacion = validarArticulo($datos, 'crear');
            if (isset($validacion['error'])) {
                echo json_encode($validacion);
                exit;
            }

            if (isset($_FILES['articulo_imagen']) && $_FILES['articulo_imagen']['error'] === UPLOAD_ERR_OK) {
                $nombreLimpio = preg_replace('/[^a-zA-Z0-9_-]/', '_', strtolower($datos['articulo_nombre']));
                $extension = pathinfo($_FILES['articulo_imagen']['name'], PATHINFO_EXTENSION);
                $nombreArchivo = $nombreLimpio . '_' . uniqid() . '.' . $extension;
                $directorio = '../img/articulos/';
                if (!is_dir($directorio)) mkdir($directorio, 0755, true);
                $rutaRelativa = 'img/articulos/' . $nombreArchivo;
                move_uploaded_file($_FILES['articulo_imagen']['tmp_name'], $directorio . $nombreArchivo);
                $datos['articulo_imagen'] = $rutaRelativa;
            } else {
                $datos['articulo_imagen'] = 'img/icons/articulo.png';
            }

            $resultado = $articulo->crear(
                $datos['articulo_codigo'], $datos['articulo_nombre'], $datos['articulo_modelo'],
                $datos['marca_id'], $datos['clasificacion_id'],
                $datos['articulo_descripcion'], $datos['articulo_estado'], $datos['articulo_imagen']
            );

            echo json_encode([
                'exito' => true,
                'mensaje' => 'Artículo registrado correctamente',
                'resultado' => $resultado
            ]);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode([
                'error' => true,
                'mensaje' => 'Error al crear artículo',
                'detalle' => $e->getMessage()
            ]);
        }
    break;

    case 'actualizar':
        try {
            header('Content-Type: application/json');
            $id = $_POST['articulo_id'] ?? '';
            if (!$id) {
                echo json_encode(['error' => true, 'mensaje' => 'ID de artículo no proporcionado']);
                exit;
            }

            $datos = [
                'articulo_codigo'   => $_POST['articulo_codigo'] ?? '',
                'clasificacion_id'  => $_POST['clasificacion_id'] ?? '',
                'articulo_nombre'   => $_POST['articulo_nombre'] ?? '',
                'articulo_descripcion' => $_POST['articulo_descripcion'] ?? '',
                'articulo_estado'   => 1,
                'articulo_imagen'   => ''
            ];

            $clasificacionObj = new clasificacion();
            $clasificacion = $clasificacionObj->leer_por_id($datos['clasificacion_id']);
            $categoriaTipo = $clasificacion ? intval($clasificacion['categoria_tipo']) : 0;

            if ($categoriaTipo === 1) {
                $datos['articulo_modelo'] = $_POST['articulo_modelo'] ?? '';
                $datos['marca_id']        = !empty($_POST['marca_id']) ? $_POST['marca_id'] : null;
            } else {
                $datos['articulo_modelo'] = '';
                $datos['marca_id']        = null;
            }

            $validacion = validarArticulo($datos, 'actualizar', $id);
            if (isset($validacion['error'])) {
                echo json_encode($validacion);
                exit;
            }

            $actual = $articulo->leer_por_id($id);
            if (!$actual) throw new Exception("No se encontró el artículo con ID $id");

            if (isset($_FILES['articulo_imagen']) && $_FILES['articulo_imagen']['error'] === UPLOAD_ERR_OK) {
                if (!empty($actual['articulo_imagen'])) {
                    $rutaAnterior = '../' . $actual['articulo_imagen'];
                    if (strpos($actual['articulo_imagen'], 'img/icons/') !== 0 && file_exists($rutaAnterior)) {
                        unlink($rutaAnterior);
                    }
                }
                $nombreLimpio = preg_replace('/[^a-zA-Z0-9_-]/', '_', strtolower($datos['articulo_nombre']));
                $extension = pathinfo($_FILES['articulo_imagen']['name'], PATHINFO_EXTENSION);
                $nombreArchivo = $nombreLimpio . '_' . uniqid() . '.' . $extension;
                $directorio = '../img/articulos/';
                if (!is_dir($directorio)) mkdir($directorio, 0755, true);
                $rutaRelativa = 'img/articulos/' . $nombreArchivo;
                move_uploaded_file($_FILES['articulo_imagen']['tmp_name'], $directorio . $nombreArchivo);
                $datos['articulo_imagen'] = $rutaRelativa;
            } else {
                $datos['articulo_imagen'] = !empty($actual['articulo_imagen'])
                    ? $actual['articulo_imagen']
                    : 'img/icons/articulo.png';
            }

            $resultado = $articulo->actualizar(
                $datos['articulo_codigo'], $datos['articulo_nombre'], $datos['articulo_modelo'],
                $datos['marca_id'], $datos['clasificacion_id'],
                $datos['articulo_descripcion'], $datos['articulo_estado'], $datos['articulo_imagen'], $id
            );

            echo json_encode([
                'exito' => true,
                'mensaje' => 'Artículo actualizado correctamente',
                'resultado' => $resultado
            ]);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode([
                'error' => true,
                'mensaje' => 'Error al actualizar artículo',
                'detalle' => $e->getMessage()
            ]);
        }
    break;

    case 'obtener_articulo':
        header('Content-Type: application/json');
        $id = $_POST['id'] ?? '';
        if (!$id) {
            echo json_encode(['error' => true, 'mensaje' => 'ID no proporcionado']);
            exit;
        }
        $datos = $articulo->leer_por_id($id);
        echo json_encode($datos ? ['exito' => true, 'articulo' => $datos] : ['error' => true, 'mensaje' => 'Artículo no encontrado']);
    break; 

    case 'deshabilitar_articulo':
        try {
            $id = $_POST['id'] ?? '';
            if (!$id) throw new Exception('ID no proporcionado');
            $exito = $articulo->desincorporar($id);
            echo json_encode([
                'exito' => $exito,
                'mensaje' => $exito ? 'Artículo deshabilitado correctamente' : 'No se pudo deshabilitar el artículo'
            ]);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode([
                'exito' => false,
                'mensaje' => 'Error al deshabilitar artículo',
                'detalle' => $e->getMessage()
            ]);
        }
    break;

        case 'recuperar_articulo':
        try {
            $id = $_POST['id'] ?? '';
            if (!$id) throw new Exception('ID no proporcionado');
            $exito = $articulo->recuperar($id);
            echo json_encode([
                'exito' => $exito,
                'mensaje' => $exito ? 'Artículo recuperado correctamente' : 'Error al recuperar artículo'
            ]);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode([
                'exito' => false,
                'mensaje' => 'Error al recuperar artículo',
                'detalle' => $e->getMessage()
            ]);
        }
    break;

    // acciones de seriales
    case 'listar_seriales':
        try {
            $id = $_POST['id'] ?? '';
            if (!$id) {
                echo json_encode(['data' => [], 'error' => true, 'mensaje' => 'No se proporcionó el identificador del artículo']);
                exit;
            }
            $seriales = $articulo->leer_seriales_articulo($id);
            echo json_encode(['data' => $seriales]);
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
            $repetidos = [];
            foreach ($seriales as $s) {
                if ($articulo->existeSerial($s)) {
                    $repetidos[] = $s;
                }
            }
            echo json_encode(['exito' => true, 'repetidos' => $repetidos]);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode([
                'error'   => true,
                'mensaje' => 'Error al validar los seriales',
                'detalle' => $e->getMessage()
            ]);
        }
        break;

    case 'actualizar_seriales':
        try {
            $id = $_POST['id'] ?? '';
            $seriales = [];
            if (isset($_POST['seriales'])) {
                $decoded = json_decode($_POST['seriales'], true);
                if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                    $seriales = $decoded;
                }
            }

            if (!$id || empty($seriales)) {
                echo json_encode(['error' => true, 'mensaje' => 'Datos insuficientes para actualizar']);
                exit;
            }

            $resultado = $articulo->actualizar_seriales((int)$id, $seriales);
            echo json_encode($resultado);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode([
                'error'   => true,
                'mensaje' => 'Error al actualizar los seriales',
                'detalle' => $e->getMessage()
            ]);
        }
        break;

    case 'stock_articulo':
        try {
            $id = $_POST['id'] ?? '';
            if (!$id) {
                echo json_encode(['error' => true, 'mensaje' => 'No se proporcionó el identificador del artículo']);
                exit;
            }
            $stock = $articulo->obtener_stock_articulo($id);
            echo json_encode(['exito' => true, 'stock' => $stock]);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode([
                'error'   => true,
                'mensaje' => 'Error al obtener el stock del artículo',
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
