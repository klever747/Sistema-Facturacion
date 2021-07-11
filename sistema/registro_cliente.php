<?PHP
session_start();

include '../conexion/conexion.php';
if (!empty($_POST)) {
    $alert = '';
    if (empty($_POST['nombre']) || empty($_POST['telefono']) || empty($_POST['direccion'])) {
        $alert = '<p class= "msg_error">Todos los campos deben llenarse</p>';
    } else {
        $nit = $_POST['nit'];
        $nombre = $_POST['nombre'];
        $telefono = $_POST['telefono'];
        $direccion = $_POST['direccion'];
        $tipo_cliente = $_POST['tipo_cliente'];
        $usuario_id = $_SESSION['idUser'];
        
        $result = 0;
        
        if(is_numeric($nit) and $nit!=0){
            $query = mysqli_query($conexion, "SELECT * FROM cliente WHERE nit ='$nit'");
            $result = mysqli_fetch_array($query);
        }
        if($result > 0){
              $alert = '<p class = "msg_error">El numero de CI ya existe</p>';
        } else {
           $query_insert = mysqli_query($conexion, "INSERT INTO cliente(nit,idtipo,nombre,telefono,direccion,usuario_id)"
                   . "       VALUES('$nit','$tipo_cliente','$nombre','$telefono','$direccion','$usuario_id')");
           
            if ($query_insert) {
                $alert = '<p class="msg_save">Cliente guardado correctamente</p>';
            } else {
                $alert = '<p class="msg_error">Error al guardar el cliente</p>';
            }
        }

    }    
        
         mysqli_close($conexion);
      
}

?>

<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <?PHP
        include "../sistema/recursos/scripts.php";
        ?>

        <title>Registro Cliente</title>
    </head>
    <body>
        <?PHP
        include "../sistema/recursos/header.php";
        ?>
        <section id="container">
            <div class="form_register">
                <h1><i class="fas fa-user-plus"></i> Registro Cliente</h1>
                <hr>
                <div class="alert"><?PHP echo isset($alert) ? $alert : ''; ?></div>
                <form action="" method="POST">
                    <label for="nit">CI</label>
                    <input type="number" name="nit" id="nit" placeholder="Cedula de Identidad">
                    <label for="nombre">Nombre</label>
                    <input type="text" name="nombre" id="nombre" placeholder="Nombre Completo">
                    <label for="telefono">Telefono</label>
                    <input type="number" name="telefono" id="usuario" placeholder="Telefono">
                    <label for="direccion">Direccion</label>
                    <input type="text" name="direccion" id="direccion" placeholder="Direccion Completa">
                    <label for="tipo_cliente">Tipo de cliente</label>
                     <?PHP
                    include '../conexion/conexion.php';

                        $query_rol_cliente = mysqli_query($conexion,"SELECT * FROM tipo_cliente" );
                        mysqli_close($conexion);
                        $result_rol = mysqli_num_rows($query_rol_cliente);
                       
                        
                    ?>
                    <select name="tipo_cliente" id="tipo_cliente">
                       <?PHP
                             if($result_rol > 0){
                            while ($rol = mysqli_fetch_array($query_rol_cliente)){
                                
                        ?>
                            
                        
                        
                       
                        <option value="<?PHP echo $rol["idtipo"]; ?>"><?PHP echo $rol["tipo"]; ?></option>
                        <?PHP
                        
                             }
                             
                            }
                        ?>
                    </select>
                    <br>
                    <button type="submit" class="btn_save"> <i class="far fa-save"></i> Crear Cliente</button>
                </form>
            </div>
        </section>
        <?PHP
        include "../sistema/recursos/footer.php";
        ?>
    </body>
</html>