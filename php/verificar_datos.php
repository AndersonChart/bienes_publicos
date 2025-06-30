<?php
    if (!empty($_POST["usuario_email"]) && !empty($_POST["recuperar"])) {
        $correo = $_POST["usuario_email"];
        
        // Comprobar datos de usuario
        $stmt = $pdo->prepare("SELECT * FROM usuario WHERE usuario_email = :correo");
        $stmt->execute([':correo' => $correo]);
        if ($fila = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $form = 1;
        } else {
            echo "<h2>Hubo un error al encontrar los datos, intente más tarde</h2>";
            exit();
        }
    }
?>