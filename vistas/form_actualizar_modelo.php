<?php

require_once 'php/modelo.php';
$modelo = new modelo();
$registro = $modelo->leer_por_id($_GET['id']);

require_once 'php/marca.php';
$marca = new marca();
$registros_marca = $marca->leer_todos();
?>
<button><a href="index.php?vista=listar_modelo">Volver</a></button>
<h1>Actualizar modelo</h1>
<form action="index.php?vista=actualizar_modelo" method="POST">
<!-- Este primer input es para saber el id del registro, pero no es visible ante el usuario -->
    <input type="hidden" name="id" value="<?= $registro['modelo_id'] ?>">
        <div>
            <label for="nom">Nombre:</label>
            <input type="text" name="nombre" id="nom" value="<?= $registro['modelo_nombre'] ?>">
        </div>
        <div>
            <label for="mar">Marca:</label>
            <select name="marca" id="mar">
                <?php foreach($registros_marca as $row){ ?>
                    <option value="<?= $row['marca_id']; ?>"
                        <?php if($row['marca_id'] == $registro['marca_id']) echo 'selected'; ?>>
                        <?= $row['marca_nombre']; ?>
                    </option>
                <?php } ?>
            </select>
        </div>
        <div>
            <button type="submit">Actualizar</button>
        </div>
</form>