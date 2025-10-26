<?php
header('Content-Type: application/json');

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
        // Recoger los datos del formulario
        $datos = [
            'usuario_nombre'   => isset($_POST['usuario_nombre']) ? $_POST['usuario_nombre'] : '',
            'usuario_apellido' => isset($_POST['usuario_apellido']) ? $_POST['usuario_apellido'] : '',
            'usuario_correo'   => isset($_POST['usuario_correo']) ? $_POST['usuario_correo'] : '',
            'usuario_telefono' => isset($_POST['usuario_telefono']) ? $_POST['usuario_telefono'] : '',
            'usuario_cedula'   => isset($_POST['usuario_cedula']) ? $_POST['usuario_cedula'] : '',
            'usuario_nac'   => isset($_POST['usuario_nac']) ? $_POST['usuario_nac'] : '',
            'usuario_direccion'   => isset($_POST['usuario_direccion']) ? $_POST['usuario_direccion'] : '',
            'usuario_sexo' => (isset($_POST['usuario_sexo']) && $_POST['usuario_sexo'] !== '') ? $_POST['usuario_sexo'] : '',
            'usuario_usuario'  => isset($_POST['usuario_usuario']) ? $_POST['usuario_usuario'] : '',
            'usuario_clave'    => isset($_POST['usuario_clave']) ? $_POST['usuario_clave'] : '',
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
            if (!isset($datos[$campo]) || trim($datos[$campo]) === '') {
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
        //Cifrar la contraseña antes de guardar
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

        $rol = isset($_POST['rol_id']) ? $_POST['rol_id'] : 1;
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
