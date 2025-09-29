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






function togglePassword() {
    const input = document.getElementById("password");
    input.type = input.type === "password" ? "text" : "password";
}

