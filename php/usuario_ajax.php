<?php

require_once 'usuario.php';
$usuario = new usuario();

function validarUsuario($datos, $modo = 'crear', $id = null) {
    $usuario = new usuario();
    $erroresFormato = [];
    $camposObligatorios = [
        'usuario_nombre',
        'usuario_apellido',
        'usuario_correo',
        'usuario_cedula',
        'usuario_sexo',
        'usuario_nac',
        'usuario_usuario',
        'usuario_clave'
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

    // Validaciones de formato
    if (!preg_match('/^[A-Za-zÁÉÍÓÚáéíóúÑñ\s]{1,30}$/u', $datos['usuario_nombre'])) {
        $erroresFormato['usuario_nombre'] = 'El nombre tiene máximo 30 caracteres sin símbolos';
    }

    if (!preg_match('/^[A-Za-zÁÉÍÓÚáéíóúÑñ\s]{1,30}$/u', $datos['usuario_apellido'])) {
        $erroresFormato['usuario_apellido'] = 'El apellido tiene máximo 30 caracteres sin símbolos';
    }

    if (!filter_var($datos['usuario_correo'], FILTER_VALIDATE_EMAIL) || strlen($datos['usuario_correo']) > 100) {
        $erroresFormato['usuario_correo'] = 'Correo inválido o demasiado largo';
    }

    if (!preg_match('/^[VE]-\d{8}$/', $datos['usuario_cedula'])) {
        $erroresFormato['usuario_cedula'] = 'La cédula debe tener 8 dígitos';
    }

    if (!empty($datos['usuario_direccion']) && strlen($datos['usuario_direccion']) > 100) {
        $erroresFormato['usuario_direccion'] = 'Dirección demasiado larga';
    }

    if (!empty($datos['usuario_telefono']) && !preg_match('/^\d{1,20}$/', $datos['usuario_telefono'])) {
        $erroresFormato['usuario_telefono'] = 'Teléfono inválido';
    }

    if (!empty($datos['usuario_nac'])) {
        $fechaNac = DateTime::createFromFormat('Y-m-d', $datos['usuario_nac']);
        $hoy = new DateTime();
        if ($fechaNac) {
            $edad = $hoy->diff($fechaNac)->y;
            if ($edad < 10 || $edad > 100) {
                $erroresFormato['usuario_nac'] = 'Edad no permitida';
            }
        } else {
            $erroresFormato['usuario_nac'] = 'Formato de fecha inválido';
        }
    }

    if (!preg_match('/^.{8,30}$/', $datos['usuario_usuario'])) {
        $erroresFormato['usuario_usuario'] = 'Usuario debe tener entre 8 y 30 caracteres';
    }

    if (!preg_match('/^.{8,30}$/', $datos['usuario_clave'])) {
        $erroresFormato['usuario_clave'] = 'Contraseña debe tener entre 8 y 30 caracteres';
    }

    if (isset($_POST['repetir_clave']) && $_POST['repetir_clave'] !== $datos['usuario_clave']) {
        $erroresFormato['repetir_clave'] = 'Las contraseñas no coinciden';
    }

    if (isset($_FILES['usuario_foto']) && $_FILES['usuario_foto']['error'] === UPLOAD_ERR_OK) {
        $extension = strtolower(pathinfo($_FILES['usuario_foto']['name'], PATHINFO_EXTENSION));
        $peso = $_FILES['usuario_foto']['size'];
        if (!in_array($extension, ['jpg', 'jpeg', 'png']) || $peso > 3 * 1024 * 1024) {
            $erroresFormato['usuario_foto'] = 'Foto inválida o demasiado pesada';
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

    $original = ($modo === 'actualizar' && $id) ? $usuario->leer_por_id($id) : [];

    if ($modo === 'crear' && $usuario->existeCorreo($datos['usuario_correo'])) {
        $erroresDuplicados['usuario_correo'] = 'Correo ya registrado';
    }

    if ($modo === 'crear' && $usuario->existeCedula($datos['usuario_cedula'])) {
        $erroresDuplicados['usuario_cedula'] = 'Cédula ya registrada';
    }

    if ($modo === 'crear' && $usuario->existeUsuario($datos['usuario_usuario'])) {
        $erroresDuplicados['usuario_usuario'] = 'Usuario ya registrado';
    }

    if ($modo === 'actualizar' && isset($original['usuario_correo']) && $datos['usuario_correo'] !== $original['usuario_correo'] && $usuario->existeCorreo($datos['usuario_correo'], $id)) {
    $erroresDuplicados['usuario_correo'] = 'Correo ya registrado';
    }

    if ($modo === 'actualizar' && isset($original['usuario_cedula']) && $datos['usuario_cedula'] !== $original['usuario_cedula'] && $usuario->existeCedula($datos['usuario_cedula'], $id)) {
        $erroresDuplicados['usuario_cedula'] = 'Cédula ya registrada';
    }

    if ($modo === 'actualizar' && isset($original['usuario_usuario']) && $datos['usuario_usuario'] !== $original['usuario_usuario'] && $usuario->existeUsuario($datos['usuario_usuario'], $id)) {
        $erroresDuplicados['usuario_usuario'] = 'Usuario ya registrado';
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
            $registros = $usuario->leer_por_estado($estado);
            echo json_encode(['data' => $registros]);
        } catch (Exception $e) {
            http_response_code(500); // opcional: marca error HTTP
            echo json_encode([
                'data' => [],
                'error' => true,
                'mensaje' => 'Error al leer usuarios',
                'detalle' => $e->getMessage()
            ]);
        }
    break;

    
    case 'crear':
        try {
            header('Content-Type: application/json');

            // Recoger datos del formulario
            $datos = [
                'usuario_nombre'    => $_POST['usuario_nombre'] ?? '',
                'usuario_apellido'  => $_POST['usuario_apellido'] ?? '',
                'usuario_correo'    => $_POST['usuario_correo'] ?? '',
                'usuario_telefono'  => $_POST['usuario_telefono'] ?? '',
                'usuario_cedula'    => ($_POST['usuario_cedula'] ?? '') !== 'V-' && ($_POST['usuario_cedula'] ?? '') !== 'E-' ? $_POST['usuario_cedula'] : '',
                'usuario_nac'       => $_POST['usuario_nac'] ?? '',
                'usuario_direccion' => $_POST['usuario_direccion'] ?? '',
                'usuario_sexo'      => $_POST['usuario_sexo'] ?? '',
                'usuario_usuario'   => $_POST['usuario_usuario'] ?? '',
                'usuario_clave'     => $_POST['usuario_clave'] ?? '',
                'rol_id'            => $_POST['rol_id'] ?? ''
            ];

            // Validar datos
            $validacion = validarUsuario($datos, 'crear');
            if (isset($validacion['error'])) {
                echo json_encode($validacion);
                exit;
            }

            // Cifrar contraseña
            $datos['usuario_clave'] = password_hash($datos['usuario_clave'], PASSWORD_DEFAULT);

            // Procesar imagen
            if (isset($_FILES['usuario_foto']) && $_FILES['usuario_foto']['error'] === UPLOAD_ERR_OK) {
                $nombreCompleto = $datos['usuario_nombre'] . '_' . $datos['usuario_apellido'];
                $nombreLimpio = preg_replace('/[^a-zA-Z0-9_-]/', '_', strtolower($nombreCompleto));
                $extension = pathinfo($_FILES['usuario_foto']['name'], PATHINFO_EXTENSION);
                $nombreArchivo = $nombreLimpio . '_' . uniqid() . '.' . $extension;

                $directorio = '../img/users/';
                if (!is_dir($directorio)) {
                    mkdir($directorio, 0755, true);
                }

                $rutaRelativa = 'img/users/' . $nombreArchivo;
                $rutaCompleta = $directorio . $nombreArchivo;

                move_uploaded_file($_FILES['usuario_foto']['tmp_name'], $rutaCompleta);
                $datos['usuario_foto'] = $rutaRelativa;
            } else {
                $datos['usuario_foto'] = 'img/icons/perfil.png';
            }

            // Rol y estado por defecto
            $rol = $_POST['rol_id'] ?? 1;
            $estado = 1;

            // Crear usuario
            $resultado = $usuario->crear(
                $datos['usuario_nombre'],
                $datos['usuario_apellido'],
                $datos['usuario_correo'],
                $datos['usuario_telefono'],
                $datos['usuario_cedula'],
                $datos['usuario_sexo'],
                $datos['usuario_nac'],
                $datos['usuario_direccion'],
                $datos['usuario_clave'],
                $datos['usuario_usuario'],
                $rol,
                $datos['usuario_foto'],
                $estado
            );

            echo json_encode([
                'exito' => true,
                'mensaje' => 'Usuario guardado correctamente',
                'resultado' => $resultado
            ]);
        } catch (Exception $e) {
            echo json_encode([
                'error' => true,
                'mensaje' => 'Error al crear usuario',
                'detalle' => $e->getMessage()
            ]);
        }
    break;
    
    case 'obtener_usuario':
        header('Content-Type: application/json');

        $id = $_POST['id'] ?? '';
        if (!$id) {
            echo json_encode(['error' => true, 'mensaje' => 'ID no proporcionado']);
            exit;
        }

        $datos = $usuario->leer_por_id($id);
        if ($datos) {
            echo json_encode(['exito' => true, 'usuario' => $datos]);
        } else {
            echo json_encode(['error' => true, 'mensaje' => 'Usuario no encontrado']);
        }
    break;


    case 'actualizar':
        try {
            header('Content-Type: application/json');

            $id = $_POST['usuario_id'] ?? '';
            if (!$id) {
                echo json_encode(['error' => true, 'mensaje' => 'ID de usuario no proporcionado']);
                exit;
            }

            // Recoger datos del formulario
            $datos = [
                'usuario_nombre'    => $_POST['usuario_nombre'] ?? '',
                'usuario_apellido'  => $_POST['usuario_apellido'] ?? '',
                'usuario_correo'    => $_POST['usuario_correo'] ?? '',
                'usuario_telefono'  => $_POST['usuario_telefono'] ?? '',
                'usuario_cedula'    => ($_POST['usuario_cedula'] ?? '') !== 'V-' && ($_POST['usuario_cedula'] ?? '') !== 'E-' ? $_POST['usuario_cedula'] : '',
                'usuario_nac'       => $_POST['usuario_nac'] ?? '',
                'usuario_direccion' => $_POST['usuario_direccion'] ?? '',
                'usuario_sexo'      => $_POST['usuario_sexo'] ?? '',
                'usuario_usuario'   => $_POST['usuario_usuario'] ?? '',
                'usuario_clave'     => $_POST['usuario_clave'] ?? ''
            ];

            // Validar datos
            $validacion = validarUsuario($datos, 'actualizar', $id);
            if (isset($validacion['error'])) {
                echo json_encode($validacion);
                exit;
            }

            $actual = $usuario->leer_por_id($id);
            if (!$actual) {
                throw new Exception("No se encontró el usuario con ID $id");
            }

            // Cifrar contraseña si se envía, si no mantener la actual
            if (!empty($datos['usuario_clave'])) {
                $datos['usuario_clave'] = password_hash($datos['usuario_clave'], PASSWORD_DEFAULT);
            } else {
                $datos['usuario_clave'] = !empty($datos['usuario_clave']) 
                ? password_hash($datos['usuario_clave'], PASSWORD_DEFAULT) 
                : $actual['usuario_clave'];
            }

            if (isset($_FILES['usuario_foto']) && $_FILES['usuario_foto']['error'] === UPLOAD_ERR_OK) {
                // Eliminar foto anterior solo si está en la carpeta img/users
                if (!empty($actual['usuario_foto']) && str_starts_with($actual['usuario_foto'], 'img/users/')) {
                    $rutaAnterior = '../' . $actual['usuario_foto'];
                    if (file_exists($rutaAnterior)) {
                        unlink($rutaAnterior);
                    }
                }

                $nombreCompleto = $datos['usuario_nombre'] . '_' . $datos['usuario_apellido'];
                $nombreLimpio = preg_replace('/[^a-zA-Z0-9_-]/', '_', strtolower($nombreCompleto));
                $extension = pathinfo($_FILES['usuario_foto']['name'], PATHINFO_EXTENSION);
                $nombreArchivo = $nombreLimpio . '_' . uniqid() . '.' . $extension;

                $directorio = '../img/users/';
                if (!is_dir($directorio)) {
                    mkdir($directorio, 0755, true);
                }

                $rutaRelativa = 'img/users/' . $nombreArchivo;
                $rutaCompleta = $directorio . $nombreArchivo;

                move_uploaded_file($_FILES['usuario_foto']['tmp_name'], $rutaCompleta);
                $datos['usuario_foto'] = $rutaRelativa;
            } else {
                $datos['usuario_foto'] = $actual['usuario_foto'];
            }



            $rol = isset($_POST['rol_id']) ? $_POST['rol_id'] : $actual['rol_id'];
            $estado = 1;

            // Actualizar usuario
            $resultado = $usuario->actualizar(
                $datos['usuario_nombre'],
                $datos['usuario_apellido'],
                $datos['usuario_correo'],
                $datos['usuario_telefono'],
                $datos['usuario_cedula'],
                $datos['usuario_nac'],
                $datos['usuario_direccion'],
                $datos['usuario_sexo'],
                $datos['usuario_clave'],
                $datos['usuario_usuario'],
                $rol,
                $datos['usuario_foto'],
                $estado,
                $id
            );

            if (!$resultado) {
                throw new Exception("La actualización falló. Verifica los datos enviados.");
            }


            echo json_encode([
                'exito' => true,
                'mensaje' => 'Usuario actualizado correctamente',
                'resultado' => $resultado
            ]);
        } catch (Exception $e) {
            http_response_code(500); // Marca el error como interno
            echo json_encode([
                'error' => true,
                'mensaje' => 'Error al actualizar usuario',
                'detalle' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }

    break;


    case 'deshabilitar_usuario':
        header('Content-Type: application/json');

        try {
            $id = $_POST['id'] ?? '';
            if (!$id) {
                throw new Exception('ID no proporcionado');
            }

            $exito = $usuario->desincorporar($id);

            echo json_encode([
                'exito' => $exito,
                'mensaje' => $exito ? 'Usuario deshabilitado correctamente' : 'No se pudo deshabilitar el usuario'
            ]);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode([
                'exito' => false,
                'mensaje' => 'Error al deshabilitar usuario',
                'detalle' => $e->getMessage()
            ]);
        }
    break;


    case 'recuperar_usuario':
        try {
            $exito = $usuario->recuperar($_POST['id']);
            echo json_encode([
                'exito' => $exito,
                'mensaje' => $exito ? 'Usuario recuperado correctamente' : 'Error al recuperar'
            ]);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode([
                'exito' => false,
                'mensaje' => 'Error al recuperar usuario',
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