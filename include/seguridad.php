<?php
    if(empty($_SESSION["id"])){
    header("location: index.php?vista=login");
}
?>
