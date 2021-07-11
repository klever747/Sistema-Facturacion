<?PHP

    session_start();
    if($_SESSION['rol']!=1 and $_SESSION['rol']!=3){
        header("Location: ../sistema/sistema.php");
    }
         include "../conexion/conexion.php";
    if(!empty($_POST)){
        
        if(empty($_POST['idproveedor'])){
            header:'Location: listar_proveedores.php';
            mysqli_close($conexion); 
        }
        $idproveedor = $_POST['idproveedor'];
      //  $query_delete = mysqli_query($conexion, "DELETE FROM usuario WHERE idusuario = $idusuario"); borrado fisico
        $query_delete = mysqli_query($conexion, "UPDATE proveedor SET estatus = 0 WHERE codproveedor = $idproveedor"); //borrado logico
       mysqli_close($conexion);
        if($query_delete){
            header("Location: listar_proveedores.php");
        }else{
            echo "Error al eliminar";
        }
        
    }
    
    //funciones para verificar que busque el id del cliente.

    if(empty($_REQUEST['id'])){
        header("Location: listar_proveedores.php");
        mysqli_close($conexion);
    }else{
        $idproveedor = $_REQUEST['id'];
        
        $query = mysqli_query($conexion, "SELECT * FROM proveedor WHERE codproveedor = $idproveedor");
        mysqli_close($conexion);
        $result = mysqli_num_rows($query);
        if($result > 0){
            while ($data = mysqli_fetch_array($query)){
                
                $proveedor = $data['proveedor'];
            }
        }else{
              header("Location: listar_proveedores.php");
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
	<title>Eliminar Proveedor</title>
</head>
<body>
        <?PHP
        include "../sistema/recursos/header.php";
        ?>
	<section id="container">
            <div class="data_delete">
                <i class="far fa-building fa-7x" style="color: #e66262"></i>
                <h2>Estas seguro de eliminar el siguiente registro? </h2>
                <p>Nombre del Proveedor : <span><?PHP echo $proveedor; ?></span></p>
                
                
                <form method="POST" action="">
                    <input type="hidden" name="idproveedor" value="<?PHP echo $idproveedor;?>">
                    <a href="listar_proveedores.php" class="btn_cancel">Cancelar</a>
                    <input type="submit" value="Aceptar" class="btn_ok">
                </form> 
            </div>
	</section>
    <?PHP
        include "../sistema/recursos/footer.php";
    ?>
</body>
</html>