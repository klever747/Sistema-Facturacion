<?PHP
session_start();
    if($_SESSION['rol']!= 1){
        header("Location: ../sistema/sistema.php");
    }
include '../conexion/conexion.php';
if (!empty($_POST)) {
    $alert = '';
    if (empty($_POST['nombre']) || empty($_POST['correo']) || empty($_POST['usuario']) || empty($_POST['clave']) || empty($_POST['rol'])) {
        $alert = '<p class= "msg_error">Todos los campos deben llenarse</p>';
    } else {

        $nombre = $_POST['nombre'];
        $correo = $_POST['correo'];
        $usuario = $_POST['usuario'];
        $clave = md5($_POST['clave']);
        $rol = $_POST['rol'];

        $query = mysqli_query($conexion, "SELECT * FROM usuario WHERE usuario ='$user' OR correo = '$correo'");
        mysqli_close($conexion);
        $result = mysqli_fetch_array($query);
        if ($result > 0) {
            $alert = '<p class = "msg_error">El correo o el usuario ya existe</p>';
        } else {
            include '../conexion/conexion.php';
            $query_insert = mysqli_query($conexion, "INSERT INTO usuario(nombre,correo,usuario,clave,rol) VALUES('$nombre','$correo','$usuario','$clave','$rol')");
            mysqli_close($conexion);
            if ($query_insert) {
                $alert = '<p class="msg_save">Usuario creado correctamente</p>';
            } else {
                $alert = '<p class="msg_error">Usuario no registrado</p>';
            }
        }
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

        <title>Registro Usuario</title>
    </head>
    <body>
        <?PHP
        include "../sistema/recursos/header.php";
        ?>
        <section id="container">
            <div class="form_register">
                <h1><i class="fas fa-user-plus"></i> Registro Usuario</h1>
                <hr>
                <div class="alert"><?PHP echo isset($alert) ? $alert : ''; ?></div>
                <form action="" method="POST">
                    <label for="nombre">Nombre</label>
                    <input type="text" name="nombre" id="nombre" placeholder="Nombre Completo">
                    <label for="correo">Correo Electronico</label>
                    <input type="email" name="correo" id="correo" placeholder="Correo Electronico">
                    <label for="usuario">Usuario</label>
                    <input type="text" name="usuario" id="usuario" placeholder="Usuario">
                    <label for="clave">Clave</label>
                    <input type="password" name="clave" id="clave" placeholder="Password">
                    <label for="rol">Tipo de usuario</label>
                    <?PHP
                    include '../conexion/conexion.php';

                        $query_rol = mysqli_query($conexion,"SELECT * FROM rol" );
                        mysqli_close($conexion);
                        $result_rol = mysqli_num_rows($query_rol);
                       
                        
                    ?>
                    
                    <select name="rol" id="rol">
                       <?PHP
                             if($result_rol > 0){
                            while ($rol = mysqli_fetch_array($query_rol)){
                                
                        ?>
                            
                        
                        
                       
                        <option value="<?PHP echo $rol["idrol"]; ?>"><?PHP echo $rol["rol"]; ?></option>
                        <?PHP
                        
                             }
                             
                            }
                        ?>
                    </select>
                    <br>
                    <button type="submit" class="btn_save"> <i class="far fa-save"></i> Crear usuario</button>
                </form>
            </div>
        </section>
        <?PHP
        include "../sistema/recursos/footer.php";
        ?>
    </body>
</html>