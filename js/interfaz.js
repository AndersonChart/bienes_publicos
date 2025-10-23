document.addEventListener('DOMContentLoaded', () => {
    const icons = document.querySelectorAll('.icon');
    const menus = document.querySelectorAll('.menu-content');

    icons.forEach(icon => {
        icon.addEventListener('click', () => {
        const target = icon.getAttribute('data-menu');
        icons.forEach(i => i.classList.remove('active'));
        menus.forEach(menu => menu.classList.remove('active'));
        icon.classList.add('active');
        const targetMenu = document.getElementById(target);
        if (targetMenu) {
            targetMenu.classList.add('active');
        }
        });
    });
});

//Función para mostrar contraseña
function togglePassword() {
    const input = document.getElementById("password");
    input.type = input.type === "password" ? "text" : "password";
}

//Generar ventanas modales
const botones = document.querySelectorAll('[data-modal-target]');
// Recorremos cada botón
botones.forEach(boton => {
    boton.addEventListener('click', () => {
        const modalID = boton.getAttribute('data-modal-target');
        const modal = document.querySelector(`[data-modal="${modalID}"]`);
        if (modal) {
        modal.showModal();
        }
    });
});

