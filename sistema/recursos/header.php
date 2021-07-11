<?PHP
//verificacion si la sesion existe 
if (empty($_SESSION['active'])) {
    header('location: ../');
}
?>
<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">

    <header>
        <div class="header">

            <h1>Sistema Facturación</h1>
            <div class="optionsBar">
                <p>Ecuador, <?PHP echo fechaC(); ?></p>
                <span>|</span>
                <span class="user"><?PHP echo $_SESSION['user'] . '-' . $_SESSION['rol'] . '-' . $_SESSION['email']; ?></span>
                <img class="photouser" src="../sistema/img/user.png" alt="Usuario">
                <a href="../sistema/salir.php"><img class="close" src="../sistema/img/salir.png" alt="Salir del sistema" title="Salir"></a>
            </div>
        </div>
        <?PHP
        include "../sistema/recursos/nav.php";
        ?>
    </header>
</head>
<body>
    <div class="modal">
    <div class="bodyModal">
    </div>
</div>
<div class="modalFacturacion">
    <div class="bodyModalFacturacion">
        <section id="container">
            <div class="title_page">
                <h1><i class="fas fa-cube"></i> Pagar Compras</h1>
            </div>
            <div class="datos_cliente">
                <div class="action_cliente">
                    <h4>Datos del Cliente</h4>

                </div>


                <form name="form_new_cliente_venta" id="form_new_cliente_venta_" class="datos" onsubmit="event.preventDefault();">
                    <input type="hidden" name="action" value="addCliente">
                    <input type="hidden" name="idCliente" id="idcliente_" value="" required>
                    <div class="wd30">
                        <label>CI</label>
                        <input type="text" name="nit_cliente" id="nit_cliente_">
                    </div>
                    <div class="wd30">
                        <label>Nombre</label>
                        <input type="text" name="nom_cliente" id="nom_cliente_" disabled required>
                    </div>
                    <div class="wd30">
                        <label>Telefono</label>
                        <input type="number" name="tel_cliente" id="tel_cliente_" disabled required> 
                    </div>
                    <div class="wd100">
                        <label>Direccion</label>
                        <input type="text" name="dir_cliente" id="dir_cliente_" disabled required> 
                    </div>

                </form>
            </div>
            <div class="datos_venta">
                <h4>Datos Vendedor</h4>
                <div class="datos">
                    <div class="wd50">
                        <label>Vendedor</label>
                        <p><?PHP echo $_SESSION['nombre']; ?></p>
                    </div>
                    <div class="wd50">
                        <label>Acciones</label>
                        <div id="acciones_venta">
                            <a href="#" class="btn_ok txtcenter" id="btn_cancelar_" onclick="closeModalFactura();"><i class="fas fa-ban"></i> Cerrar </a>
                            <a href="#" class="btn_new txtcenter" id="btn_facturar_venta_" style="display: none;"><i class="far fa-edit"></i> Procesar</a>
                        </div>
                    </div>
                    <?PHP
                    include '../conexion/conexion.php';

                    $query_tipo_factura = mysqli_query($conexion, "SELECT * FROM tipo_factura");
                    //  mysqli_close($conexion);
                    $result_tipo = mysqli_num_rows($query_tipo_factura);
                    ?>
                    <select name="tipo_factura_" id="tipo_factura_" disabled >
                        <?PHP
                        if ($result_tipo > 0) {
                            while ($tipoF = mysqli_fetch_array($query_tipo_factura)) {
                                if ($tipoF['id_tipoF'] == 1) {
                                    ?>

                                    <option value="<?PHP echo $tipoF["id_tipoF"]; ?>"><?PHP echo $tipoF["tipoFact"]; ?></option>
                                    <?PHP
                                }
                            }
                        }
                        ?>
                    </select>
                </div>
            </div>

            <table class="tbl_venta">
                <thead>

                    <tr>
                        <th>Código</th>
                        <th colspan="2">Descripción</th>
                        <th>Cantidad</th>
                        <th class="txtright">Precio</th>
                        <th class="txtright">Precio Total</th>

                    </tr>
                </thead>

                <tbody id="detalle_venta_">
                    <!---CONTENIDO AJAX--->
                </tbody>
                <tfoot id="detalle_totales_">
                    <!--CONTENIDO AJAX--->
                </tfoot>


                <div class="wd30">     
                    <label>Monto a pagar $</label>
                    <input type="text" name="monto" id="monto">                     
                    <br>
                    <div class="wd100" id="detalleMonto">
                        <!---Contenido AJAX cargar MONTO--->
                    </div>

                </div>

            </table>

        </section>
    </div>
</div>

</body>
</html>



