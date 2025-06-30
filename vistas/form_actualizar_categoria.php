<?php

require_once 'php/categoria.php';
$categoria = new categoria();
$registro = $categoria->leer_por_id($_GET['id']);
?>
<button><a href="index.php?vista=listar_categoria">Volver</a></button>
<h1>Actualizar Categoría</h1>
<form action="index.php?vista=actualizar_categoria" method="POST">
<!-- Este primer input es para saber el id del registro, pero no es visible ante el usuario -->
    <input type="hidden" name="id" value="<?= $registro['categoria_id'] ?>">
        <div>
            <label for="nom">Nombre</label>
            <input type="text" name="nombre" id="nom" value="<?= $registro['categoria_nombre'] ?>">
        </div>
        <div>
        <div>
            <label for="desc">Descripción:</label>
            <textarea name="descripcion" id="desc" rows="2"><?= htmlspecialchars($registro['categoria_descripcion']) ?></textarea>
        </div>
        <div>
            <button type="submit">Registrar</button>
        </div>
</form>