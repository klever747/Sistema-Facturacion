<?PHP
//control segun el rol redirecciona si rol 1 deja listar y buscar si no pagina principal
  session_start();
    if($_SESSION['rol']!=1){
        header("Location: ../sistema/sistema.php");
        
    }
include '../conexion/conexion.php';
if (!empty($_POST)) { 
    $alert = '';
    if (empty($_POST['nombre']) || empty($_POST['correo']) || empty($_POST['usuario']) || empty($_POST['rol'])) {
        $alert = '<p class= "msg_error">Todos los campos deben llenarse</p>';
    } else {
        $idUsuario=$_POST['id'];
        $nombre = $_POST['nombre'];
        $correo = $_POST['correo'];
        $usuario = $_POST['usuario'];
        $clave = md5($_POST['clave']);
        $rol = $_POST['rol'];

        $query = mysqli_query($conexion, "SELECT * FROM usuario "
                . "                       WHERE (usuario ='$user' "
                . "                       AND idusuario != $idUsuario) OR "
                . "                       (correo != '$correo' AND idusuario = $idUsuario)");
        $result = mysqli_fetch_array($query);
      //  $result = count($result);
        
        if ($result > 0) {
            $alert = '<p class = "msg_error">El correo o el usuario ya existe</p>';
        } else {
            if(empty($_POST['clave'])){
                $sql_update = mysqli_query($conexion, "UPDATE usuario SET nombre = '$nombre',correo ='$correo', usuario='$usuario',rol ='$rol' WHERE idusuario = $idUsuario");
            }else{
                 $sql_update = mysqli_query($conexion, "UPDATE usuario SET nombre = '$nombre',correo ='$correo', usuario='$usuario',clave ='$clave',rol ='$rol' WHERE idusuario = $idUsuario");
            }
        
            if ($sql_update) {
                $alert = '<p class="msg_save">Usuario actualizado correctamente</p>';
            } else {
                $alert = '<p class="msg_error">Usuario no actualizado</p>';
            }
        }
    }
    
}

//metodo para mostrar los datos
if(empty($_REQUEST['id'])){
    header('Location: listar_usuario.php'); 
    mysqli_close($conexion);
    
}

$iduser = $_REQUEST['id'];
$query = mysqli_query($conexion, "SELECT u.idusuario,u.nombre, u.correo,u.usuario,(u.rol) as idrol, (r.rol) as rol "
        . "                       FROM usuario u INNER JOIN rol r ON u.rol = r.idrol "
        . "                       WHERE idusuario=$iduser and estatus=1");

mysqli_close($conexion);

$result_sql = mysqli_num_rows($query);
 
if($result_sql == 0){
    header('Location: listar_usuario.php');
    mysqli_close($conexion);
    
}else{
    $option = '';
    while ($data = mysqli_fetch_array($query)){
        $iduser = $data['idusuario'];
        $nombre = $data['nombre'];
        $correo = $data['correo'];
        $usuario = $data['usuario'];
        $idrol = $data['idrol'];
        $rol = $data['rol'];
        if($idrol == 1){
            $option = '<option value="'.$idrol.'" select>'.$rol.'</option>';
        } else if($idrol == 2) {
            $option = '<option value="'.$idrol.'" select>'.$rol.'</option>';
        }else if($idrol == 3){
            $option = '<option value="'.$idrol.'" select>'.$rol.'</option>';
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

        <title>Actualizar Usuario</title>
    </head>
    <body>
        <?PHP
        include "../sistema/recursos/header.php";
        ?>
        <section id="container">
            <div class="form_register">
                <h1><i class="far fa-edit"></i> Actualizar Usuario</h1>
                <hr>
                <div class="alert"><?PHP echo isset($alert) ? $alert : ''; ?></div>
                <form action="" method="POST">
                    <input type="hidden" name="id" value="<?PHP echo $iduser; ?>">
                    <label for="nombre">Nombre</label>
                    <input type="text" name="nombre" id="nombre" placeholder="Nombre Completo" value="<?PHP echo $nombre;?>">
                    <label for="correo">Correo Electronico</label>
                    <input type="email" name="correo" id="correo" placeholder="Correo Electronico" value="<?PHP echo $correo;?>">
                    <label for="usuario">Usuario</label>
                    <input type="text" name="usuario" id="usuario" placeholder="Usuario" value="<?PHP echo $usuario;?>">
                    <label for="clave">Clave</label>
                    <input type="password" name="clave" id="clave" placeholder="Password">
                    <label for="rol">Tipo de usuario</label>
                    <?PHP
                    include '../conexion/conexion.php';
                        $query_rol = mysqli_query($conexion,"SELECT * FROM rol" );
                        mysqli_close($conexion);
                        $result_rol = mysqli_num_rows($query_rol);
                       
                        
                    ?>
                    
                    <select name="rol" id="rol" class="notItemOne">
                       <?PHP 
                       echo $option;
                             if($result_rol > 0){
                            while ($rol = mysqli_fetch_array($query_rol)){
                                
                        ?>
     
                        <option value="<?PHP echo $rol["idrol"]; ?>"><?PHP echo $rol["rol"]; ?></option>
                        <?PHP
                        
                             }
                             
                            }
                        ?>
                    </select>
                    
                    <button class="btn_save" ><i class="far fa-edit"></i> Actualizar Usuario</button>
                </form>
            </div>
        </section>
        <?PHP
        include "../sistema/recursos/footer.php";
        ?>
    </body>
</html>