<?php
echo password_hash("admin123", PASSWORD_DEFAULT);
?>


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

<!-- Modal: error -->
<dialog data-modal="success" class="modal modal_success">
    <form method="dialog">
        <div class="modal_icon"></div>
        <h2 class="modal_title">¡Proceso éxitoso!</h2>
        <p class="modal_success-message" id="success-message"></p>
        <button class="modal__close-success" id="close-success_articulo">Aceptar</button>
    </form>
</dialog>

<!-- Modal: Confirmar eliminación -->
<dialog data-modal="eliminar_articulo" class="modal modal_confirmar">
    <div class="modal_header-confirmar">
        <h2 class="modal_title">¿Estás seguro de deshabilitar este artículo? <br>podría ocasionar problemas</h2>
    </div>
    <div class="img_info">
        <img id="delete_imagen_articulo" class="foto_info imagen_info">
    </div>
    <div class="delete_container">
        <span class="delete_data-title" id="delete_codigo_articulo"></span>
        <span class="delete_data" id="delete_nombre_articulo"></span>
        <span class="delete_data" id="delete_categoria_articulo"></span>
        <span class="delete_data" id="delete_clasificacion_articulo"></span>
    </div>
    <div class="modal_delete-buttons">
        <form method="dialog"><button class="modal__close modal__close-confirm">Cancelar</button></form>
        <form id="form_delete_articulo" method="POST">
            <input type="submit" value="Aceptar" name="delete" class="register_submit-confirm" id="btn_borrar">
        </form>
    </div>
</dialog>





        function cargarUsuarios() {
    fetch('php/usuario_ajax.php', {
        method: 'POST',
        body: new URLSearchParams({ accion: 'leer_todos' })
    })
    .then(res => res.json())
    .then(data => {
        console.log(data);

        const contenedor = document.querySelector('.grid.grid-usuario');
        if (!contenedor) {
            console.error('No se encontró el contenedor .grid.grid-usuario');
            return;
        }

        const usuarioRol = document.getElementById('usuario')?.dataset.id;

        // Limpiar filas anteriores
        const filasAnteriores = contenedor.querySelectorAll('.row');
        filasAnteriores.forEach(fila => fila.remove());

        if (Array.isArray(data) && data.length > 0) {
            data.forEach(usuario => {
                const telefono = usuario.usuario_telefono || '';
                const foto = usuario.usuario_foto || 'img/icons/perfil.png';

                const campos = [
                    usuario.usuario_id,
                    usuario.usuario_nombre,
                    usuario.usuario_apellido,
                    usuario.usuario_cedula,
                    usuario.usuario_correo,
                    telefono
                ];

                campos.forEach(valor => {
                    const celda = document.createElement('div');
                    celda.classList.add('row');
                    celda.textContent = valor;
                    contenedor.appendChild(celda);
                });

                // Celda de la foto
                const celdaFoto = document.createElement('div');
                celdaFoto.classList.add('row');
                const img = document.createElement('img');
                img.src = foto;
                img.alt = 'Foto';
                img.width = 40;
                celdaFoto.appendChild(img);
                contenedor.appendChild(celdaFoto);

                // Celda de acciones
                const celdaAcciones = document.createElement('div');
                celdaAcciones.classList.add('row');

                if (usuarioRol === "2") {
                    // Botón: Actualizar
                    const btnActualizar = document.createElement('div');
                    btnActualizar.classList.add('icon-action');
                    btnActualizar.setAttribute('data-modal-target', 'new_user');
                    btnActualizar.setAttribute('title', 'Actualizar');
                    const imgActualizar = document.createElement('img');
                    imgActualizar.src = 'img/icons/actualizar.png';
                    imgActualizar.alt = 'Actualizar';
                    btnActualizar.appendChild(imgActualizar);

                    // Botón: Info
                    const btnInfo = document.createElement('div');
                    btnInfo.classList.add('icon-action');
                    btnInfo.setAttribute('data-modal-target', 'info_usuario');
                    btnInfo.setAttribute('title', 'Info');
                    const imgInfo = document.createElement('img');
                    imgInfo.src = 'img/icons/info.png';
                    imgInfo.alt = 'Info';
                    btnInfo.appendChild(imgInfo);

                    // Botón: Eliminar
                    const btnEliminar = document.createElement('div');
                    btnEliminar.classList.add('icon-action');
                    btnEliminar.setAttribute('data-modal-target', 'eliminar_usuario');
                    btnEliminar.setAttribute('title', 'Eliminar');
                    const imgEliminar = document.createElement('img');
                    imgEliminar.src = 'img/icons/eliminar.png';
                    imgEliminar.alt = 'Eliminar';
                    btnEliminar.appendChild(imgEliminar);

                    celdaAcciones.appendChild(btnActualizar);
                    celdaAcciones.appendChild(btnInfo);
                    celdaAcciones.appendChild(btnEliminar);
                } else {
                    const span = document.createElement('span');
                    span.classList.add('text-empty');
                    span.textContent = 'Ninguno';
                    celdaAcciones.appendChild(span);
                }

                contenedor.appendChild(celdaAcciones);
            });
        } else {
            const vacio = document.createElement('div');
            vacio.classList.add('text-empty');
            vacio.textContent = 'No hay ningún registro';
            contenedor.appendChild(vacio);
        }
    })
    .catch(err => {
        console.error('Error AJAX:', err);
    });
}

cargarUsuarios();
