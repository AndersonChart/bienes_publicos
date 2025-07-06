<button><a href="index.php?vista=listar_marca">Volver</a></button>
<h1>Registrar Nueva Marca</h1>
<fieldset>
    <legend>Rellene los campos</legend>
    <form action="index.php?vista=registrar_marca" method="POST" class="FormularioAjax">
        <div>
            <label for="nom">Nombre: </label>
            <input type="text" name="nombre" id="nom">
        </div>
            <label for="img">Imagen: </label>
            <input type="file" id="img">
        </div>
        <div>
            <button type="submit">Registrar</button>
        </div>
        <div class="form-resultado"></div>
    </form>
</fieldset>