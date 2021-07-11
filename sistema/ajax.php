<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
include '../conexion/conexion.php';
session_start();
//print_r($_POST); exit;
if (!empty($_POST)) {
    //extraer datos del producto
    
    if ($_POST['action'] == 'infoProducto') {
        
        $producto_id = $_POST['producto'];
        $query = mysqli_query($conexion, "SELECT codproducto, descripcion,existencia,precio"
                . "                       FROM producto"
                . "                       WHERE descripcion LIKE '%$producto_id%'AND estatus = 1");
        mysqli_close($conexion);

        $result = mysqli_num_rows($query);

        if ($result > 0) {
            $data = mysqli_fetch_assoc($query);
            echo json_encode($data, JSON_UNESCAPED_UNICODE);
            exit;
        } else {
            echo 'error';
            exit;
        }
    }
    //extraer datos del producto
    
    if ($_POST['action'] == 'infoProductoAgregar') {
        
        $producto_id = $_POST['producto'];
        $query = mysqli_query($conexion, "SELECT codproducto, descripcion,existencia,precio"
                . "                       FROM producto"
                . "                       WHERE codproducto LIKE '%$producto_id%'AND estatus = 1");
        mysqli_close($conexion);

        $result = mysqli_num_rows($query);

        if ($result > 0) {
            $data = mysqli_fetch_assoc($query);
            echo json_encode($data, JSON_UNESCAPED_UNICODE);
            exit;
        } else {
            echo 'error';
            exit;
        }
    }
    //agregar productos a entradas
    if ($_POST['action'] == 'addProduct') {
        if (!empty($_POST['cantidad']) || !empty($_POST['precio']) || !empty($_POST['producto_id'])) {
            $cantidad = $_POST['cantidad'];
            $precio = $_POST['precio'];
            $producto_id = $_POST['producto_id'];
            $usuario_id = $_SESSION['idUser'];

            $query_insert = mysqli_query($conexion, "INSERT INTO entradas("
                    . "                              codproducto,cantidad,precio,usuario_id) "
                    . "                              VALUES($producto_id,$cantidad,$precio,$usuario_id)");

            if ($query_insert) {
                //ejecutar procedimiento almacenado
                $query_upd = mysqli_query($conexion, "CALL actualizar_precio_producto($cantidad,$precio,$producto_id)");

                $result_pro = mysqli_num_rows($query_upd);
                if ($result_pro > 0) {
                    $data = mysqli_fetch_assoc($query_upd);
                    $data['producto_id'] = $producto_id;
                    echo json_encode($data, JSON_UNESCAPED_UNICODE);
                    exit;
                }
            } else {
                echo 'error';
            }
            mysqli_close($conexion);
        } else {
            echo 'error';
        }
        exit;
    }

    //eliminar producto
    if ($_POST['action'] == 'delProduct') {

        if (empty($_POST['producto_id']) || !is_numeric($_POST['producto_id'])) {
            echo "error";
        } else {
            $idproducto = $_POST['producto_id'];
            //  $query_delete = mysqli_query($conexion, "DELETE FROM usuario WHERE idusuario = $idusuario"); borrado fisico
            $query_delete = mysqli_query($conexion, "UPDATE producto SET estatus = 0 WHERE codproducto = $idproducto"); //borrado logico
            mysqli_close($conexion);
            if ($query_delete) {
                echo 'ok';
                // header("Location: listar_proveedores.php");
            } else {

                echo "Error al eliminar";
            }
        }
        echo 'error';
        exit;
    }

    //metodo para buscar cliente
    if ($_POST['action'] == 'searchCliente') {
        if (!empty($_POST['cliente'])) {
            $nit = $_POST['cliente'];
            $query = mysqli_query($conexion, "SELECT * FROM cliente WHERE nit LIKE '$nit' and estatus = 1");
            mysqli_close($conexion);
            $result = mysqli_num_rows($query);

            $data = '';
            if ($result > 0) {
                $data = mysqli_fetch_assoc($query);
            } else {
                $data = 0;
            }
            echo json_encode($data, JSON_UNESCAPED_UNICODE);
        }
        exit;
    }
    //funcion para cargar el detalle de las ventas fiadas
    if ($_POST['action'] == 'cargarDetalleFiado') {
        if (!empty($_POST['cliente'])) {
            // print_r($_POST);
            // exit;
            $idCliente = $_POST['cliente'];
            $token = md5($_SESSION['idUser']);

            $detalleTabla = '';
            $sub_total = 0;
            $iva = 0;
            $total = 0;
            $arrayData = array();

            $query_PA_datos = mysqli_query($conexion, "SELECT  DF.codproducto,p.descripcion,DF.cantidad,DF.precio_venta FROM factura f 
			INNER JOIN detallefactura DF
			ON 
			DF.nofactura = f.nofactura 
			INNER JOIN producto p 
			on p.codproducto = DF.codproducto
			WHERE f.id_tipoF = 3 and f.codcliente = $idCliente AND f.estatus = 1");

            $query_iva = mysqli_query($conexion, "SELECT iva FROM configuracion");
            $result_iva = mysqli_num_rows($query_iva);

            $result_PA_datos = mysqli_num_rows($query_PA_datos);
            if ($result_PA_datos > 0) {
                if ($result_iva > 0) {
                    $info_va = mysqli_fetch_assoc($query_iva);
                    $iva = $info_va['iva'];
                }
                while ($datosPA = mysqli_fetch_assoc($query_PA_datos)) {
                    //variables para calcular los totales de la factura
                    $precioTotal = round(($datosPA['cantidad'] * $datosPA['precio_venta']), 2);
                    $sub_total = round($sub_total + $precioTotal, 2);
                    $total = round($total + $precioTotal, 2);

                    //codigo para enviar los datos a la tabla detalle temp
                    $codP = $datosPA['codproducto'];
                    $cantidadP = $datosPA['cantidad'];
                    $precioP = $datosPA['precio_venta'];

                    $addFiado = mysqli_query($conexion, "CALL addDetalleTempFiado($codP,$cantidadP,'$token')");

                    //armado de la tabla detalle

                    $detalleTabla .= '<tr>
                      <td>' . $datosPA['codproducto'] . '</td>
                      <td colspan="2">' . $datosPA['descripcion'] . '</td>
                      <td class="txtcenter">' . $datosPA['cantidad'] . '</td>
                      <td class="txtright">' . $datosPA['precio_venta'] . '</td>
                      <td class="txtright">' . $precioTotal . '</td>
                      <td class="">
                      
                      </td>
                      </tr>';
                }
                //consulta para extraer el saldode la factura 
               
                $saldoAnterior = mysqli_query($conexion, "SELECT saldoFactura from saldo_factura_cliente WHERE (idcliente = $idCliente)");
                $respSaldoAnt = mysqli_fetch_assoc($saldoAnterior);
                $impuesto = round($sub_total * ($iva / 100), 2);
                $saldoAnt = $respSaldoAnt['saldoFactura'];
                $tl_sniva = round($sub_total - $impuesto, 2);
                $total = round(($tl_sniva + $impuesto), 2);
                $total_SA = round($total + $saldoAnt, 2);
                $detalleTotales = ' <tr>
                      <td colspan="5" class="textrigth">Subtotal $.</td>
                      <td class="textrigth">' . $tl_sniva . '</td>
                      </tr>
                      <tr>
                      <td colspan="5" class="textrigth"> IVA(' . $iva . '%)</td>
                      <td class="textrigth">' . $impuesto . '</td>
                      </tr>
                      <tr>
                      <td colspan="5" class="txtright"> TOTAL $.</td>
                      <td class="textrigth" >' . $total . '</td>
                      </tr>
                      <tr>
                      <td colspan="5" class="txtright"> SALDO ANTERIOR $.</td>
                      <td class="textrigth">' . $saldoAnt . '</td>
                      </tr>
                      <tr>
                      <td colspan="5" class="txtright"> SALDO ANTERIOR MAS TOTAL ACTUAL $.</td>
                      <td class="textrigth" id = "totalSA">' . $total_SA . '</td>
                      </tr>
                      <tr>';


                $arrayData['detalle'] = $detalleTabla;
                $arrayData['totales'] = $detalleTotales;

                echo json_encode($arrayData, JSON_UNESCAPED_UNICODE);
            } else {
                //echo "error";
                
                $saldoAnterior = mysqli_query($conexion, "SELECT saldoFactura from saldo_factura_cliente WHERE (idcliente = $idCliente)");
                $respSaldoAnt = mysqli_fetch_assoc($saldoAnterior);
                $impuesto = round($sub_total * ($iva / 100), 2);
                $saldoAnt = $respSaldoAnt['saldoFactura'];
                $tl_sniva = round($sub_total - $impuesto, 2);
                $total = round(($tl_sniva + $impuesto), 2);
                $total_SA = round($total + $saldoAnt, 2);
                $detalleTotales = ' <tr>
                      <td colspan="5" class="textrigth">Subtotal $.</td>
                      <td class="textrigth">' . $tl_sniva . '</td>
                      </tr>
                      <tr>
                      <td colspan="5" class="textrigth"> IVA(' . $iva . '%)</td>
                      <td class="textrigth">' . $impuesto . '</td>
                      </tr>
                      <tr>
                      <td colspan="5" class="txtright"> TOTAL $.</td>
                      <td class="textrigth" >' . $total . '</td>
                      </tr>
                      <tr>
                      <td colspan="5" class="txtright"> SALDO ANTERIOR $.</td>
                      <td class="textrigth">' . $saldoAnt . '</td>
                      </tr>
                      <tr>
                      <td colspan="5" class="txtright"> SALDO ANTERIOR MAS TOTAL ACTUAL $.</td>
                      <td class="textrigth" id = "totalSA">' . $total_SA . '</td>
                      </tr>
                      <tr>';
                
                $detalleTabla .= '<tr>
                      <td>' . '1111'. '</td>
                      <td colspan="2">' . 'Saldo Anterior de la deuda' . '</td>
                      <td class="txtcenter">' . '-' . '</td>
                      <td class="txtright">' . '-' . '</td>
                      <td class="txtright">' .$saldoAnt . '</td>
                      <td class="">
                      
                      </td>
                      </tr>';
                $arrayData['detalle'] = $detalleTabla;
                $arrayData['totales'] = $detalleTotales;
                echo json_encode($arrayData, JSON_UNESCAPED_UNICODE);
                
            }
            mysqli_close($conexion);
        }
        exit;
    }
    //metodo para buscar el cliente y cargarlo dentro del formulario pagar cliente 
    if ($_POST['action'] == 'buscarCliente') {
        if (!empty($_POST['cliente'])) {
            $idcliente = $_POST['cliente'];
            $query = mysqli_query($conexion, "SELECT nit,nombre,telefono,direccion FROM cliente WHERE idcliente = $idcliente and estatus = 1");
            mysqli_close($conexion);
            $result = mysqli_num_rows($query);

            $data = '';
            if ($result > 0) {
                $data = mysqli_fetch_assoc($query);
            } else {
                $data = 0;
            }
            echo json_encode($data, JSON_UNESCAPED_UNICODE);
        }
        exit;
    }
    //metodo para registrar un cliente desde una venta
    if ($_POST['action'] == 'addCliente') {

        $nit = $_POST['nit_cliente'];
        $nombre = $_POST['nom_cliente'];
        $telefono = $_POST['tel_cliente'];
        $direccion = $_POST['dir_cliente'];
        $usuario_id = $_SESSION['idUser'];

        $query_insert = mysqli_query($conexion, "INSERT INTO cliente(nit,nombre,telefono,direccion,usuario_id)"
                . "       VALUES('$nit','$nombre','$telefono','$direccion','$usuario_id')");


        if ($query_insert) {
            $codCliente = mysqli_insert_id($conexion);
            $msg = $codCliente;
        } else {
            $msg = 'error';
        }
        mysqli_close($conexion);
        echo $msg;
        exit;
    }
    //funcion para enviar el monto de pago de la factura
    if ($_POST['action'] == 'pagarCuenta') {
        if (!empty($_POST['montoPago'])) {
            $saldoAN = $_POST['saldoAN'];
            $monto = $_POST['montoPago'];
            $saldoActual = 0.00;
            $cambio = 0.00;
            $arrayData = array();
            //validar para poder restar los saldos
            if ($saldoAN > $monto) {
                $saldoActual = round(($saldoAN - $monto), 2);
                $cambio = 0.00;

                $monto = '
                    <tr>
                        <td colspan="5" class="txtright" id="txtCambio"> CAMBIO $.</td>
                        <td class="textleft" id = "cambio">' . $cambio . '</td>
                    </tr>
                    <tr>
                        <td colspan="5" class="txtright" id="txtSActual"> SALDO ACTUAL $.</td>
                        <td class="textleft" id = "saldoActual">' . $saldoActual . '</td>
                    </tr>';
                $arrayData['detalleMonto'] = $monto;
                echo json_encode($arrayData, JSON_UNESCAPED_UNICODE);
            } else {
                $saldoActual = 0.00;
                $cambio = round(($monto - $saldoAN), 2);
                $monto = '
                    <tr>
                        <td colspan="5" class="txtright"> CAMBIO $.</td>
                        <td class="textleft" id = "cambio">' . $cambio . '</td>
                    </tr>
                    <tr>
                        <td colspan="5" class="txtright"> SALDO ACTUAL $.</td>
                        <td class="textleft" id = "saldoActual">' . $saldoActual . '</td>
                    </tr>';
                $arrayData['detalleMonto'] = $monto;
                echo json_encode($arrayData, JSON_UNESCAPED_UNICODE);
            }
        } else{
            echo 'error';
        }
        exit;
    }
    //agregar producto al detalle temporal de ventas
    if ($_POST['action'] == 'addProductoDetalle') {
        // print_r($_POST);exit;
        if (empty($_POST['producto']) || empty($_POST['cantidad'])) {
            echo 'error';
            
        } else {
            
            $codproducto = $_POST['producto'];
            $cantidad = $_POST['cantidad'];
            $token = md5($_SESSION['idUser']);

            $query_iva = mysqli_query($conexion, "SELECT iva FROM configuracion");
            $result_iva = mysqli_num_rows($query_iva);

            $query_detalle_temp = mysqli_query($conexion, "CALL add_detalle_temp($codproducto,$cantidad,'$token')");
            $result = mysqli_num_rows($query_detalle_temp);

            $detalleTabla = '';
            $sub_total = 0;
            $iva = 0;
            $total = 0;
            $arrayData = array();

            if ($result > 0) {
                if ($result_iva > 0) {
                    $info_va = mysqli_fetch_assoc($query_iva);
                    $iva = $info_va['iva'];
                }

                while ($data = mysqli_fetch_assoc($query_detalle_temp)) {
                    $precioTotal = round(($data['cantidad'] * $data['precio_venta']), 2);
                    $sub_total = round($sub_total + $precioTotal, 2);
                    $total = round($total + $precioTotal, 2);
                    $codP = $data['codproducto'];

                    $detalleTabla .= '<tr>
                        <td>' . $data['codproducto'] . '</td>
                        <td colspan="2">' . $data['descripcion'] . '</td>
                        <td class="txtcenter">' . $data['cantidad'] . '</td>
                        <td class="txtright">' . $data['precio_venta'] . '</td>
                        <td class="txtright">' . $precioTotal . '</td>
                        <td class="">
                            <a class="link_delete" href="#" onclick = "event.preventDefault(); del_product_detalle(' . $data['correlativo'] . ');"><i class="far fa-trash-alt"></i></a>
                        </td>
                    </tr>';
                }
                $impuesto = round($sub_total * ($iva / 100), 2);
                $tl_sniva = round($sub_total - $impuesto, 2);
                $total = round($tl_sniva + $impuesto, 2);

                $detalleTotales = ' <tr>
                        <td colspan="5" class="textrigth">Subtotal Q.</td>
                        <td class="textrigth">' . $tl_sniva . '</td>
                    </tr>
                    <tr>
                        <td colspan="5" class="textrigth"> IVA(' . $iva . '%)</td>
                        <td class="textrigth">' . $impuesto . '</td>
                    </tr>
                    <tr>
                        <td colspan="5" class="txtright"> TOTAL Q.</td>
                        <td class="textrigth">' . $total . '</td>
                    </tr>';

                $arrayData['detalle'] = $detalleTabla;
                $arrayData['totales'] = $detalleTotales;

                echo json_encode($arrayData, JSON_UNESCAPED_UNICODE);
            } else {
                echo 'error';
            }
            mysqli_close($conexion);
        }
        exit;
    }


    //Extraer datos de detalle temp
    if ($_POST['action'] == 'serchForDetalle') {
        // print_r($_POST);exit;
        if (empty($_POST['user'])) {
            echo 'error';
        } else {

            $token = md5($_SESSION['idUser']);

            $query = mysqli_query($conexion, "SELECT tmp.correlativo,"
                    . "                       tmp.token_user,"
                    . "                       tmp.cantidad,"
                    . "                       tmp.precio_venta,"
                    . "                       p.codproducto,"
                    . "                       p.descripcion"
                    . "                       FROM detalle_temp tmp "
                    . "                       INNER JOIN producto p"
                    . "                       ON"
                    . "                       tmp.codproducto = p.codproducto"
                    . "                        WHERE token_user = '$token'");

            $result = mysqli_num_rows($query);

            $query_iva = mysqli_query($conexion, "SELECT iva FROM configuracion");
            $result_iva = mysqli_num_rows($query_iva);

            $detalleTabla = '';
            $sub_total = 0;
            $iva = 0;
            $total = 0;
            $arrayData = array();

            if ($result > 0) {
                if ($result_iva > 0) {
                    $info_va = mysqli_fetch_assoc($query_iva);
                    $iva = $info_va['iva'];
                }

                while ($data = mysqli_fetch_assoc($query)) {
                    $precioTotal = round($data['cantidad'] * $data['precio_venta'], 2);
                    $sub_total = round($sub_total + $precioTotal, 2);
                    $total = round($total + $precioTotal, 2);

                    $detalleTabla .= '<tr>
                        <td>' . $data['codproducto'] . '</td>
                        <td colspan="2">' . $data['descripcion'] . '</td>
                        <td class="txtcenter">' . $data['cantidad'] . '</td>
                        <td class="txtright">' . $data['precio_venta'] . '</td>
                        <td class="txtright">' . $precioTotal . '</td>
                        <td class="">
                            <a class="link_delete" href="#" onclick = "event.preventDefault(); del_product_detalle(' . $data['correlativo'] . ');"><i class="far fa-trash-alt"></i></a>
                        </td>
                    </tr>';
                }
                $impuesto = round($sub_total * ($iva / 100), 2);
                $tl_sniva = round($sub_total - $impuesto, 2);
                $total = round($tl_sniva + $impuesto, 2);

                $detalleTotales = ' <tr>
                        <td colspan="5" class="textrigth">Subtotal Q.</td>
                        <td class="textrigth">' . $tl_sniva . '</td>
                    </tr>
                    <tr>
                        <td colspan="5" class="textrigth"> IVA(' . $iva . '%)</td>
                        <td class="textrigth">' . $impuesto . '</td>
                    </tr>
                    <tr>
                        <td colspan="5" class="txtright"> TOTAL Q.</td>
                        <td class="textrigth">' . $total . '</td>
                    </tr>';

                $arrayData['detalle'] = $detalleTabla;
                $arrayData['totales'] = $detalleTotales;

                echo json_encode($arrayData, JSON_UNESCAPED_UNICODE);
            } else {
                echo 'error';
            }
            mysqli_close($conexion);
        }
        exit;
    }

    //metodo para eliminar delProductDetalle
    if ($_POST['action'] == 'delProductoDetalle') {
        // print_r($_POST);exit;
        if (empty($_POST['id_detalle'])) {
            echo 'error';
        } else {

            $token = md5($_SESSION['idUser']);
            $id_detalle = $_POST['id_detalle'];


            $query_iva = mysqli_query($conexion, "SELECT iva FROM configuracion");
            $result_iva = mysqli_num_rows($query_iva);

            $query_detalle_temp = mysqli_query($conexion, "CALL del_detalle_temp($id_detalle, '$token')");
            $result = mysqli_num_rows($query_detalle_temp);

            $detalleTabla = '';
            $sub_total = 0;
            $iva = 0;
            $total = 0;
            $arrayData = array();

            if ($result > 0) {
                if ($result_iva > 0) {
                    $info_va = mysqli_fetch_assoc($query_iva);
                    $iva = $info_va['iva'];
                }

                while ($data = mysqli_fetch_assoc($query_detalle_temp)) {
                    $precioTotal = round($data['cantidad'] * $data['precio_venta'], 2);
                    $sub_total = round($sub_total + $precioTotal, 2);
                    $total = round($total + $precioTotal, 2);

                    $detalleTabla .= '<tr>
                        <td>' . $data['codproducto'] . '</td>
                        <td colspan="2">' . $data['descripcion'] . '</td>
                        <td class="txtcenter">' . $data['cantidad'] . '</td>
                        <td class="txtright">' . $data['precio_venta'] . '</td>
                        <td class="txtright">' . $precioTotal . '</td>
                        <td class="">
                            <a class="link_delete" href="#" onclick = "event.preventDefault(); del_product_detalle(' . $data['correlativo'] . ');"><i class="far fa-trash-alt"></i></a>
                        </td>
                    </tr>';
                }
                $impuesto = round($sub_total * ($iva / 100), 2);
                $tl_sniva = round($sub_total - $impuesto, 2);
                $total = round($tl_sniva + $impuesto, 2);

                $detalleTotales = ' <tr>
                        <td colspan="5" class="textrigth">Subtotal $.</td>
                        <td class="textrigth">' . $tl_sniva . '</td>
                    </tr>
                    <tr>
                        <td colspan="5" class="textrigth"> IVA(' . $iva . '%)</td>
                        <td class="textrigth">' . $impuesto . '</td>
                    </tr>
                    <tr>
                        <td colspan="5" class="txtright"> TOTAL $.</td>
                        <td class="textrigth">' . $total . '</td>
                    </tr>';

                $arrayData['detalle'] = $detalleTabla;
                $arrayData['totales'] = $detalleTotales;

                echo json_encode($arrayData, JSON_UNESCAPED_UNICODE);
            } else {
                echo 'error';
            }
            mysqli_close($conexion);
        }
        exit;
    }

    // funcion para limpiar o anular la una venta

    if ($_POST['action'] == 'anularVenta') {
        $token = md5($_SESSION['idUser']);
        $query_del = mysqli_query($conexion, "DELETE FROM detalle_temp WHERE token_user = '$token'");
        mysqli_close($conexion);

        if ($query_del) {
            echo 'ok';
        } else {
            echo 'error';
        }
        exit;
    }
    //funcion para anular el pago de una cuenta fiada
    if ($_POST['action'] == 'anularPago') {
        $token = md5($_SESSION['idUser']);
        $query_del = mysqli_query($conexion, "DELETE FROM detalle_temp WHERE token_user = '$token'");
        mysqli_close($conexion);

        if ($query_del) {
            echo 'ok';
        } else {
            echo 'error';
        }
        exit;
    }
    //funcion para procesar la venta o facturar la venta de un cliente que fia
    if ($_POST['action'] == 'procesarVenta_') {
        
        if (empty($_POST['nitcliente'])) {
            $codCliente = 15;
        } else {
            $CICliente = $_POST['nitcliente'];
            $queryCodCli = mysqli_query($conexion, "SELECT idcliente FROM cliente c where c.nit = '$CICliente' ");
            $datoscodCli = mysqli_fetch_assoc($queryCodCli);
            $codCliente = $datoscodCli['idcliente'];
        }

        //validar que el tipo de factura no vaya vacio
        if (empty($_POST['tipo_fact'])) {
            $tipoFactura = 1;
        } else {
            $tipoFactura = $_POST['tipo_fact'];
        }

        $token = md5($_SESSION['idUser']);
        $usuario = $_SESSION['idUser'];
        $saldoACt = $_POST['saldoQueda'];
        

        $query = mysqli_query($conexion, "SELECT * FROM  detalle_temp WHERE token_user = '$token'");
        $result = mysqli_num_rows($query);
        
        if ($result > 0) {
            $queryDelete = mysqli_query($conexion, "UPDATE factura SET estatus = 0 WHERE (codcliente = $codCliente AND id_tipoF = 3)");
         //   $queryActualizarSaldo = mysqli_query($conexion, "UPDATE saldo_factura_cliente SET saldoFactura = $saldoACt WHERE (codCliente = $codCliente) ");
            $querySaldo = mysqli_query($conexion, "SELECT saldoFactura from saldo_factura_cliente WHERE idcliente = $codCliente");
            $resultSaldo = mysqli_num_rows($querySaldo);
            if($resultSaldo > 0){
                $updateSaldo = mysqli_query($conexion, "UPDATE saldo_factura_cliente SET saldoFactura = $saldoACt WHERE (idcliente = $codCliente)");
            }else{
                $queryInsert = mysqli_query($conexion, "INSERT saldo_factura_cliente(idcliente,saldoFactura)"
                        . "                 VALUES($codCliente,$saldoACt)");
            }
            $query_procesar = mysqli_query($conexion, "CALL procesar_venta($usuario,$codCliente,'$token',$tipoFactura)");

            //insertar saldo en los clientes
           
            $result_detalle = mysqli_num_rows($query_procesar);
            if ($result_detalle > 0) {
                $data = mysqli_fetch_assoc($query_procesar);
                echo json_encode($data, JSON_UNESCAPED_UNICODE);
            } else {
                echo "error procedimiento";
            }
        } else {
            //$queryDelete = mysqli_query($conexion, "UPDATE factura SET estatus = 0 WHERE (codcliente = $codCliente AND id_tipoF = 3)");
         //   $queryActualizarSaldo = mysqli_query($conexion, "UPDATE saldo_factura_cliente SET saldoFactura = $saldoACt WHERE (codCliente = $codCliente) ");
            $querySaldo = mysqli_query($conexion, "SELECT saldoFactura from saldo_factura_cliente WHERE idcliente = $codCliente");
            $resultSaldo = mysqli_num_rows($querySaldo);
            if($resultSaldo > 0){
                $updateSaldo = mysqli_query($conexion, "UPDATE saldo_factura_cliente SET saldoFactura = $saldoACt WHERE (idcliente = $codCliente)");
            }else{
                $queryInsert = mysqli_query($conexion, "INSERT saldo_factura_cliente(idcliente,saldoFactura)"
                        . "                 VALUES($codCliente,$saldoACt)");
            }
            $query_procesar = mysqli_query($conexion, "CALL procesar_venta($usuario,$codCliente,'$token',$tipoFactura)");

            //insertar saldo en los clientes
           
            $result_detalle = mysqli_num_rows($query_procesar);
            if ($result_detalle > 0) {
                $data = array(
                    "codcliente" => $codCliente,
                    "nofactura" => "010101"     
                );
                echo json_encode($data, JSON_UNESCAPED_UNICODE); //modificado el codigo del cliente para facturas solo con deudas atrasadas 
            } else {
                echo "error procedimiento";
            }
            
        }
        mysqli_close($conexion);
        exit;
    }

    //funcion para procesar venta
    if ($_POST['action'] == 'procesarVenta') {

        if (empty($_POST['codcliente'])) {
            $codCliente = 15;
        } else {
            $codCliente = $_POST['codcliente'];
        }
        //validar que el tipo de factura no vaya vacio
        if (empty($_POST['tipo_fact'])) {
            $tipoFactura = 1;
        } else {
            $tipoFactura = $_POST['tipo_fact'];
        }

        $token = md5($_SESSION['idUser']);
        $usuario = $_SESSION['idUser'];

        $query = mysqli_query($conexion, "SELECT * FROM  detalle_temp WHERE token_user = '$token'");
        $result = mysqli_num_rows($query);
        if ($result > 0) {
            $query_procesar = mysqli_query($conexion, "CALL procesar_venta($usuario,$codCliente,'$token',$tipoFactura)");

            $result_detalle = mysqli_num_rows($query_procesar);

            if ($result_detalle > 0) {
                $data = mysqli_fetch_assoc($query_procesar);
                echo json_encode($data, JSON_UNESCAPED_UNICODE);
            } else {
                echo "error procedimiento";
            }
        } else {
            echo "errorconsultasql";
        }
        mysqli_close($conexion);
        exit;
    }
    //validar Info-Factura
    if ($_POST['action'] == 'infoFactura') {
        if (!empty($_POST['nofactura'])) {
            $nofactura = $_POST['nofactura'];
            $query = mysqli_query($conexion, "SELECT * FROM factura WHERE nofactura = '$nofactura' and estatus = 1");
            mysqli_close($conexion);
            $result = mysqli_num_rows($query);
            if ($result > 0) {
                $data = mysqli_fetch_assoc($query);
                echo json_encode($data, JSON_UNESCAPED_UNICODE);
                exit;
            }
        }
        echo "error";
        exit;
    }

    //funcion para anular la factura
    if ($_POST['action'] == 'anularFactura') {
        if (!empty($_POST['noFactura'])) {
            $nofactura = $_POST['noFactura'];

            $query_anular = mysqli_query($conexion, "CALL anular_factura($nofactura)");
            mysqli_close($conexion);
            $result = mysqli_num_rows($query_anular);
            if ($result > 0) {
                $data = mysqli_fetch_assoc($query_anular);
                echo json_encode($data, JSON_UNESCAPED_UNICODE);
                exit;
            }
        }
        echo "error noFactura";
        exit;
    }
}
exit;
?>
