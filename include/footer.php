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
