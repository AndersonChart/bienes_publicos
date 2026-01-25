<?php
echo password_hash("admin123", PASSWORD_DEFAULT);


// Crear nueva recepción
    public function crear($fecha, $descripcion, $articulos = []) {
        try {
            $this->pdo->beginTransaction();

            // Insertar cabecera del ajuste (tipo 1 = Entrada)
            $stmtCab = $this->pdo->prepare(
                "INSERT INTO ajuste (ajuste_fecha, ajuste_descripcion, ajuste_tipo, ajuste_estado)
                VALUES (?, ?, 1, 1)"
            );
            $stmtCab->execute([$fecha, $descripcion]);
            $ajuste_id = $this->pdo->lastInsertId();

            if (!empty($articulos)) {
                //  ahora incluimos articulo_serial_observacion en el insert y lo fijamos vacío
                $stmtSerial = $this->pdo->prepare(
                    "INSERT INTO articulo_serial (articulo_id, articulo_serial, articulo_serial_observacion, estado_id)
                    VALUES (?, ?, '', 1)"
                );

                $stmtAjusteArticulo = $this->pdo->prepare(
                    "INSERT INTO ajuste_articulo (articulo_serial_id, ajuste_id)
                    VALUES (?, ?)"
                );

                // Validar duplicados entre artículos en el mismo payload
                $todosSeriales = [];
                foreach ($articulos as $articulo) {
                    if (isset($articulo['seriales']) && is_array($articulo['seriales'])) {
                        foreach ($articulo['seriales'] as $s) {
                            $val = is_string($s) ? trim($s) : '';
                            if ($val !== '') {
                                if (in_array($val, $todosSeriales)) {
                                    throw new Exception("El serial {$val} está repetido en distintos artículos de la recepción.");
                                }
                                $todosSeriales[] = $val;
                            }
                        }
                    }
                }

                foreach ($articulos as $articulo) {
                    if (!isset($articulo['articulo_id'])) {
                        throw new Exception('articulo_id faltante en payload');
                    }
                    $articuloId = (int)$articulo['articulo_id'];
                    $cantidad   = isset($articulo['cantidad']) ? (int)$articulo['cantidad'] : 0;
                    $seriales   = isset($articulo['seriales']) && is_array($articulo['seriales'])
                        ? $articulo['seriales']
                        : [];

                    if ($cantidad > 0) {
                        $faltantes = max(0, $cantidad - count($seriales));
                        if ($faltantes > 0) {
                            $seriales = array_merge($seriales, array_fill(0, $faltantes, null));
                        }
                    }
                    if ($cantidad <= 0 && count($seriales) > 0) {
                        $cantidad = count($seriales);
                    }
                    if ($cantidad <= 0) {
                        continue;
                    }

                    foreach ($seriales as $serial) {
                        $valorSerial = (is_string($serial) && trim($serial) !== '') ? trim($serial) : '';

                        // Validar duplicados en BD (ignora estado 4)
                        if ($valorSerial !== '' && $this->existe_serial($valorSerial)) {
                            throw new Exception("El serial {$valorSerial} ya existe en el inventario.");
                        }

                        //  Insertamos siempre observación vacía
                        $stmtSerial->execute([$articuloId, $valorSerial]);
                        $serialId = $this->pdo->lastInsertId();

                        $stmtAjusteArticulo->execute([$serialId, $ajuste_id]);
                    }
                }
            }

            $this->pdo->commit();
            return $ajuste_id;

        } catch (Exception $e) {
            $this->pdo->rollBack();
            throw $e;
        }
    }

































