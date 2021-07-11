<?PHP
session_start();
if ($_SESSION['rol'] != 1 and $_SESSION['rol'] != 2) {
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
        <title>Buscar Clientes</title>
    </head>
    <body>
        <?PHP
        include "../sistema/recursos/header.php";
        ?>
        <section id="container">
            <?PHP
            $busqueda = strtolower($_REQUEST['busqueda']);  //convierte toda la cadena a minuscula
            if (empty($busqueda)) {
                header("location: listar_proveedores.php");
                mysqli_close($conexion);
            }
            ?>
            <h1> <i class="far fa-building fa-2x"></i> Lista de Proveedores</h1>
            <a href="../sistema/registroProveedores.php" class="btn_new"> <i class="fas fa-plus"></i> Crear Proveedor</a>

            <form action="buscar_proveedor.php" method="GET" class="form_search">
                <input type="text" name="busqueda" id ="busqueda" placeholder="Buscar" <?PHP echo $busqueda; ?>>
                <input type="submit" value="Buscar" class="btn_search">
            </form>
            <table>
                <tr>
                    <td>ID</td>
                    <td>Proveedor</td>
                    <td>Contacto</td>
                    <td>Telefono</td>
                    <td>Direccion</td>
                    <td>Fecha</td>
                    <td>Acciones</td>
                </tr>
                <?PHP
                //seccion para el paginador

                $sql_registe = mysqli_query($conexion, "SELECT COUNT(*) as total_registro FROM proveedor "
                        . "                                             WHERE(codproveedor LIKE '%$busqueda%' OR "
                        . "                                             proveedor LIKE '%$busqueda%' OR "
                        . "                                             contacto LIKE '%$busqueda%' OR "
                        . "                                             telefono LIKE '$busqueda') "
                        . "                                             AND estatus=1");
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
                $query = mysqli_query($conexion, "SELECT * FROM proveedor "
                        . "                       WHERE ("
                        . "                               codproveedor LIKE '%$busqueda%' OR"
                        . "                               proveedor LIKE '%$busqueda%' OR"
                        . "                               contacto LIKE '%$busqueda%' OR"
                        . "                               telefono LIKE '%$busqueda%' )"
                        . "                                 AND"
                        . "                        estatus = 1 ORDER BY  codproveedor  ASC LIMIT $desde,$por_pagina ");
                mysqli_close($conexion);
                $result = mysqli_num_rows($query);
                if ($result > 0) {
                    while ($data = mysqli_fetch_array($query)) {
                        $formato = 'Y-m-d H:i:s';
                        $fecha = DateTime::createFromFormat($formato, $data["date_add"]);
                        ?>
                        <tr>
                            <td><?PHP echo $data["codproveedor"]; ?></td>
                            <td><?PHP echo $data["proveedor"]; ?> </td>
                            <td><?PHP echo $data["contacto"]; ?> </td>
                            <td><?PHP echo $data["telefono"]; ?></td>
                            <td><?PHP echo $data["direccion"]; ?></td>
                            <td><?PHP echo $fecha->format('d-m-Y'); ?> </td>
                            <td>
                                <a class="link_edit" href="../sistema/editar_proveedor.php?id=<?PHP echo $data["codproveedor"]; ?>"> <i class="far fa-edit"></i>  Editar</a>
                              
                                    |
                                    <a class="link_delete" href="../sistema/eliminar_confirmar_proveedor.php?id=<?PHP echo $data["codproveedor"]; ?>"><i class="far fa-trash-alt"></i> Eliminar</a>
                               
                            </td>
                        </tr>
                        <?PHP
                    }
                }
                ?>

            </table>
            <?PHP
            if ($total_registro != 0) {
                ?>
                <div class="paginador">
                    <ul>
                        <?PHP
                        if ($pagina != 1) {
                            ?>

                            <li><a href="?pagina=<?PHP echo 1; ?> &busqueda=<?PHP echo $busqueda; ?>">|<</a></li>
                            <li><a href="?pagina=<?PHP echo $pagina - 1; ?> &busqueda=<?PHP echo $busqueda; ?>"><<</a></li>
                            <?PHP
                        }
                        for ($i = 1; $i <= $total_paginas; $i++) {
                            if ($i == $pagina) {
                                echo '<li class= "pageSelected">' . $i . '</a></li>';
                            } else {
                                echo '<li><a href="?pagina=' . $i . '&busqueda=' . $busqueda . '">' . $i . '</a></li>';
                            }
                        }
                        if ($pagina != $total_paginas) {
                            ?>
                            <li><a href="?pagina=<?PHP echo $pagina + 1; ?> &busqueda=<?PHP echo $busqueda; ?>">>></a></li>
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
