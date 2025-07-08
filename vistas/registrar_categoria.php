<?php

require_once 'php/categoria.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = isset($_POST["nombre"]) ? $_POST["nombre"] : '';
    if (!empty($nombre)) {
        // Verificar si ya existe el nombre de la categoria
        $stmt = $pdo->prepare("SELECT categoria_nombre FROM categoria WHERE categoria_nombre = ?");
        $stmt->execute([$nombre]);
        if ($stmt->fetch()) {
            $_SESSION['mensaje'] = '<div class="alert alert-danger">El nombre de la categoría ya está registrada.</div>';
            header("Location: index.php?vista=form_registrar_categoria");
            exit;
        } else {
            $categoria = new categoria();
            $exito = $categoria->crear($nombre, isset($_POST['descripcion']) ? $_POST['descripcion'] : '');
            if ($exito) {
                $_SESSION['mensaje'] = "<div class='alert alert-success'>¡El registro de la categoría fue exitosa!</div>";
            } else {
                $_SESSION['mensaje'] = "<div class='alert alert-danger'>Hubo un error al registrar la categoría, intente nuevamente</div>";
            }
            header("Location: index.php?vista=form_registrar_categoria");
            exit;
        }
    }
}
?>