/* Crear Recepcion */
document.addEventListener('DOMContentLoaded', function () {
    // Buffer global compartido por tabla de artículos, resumen y formulario
    window.cantidadesIngresadas = window.cantidadesIngresadas || {};
    const cantidadesIngresadas = window.cantidadesIngresadas;

    const formRecepcion = document.getElementById('form_nuevo_recepcion');
    const errorContainer = document.getElementById('error-container-recepcion');
    const btnNuevo = document.querySelector('[data-modal-target="new_recepcion"]');
    const modalRecepcion = document.querySelector('dialog[data-modal="new_recepcion"]');
    const fechaInput = document.getElementById('ajuste_fecha');

    // Abrir modal y limpiar formulario
    if (btnNuevo && modalRecepcion) {
        btnNuevo.addEventListener('click', () => {
            if (modalRecepcion.showModal) modalRecepcion.showModal();
            limpiarFormulario(formRecepcion);

            // Reasignar fecha de hoy después de limpiar
            if (fechaInput) {
                const hoy = new Date();
                const yyyy = hoy.getFullYear();
                const mm = String(hoy.getMonth() + 1).padStart(2, '0');
                const dd = String(hoy.getDate()).padStart(2, '0');
                const fechaHoy = `${yyyy}-${mm}-${dd}`;

                fechaInput.value = fechaHoy;
                fechaInput.setAttribute('max', fechaHoy);

                console.log("Fecha asignada automáticamente:", fechaHoy);
            }
        });
    }

    // Envío del formulario
    if (formRecepcion) {
        formRecepcion.addEventListener('submit', function (e) {
            e.preventDefault();
            console.log("Interceptado submit de recepción");

            const fecha = fechaInput ? fechaInput.value : '';
            const descripcion = document.getElementById('ajuste_descripcion').value.trim();

            // Resetear errores previos
            errorContainer.innerHTML = '';
            errorContainer.style.display = 'none';

            // Validación personalizada de fecha
            if (!fecha) {
                errorContainer.innerHTML = '<p>Debe rellenar la fecha del ajuste</p>';
                errorContainer.style.display = 'block';
                fechaInput.classList.add('input-error');
                fechaInput.focus();
                return;
            }
            const regexFecha = /^\d{4}-\d{2}-\d{2}$/;
            if (!regexFecha.test(fecha)) {
                errorContainer.innerHTML = '<p>La fecha debe tener formato YYYY-MM-DD</p>';
                errorContainer.style.display = 'block';
                fechaInput.classList.add('input-error');
                fechaInput.focus();
                return;
            }
            const hoy = new Date();
            const yyyy = hoy.getFullYear();
            const mm = String(hoy.getMonth() + 1).padStart(2, '0');
            const dd = String(hoy.getDate()).padStart(2, '0');
            const fechaHoy = `${yyyy}-${mm}-${dd}`;
            if (fecha > fechaHoy) {
                errorContainer.innerHTML = '<p>La fecha no puede ser posterior al día de hoy</p>';
                errorContainer.style.display = 'block';
                fechaInput.classList.add('input-error');
                fechaInput.focus();
                return;
            }

            // Artículos seleccionados desde cantidadesIngresadas (buffer global)
            const resumen = Object.values(cantidadesIngresadas)
                .filter(item => item.cantidad && item.cantidad > 0)
                .map(item => ({
                    articulo_id: item.articulo_id,
                    cantidad: item.cantidad,
                    seriales: item.seriales || Array(parseInt(item.cantidad, 10)).fill("")
                }));

            console.log("Resumen a enviar:", resumen);

            // Validación personalizada de artículos
            if (resumen.length === 0) {
                errorContainer.innerHTML = '<p>Debe ingresar al menos un artículo con cantidad</p>';
                errorContainer.style.display = 'block';
                return;
            }

            const formData = new FormData();
            formData.append('accion', 'crear');
            formData.append('ajuste_fecha', fecha);
            formData.append('ajuste_descripcion', descripcion);
            formData.append('ajuste_tipo', 1);
            formData.append('articulos', JSON.stringify(resumen));

            console.log("JSON articulos:", formData.get('articulos'));

            fetch('php/recepcion_ajax.php', {
                method: 'POST',
                body: formData
            })
            .then(res => res.json())
            .then(data => {
                console.log("Respuesta backend:", data);

                errorContainer.innerHTML = '';
                errorContainer.style.display = 'none';

                if (data.error) {
                    errorContainer.innerHTML = `<p>${data.mensaje}</p>`;
                    errorContainer.style.display = 'block';

                    if (data.campos && Array.isArray(data.campos)) {
                        data.campos.forEach((campo, index) => {
                            const input = formRecepcion.querySelector(`[name="${campo}"]`);
                            if (input) {
                                input.classList.add('input-error');
                                if (index === 0) input.focus();
                            }
                        });
                    }
                } else if (data.exito) {
                    mostrarModalExito(data.mensaje);

                    if (modalRecepcion && modalRecepcion.open) {
                        modalRecepcion.close();
                    }

                    limpiarFormulario(formRecepcion);

                    // Reasignar fecha de hoy también después de guardar
                    if (fechaInput) {
                        const hoy = new Date();
                        const yyyy = hoy.getFullYear();
                        const mm = String(hoy.getMonth() + 1).padStart(2, '0');
                        const dd = String(hoy.getDate()).padStart(2, '0');
                        const fechaHoy = `${yyyy}-${mm}-${dd}`;
                        fechaInput.value = fechaHoy;
                        fechaInput.setAttribute('max', fechaHoy);
                    }

                    if ($('#recepcionTabla').length) {
                        $('#recepcionTabla').DataTable().ajax.reload(null, false);
                    }
                }
            })
            .catch((err) => {
                console.error("Error en fetch:", err);
                errorContainer.innerHTML = 'Hubo un error con el servidor';
                errorContainer.style.display = 'block';
            });
        });
    }
});

// Buffer global: cantidades ingresadas por artículo (id => {cantidad, seriales, codigo, nombre})
window.cantidadesIngresadas = window.cantidadesIngresadas || {};

