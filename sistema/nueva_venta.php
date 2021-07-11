<?PHP
session_start();
include "../conexion/conexion.php";

?>

<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <?PHP
        include "../sistema/recursos/scripts.php";
        ?>
        <title>Nueva Venta</title>
    </head>
    <body>
        <?PHP
        include "../sistema/recursos/header.php";
        ?>
        <section id="container">
            <div class="title_page">
                <h1><i class="fas fa-cube"></i> Nueva Venta</h1>
            </div>
            <div class="datos_cliente">
                <div class="action_cliente">
                    <h4>Datos del Cliente</h4>
                    <a href="#" class="btn_new btn_new_cliente"><i class="fas fa-plus"></i> Nuevo Cliente</a>  
                </div>


                <form name="form_new_cliente_venta" id="form_new_cliente_venta" class="datos">
                    <input type="hidden" name="action" value="addCliente">
                    <input type="hidden" name="idCliente" id="idcliente" value="" required>
                    <div class="wd30">
                        <label>CI</label>
                        <input type="text" name="nit_cliente" id="nit_cliente">
                    </div>
                    <div class="wd30">
                        <label>Nombre</label>
                        <input type="text" name="nom_cliente" id="nom_cliente" disabled required>
                    </div>
                    <div class="wd30">
                        <label>Telefono</label>
                        <input type="number" name="tel_cliente" id="tel_cliente" disabled required> 
                    </div>
                    <div class="wd100">
                        <label>Direccion</label>
                        <input type="text" name="dir_cliente" id="dir_cliente" disabled required> 
                    </div>
                    <div id="div_registro_cliente" class="wd100">
                        <button type="submit" class="btn_save"><i class="far fa-save fa-lg"></i> Guardar</button>
                    </div>  
                </form>
            </div>
            <div class="datos_venta">
                <h4>Datos Venta</h4>
                <div class="datos">
                    <div class="wd50">
                        <label>Vendedor</label>
                        <p><?PHP echo $_SESSION['nombre'];?></p>
                    </div>
                    <div class="wd50">
                        <label>Acciones</label>
                        <div id="acciones_venta">
                            <a href="#" class="btn_ok txtcenter" id="btn_anular_venta"><i class="fas fa-ban"></i> Anular</a>
                            <a href="#" class="btn_new txtcenter" id="btn_facturar_venta" style="display: none;"><i class="far fa-edit"></i> Procesar</a>
                        </div>
                    </div>
                     <?PHP
                    include '../conexion/conexion.php';

                        $query_tipo_factura = mysqli_query($conexion,"SELECT * FROM tipo_factura" );
                        mysqli_close($conexion);
                        $result_tipo = mysqli_num_rows($query_tipo_factura);
                       
                        
                    ?>
                    <select name="tipo_factura" id="tipo_factura" >
                       <?PHP
                             if($result_tipo > 0){
                            while ($tipoF = mysqli_fetch_array($query_tipo_factura)){
                                
                        ?>
                            
                        
                        
                       
                        <option value="<?PHP echo $tipoF["id_tipoF"]; ?>"><?PHP echo $tipoF["tipoFact"]; ?></option>
                        <?PHP
                        
                             }
                             
                            }
                        ?>
                    </select>
                </div>
            </div>
            <input type="hidden" name="cod_producto" id="cod_producto" value="">
            <table class="tbl_venta">
                <thead>
                    
                    <tr>
                        <th width="100px">Código</th>
                        <th>Descripción</th>
                        <th>Existencia</th>
                        <th width="100px">Cantidad</th>
                        <th class="textrigth">Precio</th>
                        <th class="textrigth">Precio Total</th>
                        <th>Acción</th>
                    </tr>
                    <tr>
                        
                        <td><input type="text" name="txt_cod_producto" id="txt_cod_producto"></td>              
                        <td id="txt_descripcion">-</td>
                        <td id="txt_existencia">-</td>
                        <td><input type="text" name="txt_cant_producto" id="txt_cant_producto" value="0" min="1" disabled></td>
                        <td id="txt_precio" class="txtright">0.00</td>
                        <td id="txt_precio_total" class="txtright">0.00</td>
                        <td><a href="#" id="add_product_venta" class="link_add"><i class="fas fa-plus"></i> Agregar</a></td>
                    </tr>
                    
                    <tr>
                        <th>Código</th>
                        <th colspan="2">Descripción</th>
                        <th>Cantidad</th>
                        <th class="txtright">Precio</th>
                        <th class="txtright">Precio Total</th>
                        <th>Acción</th>
                    </tr>
                </thead>
                <tbody id="detalle_venta">
                   <!---CONTENIDO AJAX--->
                </tbody>
                <tfoot id="detalle_totales">
                   <!--CONTENIDO AJAX--->
                </tfoot>
            </table>
        </section>
        <?PHP
        include "../sistema/recursos/footer.php";
        ?>
        <script type="text/javascript">  
            $(document).ready(function(){
                var usuarioid = '<?PHP echo $_SESSION['idUser'];?>';
                serchForDetalle(usuarioid);
            });
        </script>
    </body>
</html>