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
            <h1> <i class="fas fa-cube"></i> Lista de productos</h1>
            <a href="../sistema/registro_producto.php" class="btn_new"> <i class="fas fa-plus"></i> Registrar productos</a>

            <form action="buscar_productos.php" method="GET" class="form_search">
                <input type="text" name="busqueda" id ="busqueda" placeholder="Buscar">
                <input type="submit" value="Buscar" class="btn_search">
            </form>
            <table>
                <tr>
                    <td>Codigo</td>
                    <td>Descripcion</td>
                    <td>Precio</td>
                    <td>Existencia</td>
                    <td>
                        <?PHP
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
                                    ?>
                                    <option value="<?PHP echo $proveedor['codproveedor']; ?>"><?PHP echo $proveedor['proveedor']; ?></option>
                                    <?PHP
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
                $sql_registe = mysqli_query($conexion, "SELECT count(*) as total_registro FROM producto WHERE estatus=1");
                $result_register = mysqli_fetch_array($sql_registe);
                $total_registro = $result_register['total_registro'];
                $por_pagina = 10; //modificar para tener n registros por pagina dentro del paginador
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
                        . "                       WHERE p.estatus = 1 ORDER BY  p.codproducto DESC LIMIT $desde,$por_pagina ");
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
            <div class="paginador">
                <ul>
                    <?PHP
                    if ($pagina != 1) {
                        ?>

                        <li><a href="?pagina=<?PHP echo 1; ?>"> <i class="fas fa-step-backward"></i> </a></li>
                        <li><a href="?pagina=<?PHP echo $pagina - 1; ?>"> <i class="fas fa-caret-left fa-lg"></i> </a></li>
                            <?PHP
                        }
                        for ($i = 1; $i <= $total_paginas; $i++) {
                            if ($i == $pagina) {
                                echo '<li class= "pageSelected">' . $i . '</a></li>';
                            } else {
                                echo '<li><a href="?pagina=' . $i . '">' . $i . '</a></li>';
                            }
                        }
                        if ($pagina != $total_paginas) {
                            ?>
                        <li><a href="?pagina=<?PHP echo $pagina + 1; ?>">>></a></li>
                        <li><a href="?pagina=<?PHP echo $total_paginas; ?>">>|</a></li>
                    <?PHP } ?>
                </ul>
            </div>
        </section>
        <?PHP
        include "../sistema/recursos/footer.php";
        ?>
    </body>
</html>

