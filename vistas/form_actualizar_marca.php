<?php

require_once 'php/marca.php';
$marca = new marca();
$registro = $marca->leer_por_id($_GET['id']);
?>
<button><a href="index.php?vista=listar_marca">Volver</a></button>
<h1>Actualizar Marca</h1>
<form action="index.php?vista=actualizar_marca" method="POST" class="FormularioAjax">
<!-- Este primer input es para saber el id del registro, pero no es visible ante el usuario -->
    <input type="hidden" name="id" value="<?= $registro['marca_id'] ?>">
        <div>
            <label for="nom">Nombre:</label>
            <input type="text" name="nombre" id="nom" value="<?= $registro['marca_nombre'] ?>">
        </div>
        </div>
            <label for="img">Imagen: </label>
            <input type="file" id="img" value="<?= $registro['marca_imagen'] ?>">
        </div>
        <div>
            <button type="submit">Registrar</button>
        </div>
        <div class="form-resultado"></div>
</form>