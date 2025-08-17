<?php

    require_once '../util/functions/Helper.class.php';

    session_name("FaisanesMarket");
    session_start();
    
    if ( ! isset($_SESSION["s_usuario"])){
        Helper::mensaje("Para ingersar a esta página primero debe iniciar sesión", "e", "index.html", 5);
    }
    
    $nombreUsuario = $_SESSION["s_usuario"];
    $cargoUsuario  = $_SESSION["s_cargo"];
    
?>


<!DOCTYPE html>
<html>
    <head>
        <meta charset="UTF-8">
        <title></title>
    </head>
    <body>
        <h1>Bienvenido</h1>
        Usuario: <?php echo $nombreUsuario; ?>
        <br>
        Cargo: <?php echo $cargoUsuario; ?>
    </body>
</html>
