<?PHP
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
        $proveedor = $_POST['proveedor'];
        $contacto = $_POST['contacto'];
        $telefono = $_POST['telefono'];
        $direccion = $_POST['direccion'];
        $usuario_id = $_SESSION['idUser'];

           $query_insert = mysqli_query($conexion, "INSERT INTO proveedor(proveedor,contacto,telefono,direccion,usuario_id)"
                   . "       VALUES('$proveedor','$contacto','$telefono','$direccion','$usuario_id')");
           
            if ($query_insert) {
                $alert = '<p class="msg_save">Cliente guardado correctamente</p>';
            } else {
                $alert = '<p class="msg_error">Error al guardar el cliente</p>';
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

        <title>Registro Proveedor</title>
    </head>
    <body>
        <?PHP
        include "../sistema/recursos/header.php";
        ?>
        <section id="container">
            <div class="form_register">
                <h1> <i class="far fa-building"></i> Registro Proveedor</h1>
                <hr>
                <div class="alert"><?PHP echo isset($alert) ? $alert : ''; ?></div>
                <form action="" method="POST">
                    <label for="proveedor">Nombre del Proveedor</label>
                    <input type="text" name="proveedor" id="proveedor" placeholder="Nombre del Proveedor">
                    <label for="contacto">Contacto</label>
                    <input type="text" name="contacto" id="contacto" placeholder="Nombre del Contacto">
                    <label for="telefono">Telefono</label>
                    <input type="number" name="telefono" id="usuario" placeholder="Telefono">
                    <label for="direccion">Direccion</label>
                    <input type="text" name="direccion" id="direccion" placeholder="Direccion Completa">
                    <br>
                     <button type="submit" class="btn_save"> <i class="far fa-save"></i> Guardar Proveedor</button>
                </form>
            </div>
        </section>
        <?PHP
        include "../sistema/recursos/footer.php";
        ?>
    </body>
</html>
