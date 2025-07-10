<?php
require_once 'php/bien.php';
$bien = new bien();
$registros = $bien->leer_todos();
?>

<div class="container mt-5">

    <div class="mb-3 text-start">
        <a href="index.php?vista=inicio" class="btn btn-outline-danger">← Volver</a>
    </div>

    <h2 class="text-danger mb-4">Lista de Bienes</h2>

    <div class="table-responsive">
        <table class="table table-bordered table-hover align-middle bg-white">
        <thead class="table-danger text-center">
            <tr>
            <th>ID</th>
            <th>No. Serie</th>
            <th>Nombre</th>
            <th>Descripción</th>
            <th>Categoría</th>
            <th>Fecha de add.</th>
            <th>Marca</th>
            <th>Modelo</th>
            <th>Estado</th>
            <th>Imagen</th>
            <th>Acta</th>
            <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($registros)) {
            foreach ($registros as $row) { ?>
                <tr>
                <td><?= $row['bien_id'] ?></td>
                <td><?= $row['bien_serie'] ?></td>
                <td><?= $row['bien_nombre'] ?></td>
                <td><?= $row['bien_descripcion'] ?></td>
                <td><?= $row['categoria_id'] ?></td>
                <td><?= $row['fecha_add'] ?></td>
                <td><?= $row['marca_id'] ?></td>
                <td><?= $row['modelo_id'] ?></td>
                <td><?= $row['estado_id'] ?></td>
                <td><?= $row['bien_imagen'] ?></td>
                <td><?= $row['bien_acta'] ?></td>
                <td class="text-center">
                    <a href="index.php?vista=form_actualizar_bien&id=<?= $row['bien_id'] ?>" class="btn btn-sm btn-outline-danger mb-1">Actualizar</a>
                    <a href="index.php?vista=desincorporar_bien&id=<?= $row['bien_id'] ?>" onclick="return confirm('¿Estás seguro de Desincorporar este Bien?')" class="btn btn-sm btn-danger">Desincorporar</a>
                </td>
                </tr>
            <?php }
            } else { ?>
            <tr>
                <td colspan="12" class="text-center text-muted">No hay ningún registro</td>
            </tr>
            <?php } ?>
        </tbody>
        </table>
    </div>

    <div class="text-end mt-3">
        <a href="index.php?vista=form_registrar_bien" class="btn btn-danger">Nuevo Registro</a>
    </div>
</div>
<div class="form-resultado">
        <?php
            if (isset($_SESSION['mensaje'])) {
                echo $_SESSION['mensaje'];
                unset($_SESSION['mensaje']);
            } 
            ?>
</div>