window.addEventListener('load', function () {
    if (typeof $ !== 'function') {
        console.error('jQuery no está disponible');
        return;
    }

    // Inicializar DataTable de seriales una sola vez
    const tablaSeriales = $('#recepcionSerialIdTabla').DataTable({
        scrollY: '300px',
        scrollCollapse: true,
        responsive: true,
        paging: false,
        searching: false,
        info: false,
        ordering: true,
        columns: [
        { data: 'numero', title: 'Número', orderable: true },
        { data: 'serial', title: 'Serial', orderable: false }
        ],
        language: {
        emptyTable: 'No hay filas para mostrar'
        }
    });

    // Utilidad: mostrar error
    function showError(msg) {
        const ec = document.getElementById('error-container-recepcion-serial');
        if (!ec) return;
        ec.textContent = msg || 'Ocurrió un error';
        ec.style.display = 'block';
    }

    // Utilidad: limpiar/ocultar error
    function clearError() {
        const ec = document.getElementById('error-container-recepcion-serial');
        if (!ec) return;
        ec.innerHTML = '';
        ec.style.display = 'none';
    }

    // Abre el modal y coloca encabezado + genera filas según buffer
    function abrirModalSeriales(dataBackend, articuloBuffer) {
        // Imagen
        const img = document.getElementById('serial_imagen_articulo');
        img.src =
        (dataBackend.articulo_imagen && dataBackend.articulo_imagen.trim() !== '')
            ? `${dataBackend.articulo_imagen}?t=${Date.now()}`
            : 'img/icons/articulo.png';

        // Código y nombre (acepta variantes, usa buffer como fallback)
        const codigo =
        dataBackend.articulo_codigo ||
        dataBackend.codigo ||
        articuloBuffer?.codigo ||
        '';
        const nombre =
        dataBackend.articulo_nombre ||
        dataBackend.nombre ||
        articuloBuffer?.nombre ||
        '';

        document.getElementById('serial_codigo_articulo').textContent = codigo;
        document.getElementById('serial_nombre_articulo').textContent = nombre;

        // Limpiar y ocultar error
        clearError();

        // Generar filas
        tablaSeriales.clear();
        const cantidad = parseInt(articuloBuffer?.cantidad, 10) || 0;

        if (cantidad <= 0) {
        tablaSeriales.rows.add([{ numero: '', serial: 'Ingrese una cantidad válida en la tabla principal' }]).draw();
        } else {
        // Asegurar array de seriales en buffer
        if (!Array.isArray(articuloBuffer.seriales) || articuloBuffer.seriales.length !== cantidad) {
            articuloBuffer.seriales = Array.from({ length: cantidad }, () => '');
        }

        const filas = Array.from({ length: cantidad }, (_, i) => ({
            numero: i + 1,
            serial: `<input type="text"
                            class="input_text input_serial"
                            data-articulo="${articuloBuffer.articulo_id}"
                            data-index="${i}"
                            value="${articuloBuffer.seriales[i] || ''}">`
        }));

        tablaSeriales.rows.add(filas).draw();
        }

        // Abrir modal
        document.querySelector('dialog[data-modal="seriales_articulo"]')?.showModal();
    }

    // Evento público para abrir modal desde la tabla principal
    // Llama: abrirSerialesDesdeTabla(articuloId)
    window.abrirSerialesDesdeTabla = function (articuloId) {
        if (!articuloId) return showError('ID de artículo inválido');

        const articuloBuffer = window.cantidadesIngresadas[articuloId];
        if (!articuloBuffer || (parseInt(articuloBuffer.cantidad, 10) || 0) <= 0) {
        return showError('Primero ingrese una cantidad válida en la tabla principal');
        }

        // Pedir datos completos al backend (imagen, código, nombre)
        fetch('php/articulo_ajax.php', {
        method: 'POST',
        body: new URLSearchParams({ accion: 'obtener_articulo', id: articuloId })
        })
        .then(res => res.json())
        .then(data => {
            if (data.exito && data.articulo) {
            abrirModalSeriales(data.articulo, articuloBuffer);
            } else {
            showError(data.mensaje || 'No se pudo obtener el artículo');
            }
        })
        .catch(err => {
            console.error('Error al obtener artículo:', err);
            showError('Error de comunicación con el servidor');
        });
    };

    // Guardar seriales desde el modal
    $('#form_recepcion_articulo_id').on('submit', function (e) {
        e.preventDefault();

        try {
        const inputs = document.querySelectorAll('#recepcionSerialIdTabla tbody .input_serial');

        if (!inputs || inputs.length === 0) {
            showError('No hay filas para guardar');
            return;
        }

        let articuloId = null;

        inputs.forEach(input => {
            const id = input.getAttribute('data-articulo');
            const idx = parseInt(input.getAttribute('data-index'), 10);
            const val = input.value.trim();

            if (!articuloId) articuloId = id;
            if (!window.cantidadesIngresadas[id]) return;

            window.cantidadesIngresadas[id].seriales[idx] = val;
        });

        // Cerrar modal
        document.querySelector('dialog[data-modal="seriales_articulo"]')?.close();
        } catch (ex) {
        console.error('Error guardando seriales:', ex);
        showError('No se pudieron guardar los seriales');
        }
    });
    });


    // Actualizar tabla al cambiar filtros
    document.addEventListener('change', function (e) {
        if ((e.target.matches('#categoria_filtro') || e.target.matches('#clasificacion_filtro'))
            && $('#recepcionArticuloTabla').length) {
            $('#recepcionArticuloTabla').DataTable().ajax.reload(null, false);
        }
});


















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
