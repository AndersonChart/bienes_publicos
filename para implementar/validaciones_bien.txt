document.addEventListener('DOMContentLoaded', () => {
    const formulario = document.querySelector('form[action*="registrar_bien"]');
    if(!formulario) return;
    formulario.addEventListener('submit', function(e) {
        let errores = [];
        let contenedor = this.querySelector('.form-resultado');
        if(!contenedor){
            contenedor = document.createElement("div");
            contenedor.className = "form-resultado";
            this.appendChild(contenedor);
        }
        contenedor.innerHTML = "";

        // Número de serie: obligatorio, mínimo 2 caracteres, solo letras y números
        let serie = this.serie.value.trim();
        if (serie === "") {
            errores.push("El número de serie es obligatorio.");
        }
        if (serie.length > 0 && serie.length < 2) {
            errores.push("El número de serie debe tener al menos 2 caracteres.");
        }
        if (serie.length > 0 && !/^[a-zA-Z0-9\-]+$/.test(serie)) {
            errores.push("El número de serie solo puede tener letras, números y guiones.");
        }

        // Nombre: obligatorio, mínimo 3 caracteres, sin caracteres especiales
        let nombre = this.nombre.value.trim();
        if (nombre === "") {
            errores.push("El nombre del bien es obligatorio.");
        }
        if (nombre.length > 0 && nombre.length < 3) {
            errores.push("El nombre debe tener al menos 3 caracteres.");
        }
        if (nombre.length > 0 && !/^[a-zA-Z0-9\s]+$/.test(nombre)) {
            errores.push("El nombre solo puede tener letras, números y espacios.");
        }

        // Descripción: obligatorio, mínimo 5 caracteres
        let descripcion = this.descripcion.value.trim();
        if (descripcion === "") {
            errores.push("La descripción es obligatoria.");
        }
        if (descripcion.length > 0 && descripcion.length < 5) {
            errores.push("La descripción debe tener al menos 5 caracteres.");
        }

        // Categoría: obligatorio
        let categoria = this.categoria.value;
        if (!categoria) {
            errores.push("Debes seleccionar una categoría.");
        }

        // Fecha de adquisición: obligatorio, no en el futuro
        let fecha = this.add.value;
        if (fecha === "") {
            errores.push("La fecha de adquisición es obligatoria.");
        } else {
            let hoy = new Date();
            let fechaIngresada = new Date(fecha);
            if (fechaIngresada > hoy) {
                errores.push("La fecha de adquisición no puede ser futura.");
            }
        }

        // Marca: obligatorio
        let marca = this.marca.value;
        if (!marca) {
            errores.push("Debes seleccionar una marca.");
        }

        // Modelo: obligatorio
        let modelo = this.modelo.value;
        if (!modelo) {
            errores.push("Debes seleccionar un modelo.");
        }

        // Estado: obligatorio
        let estado = this.estado.value;
        if (!estado) {
            errores.push("Debes seleccionar un estado.");
        }

        // Imagen: opcional, si hay debe ser imagen
        let imagen = this.querySelector('input[type="file"][name="imagen"]');
        if (imagen && imagen.files.length > 0) {
            let file = imagen.files[0];
            if (!file.type.match(/^image\//)) {
                errores.push("La imagen debe ser un archivo de imagen válido.");
            }
        }

        // Acta: opcional, si hay debe ser PDF
        let acta = this.querySelector('input[type="file"][name="acta"]');
        if (acta && acta.files.length > 0) {
            let file = acta.files[0];
            if (file.type !== "application/pdf") {
                errores.push("El acta debe ser un archivo PDF.");
            }
        }

        if (errores.length > 0) {
            e.preventDefault();
            contenedor.innerHTML = `<div class="alert alert-danger">${errores.join('<br>')}</div>`;
        }
    });
});