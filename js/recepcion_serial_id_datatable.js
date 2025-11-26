window.addEventListener('load', function () {
    if (typeof $ !== 'function') {
        console.error('jQuery no está disponible');
        return;
    }

    // Inicializar tabla de ingreso de seriales
    const tablaSeriales = $('#recepcionSerialIdTabla').DataTable({
        scrollY: '300px',        // altura del scroll
        scrollCollapse: true,    // colapsa si hay pocas filas
        responsive: true,        //  que se adapte al modal
        paging: false,           //  sin paginación
        searching: false,        //  sin buscador
        info: false,             //  sin info de registros
        ordering: true,          //  permitir ordenar columnas
        ajax: null,              //  no carga datos vía AJAX, se llenará dinámicamente
        columns: [
            { data: 'numero', title: 'Número', orderable: true },
            { data: 'serial', title: 'Serial', orderable: false }
        ],
        language: {
            emptyTable: "No hay filas para mostrar"
        },
        // Ajustar columnas después de cada draw (opcional, aquí solo para asegurar consistencia)
        drawCallback: function (settings) {
            const api = this.api();
            const data = api.rows({ page: 'current' }).data();

            if (!data || data.length === 0) {
                console.warn('Tabla de seriales vacía');
                return;
            }

            // Aquí podrías añadir lógica futura de validación o ajuste visual
        }
    });

    // 👉 Ejemplo de cómo llenarla dinámicamente
    function cargarSeriales(articuloBuffer) {
        tablaSeriales.clear();

        const cantidad = parseInt(articuloBuffer.cantidad, 10) || 0;
        if (cantidad <= 0) {
            tablaSeriales.rows.add([{ numero: '', serial: 'Intente ingresar cantidad' }]).draw();
        } else {
            const filas = [];
            for (let i = 0; i < cantidad; i++) {
                filas.push({
                    numero: i + 1,
                    serial: `<input type="text" 
                                class="input_text input_serial" 
                                data-articulo="${articuloBuffer.articulo_id}" 
                                data-index="${i}" 
                                value="${articuloBuffer.seriales[i] || ''}">`
                });
            }
            tablaSeriales.rows.add(filas).draw();
        }
    }

    // Ejemplo de uso: cuando abras el modal
    // cargarSeriales(cantidadesIngresadas[articuloId]);
});
