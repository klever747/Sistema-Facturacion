<?PHP

    session_start();
    include '../conexion/conexion.php';
    $busqueda = '';
    $fecha_de = '';
    $fecha_a = '';

    if(!empty($_REQUEST['busqueda'])){
        if(!is_numeric($_REQUEST['busqueda'])){
            header("location: ventas.php");
        }
        $busqueda = strtolower($_REQUEST['busqueda']);
        $where = "nofactura = $busqueda";
        $buscar = "busqueda = $busqueda";
    }
    
    if(!empty($_REQUEST['fecha_de']) && !empty($_REQUEST['fecha_a'])){
        $fecha_de = $_REQUEST['fecha_de'];
        $fecha_a = $_REQUEST['fecha_a'];
        
        $buscar = '';
        
        if($fecha_de > $fecha_a){
            header("location: ventas.php");
        }else if($fecha_de == $fecha_a){
            $where = "fecha LIKE '$fecha_de%'";
            $buscar = "fecha_de = $fecha_de&fecha_a=$fecha_a";
        }else{
            $f_de = $fecha_de.'00:00:00';
            $f_a = $fecha_a.'23:59:59';
            $where = "fecha BETWEEN '$f_de' AND '$f_a'";
            $buscar = "fecha_de=$fecha_de&fecha_a=$fecha_a";
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
        <title>Lista de ventas</title>
    </head>
    <body>
        <?PHP
        include "../sistema/recursos/header.php";
        ?>
        <section id="container">
            <h1> <i class="far fa-newspaper fa-2x"></i> Lista de ventas</h1>
            <a href="../sistema/nueva_venta.php" class="btn_new"> <i class="fas fa-plus"></i> Crear venta</a>
            
            <form action="buscar_venta.php" method="GET" class="form_search">
                <input type="text" name="busqueda" id ="busqueda" placeholder="No. Factura">
                <input type="submit" value="Buscar" class="btn_search">
            </form>
            
            <div>
                <h5>Buscar por Fecha</h5>
                <form action="buscar_venta.php" method="GET" class="form_search_date">
                    <label>De: </label>
                    <input type="date" name="fecha_de" id="fecha_de" required>
                    <label>A</label>
                    <input type="date" name="fecha_a" id="fecha_a" required>
                    <button type="submit" class="btn_view"><i class="fas fa-search"></i></button>
                </form>
            </div>
            <table>
                <tr>
                    <th>No.</th>
                    <th>Fecha / Hora</th>
                    <th>Cliente</th>
                    <th>Vendedor</th>
                    <th>Estado</th>
                    <th class="textright">Total Factura</th>
                    <th class="textright">Acciones</th>
                </tr>
                <?PHP
                //seccion para el paginador
                $sql_registe = mysqli_query($conexion,"SELECT count(*) as total_registro FROM factura WHERE $where");
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
                $query = mysqli_query($conexion, "SELECT f.nofactura, f.fecha, f.totalfactura,f.codcliente,f.estatus,"
                        . "                       u.nombre as vendedor,"
                        . "                       cl.nombre as cliente"
                        . "                       FROM factura f"
                        . "                       INNER JOIN usuario u"
                        . "                       ON f.usuario = u.idusuario"
                        . "                       INNER JOIN cliente cl"
                        . "                       ON f.codcliente = cl.idcliente"
                        . "                       WHERE $where AND f.estatus != 10"
                        . "                       ORDER BY f.fecha DESC LIMIT $desde,$por_pagina");
                mysqli_close($conexion);
                $result = mysqli_num_rows($query);
                if ($result > 0) {
                    while ($data = mysqli_fetch_array($query)) {
                       if($data["estatus"]==1){
                           $estado = '<span class="pagada"> Pagada </span>';
                       }else{
                           $estado = '<span class="anulada"> Anulada</span>';
                       }
                        ?>
                         <tr id="row_<?PHP echo $data["nofactura"];?>"> 
                            <td><?PHP echo $data["nofactura"];?></td>
                            <td><?PHP echo $data["fecha"];?></td>
                            <td><?PHP echo $data["cliente"];?> </td>
                            <td><?PHP echo $data["vendedor"];?></td>
                            <td class="estado"><?PHP echo $estado;?></td>
                            <td class="textright totalfactura"><span>$.</span><?PHP echo $data["totalfactura"];?></td>
                            <td>
                                <div class="div_acciones">
                                    <div>
                                        <button class="btn_view view_factura" type="button" cl="<?PHP echo $data["codcliente"];?>" f ="<?PHP echo $data['nofactura'];?>"><i class="fas fa-eye"></i></button>
                                    </div>
                                
                                
                                <?PHP
                                    if($_SESSION['rol'] ==1 || $_SESSION['rol'] == 2){
                                        if($data["estatus"] == 1){
                                    
                                ?>
                                <div class="div_factura">
                                    <button class="btn_anular anular_factura" fac="<?PHP echo $data["nofactura"];?>" > <i class="fas fa-ban"></i></button>
                                </div>
                                        <?PHP }else{ ?>
                                            <div class="div_factura">
                                                <button type="button" class="btn_anular inactive"> <i class="fas fa-ban"></i></button>
                                            </div>
                                        <?PHP   }
                                    }?>
                                    </div>
                            </td>
                        </tr>
                  <?PHP
                        }

                }
                ?>

                    </table>
            <div class="paginador">
                <ul>
                    <?PHP
                                    if ($pagina!=1){
                    ?>
                    
                    <li><a href="?pagina=<?PHP echo 1; ?>&<?PHP echo $buscar;?>"><i class="fas fa-step-backward"></i> </a></li>
                    <li><a href="?pagina=<?PHP echo $pagina-1;?>&<?PHP echo $buscar;?>"> <i class="fas fa-caret-left fa-lg"></i> </a></li>
                    <?PHP
                                    }
                        for ($i = 1; $i <= $total_paginas; $i++) {
                        if($i == $pagina){
                              echo '<li class= "pageSelected">'.$i.'</a></li>';
                        }else{
                            echo '<li><a href="?pagina='.$i.'&'.$buscar.'">'.$i.'</a></li>';
                        }
                                
                        }
                        if($pagina!= $total_paginas){
                     ?>
                    <li><a href="?pagina=<?PHP echo $pagina +1; ?>&<?PHP echo $buscar;?>"><i class="fas fa-forward"></i></a></li>
                    <li><a href="?pagina=<?PHP echo $total_paginas; ?>&<?PHP echo $buscar;?>"><i class="fas fa-step-forward"></i></a></li>
                        <?PHP } ?>
                </ul>
            </div>
            </section>
        <?PHP
        include "../sistema/recursos/footer.php";
        ?>
    </body>
</html>