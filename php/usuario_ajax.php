<?php

require_once 'usuario.php';
$usuario = new usuario();

// Verifica que se haya enviado una acción
$accion = isset($_POST['accion']) ? $_POST['accion'] : '';

switch ($accion) {
    case 'leer_todos':
        try {
            $registros = $usuario->leer_todos();
            echo json_encode($registros);
        } catch (Exception $e) {
            echo json_encode([
                'error' => true,
                'mensaje' => 'Error al leer usuarios',
                'detalle' => $e->getMessage()
            ]);
        }
        break;
    
    case 'crear':
        try {
            // Encabezado para asegurar respuesta JSON
            header('Content-Type: application/json');

            // Recoger los datos del formulario
            $datos = [
                'usuario_nombre'    => isset($_POST['usuario_nombre']) ? $_POST['usuario_nombre'] : '',
                'usuario_apellido'  => isset($_POST['usuario_apellido']) ? $_POST['usuario_apellido'] : '',
                'usuario_correo'    => isset($_POST['usuario_correo']) ? $_POST['usuario_correo'] : '',
                'usuario_telefono'  => isset($_POST['usuario_telefono']) ? $_POST['usuario_telefono'] : '',
                'usuario_cedula'    => (isset($_POST['usuario_cedula']) && $_POST['usuario_cedula'] !== 'V-' && $_POST['usuario_cedula'] !== 'E-') ? $_POST['usuario_cedula'] : '',
                'usuario_nac'       => isset($_POST['usuario_nac']) ? $_POST['usuario_nac'] : '',
                'usuario_direccion' => isset($_POST['usuario_direccion']) ? $_POST['usuario_direccion'] : '',
                'usuario_sexo'      => isset($_POST['usuario_sexo']) && $_POST['usuario_sexo'] !== '' ? $_POST['usuario_sexo'] : '',
                'usuario_usuario'   => isset($_POST['usuario_usuario']) ? $_POST['usuario_usuario'] : '',
                'usuario_clave'     => isset($_POST['usuario_clave']) ? $_POST['usuario_clave'] : '',
            ];


            // Validar campos obligatorios
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
                echo json_encode([
                    'error' => true,
                    'mensaje' => 'Rellene los campos obligatorios',
                    'campos' => $camposFaltantes
                ]);
                exit;
            }
            
            //Validaciones
            $erroresFormato = [];

            // Nombre: letras y espacios, máximo 30 caracteres
            if (!preg_match('/^[A-Za-zÁÉÍÓÚáéíóúÑñ\s]{1,30}$/u', $datos['usuario_nombre'])) {
                $erroresFormato['usuario_nombre'] = 'El nombre tiene máximo 30 caracteres sin números o símbolos especiales';
            }

            // Apellido: igual que nombre
            if (!preg_match('/^[A-Za-zÁÉÍÓÚáéíóúÑñ\s]{1,30}$/u', $datos['usuario_apellido'])) {
                $erroresFormato['usuario_apellido'] = 'El apellido tiene máximo 30 caracteres sin números ni símbolos especiales';
            }

            // Correo: formato válido y máximo 100 caracteres
            if (!filter_var($datos['usuario_correo'], FILTER_VALIDATE_EMAIL) || strlen($datos['usuario_correo']) > 100) {
                $erroresFormato['usuario_correo'] = 'El correo debe tener formato válido';
            }

            // Cédula: exactamente 8 dígitos numéricos
            if (!preg_match('/^[VE]-\d{8}$/', $datos['usuario_cedula'])) {
                $erroresFormato['usuario_cedula'] = 'La cédula debe tener 8 dígitos';
            }

            // Dirección: máximo 100 caracteres
            if (!empty($datos['usuario_direccion']) && strlen($datos['usuario_direccion']) > 100) {
                $erroresFormato['usuario_direccion'] = 'La dirección debe tener máximo 100 caracteres';
            }

            // Teléfono: solo números, máximo 20 dígitos
            if (!empty($datos['usuario_telefono']) && !preg_match('/^\d{1,20}$/', $datos['usuario_telefono'])) {
                $erroresFormato['usuario_telefono'] = 'El teléfono debe tener solo números';
            }

            // Fecha de nacimiento: entre 15 y 100 años desde hoy
            if (!empty($datos['usuario_nac'])) {
                $fechaNac = DateTime::createFromFormat('Y-m-d', $datos['usuario_nac']);
                $hoy = new DateTime();
                if ($fechaNac) {
                    $edad = $hoy->diff($fechaNac)->y;
                    if ($edad < 15 || $edad > 100) {
                        $erroresFormato['usuario_nac'] = 'La fecha de nacimiento no tiene una fecha válida';
                    }
                } else {
                    $erroresFormato['usuario_nac'] = 'La fecha de nacimiento no tiene un formato válido';
                }
            }

            // Usuario: entre 8 y 30 caracteres alfanuméricos, guiones y guiones bajos
            if (!preg_match('/^.{8,30}$/', $datos['usuario_usuario'])) {
                $erroresFormato['usuario_usuario'] = 'El nombre de usuario debe tener entre 8 y 30 caracteres';
            }

            // Contraseña: igual que usuario
            if (!preg_match('/^.{8,30}$/', $datos['usuario_clave'])) {
                $erroresFormato['usuario_clave'] = 'La contraseña debe tener entre 8 y 30 caracteres';
            }

            // Repetición de contraseña (solo si se envía desde el frontend)
            if (isset($_POST['repetir_clave']) && $_POST['repetir_clave'] !== $datos['usuario_clave']) {
                $erroresFormato['repetir_clave'] = 'Las contraseñas no coinciden';
            }

            // Foto de perfil: .jpg o .png, máximo 3MB
            if (isset($_FILES['usuario_foto']) && $_FILES['usuario_foto']['error'] === UPLOAD_ERR_OK) {
                $extension = strtolower(pathinfo($_FILES['usuario_foto']['name'], PATHINFO_EXTENSION));
                $peso = $_FILES['usuario_foto']['size'];
                if (!in_array($extension, ['jpg', 'jpeg', 'png']) || $peso > 3 * 1024 * 1024) {
                    $erroresFormato['usuario_foto'] = 'La foto debe ser .jpg o .png y pesar máximo 3MB';
                }
            }

            // Si hay errores de formato, detener ejecución
            if (!empty($erroresFormato)) {
                $primerCampo = array_key_first($erroresFormato);
                echo json_encode([
                    'error' => true,
                    'mensaje' => $erroresFormato[$primerCampo],
                    'errores' => [$primerCampo => $erroresFormato[$primerCampo]],
                    'campos' => [$primerCampo]
                ]);
                exit;
            }
            
            // Validar duplicados únicos con mensajes específicos
            $erroresDuplicados = [];

            if ($usuario->existeCorreo($datos['usuario_correo'])) {
                $erroresDuplicados['usuario_correo'] = 'Ya existe un usuario con ese correo';
            }

            if ($usuario->existeCedula($datos['usuario_cedula'])) {
                $erroresDuplicados['usuario_cedula'] = 'Ya existe un usuario con esa cédula';
            }

            if ($usuario->existeUsuario($datos['usuario_usuario'])) {
                $erroresDuplicados['usuario_usuario'] = 'Ya existe un usuario con ese nombre de usuario';
            }

            if (!empty($erroresDuplicados)) {
                echo json_encode([
                    'error' => true,
                    'mensaje' => 'Error',
                    'errores' => $erroresDuplicados,
                    'campos' => array_keys($erroresDuplicados)
                ]);
                exit;
            }

            // Cifrar la contraseña
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

            $rol = $_POST['rol_id'] ?? 1;
            $estado = 1;

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
            header('Content-Type: application/json');
            echo json_encode([
                'error' => true,
                'mensaje' => 'Error al crear usuario',
                'detalle' => $e->getMessage()
            ]);
        }
    break;


    // Puedes agregar más casos aquí para otras acciones AJAX
    // case 'actualizar':
    // case 'eliminar':
    // etc.

    default:
        echo json_encode([
            'error' => true,
            'mensaje' => 'Acción no válida'
        ]);
        break;
}
