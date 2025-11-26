window.addEventListener('load', function () {
    if (typeof $ !== 'function') {
        console.error('jQuery no está disponible');
        return;
    }

    // Buffer para mantener cantidades ingresadas
    const cantidadesIngresadas = {};

    const tablaRecepcion = $('#recepcionArticuloTabla').DataTable({
        scrollY: '500px',
        scrollCollapse: true,
        responsive: true,
        ajax: {
            url: 'php/recepcion_ajax.php',
            type: 'POST',
            data: function (d) {
                d.accion = 'listar_articulos_recepcion';
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
                    if (!data || data.trim() === '') return '';
                    return `<div class="imagen_celda_wrapper">
                                <img src="${data}?t=${new Date().getTime()}" class="tabla_imagen">
                            </div>`;
                },
                orderable: false
            },
            { 
                data: null, title: 'Cantidad',
                render: function (row) {
                    const valor = cantidadesIngresadas[row.articulo_id]?.cantidad || '';
                    return `<input type="number" 
                                class="input_text input_cantidad" 
                                min="0" 
                                data-id="${row.articulo_id}" 
                                data-codigo="${row.articulo_codigo}" 
                                data-nombre="${row.articulo_nombre}" 
                                value="${valor}" 
                                style="width:80px">`;
                },
                orderable: false
            },
            { 
                data: null, title: 'Acciones',
                render: function (row) {
                    return `<div class="acciones">
                                <div class="icon-action btn_ver_info" 
                                    data-modal-target="info_articulo" 
                                    data-id="${row.articulo_id}" 
                                    title="Info">
                                    <img src="img/icons/info.png" alt="Info">
                                </div>
                                <div class="new-proceso btn_agregar_seriales" 
                                    data-modal-target="seriales_articulo" 
                                    data-id="${row.articulo_id}" 
                                    title="Añadir Seriales">
                                    Añadir Seriales
                                </div>
                            </div>`;
                },
                orderable: false
            }
        ],
        paging: true,
        info: true,
        dom: '<"top"Bf>rt<"bottom"lpi><"clear">',
        buttons: ['excel', 'pdf'],
        language: { /* ... tu config de idioma ... */ },
        lengthMenu: [[5, 10, 15, 20, 30], [5, 10, 15, 20, 30]],
        pageLength: 15
    });

    // Evento: captura cambios en inputs de cantidad
    $('#recepcionArticuloTabla tbody').on('input', '.input_cantidad', function () {
        const id = $(this).data('id');
        const codigo = $(this).data('codigo');
        const nombre = $(this).data('nombre');
        const cantidad = parseInt($(this).val(), 10);

        // Inicializar seriales vacíos según la cantidad
        cantidadesIngresadas[id] = { 
            articulo_id: id,
            codigo,
            nombre,
            cantidad,
            seriales: Array(cantidad).fill("")
        };

        // Actualizar tabla de resumen
        if (typeof actualizarResumenRecepcion === 'function') {
            actualizarResumenRecepcion(cantidadesIngresadas);
        }
    });

    // Re-render: volver a poner valores guardados
    tablaRecepcion.on('draw', function () {
        $('#recepcionArticuloTabla tbody .input_cantidad').each(function () {
            const id = $(this).data('id');
            if (cantidadesIngresadas[id]) {
                $(this).val(cantidadesIngresadas[id].cantidad);
            }
        });
    });

    // Evento: abrir modal de seriales
    $('#recepcionArticuloTabla tbody').on('click', '.btn_agregar_seriales', function () {
        const articuloId = $(this).data('id');
        const articulo = cantidadesIngresadas[articuloId];

        if (!articulo || articulo.cantidad <= 0) {
            alert("Primero ingrese una cantidad válida.");
            return;
        }

        // Generar inputs en la tabla de seriales del modal
        const tablaSeriales = $('#recepcionSerialIdTabla').DataTable();
        tablaSeriales.clear();

        const filas = [];
        for (let i = 0; i < articulo.cantidad; i++) {
            filas.push({
                numero: i + 1,
                serial: `<input type="text" 
                            class="input_text input_serial" 
                            data-articulo="${articuloId}" 
                            data-index="${i}" 
                            value="${articulo.seriales[i] || ''}">`
            });
        }

        tablaSeriales.rows.add(filas).draw();

        // Abrir modal
        document.querySelector('dialog[data-modal="seriales_articulo"]').showModal();
    });

    // Guardar seriales desde el modal
    $('#form_recepcion_articulo_id').on('submit', function (e) {
        e.preventDefault();

        $('#recepcionSerialIdTabla tbody .input_serial').each(function () {
            const articuloId = $(this).data('articulo');
            const index = $(this).data('index');
            const valor = $(this).val().trim();

            if (cantidadesIngresadas[articuloId]) {
                cantidadesIngresadas[articuloId].seriales[index] = valor;
            }
        });

        // Cerrar modal
        document.querySelector('dialog[data-modal="seriales_articulo"]').close();
    });
});
