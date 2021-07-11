/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
//funcion para cargar la foto Java-Script

$(document).ready(function () {

    //--------------------- SELECCIONAR FOTO PRODUCTO ---------------------
    $("#foto").on("change", function () {
        var uploadFoto = document.getElementById("foto").value;
        var foto = document.getElementById("foto").files;
        var nav = window.URL || window.webkitURL;
        var contactAlert = document.getElementById('form_alert');

        if (uploadFoto !== '')
        {
            var type = foto[0].type;
            var name = foto[0].name;
            if (type !== 'image/jpeg' && type !== 'image/jpg' && type !== 'image/png')
            {
                contactAlert.innerHTML = '<p class="errorArchivo">El archivo no es válido.</p>';
                $("#img").remove();
                $(".delPhoto").addClass('notBlock');
                $('#foto').val('');
                return false;
            } else {
                contactAlert.innerHTML = '';
                $("#img").remove();
                $(".delPhoto").removeClass('notBlock');
                var objeto_url = nav.createObjectURL(this.files[0]);
                $(".prevPhoto").append("<img id='img' src=" + objeto_url + ">");
                $(".upimg label").remove();

            }
        } else {
            alert("No selecciono foto");
            $("#img").remove();
        }
    });

    $('.delPhoto').click(function () {
        $('#foto').val('');
        $(".delPhoto").addClass('notBlock');
        $("#img").remove();

        if ($("#foto_actual") && $("#foto_remove")) {
            $("foto_remove").val('img_producto.png');
        }

    });

    //modal form add product
    $('.add_product').click(function (e) {
        e.preventDefault();
        var producto = $(this).attr('product'); // permite acceder a los atributos del product
        var action = 'infoProductoAgregar';

        $.ajax({
            url: 'ajax.php',
            type: 'POST',
            async: true,
            data: {action: action, producto: producto},
            success: function (response) {

                if (response !== 'error') {
                    var info = JSON.parse(response);
                     
                    // $('#producto_id').val(info.codproducto);
                    //$('.nameProducto').html(info.descripcion);
                    
                    $('.bodyModal').html(' <form action="" method="POST" name="form_add_product" id="form_add_product" onsubmit="event.preventDefault(); sendDataProduct();">' +
                            '<h1><i class="fas fa-cube" style="font-size: 45pt;"></i><br> Agregar Producto</h1>' +
                            '<h2 class="nameProducto">' + info.descripcion + '</h2><br>' +
                            '<input type="text" name="cantidad" id="txtCantidad" placeholder="Cantidad del producto" required><br>' +
                            '<input type="text" name="precio" id="txtPrecio" placeholder="Precio del Producto" required>' +
                            '<input type="hidden" name="producto_id" id="producto_id" value="' + info.codproducto + '" required>' +
                            '<input type="hidden" name="action" value="addProduct" required>' +
                            '<div class="alert alertAddProduct" ></div>' +
                            '<button type="submit" class="btn_new"><i class="fas fa-plus"></i> Agregar</button>' +
                            '<a href="#" class="btn_ok claseModal" onclick="closeModal();"><i class="fas fa-ban"></i> Cerrar</a>' +
                            '</form>');
                }
            },

            error: function (error) {
                console.log(error);
            }
        });


        $('.modal').fadeIn();  //permite mostrar el modal al momento de dato click en +Agregar


    });

    //modal form Delete Producto 
    $('.del_product').click(function (e) {
        e.preventDefault();
        var producto = $(this).attr('product'); // permite acceder a los atributos del product
        var action = 'infoProducto';

        $.ajax({
            url: 'ajax.php',
            type: 'POST',
            async: true,
            data: {action: action, producto: producto},
            success: function (response) {

                if (response !== 'error') {
                    var info = JSON.parse(response);

                    // $('#producto_id').val(info.codproducto);
                    //$('.nameProducto').html(info.descripcion);

                    $('.bodyModal').html(' <form action="" method="POST" name="form_del_product" id="form_del_product" onsubmit="event.preventDefault(); delProduct();">' +
                            '<h1><i class="fas fa-cube" style="font-size: 45pt;"></i><br> Eliminar Producto</h1>' +
                            '<p>Estas seguro de eliminar el siguiente registro? </p>' +
                            '<h2 class="nameProducto">' + info.descripcion + '</h2><br>' +
                            '<input type="hidden" name="producto_id" id="producto_id" value="' + info.codproducto + '" required>' +
                            '<input type="hidden" name="action" value="delProduct" required>' +
                            '<div class="alert alertAddProduct" ></div>' +
                            '<a href="#" class="btn_cancel" onclick="closeModal();" ><i class="fas fa-ban"></i> Cancelar</a>' +
                            '<button type="submit" class = "btn_ok"><i class="far fa-trash-alt"></i> Eliminar </button>' +
                            '</form>');
                }
            },

            error: function (error) {
                console.log(error);
            }
        });


        $('.modal').fadeIn();  //permite mostrar el modal al momento de dato click en +Agregar


    });

    //funcion para el search_proveedor

    $('#search_proveedor').change(function (e) {
        e.preventDefault();
        var sistema = getUrl();
        //alert("hola");
        location.href = sistema + 'buscar_productos.php?proveedor=' + $(this).val();
    });
    //funcion para activar los campos de la factura para registrar un cliente
    $('.btn_new_cliente').click(function (e) {
        e.preventDefault();
        $('#nom_cliente').removeAttr('disabled');
        $('#tel_cliente').removeAttr('disabled');
        $('#dir_cliente').removeAttr('disabled');

        $('#div_registro_cliente').slideDown();
    });

    //funcion para mostrar datos del ciente que fio
    $('.datosCliente').click(function (e) {
        e.preventDefault();

        var idcliente = $(this).attr('idCliente');
        var action = 'buscarCliente';
        $.ajax({
            url: 'ajax.php',
            type: "POST",
            async: true,
            data: {action: action, cliente: idcliente},

            success: function (response) {
                if (response != 0) {
                    var info = JSON.parse(response);

                    $('#nit_cliente_').val(info.nit);
                    $('#nom_cliente_').val(info.nombre);
                    $('#tel_cliente_').val(info.telefono);
                    $('#dir_cliente_').val(info.direccion);
                    //bloquear los campos de los datos del cliente
                    $('#nit_cliente_').attr('disabled', 'disabled');
                    $('#nom_cliente_').attr('disabled', 'disabled');
                    $('#tel_cliente_').attr('disabled', 'disabled');
                    $('#dir_cliente_').attr('disabled', 'disabled');

                }
                viewProcesarFiado(); //funcion para procesar o realizar el pago del fiado
            },
            error: function (error) {

            }
        });
        $('.modalFacturacion').fadeIn();
    });
    //funcion para mostrar el detalle de las facturas fiadas
    $('.datosCliente').click(function (e) {
        e.preventDefault();

        var idcliente = $(this).attr('idCliente');
        var action = 'cargarDetalleFiado';
        $.ajax({
            url: 'ajax.php',
            type: "POST",
            async: true,
            data: {action: action, cliente: idcliente},

            success: function (response) {
                if (response != 'error') {
                    var info = JSON.parse(response);
                    // console.log(response);
                    $('#detalle_venta_').html(info.detalle);
                    $('#detalle_totales_').html(info.totales);


                    $('#txt_cod_producto_').val('');
                    $('#txt_descripcion_').html('-');
                    $('#txt_existencia_').html('-');
                    $('#txt_cant_producto_').val('0');
                    $('#txt_precio_').html('0.00');
                    $('#txt_precio_total_').html('0.00');

                    //bloquear la cantidad 
                    $('#txt_cant_producto_').attr('disabled', 'disabled');
                    //ocultar el boton agregar
                    $('#add_product_venta_').slideUp();

                } else {
                    console.log('no datas');
                }
            },
            error: function (error) {

            }
        });

        $('.modalFacturacion').fadeIn();
    });
    //funcion pára buscar un cliente en la facturacion

    $('#nit_cliente').keyup(function (e) {
        e.preventDefault();

        var cl = $(this).val();
        var action = 'searchCliente';

        $.ajax({
            url: 'ajax.php',
            type: "POST",
            async: true,
            data: {action: action, cliente: cl},

            success: function (response) {

                if (response == 0) {
                    $('#idcliente').val('');
                    $('#nom_cliente').val('');
                    $('#tel_cliente').val('');
                    $('#dir_cliente').val('');
                    //mostrar el boton agregar
                    $('.btn_new_cliente').slideDown();
                } else {
                    var data = $.parseJSON(response);
                    $('#idcliente').val(data.idcliente);
                    $('#nom_cliente').val(data.nombre);
                    $('#tel_cliente').val(data.telefono);
                    $('#dir_cliente').val(data.direccion);
                    //ocultar el boton agregar
                    $('.btn_new_cliente').slideUp();

                    //bloquear los campos
                    $('#nom_cliente').attr('disabled', 'disabled');
                    $('#tel_cliente').attr('disabled', 'disabled');
                    $('#dir_cliente').attr('disabled', 'disabled');

                    //ocultar el boton guardar
                    $('#div_registro_cliente').slideUp();
                }
            },
            error: function (error) {

            }
        });
    });
    //funcion para actualizar el saldo de la cuenta del cliente que fio
     $('#monto').keyup(function (e) {
        e.preventDefault();

        var montoPago = $(this).val();
        var action = 'pagarCuenta';
        var codcliente = $('#idcliente').val();
        var saldoAN = $('#totalSA').html();
        $.ajax({
            url: 'ajax.php',
            type: "POST",
            async: true,
            data: {action: action, montoPago: montoPago,saldoAN:saldoAN},

            success: function (response) {
                //console.log(response);
                var info = JSON.parse(response); 
                
                if(response != 'error'){
                    
                  // console.log(info);
                     $('#detalleMonto').html(info.detalleMonto);
                }else{
                    console.log('no data')
                    $('#cambio').html('0.00');
                    $('#saldoActual').html('0.00');
                }
            },
            error: function (error) {
                console.log(error);
            }
        });
    });
    //funcion para crear un cliente ventas
    $('#form_new_cliente_venta').submit(function (e) {
        e.preventDefault();

        $.ajax({
            url: 'ajax.php',
            type: "POST",
            async: true,
            data: $('#form_new_cliente_venta').serialize(),

            success: function (response) {
                if (response != 'error') {
                    //Agregar id a input hiden
                    $('#idcliente').val(response);
                    //bloqueo de campos 
                    $('#nom_cliente').attr('disabled', 'disabled');
                    $('#tel_cliente').attr('disabled', 'disabled');
                    $('#dir_cliente').attr('disabled', 'disabled');

                    //ocultar el boton agregar
                    $('.btn_new_cliente').slideUp();
                    //oculta el boton guardar
                    $('#div_registro_cliente').slideUp();
                }
            },
            error: function (error) {

            }
        });
    });
    //funcion para buscar productos en una factura de fiado

    $('#txt_cod_producto_').keyup(function (e) {
        e.preventDefault();

        var producto = $(this).val();
        var action = 'infoProducto';
        if (producto != '') {
            $.ajax({
                url: 'ajax.php',
                type: "POST",
                async: true,
                data: {action: action, producto: producto},

                success: function (response) {
                    if (response != 'error') {
                        var info = JSON.parse(response);
                        $('#txt_descripcion_').html(info.descripcion);
                        $('#txt_existencia_').html(info.existencia);
                        $('#txt_cant_producto_').val('1');
                        $('#txt_precio_').html(info.precio);
                        $('#txt_precio_total_').html(info.precio);

                        //Activar el campo de cantidad
                        $('#txt_cant_producto_').removeAttr('disabled');

                        //Mostrar boton agregar 
                        $('#add_product_venta_').slideDown();

                    } else {
                        $('#txt_descripcion_').html('-');
                        $('#txt_existencia_').html('-');
                        $('#txt_cant_producto_').val('0');
                        $('#txt_precio_').html('0.00');
                        $('#txt_precio_total_').html('0.00');

                        //Bloquear la cantidad
                        $('#txt_cant_producto_').attr('disabled', 'disabled');
                        //ocultar boton agregar
                        $('#add_product_venta_').slideUp();
                    }
                },
                error: function (error) {

                }
            });
        }

    });
    //funcion para buscar un producto en la factura
    $('#txt_cod_producto').keyup(function (e) {
        e.preventDefault();

        var producto = $(this).val();
        var action = 'infoProducto';
        if (producto != '') {
            $.ajax({
                url: 'ajax.php',
                type: "POST",
                async: true,
                data: {action: action, producto: producto},

                success: function (response) {
                    if (response != 'error') {
                        var info = JSON.parse(response);
                        $('#cod_producto').val(info.codproducto);
                        $('#txt_descripcion').html(info.descripcion);
                        $('#txt_existencia').html(info.existencia);
                        $('#txt_cant_producto').val('1');
                        $('#txt_precio').html(info.precio);
                        $('#txt_precio_total').html(info.precio);

                        //Activar el campo de cantidad
                        $('#txt_cant_producto').removeAttr('disabled');

                        //Mostrar boton agregar 
                        $('#add_product_venta').slideDown();

                    } else {
                        $('#txt_descripcion').html('-');
                        $('#txt_existencia').html('-');
                        $('#txt_cant_producto').val('0');
                        $('#txt_precio').html('0.00');
                        $('#txt_precio_total').html('0.00');

                        //Bloquear la cantidad
                        $('#txt_cant_producto').attr('disabled', 'disabled');
                        //ocultar boton agregar
                        $('#add_product_venta').slideUp();
                    }
                },
                error: function (error) {

                }
            });
        }

    });
//funcion para validar la cantidad de productos antes de agregarlo dentro de la factura de fiar
    $('#txt_cant_producto_').keyup(function (e) {
        e.preventDefault();
        var precio_total = $(this).val() * $('#txt_precio_').html();
        var existencia = parseFloat($('#txt_existencia_').html());
        $('#txt_precio_total_').html(precio_total);

        //ocultar  el boton para agregar si la cantidad es menor que 1
        if (($(this).val() < 1 || isNaN($(this).val())) || ($(this).val() > existencia)) {
            $('#add_product_venta_').slideUp();
        } else {
            $('#add_product_venta_').slideDown();
        }
    });
//funcion para validar la cantidad del producto antes de agregarlo
    $('#txt_cant_producto').keyup(function (e) {
        e.preventDefault();
        var precio_total = $(this).val() * $('#txt_precio').html();
        var existencia = parseFloat($('#txt_existencia').html());
        $('#txt_precio_total').html(precio_total);

        //ocultar  el boton para agregar si la cantidad es menor que 1
        if (($(this).val() <= 0 || isNaN($(this).val())) || ($(this).val() > existencia)) {
            $('#add_product_venta').slideUp();
        } else {
            $('#add_product_venta').slideDown();
        }
    });
//funcion para validar la cantidad a pagar o monto a pagar
    $('#monto').keyup(function (e) {
        e.preventDefault();
        //var precio_total = $(this).val() * $('#txt_precio').html();
        //var existencia = parseFloat($('#txt_existencia').html());
       // $('#txt_precio_total').html(precio_total);

        //ocultar  el boton para agregar si la cantidad es menor que 1
        if (($(this).val() < 1 || isNaN($(this).val()))) {
           // $('#add_product_venta').slideUp();
             $('#btn_facturar_venta_').hide();
             $('#cambio').hide();
             $('#saldoActual').hide();
             $('#txtCambio').hide();
             $('#txtSActual').hide();
        } else {
             
             $('#btn_facturar_venta_').show();
        }
    });
//funcion para agregar productos al detalle de la factura de clientes que fian 
    $('#add_product_venta_').click(function (e) {
        e.preventDefault();

        if ($('#txt_cant_producto_').val() > 0) {
            var codproducto = $('#txt_cod_producto_').val();
            var cantidad = $('#txt_cant_producto_').val();
            var action = ('addProductoDetalle');
            $.ajax({
                url: 'ajax.php',
                type: 'POST',
                async: true,
                data: {action: action, producto: codproducto, cantidad: cantidad}, //permite recoger todos losdatos por el id del formulario

                success: function (response) {

                    if (response !== 'error') {
                        var info = JSON.parse(response);
                        $('#detalle_venta_').html(info.detalle);
                        $('#detalle_totales_').html(info.totales);


                        $('#txt_cod_producto_').val('');
                        $('#txt_descripcion_').html('-');
                        $('#txt_existencia_').html('-');
                        $('#txt_cant_producto_').val('0');
                        $('#txt_precio_').html('0.00');
                        $('#txt_precio_total_').html('0.00');

                        //bloquear la cantidad 
                        $('#txt_cant_producto_').attr('disabled', 'disabled');
                        //ocultar el boton agregar
                        $('#add_product_venta_').slideUp();
                    } else {
                        console.log('no data');
                    }
                    viewProcesar();
                },

                error: function (error) {

                }
            });

        }

    });
    //funcion para agregar productos al detalle de la factura
    $('#add_product_venta').click(function (e) {
        e.preventDefault();

        if ($('#txt_cant_producto').val() > 0) {
            var codproducto = $('#cod_producto').val();
            var cantidad = $('#txt_cant_producto').val();
            var action = ('addProductoDetalle');
            
            $.ajax({
                url: 'ajax.php',
                type: 'POST',
                async: true,
                data: {action: action, producto: codproducto, cantidad: cantidad}, //permite recoger todos losdatos por el id del formulario

                success: function (response) {

                    if (response !== 'error') {
                        var info = JSON.parse(response);
                        $('#detalle_venta').html(info.detalle);
                        $('#detalle_totales').html(info.totales);


                        $('#txt_cod_producto').val('');
                        $('#txt_descripcion').html('-');
                        $('#txt_existencia').html('-');
                        $('#txt_cant_producto').val('0');
                        $('#txt_precio').html('0.00');
                        $('#txt_precio_total').html('0.00');

                        //bloquear la cantidad 
                        $('#txt_cant_producto').attr('disabled', 'disabled');
                        //ocultar el boton agregar
                        $('#add_product_venta').slideUp();
                    } else {
                        console.log('no data');
                    }
                    viewProcesar();
                },

                error: function (error) {

                }
            });

        }

    });
//funcion para facturar la venta o pago de facturaas de un cliente
    $('#btn_facturar_venta_').click(function (e) {
        e.preventDefault();
        var rows = $('#detalle_venta_ tr').length;
        if (rows > 0) {
            var action = 'procesarVenta_';
            var nitcliente = $('#nit_cliente_').val();
            var tipo_fact = $('#tipo_factura_').val();
           // var saldoActual = $('#monto').val();
            var saldoQueda = $('#saldoActual').html();
         //   console.log(codcliente);

            $.ajax({
                url: 'ajax.php',
                type: 'POST',
                async: true,
                data: {action: action, nitcliente: nitcliente,tipo_fact: tipo_fact,saldoQueda:saldoQueda}, //permite recoger todos losdatos por el id del formulario

                success: function (response) {
                    console.log(response);

                    if (response != 'error') {
                        var info = JSON.parse(response);
                        console.log(info);   
                       generarPDF(info.codcliente, info.nofactura);
                       location.reload();
                    } else {
                        console.log('no data');
                    }

                },

                error: function (error) {

                }
            });
        }
    });
//funcion para anular la venta
    $('#btn_anular_venta').click(function (e) {
        e.preventDefault();
        var rows = $('#detalle_venta tr').length;
        if (rows > 0) {
            var action = 'anularVenta';

            $.ajax({
                url: 'ajax.php',
                type: 'POST',
                async: true,
                data: {action: action}, //permite recoger todos losdatos por el id del formulario

                success: function (response) {
                    console.log(response);

                    if (response != 'error') {
                        location.reload();
                    }
                },

                error: function (error) {

                }
            });
        }
    });
    //funcion para facturar venta
    $('#btn_facturar_venta').click(function (e) {
        e.preventDefault();
        var rows = $('#detalle_venta tr').length;
        if (rows > 0) {
            var action = 'procesarVenta';
            var codcliente = $('#idcliente').val();
            var tipo_fact = $('#tipo_factura').val();

            $.ajax({
                url: 'ajax.php',
                type: 'POST',
                async: true,
                data: {action: action, codcliente: codcliente, tipo_fact: tipo_fact}, //permite recoger todos losdatos por el id del formulario

                success: function (response) {
                    console.log(response);

                    if (response != 'error') {
                        var info = JSON.parse(response);
                        //console.log(info);
                        generarPDF(info.codcliente, info.nofactura);
                        location.reload();
                    } else {
                        console.log('no data');
                    }

                },

                error: function (error) {

                }
            });
        }
    });
//modal form anular factura
    $('.anular_factura').click(function (e) {
        e.preventDefault();
        var nofactura = $(this).attr('fac');
        var action = 'infoFactura';

        $.ajax({
            url: 'ajax.php',
            type: 'POST',
            async: true,
            data: {action: action, nofactura: nofactura},
            success: function (response) {

                if (response !== 'error') {
                    var info = JSON.parse(response);


                    $('.bodyModal').html(' <form action="" method="POST" name="form_anular_factura" id="form_anular_factura" onsubmit="event.preventDefault(); anularFactura();">' +
                            '<h1><i class="fas fa-cube" style="font-size: 45pt;"></i><br> Anular Factura</h1><br>' +
                            '<p>Desea anular la factura? </p>' +
                            '<p><strong>No.  ' + info.nofactura + '</strong></p>' +
                            '<p><strong>Monto.  ' + info.totalfactura + '</strong></p>' +
                            '<p><strong>Fecha.  ' + info.fecha + '</strong></p>' +
                            '<input type="hidden" name="action" value="anularFactura">' +
                            '<input type="hidden" name="no_factura" id="no_factura" value="' + info.nofactura + '" required>' +
                            '<div class="alert alertAddProduct" ></div>' +
                            '<button type="submit" class = "btn_ok"><i class="far fa-trash-alt"></i> Anular </button>' +
                            '<a href="#" class="btn_cancel" onclick="closeModal();" ><i class="fas fa-ban"></i> Cancelar</a>' +
                            '</form>');

                }
            },

            error: function (error) {
                console.log(error);
            }
        });


        $('.modal').fadeIn();  //permite mostrar el modal al momento de dato click en +Agregar


    });

    //visializar la factura 
    $('.view_factura').click(function (e) {
        e.preventDefault();
        var codCliente = $(this).attr('cl');
        var noFactura = $(this).attr('f');
        generarPDF(codCliente, noFactura);

    });
    //funcion para cancelar o elimicar los datos cargados en del detalle_temp de cliente que fia
     $('#btn_cancelar_').click(function (e) {
        e.preventDefault();
        var rows = $('#detalle_venta_ tr').length;
        if (rows > 0) {
            var action = 'anularPago';

            $.ajax({
                url: 'ajax.php',
                type: 'POST',
                async: true,
                data: {action: action}, //permite recoger todos losdatos por el id del formulario

                success: function (response) {
                    console.log(response);

                    if (response != 'error') {
                        $('#monto').val('');
                        location.reload();
                       // $('#monto').val('');
                    }
                },

                error: function (error) {

                }
            });
        }else{
            $('#monto').val('');
        }
    });
});//end ready 
//
//funcion para anular la factura
function anularFactura() {
    var noFactura = $('#no_factura').val();
    var action = 'anularFactura';

    $.ajax({
        url: 'ajax.php',
        type: 'POST',
        async: true,
        data: {action: action, noFactura: noFactura}, //permite recoger todos losdatos por el id del formulario

        success: function (response) {
            if (response == 'error') {
                $('.alertAddProduct').html('<p style = "clor:red;">Error al anular la factura</p>');
            } else {
                $('#row_' + noFactura + ' .estado').html('<span class = "anulada">Anulada</span>');
                $('#form_anular_factura .btn_ok').remove();
                $('#row_' + noFactura + ' .div_factura').html('<button type="button" class="btn_anular inactive"><i class="fas fa-ban"></i></button>');
                $('.alertAddProduct').html('<p>Factura anulada</p>')
            }
        },

        error: function (error) {

        }
    });
}
//funcion para generar el pdf o reporte de facturas
function generarPDF(cliente, factura) {
    var ancho = 1000;
    var alto = 800;
    //calcular la posicion
    var x = parseInt((window.screen.width / 2) - (ancho / 2));
    var y = parseInt((window.screen.height / 2) - (alto / 2));

    $url = 'factura/generaFactura.php?cl=' + cliente + '&f=' + factura;
    window.open($url, "Factura", "left=" + x + ",top=" + y + ",height=" + alto + ",width=" + ancho + ",scrollbar=si,location=no,resizable=si,menubar=no");
}
//funcion para eliminar un articulo
function del_product_detalle(correlativo) {
    var action = 'delProductoDetalle';
    var id_detalle = correlativo;

    $.ajax({
        url: 'ajax.php',
        type: 'POST',
        async: true,
        data: {action: action, id_detalle: id_detalle}, //permite recoger todos losdatos por el id del formulario

        success: function (response) {
            if (response != 'error') {
                var info = JSON.parse(response);

                $('#detalle_venta').html(info.detalle);
                $('#detalle_totales').html(info.totales);


                $('#txt_cod_producto').val('');
                $('#txt_descripcion').html('-');
                $('#txt_existencia').html('-');
                $('#txt_cant_producto').val('0');
                $('#txt_precio').html('0.00');
                $('#txt_precio_total').html('0.00');

                //bloquear la cantidad 
                $('#txt_cant_producto').attr('disabled', 'disabled');
                //ocultar el boton agregar
                $('#add_product_venta').slideUp();
            } else {
                $('#detalle_venta').html('');
                $('#detalle_totales').html('');
            }
            viewProcesar();
        },

        error: function (error) {

        }
    });
}
//funcion para 
function serchForDetalle(id) {
    var action = 'serchForDetalle';
    var user = id;

    $.ajax({
        url: 'ajax.php',
        type: 'POST',
        async: true,
        data: {action: action, user: user}, //permite recoger todos losdatos por el id del formulario

        success: function (response) {
            if (response !== 'error') {
                var info = JSON.parse(response);
                $('#detalle_venta').html(info.detalle);
                $('#detalle_totales').html(info.totales);

            } else {
                console.log('no data');
            }
            viewProcesar();
        },

        error: function (error) {

        }
    });
}
//funcion para obtener la URL para buscar los ´proveedores
function getUrl() {
    var loc = window.location;
    var pathName = loc.pathname.substring(0, loc.pathname.lastIndexOf('/') + 1);

    return loc.href.substring(0, loc.href.length - ((loc.pathname + loc.search + loc.hash).length - pathName.length));
}
//funcion para enviar losdatosdel producto
function sendDataProduct() {
    $('.alertAddProduct').html('');

    $.ajax({
        url: 'ajax.php',
        type: 'POST',
        async: true,
        data: $('#form_add_product').serialize(), //permite recoger todos losdatos por el id del formulario

        success: function (response) {

            if (response === 'error') {
                $('.alertAddProduct').html('<p style="color:red;">Error al agregar producto</p>');

            } else {
                var info = JSON.parse(response);
                $('.row' + info.producto_id + '.celPrecio').html(info.nuevo_precio);
                $('.row' + info.producto_id + '.celExistencia').html(info.nueva_existencia);
                $('#txtCantidad').val('');
                $('#txtPrecio').val('');
                $('.alertAddProduct').html('<p>Producto Guardado Correctamente</p>');
            }
        },

        error: function (error) {
            console.log(error);
        }
    });
}
//funcion para Eliminar un producto
function delProduct() {
    var pr = $('#producto_id').val();
    $('.alertAddProduct').html('');

    $.ajax({
        url: 'ajax.php',
        type: 'POST',
        async: true,
        data: $('#form_del_product').serialize(), //permite recoger todos losdatos por el id del formulario

        success: function (response) {
            console.log(response);

            if (response === 'error') {
                $('.alertAddProduct').html('<p style="color:red;">Error al eliminar el producto</p>');

            } else {

                $('.row' + pr).remove();
                $('#form_del_product .btn_ok').remove();
                $('.alertAddProduct').html('<p>Producto Eliminado Correctamente</p>');
            }

        },

        error: function (error) {
            console.log(error);
        }
    });
}

//funcion pàra ocultar el boton de procesar venta
function viewProcesar() {
    if ($('#detalle_venta tr').length > 0) {
        $('#btn_facturar_venta').show();
    } else {
        $('#btn_facturar_venta').hide();
    }
}
function viewProcesarFiado() {
    if ($('#detalle_venta_ tr').length > 0) {
        $('#btn_facturar_venta_').show();
    } else {
        $('#btn_facturar_venta_').hide();
    }
}
function closeModal() {
    $('.alertAddProduct').html('');
    $('#txtCantidad').val('');
    $('#txtPrecio').val('');
    $('.modal').fadeOut();
}
//funcion para cerrar el modal de facturacion
function closeModalFactura() {
    $('.modalFacturacion').fadeOut();
}