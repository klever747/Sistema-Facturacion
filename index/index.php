<?php
    $alert = '';
    session_start();
    if (!empty($_SESSION['active'])){
        header('location: ../sistema/sistema.php');
    }else{
    if(!empty($_POST)){
        if(empty($_POST['usuario'])|| empty($_POST['clave'])){
            $alert = 'Ingrese su usuario y su clave';
        }else{
            require_once '../conexion/conexion.php';
            $user = mysqli_real_escape_string($conexion,$_POST['usuario']); //quitara caracteres no aceptados por el sistema 
            $pass = md5(mysqli_real_escape_string($conexion,$_POST['clave'])); // md5 encripta la contraseña 
            
            
            $query = mysqli_query($conexion,"SELECT u.idusuario, u.nombre, u.correo,u.usuario, r.idrol,r.rol "
                    . "                     FROM usuario u "
                    . "                     INNER JOIN rol r "
                    . "                     ON u.rol = r.idrol "
                    . "                     WHERE u.usuario = '$user' AND "
                    ."                     u.clave ='$pass'");
            mysqli_close($conexion);

            $result = mysqli_num_rows($query);
            
            if($result > 0){
                $data = mysqli_fetch_array($query);
                session_start();
                $_SESSION['active'] = TRUE;
                $_SESSION['idUser'] = $data['idusuario'];
                $_SESSION['nombre'] = $data['nombre'];
                $_SESSION['email'] = $data['correo'];
                $_SESSION['user'] = $data['usuario'];
                $_SESSION['rol'] = $data['idrol'];  
                $_SESSION['rol_name'] = $data['rol'];  
                header('location: ../sistema/sistema.php');
            }else{
                $alert = 'El usuario o la clave son incorrectos';
                session_destroy();
                header('location: ../index');
            }
        }
    }
  }
?>
<!DOCTYPE html>
<!--
To change this license header, choose License Headers in Project Properties.
To change this template file, choose Tools | Templates
and open the template in the editor.
-->
<html>
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Login | Facturacion </title>
        <link rel="stylesheet" type="text/css" href="../css/style.css">
    </head>
    <body>
        <section id="container">
            <form action="" method="post">
                <h3>Iniciar Sesion</h3>
                <img src="../img/iniciar-sesion.png" alt="Login">
                <input type="text" name="usuario" placeholder="Usuario">
                <input type="password" name="clave" placeholder="contraseña">
                <div class="alert"><?php echo isset($alert)? $alert: ''; ?></div>
                <input type="submit" value="INGRESAR">
            </form>
        </section>
    </body>
</html>
