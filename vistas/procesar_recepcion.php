<!-- Filtros y botones superiores -->
<div class="banner_list">
    <div class="filters">
        <label for="categoria" class="input_label">Categoría:</label>
        <select name="categoria_id" id="categoria_filtro" class="input_text input_select categoria_filtro" >
            <option value="" selected disabled>Seleccione</option>
        </select>
        <label for="clasificacion" class="input_label">Clasificación:</label>
        <select name="clasificacion_id" id="clasificacion_filtro" class="input_text input_select clasificacion_filtro" >
            <option value="" selected disabled>Seleccione</option>
        </select>
    </div>
    <div class="basics-container">
        <a class="new_user" href="index.php?vista=listar_recepcion">← Regresar</a>
        <div class="new-proceso" data-modal-target="modal_proceso_recepcion">Procesar</div>
    </div>
</div>

<!-- Modal: Finalizar Proceso -->
<dialog data-modal="modal_proceso_recepcion" class="modal_new-recepcion">
    <button type="button" class="modal__close" onclick="this.closest('dialog').close()">X</button>
    <h2 class="modal_title">Realizar Recepción</h2>

    <div class="proceso_container">
        <!-- Columna izquierda: formulario -->
        <form id="form_proceso_recepcion" method="POST" autocomplete="off" class="user_container" novalidate>
            <div class="input_block_content">
                <label for="proceso_recepcion_fecha" class="input_label">Fecha*</label>
                <input type="date" name="ajuste_fecha" id="proceso_recepcion_fecha" class="input_text input_date">
            </div>

            <div class="input_block_content">
                <label for="proceso_recepcion_descripcion" class="input_label">Descripción</label>
                <input type="text" id="proceso_recepcion_descripcion" name="ajuste_descripcion" class="input_text">
            </div>

            <input type="hidden" name="ajuste_tipo" value="1"> <!-- 1 = Entrada -->

            <!-- Contenedor de errores -->
            <div id="error-container-proceso-recepcion" class="error-container"></div>

            <!-- Botón de envío -->
            <input type="submit" value="Guardar" class="register_submit" id="btn_guardar_proceso_recepcion">
        </form>

        <!-- Columna derecha: tabla resumen -->
        <div class="resumen_container">
            <div class="container_table_box-recepcion">
                <table id="procesoRecepcionResumenTabla" class="display">
                    <thead>
                        <tr>
                            <th colspan="4" class="title">Resumen de la Recepción</th>
                        </tr>
                        <tr>
                            <th class="header">Código</th>
                            <th class="header">Nombre</th>
                            <th class="header">Cantidad</th>
                            <th class="header">Seriales</th>
                        </tr>
                    </thead>
                </table>
            </div>
        </div>
    </div>
</dialog>

<!-- Confirmación de descartar recepción -->
<dialog data-modal="confirmar_regresar_recepcion" class="modal modal_confirmar">
    <div class="modal_header-confirmar">
        <h2 class="modal_title">¿Desea cancelar la recepción?</h2>
    </div>
    <div class="modal_delete-buttons">
        <form method="dialog">
            <button class="modal__close modal__close-confirm">Cancelar</button>
        </form>
        <form id="form_confirmar_regresar" method="POST">
            <input type="submit" value="Aceptar" class="register_submit-confirm" id="btn_confirmar_regresar">
        </form>
    </div>
</dialog>


<!-- Modal: Información -->
<dialog data-modal="info_articulo" class="modal modal_info">
    <div class="modal_header-info">
        <form method="dialog"><button class="modal__close">X</button></form>
        <h2 class="modal_title modal_title-info">Información del artículo</h2>
    </div>
    <div class="img_info">
        <img id="info_imagen" class="foto_info imagen_info">
    </div>
    <div class="info_container">
        <ul class="info_lista">
            <li><strong class="info_subtitle">Código:</strong> <span class="info_data" id="info_codigo"></span></li>
            <li><strong class="info_subtitle">Nombre:</strong> <span class="info_data" id="info_nombre"></span></li>
            <li><strong class="info_subtitle">Categoría:</strong> <span class="info_data" id="info_categoria"></span></li>
            <li><strong class="info_subtitle">Clasificación:</strong> <span class="info_data" id="info_clasificacion"></span></li>
            <li id="li_info_marca">
                <strong class="info_subtitle">Marca:</strong> 
                <span class="info_data" id="info_marca"></span>
            </li>
            <li id="li_info_modelo">
                <strong class="info_subtitle">Modelo:</strong> 
                <span class="info_data" id="info_modelo"></span>
            </li>
            <li><strong class="info_subtitle">Descripción:</strong> <span class="info_data" id="info_descripcion"></span></li>
        </ul>
    </div>
</dialog>


<!-- Modal: Seriales -->
<dialog data-modal="seriales_articulo" class="modal modal_confirmar modal_serial">
    <div class="modal_header-info">
        <form method="dialog"><button class="modal__close">X</button></form>
        <h2 class="modal_title">Añadir seriales</h2>
    </div>

    <!-- Imagen del artículo -->
    <div class="img_info">
        <img id="serial_imagen_articulo" class="foto_info imagen_info" src="img/icons/articulo.png" alt="Imagen del artículo">
    </div>

    <!-- Código y nombre -->
    <div class="delete_container">
        <span class="delete_data-title" id="serial_codigo_articulo"></span>
        <span class="delete_data" id="serial_nombre_articulo"></span>
    </div>

    <!-- Tabla de seriales -->
    <div class="seriales_container">
        <table id="procesoRecepcionSerialTabla" class="display" style="width:100%">
            <thead>
                <tr>
                    <th class="header">Número</th>
                    <th class="header">Serial</th>
                </tr>
            </thead>
        </table>
    </div>

    <!-- Contenedor de errores -->
    <div id="error-container-proceso-recepcion-serial" class="error-container"></div>

    <!-- Botón de guardar -->
    <form id="form_proceso_recepcion_seriales" method="POST">
        <input type="submit" value="Guardar" class="register_submit" id="btn_guardar_proceso_recepcion_seriales">
    </form>
</dialog>


<!-- Modal: Éxito -->
<dialog data-modal="success" class="modal modal_success">
    <form method="dialog">
        <h2 class="modal_title">Recepción procesada con éxito</h2>
        <p id="success-message"></p>
        <button class="modal__close-success" id="close-success-proceso-recepcion">Aceptar</button>
    </form>
</dialog>

<!-- Tabla principal de artículos -->
<div class="container_table_box">
    <table id="procesoRecepcionArticuloTabla" class="display" style="width:100%">
        <thead>
            <tr>
                <th colspan="7" class="title">Ingresar Artículos</th>
            </tr>
            <tr>
                <th class="header">Código</th>
                <th class="header">Nombre</th>
                <th class="header">Categoría</th>
                <th class="header">Clasificación</th>
                <th class="header">Imagen</th>
                <th class="header">Cantidad</th>
                <th class="header">Acciones</th>
            </tr>
        </thead>
    </table>
</div>

<!-- Un solo archivo JS para manejar todo -->
<script src="js/proceso_recepcion_datatable.js"></script>
