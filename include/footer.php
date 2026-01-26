<script>
    window.addEventListener('load', () => {
        const loader = document.getElementById('loader-wrapper');
        
        // Tiempo que el spinner se queda visible después de cargar (0.1 seg)
        setTimeout(() => {
            loader.classList.add('loader-hidden');

            // Este tiempo DEBE coincidir con el tiempo de transition del CSS
            // Si en CSS pusiste 0.8s, aquí pones 800
            setTimeout(() => { 
                loader.style.display = 'none'; 
            }, 800); 
            
        }, 100);
    });
</script>
<script src="js/jquery.min.js"></script>
<script src="js/datatables.min.js"></script> 
<script src="js/dataTables.buttons.min.js"></script>
<script src="js/dataTables.select.min.js"></script>
<script src="js/dataTables.responsive.min.js"></script>
<script src="js/dataTables.scroller.min.js"></script>
<script src="js/select2.min.js"></script>
<script>
    const idUsuarioSesion = <?= $_SESSION['id'] ?>;
</script>
<script src="js/interfaz.js"></script>

<?php
if ($vista !== 'login') {
  echo '</div>'; // cierre de .content
  echo '</div>'; // cierre de .inicio-background
}


?>
</body>
</html>