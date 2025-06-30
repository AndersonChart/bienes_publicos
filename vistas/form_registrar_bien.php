<?php
require_once 'php/categoria.php';
$categoria = new categoria();
$registros_categoria = $categoria->leer_todos();


require_once 'php/marca.php';
$marca = new marca();
$registros_marca = $marca->leer_todos();

?>

<button><a href="index.php?vista=listar_bien">Volver</a></button>
<h1>Registrar Nuevo Bien</h1>
<fieldset>
    <legend>Rellene los campos</legend>
    <form action="index.php?vista=registrar_bien" method="POST">
        <div>
            <label for="ser">Número de serie: </label>
            <input type="text" name="serie" id="ser">
        </div>
        <div>
            <label for="nom">Nombre: </label>
            <input type="text" name="nombre" id="nom">
        </div>
        <div>
        Descripción:
        <textarea name="descripcion" rows="1"></textarea>
        </div>
        <div>
            Categoría:
            <select name="categoria">
                <?php foreach($registros_categoria as $row){ ?>
                    <option value="<?= $row['categoria_id']; ?>"> <?= $row['categoria_nombre']; ?> </option>
                <?php } ?>
            </select>
            <button><a href="index.php?vista=registrar_categoria">Añadir</a></button>
        </div>
        <div>
            <label for="dat">Fecha de adquisición: </label>
            <input type="date" name="add" id="dat">
        </div>
        <div>
            <label for="mar">Marca: </label>
            <select name="marca" id="mar">
                <?php foreach($registros_marca as $row){ ?>
                    <option value="<?= $row['marca_id']; ?>"> <?= $row['marca_nombre']; ?> </option>
                <?php } ?>
            </select>
            <button><a href="index.php?vista=registrar_marca">Añadir</a></button>
        </div>
        <div>
            <label for="mod">Modelo: </label>
            <select name="modelo" id="mod">
                    
            </select>
        </div>
        <div>
            Estado: 
            <select name="estado">
                <option value="1">Disponible</option>
                <option value="2">Asignado</option>
                <option value="3">Mantenimiento</option>
                <option value="4">Desincorporado</option>
            </select>
        </div>
        <div>
            <label for="img">Imagen (opcional): </label>
            <input type="file" name="imagen" id="img">
        </div>
        <div>
            <label for="act">Acta (opcional):</label>
            <input type="file" name="acta" id="act">
        </div>
        <div>
            <button type="submit">Registrar</button>
        </div>
    </form>
</fieldset>