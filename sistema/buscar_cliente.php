<?PHP
      session_start();

include '../conexion/conexion.php';
?>

<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <?PHP
        include "../sistema/recursos/scripts.php";
        ?>
        <title>Buscar Clientes</title>
    </head>
    <body>
        <?PHP
        include "../sistema/recursos/header.php";
        ?>
        <section id="container">
            <?PHP
                $busqueda = strtolower($_REQUEST['busqueda']);  //convierte toda la cadena a minuscula
                if(empty($busqueda)){
                    header("location: listar_clientes.php");
                    mysqli_close($conexion);
                }
            ?>
            <h1>Lista de clientes</h1>
            <a href="../sistema/registro_cliente.php" class="btn_new">Crear usuario</a>
            
            <form action="buscar_cliente.php" method="GET" class="form_search">
                <input type="text" name="busqueda" id ="busqueda" placeholder="Buscar" <?PHP echo $busqueda; ?>>
                <input type="submit" value="Buscar" class="btn_search">
            </form>
            <table>
                <tr>
                    <td>ID</td>
                    <td>CI</td>
                    <td>Nombre</td>
                    <td>Telefono</td>
                    <td>Direccion</td>
                    <td>Acciones</td>
                </tr>
                <?PHP
                //seccion para el paginador
              
                $sql_registe = mysqli_query($conexion,"SELECT COUNT(*) as total_registro FROM cliente "
                        . "                                             WHERE(idcliente LIKE '%$busqueda%' OR "
                        . "                                             nit LIKE '%$busqueda%' OR "
                        . "                                             nombre LIKE '%$busqueda%' OR "
                        . "                                             telefono LIKE '$busqueda' OR "
                        . "                                             direccion LIKE '%$busqueda%') "
            
                        . "                                             AND estatus=1");
                $result_register = mysqli_fetch_array($sql_registe);
                $total_registro = $result_register['total_registro'];
                $por_pagina = 5; //modificar para tener n registros por pagina dentro del paginador
                if(empty($_GET['pagina'])){
                    $pagina = 1;
                }else{
                    $pagina=$_GET['pagina'];
                    
                }
                $desde = ($pagina-1)* $por_pagina;
                $total_paginas = ceil($total_registro / $por_pagina);
                $query = mysqli_query($conexion, "SELECT * FROM cliente "
                        . "                       WHERE ("
                        . "                               idcliente LIKE '%$busqueda%' OR"
                        . "                               nit LIKE '%$busqueda%' OR"
                        . "                               nombre LIKE '%$busqueda%' OR"
                        . "                               telefono LIKE '%$busqueda%' OR"
                        . "                               direccion LIKE '%$busqueda%')"
                        . "                                 AND"
                        . "                        estatus = 1 ORDER BY  idcliente  ASC LIMIT $desde,$por_pagina ");
               mysqli_close($conexion);
                $result = mysqli_num_rows($query);
                if ($result > 0) {
                    while ($data = mysqli_fetch_array($query)) {
                        ?>
                        <tr>
                            <td><?PHP echo $data["idcliente"];?></td>
                            <td><?PHP echo $data["nit"];?> </td>
                            <td><?PHP echo $data["nombre"];?></td>
                            <td><?PHP echo $data["telefono"];?></td>
                            <td><?PHP echo $data["direccion"];?></td>
                            <td>
                                <a class="link_edit" href="../sistema/editar_cliente.php?id=<?PHP echo $data["idcliente"];?>">Editar</a>
                                  <?PHP
                                    if($_SESSION['rol'] == 1 || $_SESSION['rol'] == 3){
                                ?> 
                                |
                                <a class="link_delete" href="../sistema/eliminar_confirmar_usuario.php?id=<?PHP echo $data["idcliente"];?>">Eliminar</a>
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