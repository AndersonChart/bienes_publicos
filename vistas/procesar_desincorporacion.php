<!-- Filtros y botones superiores -->
<div class="banner_list">
    <div class="filters">
        <label for="categoria" class="input_label">Categoría:</label>
        <select name="categoria_id" id="categoria_filtro" class="input_text input_select categoria_filtro">
            <option value="" selected disabled>Seleccione</option>
        </select>

        <label for="clasificacion" class="input_label">Clasificación:</label>
        <select name="clasificacion_id" id="clasificacion_filtro" class="input_text input_select clasificacion_filtro">
            <option value="" selected disabled>Seleccione</option>
        </select>
    </div>

    <div class="basics-container">
        <a class="new_user" href="index.php?vista=listar_desincorporacion">← Regresar</a>
        <div class="new-proceso" data-modal-target="modal_proceso_desincorporacion">Procesar</div>
    </div>
</div>

<!-- Confirmación de descartar recepción -->
<dialog data-modal="confirmar_regresar_desincorporacion" class="modal modal_confirmar">
    <div class="modal_header-confirmar">
        <h2 class="modal_title">¿Desea cancelar la Desincorporación?</h2>
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

<!-- Modal: Finalizar Proceso de Desincorporación -->
<dialog data-modal="modal_proceso_desincorporacion" class="modal_new-recepcion">
    <button type="button" class="modal__close" onclick="this.closest('dialog').close()">X</button>
    <h2 class="modal_title">Realizar Desincorporación</h2>

    <div class="proceso_container">
        <!-- Columna izquierda: formulario -->
        <form id="form_proceso_desincorporacion" method="POST" autocomplete="off" class="user_container" novalidate>
            <div class="input_block_content">
                <label for="proceso_desincorporacion_fecha" class="input_label">Fecha*</label>
                <input type="date" name="ajuste_fecha" id="proceso_desincorporacion_fecha" class="input_text input_date">
            </div>

            <div class="input_block_content">
                <label for="proceso_desincorporacion_descripcion" class="input_label">Descripción</label>
                <input type="text" id="proceso_desincorporacion_descripcion" name="ajuste_descripcion" class="input_text">
            </div>

            <div class="input_block_content">
                <label for="acta_desincorporacion" class="input_label">Acta de Desincorporación*</label>
                <div class="document_container">
                    <input type="file" id="acta_desincorporacion" name="acta_desincorporacion" class="input_file" 
                        accept=".pdf,.xls,.xlsx">
                    
                    <div class="document_wrapper" onclick="document.getElementById('acta_desincorporacion').click()">
                        <img id="preview_acta" src="img/icons/pdf.png" class="document_imagen" alt="Documento">
                        <span id="file_name_display">Click para subir Acta (PDF o Excel)</span>
                    </div>
                </div>
            </div>

            <input type="hidden" name="ajuste_tipo" value="1"> <!-- 1 = Entrada -->

            <!-- Contenedor de errores -->
            <div id="error-container-proceso-desincorporacion" class="error-container"></div>

            <!-- Botón de envío -->
            <input type="submit" value="Guardar" class="register_submit" id="btn_guardar_proceso_desincorporacion">
        </form>

        <!-- Columna derecha: tabla resumen -->
        <div class="resumen_container">
            <div class="container_table_box-asignacion">
                <table id="procesoDesincorporacionResumenTabla" class="display">
                    <thead>
                        <tr>
                            <th colspan="4" class="title">Resumen de la Desincorporación</th>
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

<!-- Confirmación de descartar Desincorporación -->
<dialog data-modal="confirmar_regresar-desincorporacion" class="modal modal_confirmar">
    <div class="modal_header-confirmar">
        <h2 class="modal_title">¿Desea cancelar la desincorporación?</h2>
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

<!-- Modal: Información del artículo -->
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

<!-- Modal: Seleccionar seriales -->
<dialog data-modal="seriales_articulo" class="modal modal_confirmar modal_serial">
    <div class="modal_header-info">
        <form method="dialog"><button class="modal__close">X</button></form>
        <h2 class="modal_title">Seleccionar seriales</h2>
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
        <table id="procesoDesincorporacionSerialTabla" class="display" style="width:100%">
            <thead>
                <tr>
                    <th class="header"></th> <!-- checkbox de selección -->
                    <th class="header">Serial</th>
                    <th class="header">Observación</th>
                </tr>
            </thead>
        </table>
    </div>

    <!-- Contenedor de errores -->
    <div id="error-container-proceso-desincorporacion-serial" class="error-container"></div>

    <!-- Botón de guardar selección -->
    <form id="form_proceso_desincorporacion_seriales" method="POST">
        <input type="submit" value="Confirmar selección" class="register_submit" id="btn_guardar_proceso_desincorporacion_seriales">
    </form>
</dialog>

<!-- Modal: Éxito -->
<dialog data-modal="success" class="modal modal_success">
    <form method="dialog">
        <h2 class="modal_title">Desincorporación procesada con éxito</h2>
        <p id="success-message"></p>
        <button class="modal__close-success" id="close-success-proceso-desincorporacion">Aceptar</button>
    </form>
</dialog>

<!-- Tabla principal de artículos -->
<div class="container_table_box">
    <table id="procesoDesincorporacionArticuloTabla" class="display" style="width:100%">
        <thead>
            <tr>
                <th colspan="9" class="title">Desincorporar Bienes</th>
            </tr>
            <tr>
                <th class="header">Código</th>
                <th class="header">Nombre</th>
                <th class="header">Categoría</th>
                <th class="header">Clasificación</th>
                <th class="header">Imagen</th>
                <th class="header">Ingresar</th>
                <th class="header">Activos</th>
                <th class="header">Seriales</th>
                <th class="header">Acciones</th>
            </tr>
        </thead>
    </table>
</div>

<!-- Un solo archivo JS para manejar todo -->
<script src="js/proceso_desincorporacion_datatable.js"></script>
