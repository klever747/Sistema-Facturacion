<?PHP

    session_start();
    if($_SESSION['rol']!=1 and $_SESSION['rol']!=3){
        header("Location: ../sistema/sistema.php");
    }
         include "../conexion/conexion.php";
    if(!empty($_POST)){
        
        if(empty($_POST['idcliente'])){
            header:'Location: listar_clientes.php';
            mysqli_close($conexion); 
        }
        $idcliente = $_POST['idcliente'];
      //  $query_delete = mysqli_query($conexion, "DELETE FROM usuario WHERE idusuario = $idusuario"); borrado fisico
        $query_delete = mysqli_query($conexion, "UPDATE cliente SET estatus = 0 WHERE idcliente = $idcliente"); //borrado logico
       mysqli_close($conexion);
        if($query_delete){
            header("Location: listar_clientes.php");
        }else{
            echo "Error al eliminar";
        }
        
    }
    
    //funciones para verificar que busque el id del cliente.

    if(empty($_REQUEST['id'])){
        header("Location: listar_usuario.php");
        mysqli_close($conexion);
    }else{
        $idcliente = $_REQUEST['id'];
        
        $query = mysqli_query($conexion, "SELECT * FROM cliente WHERE idcliente = $idcliente");
        mysqli_close($conexion);
        $result = mysqli_num_rows($query);
        if($result > 0){
            while ($data = mysqli_fetch_array($query)){
                $nit= $data['nit'];
                $nombre = $data['nombre'];
            }
        }else{
              header("Location: listar_clientes.php");
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
	<title>Eliminar Cliente</title>
</head>
<body>
        <?PHP
        include "../sistema/recursos/header.php";
        ?>
	<section id="container">
            <div class="data_delete">
                <h2>Estas seguro de eliminar el siguiente registro? </h2>
                <p>CI : <span><?PHP echo $nit; ?></span></p>
                <p>Nombre : <span><?PHP echo $nombre; ?></span></p>
                
                <form method="POST" action="">
                    <input type="hidden" name="idcliente" value="<?PHP echo $idcliente;?>">
                    <a href="listar_clientes_fiar.php" class="btn_cancel">Cancelar</a>
                    <input type="submit" value="Aceptar" class="btn_ok">
                </form> 
            </div>
	</section>
    <?PHP
        include "../sistema/recursos/footer.php";
    ?>
</body>
</html>