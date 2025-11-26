window.addEventListener('load', function () {
    const tablaResumen = $('#recepcionResumenTabla').DataTable({
        data: [],
        columns: [
            { data: 'codigo', title: 'Código' },
            { data: 'nombre', title: 'Nombre' },
            { data: 'cantidad', title: 'Cantidad' }
        ],
        ordering: true,
        scrollY: '500px',
        scrollCollapse: true,
        paging: false,
        searching: false,
        info: false,
        language: {
            emptyTable: "No hay artículos seleccionados"
        }
    });

    // Función global para actualizar la tabla de resumen
    window.actualizarResumenRecepcion = function (cantidadesIngresadas) {
        const resumen = Object.values(cantidadesIngresadas)
            .filter(item => item.cantidad && item.cantidad > 0)
            .map(item => ({
                codigo: item.codigo,
                nombre: item.nombre,
                cantidad: item.cantidad
            }));

        tablaResumen.clear();
        tablaResumen.rows.add(resumen);
        tablaResumen.draw();
    };
});

