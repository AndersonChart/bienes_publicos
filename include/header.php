<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/datatables.css">
    <link rel="stylesheet" href="css/buttons.dataTables.min.css">
    <link rel="stylesheet" href="css/responsive.dataTables.min.css">
    <link rel="icon" type="image/png" href="img/logo.png">
    <title>UNES - Bienes Públicos</title>
</head>
<body>
<?php
if ($vista !== 'login') {
    echo '<div class="inicio-background">';
    include("include/banner.php");
    include("include/navbar.php");
    echo '<div class="content">';
}
?>