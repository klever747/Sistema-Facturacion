<?PHP
      session_start();
    if($_SESSION['rol']!=1){
        header("Location: ../sistema/sistema.php");
    }
include '../conexion/conexion.php';
?>

<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <?PHP
        include "../sistema/recursos/scripts.php";
        ?>
        <title>Sisteme Ventas</title>
    </head>
    <body>
        <?PHP
        include "../sistema/recursos/header.php";
        ?>
        <section id="container">
            <?PHP
                $busqueda = strtolower($_REQUEST['busqueda']);  //convierte toda la cadena a minuscula
                if(empty($busqueda)){
                    header("location: listar_usuario.php");
                    mysqli_close($conexion);
                }
            ?>
            <h1>Lista de usuarios</h1>
            <a href="../sistema/registro_usuario.php" class="btn_new">Crear usuario</a>
            
            <form action="buscar_usuario.php" method="GET" class="form_search">
                <input type="text" name="busqueda" id ="busqueda" placeholder="Buscar" <?PHP echo $busqueda; ?>>
                <input type="submit" value="Buscar" class="btn_search">
            </form>
            <table>
                <tr>
                    <td>ID</td>
                    <td>Nombre</td>
                    <td>Correo</td>
                    <td>Usuario</td>
                    <td>Rol</td>
                    <td>Acciones</td>
                </tr>
                <?PHP
                //seccion para el paginador
                $rol = '';
                if($busqueda == 'administrador'){
                    $rol = "OR rol LIKE '%1%'";
                }else if($busqueda == 'supervisor'){
                    $rol= "OR rol LIKE '%3%'";
                }else if($busqueda == 'vendedor'){
                    $rol = "OR rol LIKE '%2%'";
                }
                $sql_registe = mysqli_query($conexion,"SELECT COUNT(*) as total_registro FROM usuario "
                        . "                                             WHERE(idusuario LIKE '%$busqueda%' OR "
                        . "                                             nombre LIKE '%$busqueda%' OR "
                        . "                                             correo LIKE '%$busqueda%' OR "
                        . "                                             usuario LIKE '$busqueda' ) "
                        ."                                                $rol  "  
                        . "                                             AND estatus=1");
                $result_register = mysqli_fetch_array($sql_registe);
                $total_registro = $result_register['total_registro'];
                $por_pagina = 10; //modificar para tener n registros por pagina dentro del paginador
                if(empty($_GET['pagina'])){
                    $pagina = 1;
                }else{
                    $pagina=$_GET['pagina'];
                    
                }
                $desde = ($pagina-1)* $por_pagina;
                $total_paginas = ceil($total_registro / $por_pagina);
                $query = mysqli_query($conexion, "SELECT u.idusuario, u.nombre,u.correo,u.usuario,r.rol FROM usuario u "
                        . "                       INNER JOIN rol r ON u.rol=r.idrol "
                        ."                        WHERE ("
                        . "                               u.idusuario LIKE '%$busqueda%' OR"
                        . "                               u.nombre LIKE '%$busqueda%' OR"
                        . "                               u.correo LIKE '%$busqueda%' OR"
                        . "                               u.usuario LIKE '%$busqueda%' OR"
                        . "                               u.rol LIKE '%$busqueda%')"
                        . "                                 AND"
                        . "                        estatus = 1 ORDER BY  u.idusuario  ASC LIMIT $desde,$por_pagina ");
               mysqli_close($conexion);
                $result = mysqli_num_rows($query);
                if ($result > 0) {
                    while ($data = mysqli_fetch_array($query)) {
                        ?>
                        <tr>
                            <td><?PHP echo $data["idusuario"];?></td>
                            <td><?PHP echo $data["nombre"];?> </td>
                            <td><?PHP echo $data["correo"];?></td>
                            <td><?PHP echo $data["usuario"];?></td>
                            <td><?PHP echo $data["rol"];?></td>
                            <td>
                                <a class="link_edit" href="../sistema/editar_usuario.php?id=<?PHP echo $data["idusuario"];?>">Editar</a>
                                <?PHP if($data["idusuario"] !=15){?>
                                |
                                <a class="link_delete" href="../sistema/eliminar_confirmar_usuario.php?id=<?PHP echo $data["idusuario"];?>">Eliminar</a>
                                <?PHP }?>
                            </td>
                        </tr>
                  <?PHP
                        }

                }
                ?>

                    </table>
            <?PHP
                if($total_registro!=0){
                    
                
            ?>
            <div class="paginador">
                <ul>
                    <?PHP
                                    if ($pagina!=1){
                    ?>
                    
                    <li><a href="?pagina=<?PHP echo 1; ?> &busqueda=<?PHP echo $busqueda; ?>">|<</a></li>
                    <li><a href="?pagina=<?PHP echo $pagina-1;?> &busqueda=<?PHP echo $busqueda; ?>"><<</a></li>
                    <?PHP
                                    }
                        for ($i = 1; $i <= $total_paginas; $i++) {
                        if($i == $pagina){
                              echo '<li class= "pageSelected">'.$i.'</a></li>';
                        }else{
                            echo '<li><a href="?pagina='.$i.'&busqueda='.$busqueda.'">'.$i.'</a></li>';
                        }
                                
                        }
                        if($pagina!= $total_paginas){
                     ?>
                    <li><a href="?pagina=<?PHP echo $pagina +1; ?> &busqueda=<?PHP echo $busqueda; ?>">>></a></li>
                    <li><a href="?pagina=<?PHP echo $total_paginas; ?> &busqueda=<?PHP echo $busqueda; ?>">>|</a></li>
                        <?PHP } ?>
                </ul>
            </div>
            
            <?PHP
                }
             ?>
            </section>
        <?PHP
        include "../sistema/recursos/footer.php";
        ?>
    </body>
</html>