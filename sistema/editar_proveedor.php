<?PHP
//control segun el rol redirecciona si rol 1 deja listar y buscar si no pagina principal
session_start();
    if($_SESSION['rol']!= 1 and $_SESSION['rol'] !=2){
       header("Location: ../sistema/sistema.php");
    }
include '../conexion/conexion.php';

if (!empty($_POST)) {
    $alert = '';
    if (empty($_POST['proveedor']) || empty($_POST['contacto']) || empty($_POST['telefono']) || empty($_POST['direccion'])) {
        $alert = '<p class= "msg_error">Todos los campos deben llenarse</p>';
    } else {
        
        $idproveedor     = $_POST['id'];
        $proveedor       = $_POST['proveedor'];
        $contacto        = $_POST['contacto'];
        $telefono        = $_POST['telefono'];
        $direccion       = ($_POST['direccion']);
        
            
      $sql_update = mysqli_query($conexion, "UPDATE proveedor "
              . "                            SET proveedor = '$proveedor' ,contacto='$contacto', telefono=$telefono, direccion='$direccion' "
                        . "         WHERE codproveedor = $idproveedor");
           

            if ($sql_update) {
                $alert = '<p class="msg_save">Cliente actualizado correctamente</p>';
            } else {
                $alert = '<p class="msg_error">Error al actualizar cliente </p>';
            }
        }
    
}

//metodo para mostrar los datos dentro del formulario antes de actualizar los datos del clientes
if (empty($_REQUEST['id'])) {
    header('Location: listar_proveedores.php');
    mysqli_close($conexion);
}

$idproveedor = $_REQUEST['id'];
$query = mysqli_query($conexion, "SELECT * FROM proveedor WHERE codproveedor = $idproveedor and estatus =1");

mysqli_close($conexion);

$result_sql = mysqli_num_rows($query);

if ($result_sql == 0) {
    header('Location: listar_proveedores.php');
    
} else { 
    
    while ($data = mysqli_fetch_array($query)) {
        $idproveedor   = $data['codproveedor'];
        $proveedor      = $data['proveedor'];
        $contacto       = $data['contacto'];
        $telefono       = $data['telefono'];
        $direccion      = $data['direccion']; 
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

        <title>Actualizar Proveedor</title>
    </head>
    <body>
        <?PHP
        include "../sistema/recursos/header.php";
        ?>
        <section id="container">
            <div class="form_register">
                <h1> <i class="far fa-edit"></i>  Actualizar Proveedor</h1>
                <hr>
                <div class="alert"><?PHP echo isset($alert) ? $alert : ''; ?></div>
               <form action="" method="POST">
                   <input type="hidden"name="id" value="<?PHP echo $idproveedor; ?>"
                    <label for="proveedor">Nombre del Proveedor</label>
                    <input type="text" name="proveedor" id="proveedor" placeholder="Nombre del Proveedor" value="<?PHP echo $proveedor; ?>" >
                    <label for="contacto">Contacto</label>
                    <input type="text" name="contacto" id="contacto" placeholder="Nombre del Contacto" value="<?PHP echo $contacto ?>" >
                    <label for="telefono">Telefono</label>
                    <input type="number" name="telefono" id="usuario" placeholder="Telefono" value="<?PHP echo $telefono; ?>" >
                    <label for="direccion">Direccion</label>
                    <input type="text" name="direccion" id="direccion" placeholder="Direccion Completa" value="<?PHP echo $direccion; ?>"  >
                    <br>
                     <button type="submit" class="btn_save"> <i class="far fa-edit"></i>  Actualizar Proveedor</button>
                </form>
            </div>
        </section>
<?PHP
include "../sistema/recursos/footer.php";
?>
    </body>
</html>