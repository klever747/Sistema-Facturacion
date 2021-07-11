<?PHP

  session_start();
    if($_SESSION['rol']!=1){
        header("Location: ../sistema/sistema.php");
    }
 include "../conexion/conexion.php";
    if(!empty($_POST)){
        if($_POST['idusuario'] ==15){
            header("Location: listar_usuario.php");
            mysqli_close($conexion);
            exit; 
        }
        $idusuario = $_POST['idusuario'];
      //  $query_delete = mysqli_query($conexion, "DELETE FROM usuario WHERE idusuario = $idusuario"); borrado fisico
        $query_delete = mysqli_query($conexion, "UPDATE usuario SET estatus = 0 WHERE idusuario = $idusuario"); //borrado logico
       mysqli_close($conexion);
        if($query_delete){
            header("Location: listar_usuario.php");
        }else{
            echo "Error al eliminar";
        }
        
    }

    if(empty($_REQUEST['id']) || $_REQUEST['id']==15){
        header("Location: listar_usuario.php");
        mysqli_close($conexion);
    }else{
        $idusuario = $_REQUEST['id'];
        
        $query = mysqli_query($conexion, "SELECT u.nombre, u.usuario, r.rol FROM usuario u INNER JOIN rol r ON u.rol = r.idrol WHERE u.idusuario = $idusuario");
        mysqli_close($conexion);
        $result = mysqli_num_rows($query);
        if($result > 0){
            while ($data = mysqli_fetch_array($query)){
                $nombre = $data['nombre'];
                $usuario = $data['usuario'];
                $rol = $data['rol'];
            }
        }else{
              header("Location: listar_usuario.php");
        }
    }
?>

<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
        <?PHP
            include "../sistema/recursos/scripts.php";
        ?>
	<title>Eliminar Usuario</title>
</head>
<body>
        <?PHP
        include "../sistema/recursos/header.php";
        ?>
	<section id="container">
            <div class="data_delete">
                <i class="fas fa-user-times fa-7x" style="color: #e66262"></i>
                <h2>Estas seguro de eliminar el siguiente registro? </h2>
                <p>Nombre : <span><?PHP echo $nombre; ?></span></p>
                <p>Usuario : <span><?PHP echo $usuario; ?></span></p>
                <p>Rol : <span><?PHP echo $rol; ?></span></p>
                <form method="POST" action="">
                    <input type="hidden" name="idusuario" value="<?PHP echo $idusuario;?>">
                    <a href="listar_usuario.php" class="btn_cancel"><i class="fas fa-ban"></i> Cancelar</a>
                    
                    <button type="submit" class="btn_ok"> <i class="far fa-trash-alt"></i> Eliminar</button>
                </form> 
            </div>
	</section>
    <?PHP
        include "../sistema/recursos/footer.php";
    ?>
</body>
</html>