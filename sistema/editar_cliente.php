<?PHP
//control segun el rol redirecciona si rol 1 deja listar y buscar si no pagina principal
session_start();

include '../conexion/conexion.php';
if (!empty($_POST)) {
    $alert = '';
    if (empty($_POST['nombre']) || empty($_POST['telefono']) || empty($_POST['direccion'])) {
        $alert = '<p class= "msg_error">Todos los campos deben llenarse</p>';
    } else {
        $idCliente = $_POST['id'];
        $nit = $_POST['nit'];
        $nombre = $_POST['nombre'];
        $telefono = $_POST['telefono'];
        $direccion = ($_POST['direccion']);
        
        $result =0;
        
        if(is_numeric($nit) and $nit !=0){
            $query = mysqli_query($conexion, "SELECT * FROM cliente WHERE (nit = $nit AND idcliente != idCliente)");
            
            $result = mysqli_fetch_array($query);
           // $result = count($result);
            }

        if ($result > 0) {
            $alert = '<p class = "msg_error">La CI ya existe ingrese otra</p>';
        } else {
            
            if($nit == ''){
                $nit=0;
            }
            
            
                $sql_update = mysqli_query($conexion, "UPDATE cliente SET nit = $nit ,nombre ='$nombre', telefono=$telefono, direccion='$direccion' "
                        . "         WHERE idcliente = $idCliente");
           

            if ($sql_update) {
                $alert = '<p class="msg_save">Cliente actualizado correctamente</p>';
            } else {
                $alert = '<p class="msg_error">Error al actualizar cliente </p>';
            }
        }
    }
}

//metodo para mostrar los datos dentro del formulario antes de actualizar los datos del clientes
if (empty($_REQUEST['id'])) {
    header('Location: listar_clientes.php');
    mysqli_close($conexion);
}

$idcliente = $_REQUEST['id'];
$query = mysqli_query($conexion, "SELECT * FROM cliente WHERE idcliente = $idcliente and estatus = 1");

mysqli_close($conexion);

$result_sql = mysqli_num_rows($query);

if ($result_sql == 0) {
    header('Location: listar_clientes.php');
    
} else { 
    
    while ($data = mysqli_fetch_array($query)) {
        $idcliente = $data['idcliente'];
        $nit = $data['nit'];
        $nombre = $data['nombre'];
        $telefono = $data['telefono'];
        $direccion = $data['direccion']; 
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

        <title>Actualizar Cliente</title>
    </head>
    <body>
        <?PHP
        include "../sistema/recursos/header.php";
        ?>
        <section id="container">
            <div class="form_register">
                <h1>Actualizar Cliente</h1>
                <hr>
                <div class="alert"><?PHP echo isset($alert) ? $alert : ''; ?></div>
                <form action="" method="POST">
                    <input type="hidden" name="id" value="<?PHP echo $idcliente; ?>">
                    <label for="nit">CI</label>
                    <input type="number" name="nit" id="nit" placeholder="Cedula de Identidad" value="<?PHP echo $nit; ?>" >
                    <label for="nombre">Nombre</label>
                    <input type="text" name="nombre" id="nombre" placeholder="Nombre Completo" value="<?PHP echo $nombre; ?>">
                    <label for="telefono">Telefono</label>
                    <input type="number" name="telefono" id="usuario" placeholder="Telefono" value="<?PHP echo $telefono; ?>">
                    <label for="direccion">Direccion</label>
                    <input type="text" name="direccion" id="direccion" placeholder="Direccion Completa" value="<?PHP  echo $direccion; ?>">

                    <input type="submit" value="Actualizar Cliente" class="btn_save">
                </form>
            </div>
        </section>
<?PHP
include "../sistema/recursos/footer.php";
?>
    </body>
</html>