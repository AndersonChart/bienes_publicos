window.addEventListener('load', function () {
    if (typeof $ !== 'function') {
        console.error('jQuery no está disponible');
        return;
    }

    let estadoActual = 1;
    const toggleBtn = document.getElementById('toggleEstado');

    if (toggleBtn) {
        toggleBtn.textContent = 'Deshabilitados';
        toggleBtn.classList.add('estado-rojo');

        toggleBtn.addEventListener('click', () => {
            estadoActual = estadoActual === 0 ? 1 : 0;

            if (estadoActual === 0) {
                toggleBtn.textContent = 'Habilitados';
                toggleBtn.classList.remove('estado-rojo');
                toggleBtn.classList.add('estado-verde');
            } else {
                toggleBtn.textContent = 'Deshabilitados';
                toggleBtn.classList.remove('estado-verde');
                toggleBtn.classList.add('estado-rojo');
            }
            tabla.ajax.reload(null, false);
        });
    }

    const tabla = $('#bienTipoTabla').DataTable({
        scrollY: '500px',
        scrollCollapse: true,
        responsive: true,
        ajax: {
            url: 'php/bien_tipo_ajax.php',
            type: 'POST',
            data: function (d) {
                d.accion = 'leer_todos';
                d.estado = estadoActual;
                d.categoria_id = document.getElementById('categoria_filtro')?.value || '';
                d.clasificacion_id = document.getElementById('clasificacion_filtro')?.value || '';
            },
            dataSrc: 'data',
            error: function (xhr, status, error) {
                console.error('Error AJAX:', error);
                console.log('Respuesta del servidor:', xhr.responseText);
            }
        },
        columns: [
            { data: 'bien_tipo_codigo', title: 'Código' },
            { data: 'bien_nombre', title: 'Nombre' },
            { data: 'categoria_nombre', title: 'Categoría' },
            { data: 'clasificacion_nombre', title: 'Clasificación' },
            { data: 'bien_modelo', title: 'Modelo' }, // índice 4
            { data: 'marca_nombre', title: 'Marca' }, // índice 5
            { data: 'bien_imagen', title: 'Imagen',
                render: function (data) {
                    if (!data || data.trim() === '') return '';
                    return `
                        <div class="imagen_celda_wrapper">
                            <img src="${data}?t=${new Date().getTime()}" class="tabla_imagen">
                        </div>
                    `;
                },
                orderable: false
            },
            { data: null, title: 'Acciones',
                render: function (row) {
                    const estado = parseInt(row.bien_estado);
                    let botones = '';
                    if (estado === 1) {
                        botones += `
                            <div class="acciones">
                                <div class="icon-action" data-modal-target="new_bien_tipo" title="Actualizar">
                                    <img src="img/icons/actualizar.png" alt="Actualizar">
                                </div>
                                <div class="icon-action btn_ver_info" data-modal-target="info_bien_tipo" data-id="${row.bien_tipo_id}" title="Info">
                                    <img src="img/icons/info.png" alt="Info">
                                </div>
                                <div class="icon-action btn_eliminar" data-id="${row.bien_tipo_id}" title="Eliminar">
                                    <img src="img/icons/eliminar.png" alt="Eliminar">
                                </div>
                            </div>
                        `;
                    } else {
                        botones += `
                            <div class="acciones">
                                <div class="icon-action btn_ver_info" data-modal-target="info_bien_tipo" data-id="${row.bien_tipo_id}" title="Info">
                                    <img src="img/icons/info.png" alt="Info">
                                </div>
                                <div class="icon-action btn_recuperar" data-id="${row.bien_tipo_id}" title="Recuperar">
                                    <img src="img/icons/recuperar.png" alt="Recuperar">
                                </div>
                            </div>
                        `;
                    }
                    return botones;
                },
                orderable: false
            }
        ],
        paging: true,
        info: true,
        dom: '<"top"Bf>rt<"bottom"lpi><"clear">',
        buttons: ['excel', 'pdf'],
        language: {
            search: "Buscar:",
            lengthMenu: "Mostrar _MENU_ registros",
            zeroRecords: "No se encontraron registros coincidentes",
            emptyTable: "No hay ningún registro",
            info: "Mostrando _START_ a _END_ de _TOTAL_ registros",
            infoEmpty: "Mostrando 0 a 0 de 0 registros",
            infoFiltered: "(filtrado de _MAX_ registros totales)",
            paginate: {
                previous: "◀",
                next: "▶"
            }
        },
        lengthMenu: [[5, 10, 15, 20, 30], [5, 10, 15, 20, 30]],
        pageLength: 15,

        // Ajustar columnas después de cada draw
        drawCallback: function(settings) {
  const api = this.api();
  const data = api.rows({ page: 'current' }).data();

  if (!data || data.length === 0) return;

  // Tipos vistos en la página actual, calculados con respaldo
  const tipos = [];
  for (let i = 0; i < data.length; i++) {
    const row = data[i];
    // Preferir el campo de la fila; si no existe, usar el mapa por categoria_id
    const tipoFila = row.categoria_tipo;
    const tipo = (tipoFila !== undefined && tipoFila !== null)
      ? Number(tipoFila)
      : Number(categoriaTipoMap[String(row.categoria_id)]);
    if (!Number.isNaN(tipo)) tipos.push(tipo);
  }

  if (tipos.length === 0) {
    // Sin información de tipo: mostrar columnas para no ocultar datos por error
    api.column(4).visible(true);
    api.column(5).visible(true);
    console.warn('Sin categoria_tipo en filas ni mapa: columnas visibles por seguridad');
    return;
  }

  const todasBasicas    = tipos.every(t => t === 0);
  const todasCompletas  = tipos.every(t => t === 1);

  if (todasBasicas) {
    api.column(4).visible(false); // Modelo
    api.column(5).visible(false); // Marca
  } else if (todasCompletas) {
    api.column(4).visible(true);
    api.column(5).visible(true);
  } else {
    // Mezcla: mostrar columnas; celdas vacías se verán en las filas básicas
    api.column(4).visible(true);
    api.column(5).visible(true);
  }
        }

    });

    // Eventos de acción
    $('#bienTipoTabla tbody').on('click', '.icon-action[title="Actualizar"]', function () {
        const fila = tabla.row($(this).closest('tr')).data();
        if (fila && fila.bien_tipo_id) {
            abrirFormularioEdicionBien(fila.bien_tipo_id);
        }
    });

    $('#bienTipoTabla tbody').on('click', '.btn_ver_info', function () {
        const id = $(this).data('id');
        if (!id) return;

        fetch('php/bien_tipo_ajax.php', {
            method: 'POST',
            body: new URLSearchParams({ accion: 'obtener_bien', id })
        })
        .then(res => res.json())
        .then(data => {
            if (data.exito && data.bien_tipo) {
                mostrarInfoBien(data.bien_tipo);
            }
        });
    });

        $('#bienTipoTabla tbody').on('click', '.btn_eliminar', function () {
        const id = $(this).data('id');
        if (!id) return;

        fetch('php/bien_tipo_ajax.php', {
            method: 'POST',
            body: new URLSearchParams({ accion: 'obtener_bien', id })
        })
        .then(res => res.json())
        .then(data => {
            if (data.exito && data.bien_tipo) {
                mostrarConfirmacionBien(data.bien_tipo, 'eliminar');
            }
        });
    });

    $('#bienTipoTabla tbody').on('click', '.btn_recuperar', function () {
        const id = $(this).data('id');
        if (!id) return;

        fetch('php/bien_tipo_ajax.php', {
            method: 'POST',
            body: new URLSearchParams({ accion: 'obtener_bien', id })
        })
        .then(res => res.json())
        .then(data => {
            if (data.exito && data.bien_tipo) {
                mostrarConfirmacionBien(data.bien_tipo, 'recuperar');
            }
        });
    });
});
