<?php
require_once 'php/marca.php';
$marca = new marca();
$registros_marca = $marca->leer_todos();
?>

<button><a href="index.php?vista=listar_modelo">Volver</a></button>
<h1>Registrar Nuevo modelo</h1>
<fieldset>
    <legend>Rellene los campos</legend>
    <form action="index.php?vista=registrar_modelo" method="POST" class="FormularioAjax">
        <div>
            <label for="nom">Nombre: </label>
            <input type="text" name="nombre" id="nom">
        </div>
        <div>
        <label for="mar">Marca:</label>
            <select name="marca" id="mar">
                <?php foreach($registros_marca as $row){ ?>
                    <option value="<?= $row['marca_id']; ?>"> <?= $row['marca_nombre']; ?> </option>
                <?php } ?>
            </select>
        </div>
        <div>
            <button type="submit">Registrar</button>
        </div>
        <div class="form-resultado"></div>
    </form>
</fieldset>