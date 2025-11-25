<!-- Filtros y botones superiores -->
<div id="usuario" data-id="<?php echo $_SESSION["rol"]; ?>"></div>
<div class="banner_list">
    <div class="filters">
    </div>
    <div class="basics-container">
        <a class="new_user new_user-usuario" href="index.php?vista=procesar_recepcion">+ Nueva</a>
        <?php if ($_SESSION["rol"] == 3): ?>
            <div id="toggleEstado" class="btn_toggle-estado estado-rojo">Anulados</div>
        <?php endif; ?>
    </div>
</div>


<!-- Tabla -->
<div class="container_table_box">
    <div class="top"></div>
    <table id="recepcionTabla" class="display" style="width:100%">
        <thead>
            <tr>
                <th colspan="4" class="title">Recepciones</th>
            </tr>
            <tr>
                <th class="header">ID</th>
                <th class="header">Fecha</th>
                <th class="header">Descripción</th>
                <th class="header">Acciones</th>
            </tr>
        </thead>
    </table>
    <div class="bottom paginador"></div>
</div>

<script src="js/recepcion_datatable.js"></script>