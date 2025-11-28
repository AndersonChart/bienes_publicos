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

    <!-- Mensaje de error -->
    <dialog data-modal="error" class="modal modal_success">
        <form method="dialog">
            <h2 class="modal_title">No se puede anular la recepción</h2>
            <p class="modal_error-message" id="error-message "></p>
            <button class="modal__close-success" id="close-error">Aceptar</button>
        </form>
    </dialog>

    <!-- Mensaje de exito -->
    <dialog data-modal="success" class="modal modal_success">
        <form method="dialog">
            <div class="modal_icon"></div>
            <h2 class="modal_title">¡Proceso éxitoso!</h2>
            <p class="modal_success-message" id="success-message"></p>
            <button class="modal__close-success" id="close-success">Aceptar</button>
        </form>
    </dialog>

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