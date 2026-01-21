// proceso_asignacion_datatable.js
// Configuración completa para el proceso de asignación
// Requisitos: jQuery, DataTables, backend en php/asignacion_ajax.php

window.addEventListener('load', function () {
    'use strict';

    if (typeof $ !== 'function') {
        console.error('jQuery no está disponible');
        return;
    }

    // Interceptar clic en "Regresar"
document.querySelector('.basics-container .new_user').addEventListener('click', function (e) {
    e.preventDefault(); // evitar redirección inmediata

    const modal = document.querySelector('dialog[data-modal="confirmar_regresar-asignacion"]');
    if (modal?.showModal) modal.showModal();
});

// Acción al confirmar
document.getElementById('form_confirmar_regresar').addEventListener('submit', function (e) {
    e.preventDefault();
    // Redirigir a la lista de asignaciones
    window.location.href = "index.php?vista=listar_asignacion";
});

const asignacionId = $('#proceso_asignacion_id').val();
if (asignacionId) {
  $.post('php/asignacion_ajax.php', { accion: 'obtener_asignacion', id: asignacionId }, function (resp) {
    if (resp && resp.exito && resp.asignacion) {
      const a = resp.asignacion;

      // 1) Cargo
      $('#proceso_asignacion_cargo').val(a.cargo_id);

      // 2) Persona dependiente del cargo (espera a que cargarPersona termine)
      cargarPersona({
        cargo_id: a.cargo_id,
        scope: 'form',
        onComplete: () => {
          $('#proceso_asignacion_persona').val(a.persona_id);
        }
      });

      // 3) Área y descripción
      $('#proceso_asignacion_area').val(a.area_id);
      $('#proceso_asignacion_descripcion').val(a.asignacion_descripcion || '');
      // Nota: NO seteamos fechas aquí; tu inicializador viejo las controla.
    }
  }, 'json');
}

// Precargar artículos y seriales asociados (reasignación)
if (asignacionId) {
  $.post('php/asignacion_ajax.php', { accion: 'listar_articulos_por_asignacion', id: asignacionId }, function (resp) {
    if (resp && Array.isArray(resp.data)) {
      resp.data.forEach(item => {
        // Espera que backend ahora devuelva un array de objetos [{id, serial}]
        const seriales = Array.isArray(item.seriales)
          ? item.seriales.map(s => ({ id: Number(s.id), serial: s.serial }))
          : []; // si aún es CSV, parsea “id:serial” aquí

        estado.buffer[item.articulo_id] = {
          articulo_id: item.articulo_id,
          codigo: item.articulo_codigo,
          nombre: item.articulo_nombre,
          seriales
        };
        estado.bufferMeta[item.articulo_id] = {
          codigo: item.articulo_codigo,
          nombre: item.articulo_nombre
        };
      });
      actualizarResumenAsignacion();
    }
  }, 'json');
}



const selectCargoForm   = document.getElementById('proceso_asignacion_cargo');
const selectPersonaForm = document.getElementById('proceso_asignacion_persona');

// 1) Filtro dinámico: cargo → persona
if (selectCargoForm && selectPersonaForm) {
    selectCargoForm.addEventListener('change', () => {
        const cargoId = selectCargoForm.value || '';
        cargarPersona({
            cargo_id: cargoId || undefined,
            scope: 'form',
            onComplete: (lista) => {
                selectPersonaForm.innerHTML = '';
                const opt = document.createElement('option');
                opt.value = '';
                opt.disabled = true;
                opt.selected = true;
                opt.textContent = 'Seleccione una persona';
                selectPersonaForm.appendChild(opt);

                (lista || []).forEach(p => {
                    const op = document.createElement('option');
                    op.value = String(p.persona_id);
                    op.textContent = `${p.persona_nombre} ${p.persona_apellido}`;
                    selectPersonaForm.appendChild(op);
                });
            }
        });
    });

    // 2) Al seleccionar persona → rellenar cargo
    selectPersonaForm.addEventListener('change', () => {
        const personaId = selectPersonaForm.value;
        if (!personaId) return;

        fetch('php/personal_ajax.php', {
            method: 'POST',
            body: new URLSearchParams({ accion: 'obtener_persona', persona_id: personaId })
        })
        .then(res => res.json())
        .then(data => {
            if (data.exito && data.persona) {
                selectCargoForm.value = String(data.persona.cargo_id || '');
            }
        })
        .catch(() => {
            console.error('Error al obtener datos de la persona');
        });
    });
}


    // ------------------------------
    // Inicializar y sincronizar plazo con fechas
    // ------------------------------
    function inicializarPlazoFechas(opciones = {}) {
        const plazoInput = document.getElementById('proceso_asignacion_plazo');
        const fechaInicioInput = document.getElementById('proceso_asignacion_fecha');
        const fechaFinInput = document.getElementById('proceso_asignacion_fecha_fin');

        if (!plazoInput || !fechaInicioInput || !fechaFinInput) return;

        function formatDate(date) {
            const yyyy = date.getFullYear();
            const mm = String(date.getMonth() + 1).padStart(2, '0');
            const dd = String(date.getDate()).padStart(2, '0');
            return `${yyyy}-${mm}-${dd}`;
        }

        const hoy = new Date();
        const plazoDias = opciones.plazoDefault || 180;

        // Seteo inicial
        fechaInicioInput.value = formatDate(hoy);
        plazoInput.value = plazoDias;
        const fin = new Date(hoy);
        fin.setDate(hoy.getDate() + plazoDias);
        fechaFinInput.value = formatDate(fin);

        // Evitar duplicar listeners si la función se llama varias veces
        plazoInput._plazoSyncAttached ||= (() => {
            plazoInput.addEventListener('input', () => {
                const dias = parseInt(plazoInput.value, 10);
                if (!isNaN(dias) && fechaInicioInput.value) {
                    const inicio = new Date(fechaInicioInput.value);
                    const fin = new Date(inicio);
                    fin.setDate(inicio.getDate() + dias);
                    fechaFinInput.value = formatDate(fin);
                }
            });
            [fechaInicioInput, fechaFinInput].forEach(input => {
                input.addEventListener('change', () => {
                    if (fechaInicioInput.value && fechaFinInput.value) {
                        const inicio = new Date(fechaInicioInput.value);
                        const fin = new Date(fechaFinInput.value);
                        const diff = Math.round((fin - inicio) / (1000 * 60 * 60 * 24));
                        plazoInput.value = diff > 0 ? diff : '';
                    }
                });
            });
            return true;
        })();
    }

    // Garantía: inicializar justo cuando el modal se abra (después del limpiarFormulario global)
    const dialogAsignacion = document.querySelector('dialog[data-modal="modal_proceso_asignacion"]');
    if (dialogAsignacion) {
        const obs = new MutationObserver(muts => {
            for (const m of muts) {
                if (m.attributeName === 'open' && dialogAsignacion.open) {
                    // Inicializa después de que el modal esté realmente abierto
                    inicializarPlazoFechas({ plazoDefault: 180 });
                }
            }
        });
        obs.observe(dialogAsignacion, { attributes: true, attributeFilter: ['open'] });
    }

    const estado = {
        buffer: {},
        bufferMeta: {},
        articuloActivo: null
    };

    function showDialog(selector) {
        const dlg = document.querySelector(selector);
        if (dlg && typeof dlg.showModal === 'function') dlg.showModal();
    }
    function closeDialog(selector) {
        const dlg = document.querySelector(selector);
        if (dlg && typeof dlg.close === 'function') dlg.close();
    }
    function setError(containerId, message) {
        const el = document.getElementById(containerId);
        if (el) {
            el.textContent = message || '';
            el.style.display = message ? 'block' : 'none';
        }
    }
    function clearError(containerId) { setError(containerId, ''); }

    // ------------------------------
    // DataTable: Artículos disponibles
    // ------------------------------
    const tablaArticulos = $('#procesoAsignacionArticuloTabla').DataTable({
        scrollY: '500px',
        scrollCollapse: true,
        responsive: true,
        ajax: {
            url: 'php/asignacion_ajax.php',
            type: 'POST',
            data: function (d) {
                d.accion = 'listar_articulos_asignacion';
                d.categoria_id = document.getElementById('categoria_filtro')?.value || '';
                d.clasificacion_id = document.getElementById('clasificacion_filtro')?.value || '';
            },
            dataSrc: 'data'
        },
        columns: [
            { data: 'articulo_codigo', title: 'Código' },
            { data: 'articulo_nombre', title: 'Nombre' },
            { data: 'categoria_nombre', title: 'Categoría' },
            { data: 'clasificacion_nombre', title: 'Clasificación' },
            {
                data: 'articulo_imagen', title: 'Imagen',
                render: function (data) {
                    if (!data || String(data).trim() === '') return '';
                    return `<div class="imagen_celda_wrapper">
                                <img src="${data}?t=${Date.now()}" class="tabla_imagen" alt="Imagen">
                            </div>`;
                },
                orderable: true
            },
            {
                data: 'stock_disponible',
                title: 'Seriales activos',
                className: 'dt-center',
                render: function (data) {
                    const valor = Number(data) || 0;
                    return `<span class="stock-badge activos">${valor}</span>`;
                },
                orderable: true
            },
            {
                data: 'articulo_id',
                title: 'Seleccionados',
                className: 'dt-center',
                render: function (id) {
                    const valor = estado.buffer[id]?.seriales?.length || 0;
                    return `<span class="stock-badge asignados">${valor}</span>`;
                },
                orderable: true
            },
            {
                data: null,
                title: 'Acciones',
                render: function (row) {
                const id = row.articulo_id;
                const stock = Number(row.stock_disponible) || 0;
                const asignacionId = $('#proceso_asignacion_id').val();
                const seleccionados = estado.buffer[id]?.seriales?.length || 0;

                // Deshabilitar si no hay stock y tampoco hay seleccionados previos
                const disabled = stock <= 0 && seleccionados === 0;

                return `
                    <div class="acciones">
                    <div class="icon-action btn_ver_info" data-id="${id}" title="Info">
                        <img src="img/icons/info.png" alt="Info">
                    </div>
                    <button type="button"
                        class="new-proceso btn_seleccionar_seriales${disabled ? ' is-disabled' : ''}"
                        data-id="${id}" title="Seleccionar Seriales"
                        ${disabled ? 'disabled aria-disabled="true" tabindex="-1"' : ''}>
                        Seleccionar
                    </button>
                    </div>`;
                }
                ,
                orderable: false
            }
        ],
        paging: true,
        info: true,
        dom: '<"top"Bf>rt<"bottom"lpi><"clear">',
        buttons: ['excel', 'pdf'],
        language: {
            emptyTable: 'No se encuentran registros',
            search: 'Buscar:',
            lengthMenu: 'Mostrar _MENU_ registros',
            info: 'Mostrando _START_ a _END_ de _TOTAL_ registros',
            infoEmpty: 'Mostrando 0 a 0 de 0 registros',
            infoFiltered: '(filtrado de _MAX_ registros en total)',
            paginate: { next: 'Siguiente', previous: 'Anterior' },
            zeroRecords: 'No se encontraron coincidencias'
        },
        lengthMenu: [[5, 10, 15, 20, 30], [5, 10, 15, 20, 30]],
        pageLength: 15
    });

    $('#categoria_filtro, #clasificacion_filtro').on('change', function () {
        tablaArticulos.ajax.reload(null, false);
    });

    // ------------------------------
    // Resumen con child rows
    // ------------------------------
    const tablaResumen = $('#procesoAsignacionResumenTabla').DataTable({
        data: [],
        columns: [
            { data: 'codigo', title: 'Código' },
            { data: 'nombre', title: 'Nombre' },
            { data: 'cantidad', title: 'Cantidad' },
            {
                className: 'dt-control',
                orderable: false,
                data: null,
                defaultContent: '<span class="toggle-details">▶</span>',
                title: 'Seriales'
            }
        ],
        ordering: true,
        scrollY: '300px',
        scrollCollapse: true,
        paging: false,
        searching: false,
        info: false,
        language: { emptyTable: 'No se encuentran registros' }
    });

    function formatSerialesAsignacion(rowData) {
        const bufferItem = estado.buffer[rowData.articulo_id];
        if (!bufferItem || !Array.isArray(bufferItem.seriales) || bufferItem.seriales.length === 0) {
            return '<div class="seriales-empty">No se seleccionaron seriales</div>';
        }
        let html = '<div class="seriales-list"><ul>';
        bufferItem.seriales.forEach((s, i) => {
            html += `<li><strong>${i + 1}:</strong> ${s.serial}</li>`;
        });
        html += '</ul></div>';
        return html;
    }

    $('#procesoAsignacionResumenTabla tbody').on('click', 'td.dt-control', function () {
        const tr = $(this).closest('tr');
        const row = tablaResumen.row(tr);

        if (row.child.isShown()) {
            row.child.hide();
            tr.removeClass('shown');
            $(this).find('.toggle-details').text('▶');
        } else {
            row.child(formatSerialesAsignacion(row.data())).show();
            tr.addClass('shown');
            $(this).find('.toggle-details').text('▼');
        }
    });

    function actualizarResumenAsignacion() {
        const resumen = Object.values(estado.buffer)
            .filter(item => Array.isArray(item.seriales) && item.seriales.length > 0)
            .map(item => ({
                articulo_id: item.articulo_id,
                codigo: item.codigo,
                nombre: item.nombre,
                cantidad: item.seriales.length
            }));

        tablaResumen.clear();
        tablaResumen.rows.add(resumen);
        tablaResumen.draw();
    }

    // Abrir modal de info
    $('#procesoAsignacionArticuloTabla tbody').on('click', '.btn_ver_info', function () {
        const id = $(this).data('id');

        $.post('php/asignacion_ajax.php', { accion: 'obtener_articulo', id }, function (resp) {
            if (resp && resp.exito && resp.articulo) {
                const a = resp.articulo;
                $('#info_codigo').text(a.articulo_codigo ?? '');
                $('#info_nombre').text(a.articulo_nombre ?? '');
                $('#info_categoria').text(a.categoria_nombre ?? '');
                $('#info_clasificacion').text(a.clasificacion_nombre ?? '');
                $('#info_marca').text(a.marca_nombre ?? '');
                $('#info_modelo').text(a.articulo_modelo ?? '');
                $('#info_descripcion').text(a.articulo_descripcion ?? '');

                const imgEl = document.getElementById('info_imagen');
                if (imgEl) {
                    const src = (a.articulo_imagen && String(a.articulo_imagen).trim() !== '')
                        ? `${a.articulo_imagen}?t=${Date.now()}` : '';
                    imgEl.src = src;
                    imgEl.alt = 'Imagen del artículo';
                }

                if (Number(a.categoria_tipo) === 0) {
                    $('#li_info_marca').hide();
                    $('#li_info_modelo').hide();
                } else {
                    $('#li_info_marca').show();
                    $('#li_info_modelo').show();
                }

                showDialog('dialog[data-modal="info_articulo"]');
            }
        }, 'json');
    });

    // ------------------------------
    // Modal Seleccionar seriales
    // ------------------------------
    const tablaSeriales = $('#procesoAsignacionSerialTabla').DataTable({
        scrollY: '300px',
        scrollCollapse: true,
        responsive: true,
        paging: false,
        searching: false,
        info: false,
        ordering: true,
        ajax: null,
        columns: [
            { 
                data: null, 
                title: 'No.',
                orderable: true,
                className: 'select-checkbox',
                render: function (data, type, row, meta) {
                    return meta.row + 1;
                }
            },
            { data: 'serial', title: 'Serial', orderable: true },
            { data: 'observacion', title: 'Observación', orderable: true }
        ],
        select: { style: 'multi', selector: 'td.select-checkbox' },
        language: { emptyTable: 'No se encuentran registros' }
    });

    $('#procesoAsignacionArticuloTabla tbody').on('click', '.btn_seleccionar_seriales', function () {
            if ($(this).prop('disabled') || $(this).hasClass('is-disabled')) return;

        const articuloId = $(this).data('id');
        estado.articuloActivo = articuloId;

        const asignacionId = $('#proceso_asignacion_id').val();

        // Importante: enviar asignacion_id para incluir seriales ya asignados a esta asignación
        $.post('php/asignacion_ajax.php', { accion: 'leer_seriales_articulo', id: articuloId, asignacion_id: asignacionId }, function (resp) {
        if (resp && Array.isArray(resp.data)) {
            tablaSeriales.clear();
            tablaSeriales.rows.add(resp.data).draw();

            // Preselección por ID, gracias a Parche 2
            const seleccionadosPrevios = (estado.buffer[articuloId]?.seriales || []).map(s => s.id);
            tablaSeriales.rows().every(function () {
            const rowData = this.data();
            if (rowData.id && seleccionadosPrevios.includes(rowData.id)) {
                this.select();
            }
            });
        } else {
            tablaSeriales.clear().draw();
        }
        }, 'json'); 

        $.post('php/asignacion_ajax.php', { accion: 'obtener_articulo', id: articuloId }, function (resp) {
            if (resp && resp.exito && resp.articulo) {
                $('#serial_codigo_articulo').text(resp.articulo.articulo_codigo || '');
                $('#serial_nombre_articulo').text(resp.articulo.articulo_nombre || '');
                const imgEl = document.getElementById('serial_imagen_articulo');
                if (imgEl) {
                    imgEl.src = resp.articulo.articulo_imagen ? resp.articulo.articulo_imagen + '?t=' + Date.now() : 'img/icons/articulo.png';
                    imgEl.alt = 'Imagen del artículo';
                }
                estado.bufferMeta[articuloId] = {
                    codigo: resp.articulo.articulo_codigo || String(articuloId),
                    nombre: resp.articulo.articulo_nombre || ''
                };
            }
        }, 'json');

        clearError('error-container-proceso-asignacion-serial');
        showDialog('dialog[data-modal="seriales_articulo"]');
    });

    // ------------------------------
    // Formulario de selección de seriales
    // ------------------------------
    $('#form_proceso_asignacion_seriales').on('submit', function (e) {
        e.preventDefault();

        const seleccionados = tablaSeriales.rows({ selected: true }).data().toArray();
        const meta = estado.bufferMeta[estado.articuloActivo] || { codigo: String(estado.articuloActivo), nombre: '' };

        estado.buffer[estado.articuloActivo] = {
            articulo_id: estado.articuloActivo,
            codigo: meta.codigo,
            nombre: meta.nombre,
            // Guardar objetos con id y serial
            seriales: seleccionados.map(s => ({ id: s.id, serial: s.serial }))
        };

        tablaArticulos.ajax.reload(null, false);
        actualizarResumenAsignacion();

        document.getElementById('success-message').textContent = 'Selección exitosa';
        showDialog('dialog[data-modal="success"]');
        closeDialog('dialog[data-modal="seriales_articulo"]');
    });



    // ------------------------------
    // Abrir formulario de asignación
    // ------------------------------
    document.querySelectorAll('[data-modal-target="modal_proceso_asignacion"]').forEach(el => {
        el.addEventListener('click', function () {
            // showDialog lo maneja el listener global; aquí solo dejamos que el observer inicialice
            actualizarResumenAsignacion();
            clearError('error-container-proceso-asignacion');
        });
    });

    // ------------------------------
    // Guardar asignación (delega validación al backend)
    // ------------------------------
    $('#form_proceso_asignacion').on('submit', function (e) {
        e.preventDefault();
        clearError('error-container-proceso-asignacion');

        const asignacionId = $('#proceso_asignacion_id').val();

        const payload = {
            accion: asignacionId ? 'reasignar' : 'crear',
            id: asignacionId || '',
            area_id: $('#proceso_asignacion_area').val(),
            persona_id: $('#proceso_asignacion_persona').val(),
            cargo_id: $('#proceso_asignacion_cargo').val(),
            asignacion_fecha: $('#proceso_asignacion_fecha').val(),
            asignacion_fecha_fin: $('#proceso_asignacion_fecha_fin').val(),
            asignacion_descripcion: $('#proceso_asignacion_descripcion').val(),
            seriales: JSON.stringify(
                Object.values(estado.buffer).flatMap(item => item.seriales.map(s => s.id))
            )
            };


        $.post('php/asignacion_ajax.php', payload, function (resp) {
            if (resp && resp.error) {
                setError('error-container-proceso-asignacion', resp.mensaje);

                if (Array.isArray(resp.campos)) {
                    resp.campos.forEach((campo, index) => {
                        const input = document.querySelector(`[name="${campo}"]`);
                        if (input) {
                            input.classList.add('input-error');
                            if (index === 0) input.focus();
                        }
                    });
                }
                return;
            }

            if (resp && resp.exito) {
                estado.buffer = {};
                estado.bufferMeta = {};
                estado.articuloActivo = null;
                actualizarResumenAsignacion();

                closeDialog('dialog[data-modal="modal_proceso_asignacion"]');
                document.getElementById('success-message').textContent =
                    resp.mensaje || 'Asignación registrada correctamente';
                showDialog('dialog[data-modal="success"]');

                tablaArticulos.ajax.reload(null, false);

                setTimeout(() => {
                    window.location.href = "index.php?vista=listar_asignacion";
                }, 1000);
            } else {
                setError('error-container-proceso-asignacion', 'Respuesta inesperada del servidor.');
            }
        }, 'json').fail(xhr => {
            setError('error-container-proceso-asignacion', 'Error de conexión con el servidor.');
            console.error('Respuesta del servidor:', xhr.responseText);
        });
    });


    // ------------------------------
    // Cierre de modales
    // ------------------------------
    $('#close-success-proceso-asignacion').on('click', function () {
        closeDialog('dialog[data-modal="success"]');
    });

    $('#close-error').on('click', function () {
        closeDialog('dialog[data-modal="error"]');
    });
});
