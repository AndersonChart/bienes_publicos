
<button><a href="index.php?vista=listar_categoria">Volver</a></button>
<h1>Registrar Nueva Categoria</h1>
<fieldset>
    <legend>Rellene los campos</legend>
    <form action="index.php?vista=registrar_categoria" method="POST">
        <div>
            <label for="nom">Nombre: </label>
            <input type="text" name="nombre" id="nom">
        </div>
        <div>
        <label for="des">Descripción: </label>
        <textarea name="descripcion" id="des" rows="1"></textarea>
        </div>
        <div>
            <button type="submit">Registrar</button>
        </div>
    </form>
</fieldset>