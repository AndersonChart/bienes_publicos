<!-- Filtros y botones superiores -->
<div class="banner_list">
    <div class="filters">
        <label for="categoria" class="input_label">Categoría:</label>
        <select name="categoria_id" id="categoria_filtro" class="input_text input_select categoria_filtro" required>
            <option value="" selected disabled>Seleccione</option>
        </select>
        <label for="clasificacion" class="input_label">Clasificación:</label>
        <select name="clasificacion_id" id="clasificacion_filtro" class="input_text input_select clasificacion_filtro" required>
            <option value="" selected disabled>Seleccione</option>
        </select>
    </div>
    <div class="basics-container">
        <a class="new_user" href="index.php?vista=listar_recepcion">← Regresar</a>
        <div class="new_user new-proceso" data-modal-target="new_recepcion">Procesar</div>
    </div>
</div>

<!-- Modal: Finalizar Proceso -->
<dialog data-modal="new_recepcion" class="modal modal_new-recepcion">
    <form method="dialog"><button class="modal__close">X</button></form>
    <h2 class="modal_title">Realizar Recepción</h2>
    <form id="form_nuevo_recepcion" method="POST" enctype="multipart/form-data" autocomplete="off" class="user_container">

        <div class="input_block_content">
            <label for="recepcion_fecha" class="input_label">Fecha*</label>
            <input type="date" name="recepcion_fecha" id="recepcion_fecha" class="input_text input_date" required>
        </div>

        <div class="input_block_content">
            <label for="recepcion_descripcion" class="input_label">Descripción*</label>
            <input type="text" id="recepcion_descripcion" name="recepcion_descripcion" class="input_text" required>
        </div>

        <div id="error-container-recepcion" class="error-container"></div>
        <input type="submit" value="Guardar" name="save" class="register_submit" id="btn_guardar_recepcion">
    </form>
</dialog>

<!-- Modal: Seriales -->
<dialog data-modal="seriales_articulo" class="modal modal_confirmar">
    <form method="dialog"><button class="modal__close">X</button></form>
    <div class="modal_header-confirmar">
        <h2 class="modal_title">Añadir seriales</h2>
    </div>
    <div class="img_info">
        <!-- Imagen con placeholder por defecto -->
        <img id="recepcion_imagen_articulo" class="foto_info imagen_info" src="img/icons/articulo.png" alt="Imagen del artículo">
    </div>
    <div class="recepcion_container">
        <span class="delete_data-title" id="recepcion_codigo_articulo"></span>
        <span class="delete_data" id="recepcion_nombre_articulo"></span>
        <span class="delete_data" id="recepcion_categoria_articulo"></span>
        <span class="delete_data" id="recepcion_clasificacion_articulo"></span>
        <div class="seriales">
            <div class="top"></div>
            <table id="recepcionSerialIdTabla" class="display" style="width:100%">
                <thead>
                    <tr>
                        <th class="header">Número</th>
                        <th class="header">Serial</th>
                    </tr>
                </thead>
            </table>
            <div class="bottom paginador"></div>
        </div>
        <div id="error-container-recepcion-serial" class="error-container"></div>
        <form id="form_recepcion_articulo_id" method="POST">
            <input type="submit" value="Aceptar" name="recepcion_serial" class="register_submit-confirm" id="btn_serial">
        </form>
    </div>
</dialog>

<!-- Modal: Éxito -->
<dialog data-modal="success" class="modal modal_success">
    <form method="dialog">
        <div class="modal_icon"></div>
        <h2 class="modal_title">¡Añadido con éxito!</h2>
        <p class="modal_success-message" id="success-message"></p>
        <button class="modal__close-success" id="close-success_articulo">Aceptar</button>
    </form>
</dialog>

<!-- Tabla -->
<div class="container_table_box">
    <div class="top"></div>
    <table id="recepcionArticuloTabla" class="display" style="width:100%">
        <thead>
            <tr>
                <th colspan="9" class="title">Artículos</th>
            </tr>
            <tr>
                <th class="header">Código</th>
                <th class="header">Nombre</th>
                <th class="header">Categoría</th>
                <th class="header">Clasificación</th>
                <th class="header">Modelo</th>
                <th class="header">Marca</th>
                <th class="header">Imagen</th>
                <th class="header">Cantidad</th>
                <th class="header">Acciones</th>
            </tr>
        </thead>
    </table>
    <div class="bottom paginador"></div>
</div>

<script src="js/recepcion_articulo_datatable.js"></script>
