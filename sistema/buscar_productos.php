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
        <title>Lista de productos</title>
    </head>
    <body>
        <?PHP
        include "../sistema/recursos/header.php";
        ?>
        <section id="container">

            <?PHP
            $busqueda = '';
            $search_proveedor = '';
            if (empty($_REQUEST['busqueda']) && empty($_REQUEST['proveedor'])) {
                header("location: ../sistema/lista_producto.php");
            }

            if (!empty($_REQUEST['busqueda'])) {
                $busqueda = strtolower($_REQUEST['busqueda']);
                $where = "(p.codproducto LIKE '%$busqueda%' OR p.descripcion LIKE '%$busqueda%') AND p.estatus = 1";
                //$where = " (p.codproducto LIKE '%$busqueda%' OR p.descripcion LIKE '%$busqueda%') AND p.estatus = 1";
                $buscar = 'busqueda=' . $busqueda;
            }

            if (!empty($_REQUEST['proveedor'])) {
                $search_proveedor = $_REQUEST['proveedor'];
                $where = " p.proveedor LIKE $search_proveedor AND p.estatus = 1";
                $buscar = 'busqueda=' . $search_proveedor;
            }
            ?>
            <h1> <i class="fas fa-cube"></i> Lista de productos</h1>
            <a href="../sistema/registro_producto.php" class="btn_new"> <i class="fas fa-plus"></i> Registrar productos</a>

            <form action="buscar_productos.php" method="GET" class="form_search">
                <input type="text" name="busqueda" id ="busqueda" placeholder="Buscar" value="<?PHP echo $busqueda; ?>">
                <button type="submit" class="btn_search"><i class="fas fa-search"></i></button>
            </form>
            <table>
                <tr>
                    <td>Codigo</td>
                    <td>Descripcion</td>
                    <td>Precio</td>
                    <td>Existencia</td>
                    <td>
                        <?PHP
                        $pro = 0;
                        if (!empty($_REQUEST['proveedor'])) {
                            $pro = $_REQUEST['proveedor'];
                        }
                        $query_proveedor = mysqli_query($conexion, "SELECT codproveedor, proveedor "
                                . "                                FROM proveedor WHERE estatus = 1"
                                . "                                ORDER BY proveedor ASC");
                        $result_proveedor = mysqli_num_rows($query_proveedor);
                        ?>
                        <select name="proveedor" id="search_proveedor">
                            <option value="" selected=""> Proveedor</option>
                            <?PHP
                            if ($result_proveedor > 0) {
                                while ($proveedor = mysqli_fetch_array($query_proveedor)) {
                                    if ($pro == $proveedor["codproveedor"]) {
                                        ?>
                                        <option value="<?PHP echo $proveedor['codproveedor']; ?>" selected=""><?PHP echo $proveedor['proveedor']; ?></option>
                                        <?PHP
                                    } else {
                                        ?>
                                        <option value="<?PHP echo $proveedor['codproveedor']; ?>"><?PHP echo $proveedor['proveedor']; ?></option>

                                        <?PHP
                                    }
                                }
                            }
                            ?>
                        </select>
                    </td>
                    <td>Foto</td>
                    <td>Acciones</td>
                </tr>
                <?PHP
//seccion para el paginador
                $sql_registe = mysqli_query($conexion, "SELECT count(*) as total_registro FROM producto p"
                        . "                            WHERE  $where");
                $result_register = mysqli_fetch_array($sql_registe);
                $total_registro = $result_register['total_registro'];
               

                $por_pagina = 5; //modificar para tener n registros por pagina dentro del paginador

                if (empty($_GET['pagina'])) {
                    $pagina = 1;
                } else {
                    $pagina = $_GET['pagina'];
                }
                $desde = ($pagina - 1) * $por_pagina;
                $total_paginas = ceil($total_registro / $por_pagina);
                $query = mysqli_query($conexion, "SELECT p.codproducto, p.descripcion,p.precio,p.existencia,pr.proveedor,p.foto"
                        . "                       FROM producto p"
                        . "                       INNER JOIN proveedor pr"
                        . "                       ON p.proveedor = pr.codproveedor"
                        . "                       WHERE $where"
                        . "                       ORDER BY p.codproducto DESC LIMIT $desde, $por_pagina");
                mysqli_close($conexion);
                $result = mysqli_num_rows($query);
                if ($result > 0) {
                    while ($data = mysqli_fetch_array($query)) {
                        if ($data['foto'] != 'img_producto.png') {

                            $foto = '../sistema/img/uploads/' . $data['foto'];
                        } else {
                            $foto = '../sistema/img/' . $data['foto'];
                        }
                        ?>
                        <tr class="row <?PHP echo $data['codproducto']; ?>">
                            <td><?PHP echo $data["codproducto"]; ?></td>
                            <td><?PHP echo $data["descripcion"]; ?></td>
                            <td class="celPrecio"><?PHP echo $data["precio"]; ?> </td>
                            <td class="celExistencia"><?PHP echo $data["existencia"]; ?></td>
                            <td><?PHP echo $data["proveedor"]; ?></td>
                            <td class="img_producto"><img src="<?PHP echo $foto; ?>" alt="<?PHP echo $data["descripcion"]; ?>"></td>

                            <?PHP
                            if ($_SESSION['rol'] == 1 || $_SESSION['rol'] == 3) {
                                ?>
                                <td>
                                    <a class="link_add add_product" product ="<?PHP echo $data["codproducto"]; ?>" href="#"> <i class="fas fa-plus"></i> Agregar</a>
                                    |
                                    <a class="link_edit" href="../sistema/editar_producto.php?id=<?PHP echo $data["codproducto"]; ?>"> <i class="far fa-edit"></i> Editar</a>

                                    |
                                    <a class="link_delete del_product" href="#" product ="<?PHP echo $data["codproducto"]; ?>" > <i class="far fa-trash-alt"></i>  Eliminar</a>


                                </td>
                            <?PHP } ?>
                        </tr>
                        <?PHP
                    }
                }
                ?>

            </table>
            <?PHP 
                if($total_paginas != 0){
            ?>
            <div class="paginador">
                <ul>
                    <?PHP
                    if ($pagina != 1) {
                        ?>

                    <li><a href="?pagina=<?PHP echo 1; ?>&<?PHP echo $buscar; ?>"> <i class="fas fa-step-backward"></i> </a></li>
                        <li><a href="?pagina=<?PHP echo $pagina - 1; ?> &<?PHP echo $buscar; ?>"> <i class="fas fa-caret-left fa-lg"></i> </a></li>
                            <?PHP
                        }
                        for ($i = 1; $i <= $total_paginas; $i++) {
                            if ($i == $pagina) {
                                echo '<li class= "pageSelected">' . $i . '</a></li>';
                            } else {
                                echo '<li><a href="?pagina=' . $i . '&'.$buscar.'">' . $i . '</a></li>';
                            }
                        }
                        if ($pagina != $total_paginas) {
                            ?>
                        <li><a href="?pagina=<?PHP echo $pagina + 1; ?>&<?PHP echo $buscar; ?>">>></a></li>
                        <li><a href="?pagina=<?PHP echo $total_paginas; ?>&<?PHP echo $buscar; ?>">>|</a></li>
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

