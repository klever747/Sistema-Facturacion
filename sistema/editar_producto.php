<?PHP
session_start();
if ($_SESSION['rol'] != 1 and $_SESSION['rol'] != 2) {
    header("Location: ../sistema/sistema.php");
}
include '../conexion/conexion.php';
if (!empty($_POST)) {
   
    $alert = '';
    if (empty($_POST['proveedor']) || empty($_POST['producto']) || empty($_POST['precio']) || empty($_POST['id'])
            || empty($_POST['foto_actual']) || empty($_POST['foto_remove'])) {
        $alert = '<p class= "msg_error">Todos los campos deben llenarse</p>';
    } else {
        $codproducto = $_POST['id'];
        $proveedor  = $_POST['proveedor'];
        $producto   = $_POST['producto'];
        $precio     = $_POST['precio'];
        $imgProducto   = $_POST['foto_actual'];
        $imgRemove = $_POST['foto_remove'];
        
        //busca los campos para trabajar con el archivo tipo file de la foto
        $foto        = $_FILES['foto']; //extraer lo que trae el array foto
        $nombre_foto = $foto['name'];
        $type        = $foto['type'];
        $url_temp    = $foto['tmp_name'];
        
        $upd = '';
        
        
        //validar que el campo de foto vaya vacio
        
        if($nombre_foto!=''){
            $destino      = '../sistema/img/uploads/';
            $img_nombre   ='img_'.md5(date('d-m-Y H:m:s'));
            $imgProducto        = $img_nombre.'.jpg';
            $src                = $destino.$imgProducto;
        } else {
            if ($_POST['foto_actual'] != $_POST['foto_remove']){
                $imgProducto = 'img_producto.png';
            }
        }
        $query_update = mysqli_query($conexion, "UPDATE producto SET "
                . "                               descripcion = '$producto',"
                . "                               proveedor = $proveedor,"
                . "                               precio = $precio,"
                . "                               foto = '$imgProducto'"
                . "                               WHERE codproducto = $codproducto");

        if ($query_update) {
            //verificar que la foto se este actualizando 
            if(($nombre_foto != '' && ($_POST['foto_actual'] != 'img_¨producto.png')) || ($_POST['foto_actual'] != $_POST['foto_remove'])){
                unlink('../sistema/img/uploads/'.$_POST['foto_actual']);
            }
            
            if($nombre_foto!=''){
                move_uploaded_file($url_temp, $src); //almacena la data temporal del archivo y la mueve a $src
            }
            $alert = '<p class="msg_save">Producto actualizado correctamente</p>';
        } else {
            
            $alert = '<p class="msg_error">Error al actualizar el Producto</p>';
        }
    }



    //mysqli_close($conexion);
}

//validar que la variable no este vacia
 if(empty($_REQUEST['id'])){
     header("Location: ../sistema/lista_producto.php");
 }else{
     
     $id_producto = $_REQUEST['id'];
     if(!is_numeric($id_producto)){
        header("Location: ../sistema/lista_producto.php");
     }
     $query_producto = mysqli_query($conexion, "SELECT p.codproducto,p.descripcion,p.precio,p.foto,pr.codproveedor,pr.proveedor "
             . "                                FROM producto p"
             . "                                INNER JOIN proveedor pr"
             . "                                ON p.proveedor = pr.codproveedor "
             . "                                WHERE p.codproducto = $id_producto and p.estatus = 1");
    $result_producto = mysqli_num_rows($query_producto);
    
    
    $foto = '';
    $classRemove = 'notBlock';
    
    if($result_producto > 0){
        $data_producto = mysqli_fetch_assoc($query_producto);
        
        if($data_producto['foto'] != 'img_producto.png'){
            $classRemove = '';
            $foto = '<img id ="img" src =" ../sistema/img/uploads/'.$data_producto['foto'].'" alt = "producto" >';
        }
    
    }else{
        header("Location: ../sistema/lista_producto.php"); 
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

        <title>Actualizar Producto</title>
    </head>
    <body>
<?PHP
include "../sistema/recursos/header.php";
?>
        <section id="container">
            <div class="form_register">
                <h1> <i class="fas fa-cubes"></i> Actualizar Producto</h1>
                <hr>
                <div class="alert"><?PHP echo isset($alert) ? $alert : ''; ?></div>
                <form action="" method="POST" enctype="multipart/form-data" >
                    <input type="hidden"  name="id" value="<?PHP echo $data_producto['codproducto']; ?>">
                    <input type="hidden" id="foto_actual" name="foto_actual" value="<?PHP echo $data_producto['foto']; ?>">
                    <input type="hidden" id="foto_remove" name="foto_remove" value="<?PHP echo $data_producto['foto']; ?>">
                    <label for="proveedor">Nombre del Proveedor</label>
                    
                    <?PHP
                       $query_proveedor = mysqli_query($conexion,"SELECT codproveedor, proveedor "
                               . "                                FROM proveedor WHERE estatus = 1"
                               . "                                ORDER BY proveedor ASC");
                       $result_proveedor = mysqli_num_rows($query_proveedor);
                       mysqli_close($conexion);
                    ?>
                    <select name="proveedor" id="proveedor" class="notItemOne">
                        <option value="<?PHP echo $data_producto['codproveedor'] ;?>" selected> <?PHP echo $data_producto['proveedor']; ?> </option>
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
                    <input type="text" name="producto" id="producto" placeholder="Nombre del Producto" value="<?PHP echo $data_producto['descripcion']; ?>">

                    <label for="precio">Precio</label>
                    <input type="text" name="precio" id="precio" placeholder="Precio del producto" value="<?PHP echo $data_producto['precio']; ?>">

                   


                    <div class="photo">
                        <label for="foto">Foto</label>
                        <div class="prevPhoto">
                            <span class="delPhoto <?PHP echo $classRemove; ?>">X</span>
                            <label for="foto"></label>
                            <?PHP echo $foto; ?>
                        </div>
                        <div class="upimg">
                            <input type="file" name="foto" id="foto">
                        </div>
                        <div id="form_alert"></div>
                    </div>
                    <br>
                    <button type="submit" class="btn_save"> <i class="far fa-save"></i> Actualizar Producto</button>
                </form>
            </div>
        </section>
<?PHP
include "../sistema/recursos/footer.php";
?>
    </body>
</html>
