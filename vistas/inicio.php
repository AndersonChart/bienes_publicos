<?php if (isset($_SESSION['id'])): ?>
<script>
    const idUsuarioSesion = <?php echo json_encode($_SESSION['id']); ?>;
</script>
<?php endif; ?>

<span class="welcome">
        Hola, <?php echo $_SESSION["nombre"] . " " . $_SESSION["apellido"]; ?>! Bienvenido
</span>