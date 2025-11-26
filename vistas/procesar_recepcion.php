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
        <div class="new-proceso" data-modal-target="new_recepcion">Procesar</div>
    </div>
</div>

<!-- Modal: Finalizar Proceso -->
<dialog data-modal="new_recepcion" class="modal_new-recepcion">
    <!-- Botón de cierre del modal -->
    <button type="button" class="modal__close" onclick="this.closest('dialog').close()">X</button>

    <h2 class="modal_title">Realizar Recepción</h2>
    <div class="recepcion_container">
        <!-- Columna izquierda: formulario -->
        <form id="form_nuevo_recepcion" method="POST" enctype="multipart/form-data" autocomplete="off" class="user_container" novalidate>
            <div class="input_block_content">
                <label for="ajuste_fecha" class="input_label">Fecha*</label>
                <input type="date" name="ajuste_fecha" id="ajuste_fecha" class="input_text input_date">
            </div>

            <div class="input_block_content">
                <label for="ajuste_descripcion" class="input_label">Descripción</label>
                <input type="text" id="ajuste_descripcion" name="ajuste_descripcion" class="input_text">
            </div>

            <!-- Campo oculto para el tipo -->
            <input type="hidden" name="ajuste_tipo" value="1"> <!-- 1 = Entrada -->

            <!-- Contenedor de errores -->
            <div id="error-container-recepcion" class="error-container"></div>

            <!-- Botón de envío -->
            <input type="submit" value="Guardar" name="save" class="register_submit" id="btn_guardar_recepcion">
        </form>

        <div class="separador"></div>

        <!-- Columna derecha: tabla resumen -->
        <div class="resumen_container">
            <div class="container_table_box-recepcion">
                <div class="top"></div>
                <table id="recepcionResumenTabla" class="display" style="width:100%">
                    <thead>
                        <tr>
                            <th colspan="3" class="title">Resumen Recepción</th>
                        </tr>
                        <tr>
                            <th class="header">Código</th>
                            <th class="header">Nombre</th>
                            <th class="header">Cantidad</th>
                        </tr>
                    </thead>
                </table>
                <div class="bottom paginador"></div>
            </div>
        </div>
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
<dialog data-modal="seriales_articulo" class="modal modal_confirmar">
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
        <table id="recepcionSerialIdTabla" class="display" style="width:100%">
            <thead>
                <tr>
                    <th class="header">Número</th>
                    <th class="header">Serial</th>
                </tr>
            </thead>
        </table>
    </div>

    <!-- Contenedor de errores -->
    <div id="error-container-recepcion-serial" class="error-container"></div>

        <form id="form_recepcion_articulo_id" method="POST">
            <input type="submit" value="Guardar" name="recepcion_serial" class="register_submit" id="btn_serial">
        </form>
</dialog>
<!-- Modal: Éxito -->
<dialog data-modal="success" class="modal modal_success">
    <form method="dialog">
        <div class="modal_icon"></div>
        <h2 class="modal_title">¡Recepción procesada con éxito!</h2>
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
    <div class="bottom paginador"></div>
</div>

<script src="js/recepcion_articulo_datatable.js"></script>
<script src="js/recepcion_resumen_datatable.js"></script>
<script src="js/recepcion_serial_id_datatable.js"></script>