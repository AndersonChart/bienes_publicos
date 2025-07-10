<?php
require_once 'php/categoria.php';
$categoria = new categoria();
$registros_categoria = $categoria->leer_todos();

require_once 'php/marca.php';
$marca = new marca();
$registros_marca = $marca->leer_todos();

require_once 'php/modelo.php';
$modelo = new modelo();
$registros_modelo = $modelo->leer_todos();
?>

<div class="container py-5">

    <div class="mb-4">
        <a href="index.php?vista=listar_bien" class="btn btn-outline-danger">← Volver</a>
    </div>

    <h2 class="text-danger mb-4 text-center">Registrar Nuevo Bien</h2>

    <form action="index.php?vista=registrar_bien" method="POST" enctype="multipart/form-data" class="p-4 border rounded shadow-sm bg-white">
        <fieldset>
        <legend class="fs-5 text-danger mb-3">Rellene los campos</legend>

        <div class="mb-3">
            <label for="ser" class="form-label">Número de serie</label>
            <input type="text" name="serie" id="ser" class="form-control" required>
        </div>

        <div class="mb-3">
            <label for="nom" class="form-label">Nombre</label>
            <input type="text" name="nombre" id="nom" class="form-control" required>
        </div>

        <div class="mb-3">
            <label class="form-label">Descripción</label>
            <textarea name="descripcion" rows="2" class="form-control"></textarea>
        </div>

        <div class="mb-3">
            <label class="form-label">Categoría</label>
            <div class="d-flex">
            <select name="categoria" class="form-select me-2">
                <?php foreach($registros_categoria as $row){ ?>
                <option value="<?= $row['categoria_id']; ?>"><?= $row['categoria_nombre']; ?></option>
                <?php } ?>
            </select>
            <a href="index.php?vista=form_registrar_categoria" class="btn btn-outline-danger">Añadir</a>
            </div>
        </div>

        <div class="mb-3">
            <label for="dat" class="form-label">Fecha de adquisición</label>
            <input type="date" name="add" id="dat" class="form-control">
        </div>

        <div class="mb-3">
            <label class="form-label">Marca</label>
            <div class="d-flex">
            <select name="marca" class="form-select me-2">
                <?php foreach($registros_marca as $row){ ?>
                <option value="<?= $row['marca_id']; ?>"><?= $row['marca_nombre']; ?></option>
                <?php } ?>
            </select>
            <a href="index.php?vista=form_registrar_marca" class="btn btn-outline-danger">Añadir</a>
            </div>
        </div>

        <div class="mb-3">
            <label class="form-label">Modelo</label>
            <div class="d-flex">
            <select name="modelo" class="form-select me-2">
                <?php foreach($registros_modelo as $row){ ?>
                <option value="<?= $row['modelo_id']; ?>"><?= $row['modelo_nombre']; ?></option>
                <?php } ?>
            </select>
            <a href="index.php?vista=form_registrar_modelo" class="btn btn-outline-danger">Añadir</a>
            </div>
        </div>

        <div class="mb-3">
            <label class="form-label">Estado</label>
            <select name="estado" class="form-select">
            <option value="1">Disponible</option>
            <option value="2">Asignado</option>
            <option value="3">Mantenimiento</option>
            <option value="4">Desincorporado</option>
            </select>
        </div>

        <div class="mb-3">
            <label for="img" class="form-label">Imagen (opcional)</label>
            <input type="file" name="imagen" id="img" class="form-control">
        </div>

        <div class="mb-3">
            <label for="act" class="form-label">Acta (opcional)</label>
            <input type="file" name="acta" id="act" class="form-control">
        </div>
        
        <div class="d-grid">
            <button type="submit" class="btn btn-danger">Registrar</button>
        </div>
    </fieldset>
</form>
        <div class="form-resultado">
            <?php
            if (isset($_SESSION['mensaje'])) {
                echo $_SESSION['mensaje'];
                unset($_SESSION['mensaje']);
            } 
            ?>
        </div>
</div>
