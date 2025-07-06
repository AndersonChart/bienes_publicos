//Ejemplo de validaciones de categoria (registrar/actualizar)

document.addEventListener('DOMContentLoaded', () => {
    const formulario = document.querySelector('.FormularioAjax');
    formulario.addEventListener('submit', function(e) {
        let errores = [];
        let contenedor = this.querySelector('.form-resultado');
        contenedor.innerHTML = "";

        // Validar nombre
        let nombre = this.nombre.value.trim();
        if (nombre === "") {
            errores.push("El nombre es obligatorio.");
        }
        if (nombre.length > 0 && nombre.length < 3) {
            errores.push("El nombre debe tener al menos 3 caracteres.");
        }
        if (nombre.length > 0 && !/^[a-zA-Z0-9\s]+$/.test(nombre)) {
            errores.push("El nombre solo puede tener letras, números y espacios.");
        }

        // Validar descripción
        let descripcion = this.descripcion.value.trim();
        if (descripcion === "") {
            errores.push("La descripción es obligatoria.");
        }

        // Mostrar errores si hay
        if (errores.length > 0) {
            e.preventDefault();
            contenedor.innerHTML = `<div class="alert alert-danger">${errores.join('<br>')}</div>`;
        }
    });
});