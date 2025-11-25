<?php
require_once 'personal.php'; // tu clase persona
$persona = new persona();

function validarPersona($datos, $modo = 'crear', $id = null) {
    $persona = new persona();
    $erroresFormato = [];
    $camposObligatorios = [
        'persona_nombre',
        'persona_apellido',
        'cargo_id',
        'persona_correo',
        'persona_cedula',
        'persona_sexo',
        'persona_nac'
    ];

    // Verificar campos obligatorios
    $camposFaltantes = [];
    foreach ($camposObligatorios as $campo) {
        if (!isset($datos[$campo]) || trim($datos[$campo]) === '') {
            $camposFaltantes[] = $campo;
        }
    }

    // Caso especial: cédula enviada como "V-" o "E-" sin número
    if (isset($datos['persona_cedula']) && ($datos['persona_cedula'] === 'V-' || $datos['persona_cedula'] === 'E-')) {
        $camposFaltantes[] = 'persona_cedula';
    }

    if (!empty($camposFaltantes)) {
        return [
            'error' => true,
            'mensaje' => 'Rellene los campos obligatorios',
            'campos' => $camposFaltantes
        ];
    }

    // Validaciones de formato
    if (!preg_match('/^[A-Za-zÁÉÍÓÚáéíóúÑñ\s]{1,100}$/u', $datos['persona_nombre'])) {
        $erroresFormato['persona_nombre'] = 'El nombre tiene máximo 100 caracteres sin símbolos';
    }

    if (!preg_match('/^[A-Za-zÁÉÍÓÚáéíóúÑñ\s]{1,100}$/u', $datos['persona_apellido'])) {
        $erroresFormato['persona_apellido'] = 'El apellido tiene máximo 100 caracteres sin símbolos';
    }

    if (!filter_var($datos['persona_correo'], FILTER_VALIDATE_EMAIL) || strlen($datos['persona_correo']) > 254) {
        $erroresFormato['persona_correo'] = 'Correo inválido o demasiado largo';
    }

    if (!preg_match('/^[VE]-\d{7,8}$/', $datos['persona_cedula'])) {
        $erroresFormato['persona_cedula'] = 'La cédula debe tener 8 dígitos numéricos';
    }

    if (!empty($datos['persona_direccion']) && strlen($datos['persona_direccion']) > 100) {
        $erroresFormato['persona_direccion'] = 'Dirección demasiado larga';
    }

    if (!empty($datos['persona_telefono']) && !preg_match('/^\d{1,20}$/', $datos['persona_telefono'])) {
        $erroresFormato['persona_telefono'] = 'Teléfono inválido';
    }

    if (!empty($datos['persona_nac'])) {
        $fechaNac = DateTime::createFromFormat('Y-m-d', $datos['persona_nac']);
        $hoy = new DateTime();
        if ($fechaNac) {
            $edad = $hoy->diff($fechaNac)->y;
            if ($edad < 10 || $edad > 100) {
                $erroresFormato['persona_nac'] = 'Edad no permitida';
            }
        } else {
            $erroresFormato['persona_nac'] = 'Formato de fecha inválido';
        }
    }

    // Validación de foto
    if (isset($_FILES['persona_foto']) && $_FILES['persona_foto']['error'] === UPLOAD_ERR_OK) {
        $extension = strtolower(pathinfo($_FILES['persona_foto']['name'], PATHINFO_EXTENSION));
        $peso = $_FILES['persona_foto']['size'];
        if (!in_array($extension, ['jpg', 'jpeg', 'png']) || $peso > 3 * 1024 * 1024) {
            $erroresFormato['persona_foto'] = 'Foto inválida o demasiado pesada';
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
    $original = ($modo === 'actualizar' && $id) ? $persona->leer_por_id($id) : [];

    if ($modo === 'crear' && $persona->existeCorreo($datos['persona_correo'])) {
        $erroresDuplicados['persona_correo'] = 'Correo ya registrado';
    }

    if ($modo === 'crear' && $persona->existeCedula($datos['persona_cedula'])) {
        $erroresDuplicados['persona_cedula'] = 'Cédula ya registrada';
    }

    if ($modo === 'actualizar' && isset($original['persona_correo']) && $datos['persona_correo'] !== $original['persona_correo'] && $persona->existeCorreo($datos['persona_correo'], $id)) {
        $erroresDuplicados['persona_correo'] = 'Correo ya registrado';
    }

    if ($modo === 'actualizar' && isset($original['persona_cedula']) && $datos['persona_cedula'] !== $original['persona_cedula'] && $persona->existeCedula($datos['persona_cedula'], $id)) {
        $erroresDuplicados['persona_cedula'] = 'Cédula ya registrada';
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
            $estado   = isset($_POST['estado']) ? intval($_POST['estado']) : 1;
            $cargoId  = isset($_POST['cargo_id']) && $_POST['cargo_id'] !== '' ? intval($_POST['cargo_id']) : null;

            $registros = $persona->leer_por_estado($estado, $cargoId);
            echo json_encode(['data' => $registros]);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode([
                'data' => [],
                'error' => true,
                'mensaje' => 'Error al leer personal',
                'detalle' => $e->getMessage()
            ]);
        }
    break;


    case 'crear':
        try {
            header('Content-Type: application/json');

            $datos = [
                'persona_nombre'    => $_POST['persona_nombre'] ?? '',
                'persona_apellido'  => $_POST['persona_apellido'] ?? '',
                'cargo_id'          => $_POST['cargo_id'] ?? '',
                'persona_correo'    => $_POST['persona_correo'] ?? '',
                'persona_telefono'  => $_POST['persona_telefono'] ?? '',
                'persona_cedula'    => $_POST['persona_cedula'] ?? '',
                'persona_sexo'      => $_POST['persona_sexo'] ?? '',
                'persona_nac'       => $_POST['persona_nac'] ?? '',
                'persona_direccion' => $_POST['persona_direccion'] ?? ''
            ];

            // Validar datos
            $validacion = validarPersona($datos, 'crear');
            if (isset($validacion['error'])) {
                echo json_encode($validacion);
                exit;
            }

            // Procesar imagen
            if (isset($_FILES['persona_foto']) && $_FILES['persona_foto']['error'] === UPLOAD_ERR_OK) {
                $nombreCompleto = $datos['persona_nombre'] . '_' . $datos['persona_apellido'];
                $nombreLimpio = preg_replace('/[^a-zA-Z0-9_-]/', '_', strtolower($nombreCompleto));
                $extension = pathinfo($_FILES['persona_foto']['name'], PATHINFO_EXTENSION);
                $nombreArchivo = $nombreLimpio . '_' . uniqid() . '.' . $extension;

                $directorio = '../img/personal/';
                if (!is_dir($directorio)) {
                    mkdir($directorio, 0755, true);
                }

                $rutaRelativa = 'img/personal/' . $nombreArchivo;
                $rutaCompleta = $directorio . $nombreArchivo;

                move_uploaded_file($_FILES['persona_foto']['tmp_name'], $rutaCompleta);
                $datos['persona_foto'] = $rutaRelativa;
            } else {
                $datos['persona_foto'] = 'img/icons/personal.png';
            }

            $estado = 1;

            $resultado = $persona->crear(
                $datos['persona_nombre'],
                $datos['persona_apellido'],
                $datos['cargo_id'],
                $datos['persona_correo'],
                $datos['persona_telefono'],
                $datos['persona_cedula'],
                $datos['persona_sexo'],
                $datos['persona_nac'],
                $datos['persona_direccion'],
                $datos['persona_foto'],
                $estado
            );

            echo json_encode([
                'exito' => true,
                'mensaje' => 'Personal guardado correctamente',
                'resultado' => $resultado
            ]);
        } catch (Exception $e) {
            echo json_encode([
                'error' => true,
                'mensaje' => 'Error al crear personal',
                'detalle' => $e->getMessage()
            ]);
        }
    break;

    case 'obtener_persona':
        header('Content-Type: application/json');

        $id = $_POST['persona_id'] ?? '';
        if (!$id) {
            echo json_encode(['error' => true, 'mensaje' => 'ID no proporcionado']);
            exit;
        }

        $datos = $persona->leer_por_id($id);
        if ($datos) {
            echo json_encode(['exito' => true, 'persona' => $datos]);
        } else {
            echo json_encode(['error' => true, 'mensaje' => 'Personal no encontrado']);
        }
    break;

    case 'actualizar':
        try {
            header('Content-Type: application/json');

            $id = $_POST['persona_id'] ?? '';
            if (!$id) {
                echo json_encode(['error' => true, 'mensaje' => 'ID de personal no proporcionado']);
                exit;
            }

            $datos = [
                'persona_nombre'    => $_POST['persona_nombre'] ?? '',
                'persona_apellido'  => $_POST['persona_apellido'] ?? '',
                'cargo_id'          => $_POST['cargo_id'] ?? '',
                'persona_correo'    => $_POST['persona_correo'] ?? '',
                'persona_telefono'  => $_POST['persona_telefono'] ?? '',
                'persona_cedula'    => $_POST['persona_cedula'] ?? '',
                'persona_sexo'      => $_POST['persona_sexo'] ?? '',
                'persona_nac'       => $_POST['persona_nac'] ?? '',
                'persona_direccion' => $_POST['persona_direccion'] ?? ''
            ];

            $validacion = validarPersona($datos, 'actualizar', $id);
            if (isset($validacion['error'])) {
                echo json_encode($validacion);
                exit;
            }

            $actual = $persona->leer_por_id($id);
            if (!$actual) {
                throw new Exception("No se encontró el personal con ID $id");
            }

            if (isset($_FILES['persona_foto']) && $_FILES['persona_foto']['error'] === UPLOAD_ERR_OK) {
                if (!empty($actual['persona_foto']) && str_starts_with($actual['persona_foto'], 'img/personal/')) {
                    $rutaAnterior = '../' . $actual['persona_foto'];
                    if (file_exists($rutaAnterior)) {
                        unlink($rutaAnterior);
                    }
                }

                $nombreCompleto = $datos['persona_nombre'] . '_' . $datos['persona_apellido'];
                $nombreLimpio = preg_replace('/[^a-zA-Z0-9_-]/', '_', strtolower($nombreCompleto));
                $extension = pathinfo($_FILES['persona_foto']['name'], PATHINFO_EXTENSION);
                $nombreArchivo = $nombreLimpio . '_' . uniqid() . '.' . $extension;

                $directorio = '../img/personal/';
                if (!is_dir($directorio)) {
                    mkdir($directorio, 0755, true);
                }

                $rutaRelativa = 'img/personal/' . $nombreArchivo;
                $rutaCompleta = $directorio . $nombreArchivo;

                move_uploaded_file($_FILES['persona_foto']['tmp_name'], $rutaCompleta);
                $datos['persona_foto'] = $rutaRelativa;
            } else {
                $datos['persona_foto'] = $actual['persona_foto'];
            }

            $estado = 1;

            $resultado = $persona->actualizar(
                $datos['persona_nombre'],
                $datos['persona_apellido'],
                $datos['cargo_id'],
                $datos['persona_correo'],
                $datos['persona_telefono'],
                $datos['persona_cedula'],
                $datos['persona_sexo'],
                $datos['persona_nac'],
                $datos['persona_direccion'],
                $datos['persona_foto'],
                $estado,
                $id
            );

            if (!$resultado) {
                throw new Exception("La actualización falló. Verifica los datos enviados.");
            }

            echo json_encode([
                'exito' => true,
                'mensaje' => 'Personal actualizado correctamente',
                'resultado' => $resultado
            ]);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode([
                'error' => true,
                'mensaje' => 'Error al actualizar personal',
                'detalle' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }
    break;
    
    case 'deshabilitar_persona':
        header('Content-Type: application/json');

        try {
            $id = $_POST['persona_id'] ?? '';
            if (!$id) {
                throw new Exception('ID no proporcionado');
            }

            $exito = $persona->desincorporar($id);

            echo json_encode([
                'exito' => $exito,
                'mensaje' => $exito ? 'Personal deshabilitado correctamente' : 'No se pudo deshabilitar el personal'
            ]);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode([
                'exito' => false,
                'mensaje' => 'Error al deshabilitar personal',
                'detalle' => $e->getMessage()
            ]);
        }
    break;

    case 'recuperar_persona':
        try {
            $exito = $persona->recuperar($_POST['persona_id']);
            echo json_encode([
                'exito' => $exito,
                'mensaje' => $exito ? 'Personal recuperado correctamente' : 'Error al recuperar'
            ]);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode([
                'exito' => false,
                'mensaje' => 'Error al recuperar personal',
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
