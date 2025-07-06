<?php

if (!empty($_POST["persona_user"])) {

    $correo = $_SESSION['correo'];
    $rol = 3;

    //Validar campos obligatorios
    if (!empty($_POST["usuario_usuario"]) && !empty($_POST["usuario_clave_1"]) && !empty($_POST["usuario_clave_2"])) {
        $usuario = $_POST["usuario_usuario"];
        $clave_1 = $_POST["usuario_clave_1"];
        $clave_2 = $_POST["usuario_clave_2"];

        //Verificando usuario
        $stmt = $pdo->prepare("SELECT usuario_usuario FROM usuario WHERE usuario_usuario = :usuario");
        $stmt->execute([':usuario' => $usuario]);
        if ($stmt->fetch(PDO::FETCH_OBJ)) {
            echo "<h2>El nombre de usuario ya se encuentra registrado</h2>";
            exit();
        }

        // Verificando claves
        if ($clave_1 != $clave_2) {
            echo "<h2>Las contraseñas ingresadas no son iguales</h2>";
            exit();
        } else {
            $clave = password_hash($clave_1, PASSWORD_BCRYPT, ["cost" => 10]);
        }

        // Traer datos del persona
        $stmt = $pdo->prepare("SELECT * FROM persona WHERE persona_email = :correo");
        $stmt->execute([':correo' => $correo]);
        if ($fila = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $id = $fila['persona_id'];
            $nombre = $fila['persona_nombre'];
            $apellido = $fila['persona_apellido'];
            $email = $fila['persona_email'];
            $telefono = $fila['persona_telefono'];
        } else {
            echo "<h2>Hubo un error al encontrar los datos, intente verificar más tarde</h2>";
            exit();
        }

        // Guardar Usuario
        $sql = "INSERT INTO usuario (usuario_nombre, usuario_apellido, usuario_email, usuario_clave, usuario_usuario, rol_id, usuario_telefono) VALUES (?, ?, ?, ?, ?, ?, ?)";
        $guardar_usuario = $pdo->prepare($sql);
        $exito = $guardar_usuario->execute([$nombre, $apellido, $email, $clave, $usuario, $rol, $telefono]);

        //Verificando si se realizó el registro
        if ($exito && $guardar_usuario->rowCount() == 1) {
            echo '<h2>Usuario registrado</h2>';
        } else {
            echo '<h2>Error al intentar registrar el Usuario, intente nuevamente</h2>';
        }
    } else {
        echo "<h2>Rellene todos los campos obligatorios</h2>";
    }
}

if (!empty($_POST["user_new"])) {
    $rol = 3;

    if (!empty($_POST["usuario_nombre"]) && !empty($_POST["usuario_apellido"]) && !empty($_POST["usuario_email"]) && !empty($_POST["usuario_usuario"]) && !empty($_POST["usuario_clave_1"]) && !empty($_POST["usuario_clave_2"])) {
        $nombre = isset($_POST["usuario_nombre"]) ? $_POST["usuario_nombre"] : '';
        $apellido = isset($_POST["usuario_apellido"]) ? $_POST["usuario_apellido"] : '';
        $correo = isset($_POST["usuario_email"]) ? $_POST["usuario_email"] : '';
        $telefono = isset($_POST["usuario_telefono"]) ? $_POST["usuario_telefono"] : '';
        $usuario = isset($_POST["usuario_usuario"]) ? $_POST["usuario_usuario"] : '';
        $clave_1 = isset($_POST["usuario_clave_1"]) ? $_POST["usuario_clave_1"] : '';
        $clave_2 = isset($_POST["usuario_clave_2"]) ? $_POST["usuario_clave_2"] : '';

        //Verificando usuario
        $stmt = $pdo->prepare("SELECT usuario_usuario FROM usuario WHERE usuario_usuario = :usuario");
        $stmt->execute([':usuario' => $usuario]);
        if ($stmt->fetch(PDO::FETCH_OBJ)) {
            echo "<h2>El nombre de usuario ya se encuentra registrado</h2>";
            exit();
        }

        // Verificando claves
        if ($clave_1 != $clave_2) {
            echo "<h2>Las contraseñas ingresadas no son iguales</h2>";
            exit();
        } else {
            $clave = password_hash($clave_1, PASSWORD_BCRYPT, ["cost" => 10]);
        }

        // Guardar Usuario
        $sql = "INSERT INTO usuario (usuario_nombre, usuario_apellido, usuario_email, usuario_clave, usuario_usuario, rol_id, usuario_telefono) VALUES (?, ?, ?, ?, ?, ?, ?)";
        $guardar_usuario = $pdo->prepare($sql);
        $exito_usuario = $guardar_usuario->execute([$nombre, $apellido, $correo, $clave, $usuario, $rol, $telefono]);

        // Guardar persona
        $sql = "INSERT INTO persona (persona_nombre, persona_apellido, persona_email, persona_telefono) VALUES (?, ?, ?, ?)";
        $guardar_persona = $pdo->prepare($sql);
        $exito_persona = $guardar_persona->execute([$nombre, $apellido, $correo, $telefono]);

        //Verificando si se realizó el registro completo
        if ($exito_usuario && $guardar_usuario->rowCount() == 1 && $exito_persona && $guardar_persona->rowCount() == 1) {
            echo '<h2>Usuario registrado!</h2>';
        } else {
            echo '<h2>Error al intentar registrar el Usuario, intente nuevamente</h2>';
        }
    } else {
        echo "<h2>Rellene todos los campos obligatorios</h2>";
    }
}

