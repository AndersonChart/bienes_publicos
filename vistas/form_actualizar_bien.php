<?php

require_once 'php/bien.php';
$bien = new bien();
$registro = $bien->leer_por_id($_GET['id']);


require_once 'php/categoria.php';
$categoria = new categoria();
$registros_categoria = $categoria->leer_todos();

require_once 'php/modelo.php';
$modelo = new modelo();
$registros_modelo = $modelo->leer_todos();

require_once 'php/marca.php';
$marca = new marca();
$registros_marca = $marca->leer_todos();

?>
<button><a href="index.php?vista=listar_bien">Volver</a></button>
<h1>Actualizar Bien</h1>
<form action="index.php?vista=actualizar_bien" method="POST" class="FormularioAjax">
<!-- Este primer input es para saber el id del registro, pero no es visible ante el usuario -->
    <input type="hidden" name="id" value="<?= $registro['bien_id'] ?>">
        <div>
            <label for="ser">Número de serie: </label>
            <input type="text" name="serie" id="ser" value="<?= $registro['bien_serie'] ?>">
        </div>
        <div>
            <label for="nom">Nombre: </label>
            <input type="text" name="nombre" id="nom" value="<?= $registro['bien_nombre'] ?>">
        <div>
            <label for="desc">Descripción:</label>
            <textarea name="descripcion" id="desc" rows="2"><?= htmlspecialchars($registro['bien_descripcion']) ?></textarea>
        </div>
            Categoría: 
            <select name="categoria">
                <?php foreach($registros_categoria as $row){ 
                    if ($registro['categoria_id'] == $row['categoria_id']){?>
                    <option value="<?= $row['categoria_id']; ?>" selected> <?= $row['categoria_nombre']; ?> </option>
                <?php }else{ ?>
                    <option value="<?= $row['categoria_id']; ?>"> <?= $row['categoria_nombre']; ?> </option>
                <?php }
                } ?>
            </select>
        </div>
        <div>
            <label for="dat">Fecha de adquisición: </label>
            <input type="date" name="add" id="dat" value="<?= $registro['fecha_add'] ?>">
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
            <label for="mod">Modelo:</label>
            <select name="modelo" id="mod">
                <?php foreach($registros_modelo as $row){ ?>
                    <option value="<?= $row['modelo_id']; ?>"
                        <?php if($row['modelo_id'] == $registro['modelo_id']) echo 'selected'; ?>>
                        <?= $row['modelo_nombre']; ?>
                    </option>
                <?php } ?>
            </select>
        </div>
        <div>
            <select name="estado">
                <option value="1">Disponible</option>
                <option value="2">Asignado</option>
                <option value="3">Mantenimiento</option>
                <option value="4">Desincorporado</option>
            </select>
        </div>
        <div>
            Imagen (opcional):<input type="file" name="imagen">
        </div>
        <div>
            Acta (opcional):<input type="file" name="acta">
        </div>
        <div>
            <button type="submit">Registrar</button>
        </div>
        <div class="form-resultado"></div>
</form>