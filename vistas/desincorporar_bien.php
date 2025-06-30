<?php
require_once 'php/bien.php';

if (isset($_GET['id'])) {
    $bien = new bien();
    $bien->desincorporar($_GET['id']);
    header('Location: index.php?vista=listar_bien');
    exit;
}
?>