if (!empty($_POST["admin_new"])) {

    if (!empty($_POST["usuario_nombre"]) && !empty($_POST["usuario_apellido"]) && !empty($_POST["usuario_email"]) && !empty($_POST["rol_id"]) && !empty($_POST["usuario_usuario"]) && !empty($_POST["usuario_clave_1"]) && !empty($_POST["usuario_clave_2"])) {

        $nombre = isset($_POST["usuario_nombre"]) ? $_POST["usuario_nombre"] : '';
        $apellido = isset($_POST["usuario_apellido"]) ? $_POST["usuario_apellido"] : '';
        $correo = isset($_POST["usuario_email"]) ? $_POST["usuario_email"] : '';
        $rol = isset($_POST["rol_id"]) ? $_POST["rol_id"] : '';
        $telefono = isset($_POST["usuario_telefono"]) ? $_POST["usuario_telefono"] : '';
        $usuario = isset($_POST["usuario_usuario"]) ? $_POST["usuario_usuario"] : '';
        $clave_1 = isset($_POST["usuario_clave_1"]) ? $_POST["usuario_clave_1"] : '';
        $clave_2 = isset($_POST["usuario_clave_2"]) ? $_POST["usuario_clave_2"] : '';

        //Verificando usuario
        $stmt = $pdo->prepare("SELECT usuario_usuario FROM usuario WHERE usuario_usuario = :usuario");
        $stmt->execute([':usuario' => $usuario]);
        if ($stmt->fetch(PDO::FETCH_OBJ)) {
            echo "<h2>El nombre de usuario ya se encuentra registrado</h2>";
            exit();
        }

        // Verificando claves
        if ($clave_1 != $clave_2) {
            echo "<h2>Las contraseñas ingresadas no son iguales</h2>";
            exit();
        } else {
            $clave = password_hash($clave_1, PASSWORD_BCRYPT, ["cost" => 10]);
        }

        // Guardar Usuario
        $sql = "INSERT INTO usuario (usuario_nombre, usuario_apellido, usuario_email, usuario_clave, usuario_usuario, rol_id, usuario_telefono) VALUES (?, ?, ?, ?, ?, ?, ?)";
        $guardar_usuario = $pdo->prepare($sql);
        $exito = $guardar_usuario->execute([$nombre, $apellido, $correo, $clave, $usuario, $rol, $telefono]);

        //Verificando si se realizó el registro
        if ($exito && $guardar_usuario->rowCount() == 1) {
            echo '<h2>Usuario registrado!</h2>';
        } else {
            echo '<h2>Error al intentar registrar el Usuario, intente nuevamente</h2>';
        }
    } else {
        echo "<h2>Rellene todos los campos obligatorios</h2>";
    }
}
?>

