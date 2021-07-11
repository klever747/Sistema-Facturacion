
<?PHP
session_start();
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
        include "../conexion/conexion.php";
        $query_dash = mysqli_query($conexion, "CALL dataDashboard();");
        $result_dash = mysqli_num_rows($query_dash);
        if ($result_dash > 0) {
            $data_dash = mysqli_fetch_assoc($query_dash);
            mysqli_close($conexion);
        }
        print_r($data_dash);
        ?>
        <section id="container">
            <div class="divContainer">
                <div>
                    <h1 class="tittlePanelControl">Panel de control</h1>
                </div>


                <div class="dashboard">
                    <?PHP
                    if ($_SESSION['rol'] == 1 || $_SESSION['rol'] == 2) {
                        ?>
                        <a href="listar_usuario.php">
                            <i class="fas fa-users"></i>
                            <p>
                                <strong>Usuarios</strong><br>
                                <span> <?PHP echo $data_dash['usuarios']; ?></span>
                            </p>

                        </a>
                    <?PHP } ?>
                    <a href="listar_clientes.php">
                        <i class="fas fa-user"></i>
                        <p>
                            <strong>Clientes</strong><br>
                            <span><?PHP echo $data_dash['clientes']; ?></span>
                        </p>

                    </a>
                    <?PHP
                    if ($_SESSION['rol'] == 1 || $_SESSION['rol'] == 2) {
                        ?>
                        <a href="listar_proveedores.php">
                            <i class="far fa-building"></i>
                            <p>
                                <strong>Proveedores</strong><br>
                                <span><?PHP echo $data_dash['proveedores']; ?></span>
                            </p>

                        </a>
                    <?PHP } ?>
                    <a href="lista_producto.php">
                        <i class="fas fa-cubes"></i>
                        <p>
                            <strong>Productos</strong><br>
                            <span><?PHP echo $data_dash['productos']; ?></span>
                        </p>

                    </a>
                    <a href="ventas.php">
                        <i class="far fa-file-alt"></i>
                        <p>
                            <strong>Ventas</strong><br>
                            <span><?PHP echo $data_dash['ventas']; ?></span>
                        </p>

                    </a>
                </div>
            </div>

            <div class="divInfoSistem">
                <div>
                    <h1 class="tittlePanelControl">Configuración</h1>
                </div>
                <div class="containerPerfil">
                    <div class="containerDataUser">
                        <div class="logoUser">
                            <img src="img/user.png">
                        </div>
                        <div class="divDataUser">
                            <h4>Información Personal</h4>
                           
                            <div>
                                <label>Nombre:</label> <span> <?PHP echo $_SESSION['nombre'];?></span>
                            </div>
                            <div>
                                <label>Correo:</label> <span><?PHP echo $_SESSION['email'];?></span>
                            </div>
                            <h4>Datos Usuario</h4>
                            <div>
                                <label>Rol:</label> <span><?PHP echo $_SESSION['rol_name'];?></span>
                            </div>
                            <div>
                                <label>Usuario:</label> <span><?PHP echo $_SESSION['user'];?></span>
                            </div>
                            <h4>Cambiar Contraseña</h4>
                            <form action="" method="POST" name="frmChangePass" id="frmChangePass">
                                <div>
                                    <input type="password" name="txtPassUser" id="txtPassUser" placeholder="Contraseña actual" required>

                                </div>
                                <div>
                                    <input type="password" name="txtPassConfirm" id="txtPassConfirm" placeholder="Confirmar Contraseña" required>

                                </div>
                                <div>
                                    <button type="submit" class="btn_save btnChangePass"><i class="fas fa-key"></i> Cambiar Contraseña</button>
                                </div>
                            </form>
                        </div>
                    </div>

                    <div class="containerDataEmpresa">
                        <div class="logoEmpresa">
                            <img src="img/logoEmpresa.png">
                        </div>
                        <div>
                            <h4>Datos de la empresa</h4>
                        </div>


                        <form action="" method="POST" name="frmEmpresa" id="frmEmpresa">
                            <input type="hidden" name="action" value="updateDataEmpresa">
                            <div>
                                <label>CI:</label><input type="text" name="txtNit" id="txtNit" placeholder="CI de la empresa" value="" required>

                            </div>
                            <div>
                                <label>Nombre:</label><input type="text" name="txtNombre" id="txtNombre" placeholder="Nombre de la Empresa" value="" required>

                            </div>
                            <div>
                                <label>Razón Social:</label><input type="text" name="txtRSocial" id="txtRSocial" placeholder="Razon Social" value="" required>

                            </div>
                            <div>
                                <label>Teléfono:</label><input type="text" name="txtTelEmpresa" id="txtTelEmpresa" placeholder="Número de la empresa" value="" required>

                            </div>
                            <div>
                                <label>Correo Electrónico:</label><input type="email" name="txtEmailEmpresa" id="txtEmailEmpresa" placeholder="Correo Electrónico" value="" required>

                            </div>
                            <div>
                                <label>Dirección:</label><input type="text" name="txtDirEmpresa" id="txtDirEmpresa" placeholder="Dirección de la Empresa" value="" required>

                            </div>
                            <div>
                                <label>IVA(%):</label><input type="text" name="txtIva" id="txtIva" placeholder="Impuesto al valor agregado (IVA)" value="" required>

                            </div>
                            <div class="alertFormEmpresa" style="display: none"></div>
                            <div>
                                <button type="submit" class="btn_save btnChangePass"><i class="far fa-save fa-lg"></i> Guardar Datos</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </section>
        <?PHP
        include "../sistema/recursos/footer.php";
        ?>
    </body>
</html>