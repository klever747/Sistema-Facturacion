<?PHP
session_start();
if ($_SESSION['rol'] != 1 and $_SESSION['rol'] != 2) {
    header("Location: ../sistema/sistema.php");
}
include '../conexion/conexion.php';
if (!empty($_POST)) {
   
    $alert = '';
    if (empty($_POST['proveedor']) || empty($_POST['producto']) || empty($_POST['precio']) 
            || $_POST['precio']<= 0
            || empty($_POST['cantidad']) || $_POST['cantidad']<= 0) {
        $alert = '<p class= "msg_error">Todos los campos deben llenarse</p>';
    } else {
        $proveedor  = $_POST['proveedor'];
        $producto   = $_POST['producto'];
        $precio     = $_POST['precio'];
        $cantidad   = $_POST['cantidad'];
        $usuario_id = $_SESSION['idUser'];
        
        //busca los campos para trabajar con el archivo tipo file de la foto
        $foto        = $_FILES['foto']; //extraer lo que trae el array foto
        $nombre_foto = $foto['name'];
        $type        = $foto['type'];
        $url_temp    = $foto['tmp_name'];
        $imgProducto ='img_producto.png';
        
        
        //validar que el campo de foto vaya vacio
        
        if($nombre_foto!=''){
            $destino      = '../sistema/img/uploads/';
            $img_nombre   ='img_'.md5(date('d-m-Y H:m:s'));
            $imgProducto        = $img_nombre.'.jpg';
            $src                = $destino.$imgProducto;
        }
        $query_insert = mysqli_query($conexion, "INSERT INTO producto("
                . "                                                 proveedor,descripcion,"
                . "                                                 precio,existencia,usuario_id,foto)"
                . "       VALUES('$proveedor','$producto','$precio','$cantidad','$usuario_id','$imgProducto')");

        if ($query_insert) {
            if($nombre_foto!=''){
                move_uploaded_file($url_temp, $src); //almacena la data temporal del archivo y la mueve a $src
            }
            $alert = '<p class="msg_save">Producto guardado correctamente</p>';
        } else {
            $alert = '<p class="msg_error">Error al guardar el Producto</p>';
        }
    }



    //mysqli_close($conexion);
}
?>

<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
<?PHP
include "../sistema/recursos/scripts.php";
?>

        <title>Registro Producto</title>
    </head>
    <body>
<?PHP
include "../sistema/recursos/header.php";
?>
        <section id="container">
            <div class="form_register">
                <h1> <i class="fas fa-cubes"></i> Registro Producto</h1>
                <hr>
                <div class="alert"><?PHP echo isset($alert) ? $alert : ''; ?></div>
                <form action="" method="POST" enctype="multipart/form-data" >

                    <label for="proveedor">Nombre del Proveedor</label>
                    
                    <?PHP
                       $query_proveedor = mysqli_query($conexion,"SELECT codproveedor, proveedor "
                               . "                                FROM proveedor WHERE estatus = 1"
                               . "                                ORDER BY proveedor ASC");
                       $result_proveedor = mysqli_num_rows($query_proveedor);
                       mysqli_close($conexion);
                    ?>
                    <select name="proveedor" id="proveedor">
                        <?PHP
                            if($result_proveedor>0){
                                while ($proveedor = mysqli_fetch_array($query_proveedor)){
                                   
                        ?>
                        <option value="<?PHP echo $proveedor['codproveedor']; ?>"><?PHP echo $proveedor['proveedor']; ?></option>
                        <?PHP
                                }
                            }
                        ?>
                     
                    </select>


                    <label for="producto">Producto</label>
                    <input type="text" name="producto" id="producto" placeholder="Nombre del Producto">

                    <label for="precio">Precio</label>
                    <input type="text" name="precio" id="precio" placeholder="Precio del productp">

                    <label for="cantidad">Cantidad</label>
                    <input type="text"  name="cantidad" id="cantidad" placeholder="Cantidad del Producto">


                    <div class="photo">
                        <label for="foto">Foto</label>
                        <div class="prevPhoto">
                            <span class="delPhoto notBlock">X</span>
                            <label for="foto"></label>
                        </div>
                        <div class="upimg">
                            <input type="file" name="foto" id="foto">
                        </div>
                        <div id="form_alert"></div>
                    </div>
                    <br>
                    <button type="submit" class="btn_save"> <i class="far fa-save"></i> Guardar Producto</button>
                </form>
            </div>
        </section>
<?PHP
include "../sistema/recursos/footer.php";
?>
    </body>
</html>
