-- phpMyAdmin SQL Dump
-- version 5.0.2
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 11-07-2021 a las 22:46:18
-- Versión del servidor: 10.4.13-MariaDB
-- Versión de PHP: 7.2.31

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `facturacion`
--

DELIMITER $$
--
-- Procedimientos
--
CREATE DEFINER=`root`@`localhost` PROCEDURE `actualizarCliente` (IN `_nit` INT(11), IN `_nombre` VARCHAR(80), IN `_telefono` INT(11), IN `_direccion` TEXT)  BEGIN
		UPDATE cliente SET nombre = _nombre, telefono = _telefono, direccion = _direccion WHERE nit = _nit;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `actualizarProducto` (IN `_codproducto` INT(11), IN `_descripcion` VARCHAR(100), IN `_proveedor` INT(11), IN `_precio` DECIMAL(10,2), IN `_existencia` INT(11), IN `_foto` TEXT)  BEGIN
		UPDATE producto SET descripcion = _descripcion, existencia = _existencia, foto = _foto, precio = _precio, proveedor = _proveedor WHERE codproducto = _codproducto;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `actualizarProveedor` (IN `_codproveedor` INT(11), IN `_proveedor` VARCHAR(100), IN `_contacto` VARCHAR(100), IN `_telefono` BIGINT(11), IN `_direccion` TEXT)  BEGIN
		UPDATE proveedor SET proveedor = _proveedor, contacto = _contacto, telefono = _telefono, direccion = _direccion 
        		WHERE codproveedor=_codproveedor;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `actualizarUsuarios` (IN `_nombre` VARCHAR(50), IN `_correo` VARCHAR(100), IN `_usuario` VARCHAR(15), IN `_rol` INT(11))  BEGIN
		UPDATE usuario SET nombre = _nombre, correo = _correo, usuario = _usuario, rol = _rol WHERE nombre = _nombre;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `actualizar_precio_producto` (IN `n_cantidad` INT(11), IN `n_precio` DECIMAL(10,2), IN `codigo` INT(11))  BEGIN 
		DECLARE nueva_existencia decimal(10,2);
        DECLARE nuevo_total decimal(10,2);
        DECLARE nuevo_precio decimal(10,2);
        
        DECLARE cant_actual decimal(10,2);
        DECLARE pre_actual decimal(10,2);
        
        DECLARE actual_existencia decimal(10,2);
        DECLARE actual_precio decimal(10,2);
        
        SELECT precio,existencia INTO actual_precio,actual_existencia FROM producto WHERE codproducto =codigo;
        SET nueva_existencia = actual_existencia+n_cantidad;
        SET nuevo_total = (actual_existencia*actual_precio)+(n_cantidad*n_precio);
        SET nuevo_precio = nuevo_total/nueva_existencia;
        UPDATE producto SET existencia= nueva_existencia, precio= nuevo_precio WHERE codproducto = codigo;
        SELECT nueva_existencia, nuevo_precio;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `addDetalleTempFiado` (IN `codigo` INT, IN `cantidad` DECIMAL(10,2), IN `token_user` VARCHAR(50))  BEGIN
	DECLARE precio_actual decimal(10,2);
    
    SELECT precio INTO precio_actual FROM producto WHERE codproducto = codigo;
    INSERT INTO detalle_temp(token_user,codproducto,cantidad,precio_venta)
    		VALUES(token_user,codigo,cantidad,precio_actual);
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `add_detalle_temp` (IN `codigo` INT(11), IN `cantidad` DECIMAL(10,2), IN `token_usuario` VARCHAR(50))  BEGIN
	DECLARE precio_actual decimal(10,2);
    SELECT precio INTO precio_actual FROM producto WHERE codproducto =codigo;
    INSERT INTO detalle_temp (token_user, codproducto,cantidad,precio_venta) 			  	VALUES(token_usuario,codigo,cantidad,precio_actual);
    SELECT tmp.correlativo,tmp.codproducto,p.descripcion,tmp.cantidad,tmp.precio_venta
    FROM detalle_temp tmp INNER JOIN producto p 
    ON tmp.codproducto=p.codproducto 
    WHERE tmp.token_user=token_usuario;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `anular_factura` (IN `no_factura` INT)  BEGIN
    	DECLARE existe_factura int;
        DECLARE registros int;
        DECLARE a int;
        
        DECLARE cod_producto int;
        DECLARE cant_producto DECIMAL(10,2);
        DECLARE existencia_actual DECIMAL(10,2) ;
        DECLARE nueva_existencia DECIMAL(10,2) ;
        SET existe_factura = (SELECT COUNT(*) FROM factura WHERE nofactura = no_factura AND estatus =1);
        
        IF existe_factura > 0 THEN
        	CREATE TEMPORARY TABLE tbl_tmp(
            	id BIGINT NOT null AUTO_INCREMENT PRIMARY KEY,
                cod_prod BIGINT,
                cant_prod DECIMAL(10,2) 
            );
            SET a = 1;
            SET registros = (SELECT COUNT(*) FROM detallefactura WHERE nofactura = no_factura);
            IF registros > 0 THEN
            	INSERT INTO tbl_tmp(cod_prod,cant_prod) SELECT codproducto,cantidad FROM detallefactura WHERE nofactura = no_factura;
                WHILE a <= registros DO
                	SELECT cod_prod, cant_prod INTO cod_producto,cant_producto FROM tbl_tmp WHERE id = a;
                    SELECT existencia INTO existencia_actual FROM producto WHERE codproducto = cod_producto;
                    SET nueva_existencia = existencia_actual + cant_producto;
                    UPDATE producto SET existencia = nueva_existencia WHERE codproducto = cod_producto;
                    
                    SET a = a+1;
                END WHILE;
                UPDATE factura SET estatus = 2 WHERE nofactura = no_factura;
                DROP TABLE tbl_tmp;
                SELECT * FROM factura WHERE nofactura = no_factura;
            END IF;
        ELSE
        	SELECT 0 factura;
        END IF;
    END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `buscarCliente` (IN `_nombre` VARCHAR(80))  BEGIN
	
	SELECT JSON_OBJECT('nit',c.nit,
                      	'nombre',c.nombre,
                      	'telefono',c.telefono,
                      	'direccion',c.direccion) FROM cliente c WHERE c.nombre LIKE _nombre AND estatus =1;

END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `buscarProducto` (IN `_descripcion` VARCHAR(100))  BEGIN
	
	SELECT  p.codproducto,
                       p.descripcion,
                       pr.proveedor,
                       p.precio,
                      	 p.existencia,
                      	p.foto FROM producto p INNER JOIN proveedor pr ON p.proveedor = pr.codproveedor AND descripcion LIKE _descripcion WHERE p.estatus=1;

END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `buscarProveedor` (IN `_proveedor` VARCHAR(50))  BEGIN
	
	SELECT JSON_OBJECT('cod_proveedor',p.codproveedor,
                      	'proveedor',p.proveedor,
                      	'contacto',p.contacto,
                      	'direccion',p.direccion,
                      	'telefono',p.telefono) FROM proveedor p WHERE p.proveedor LIKE _proveedor AND p.estatus=1;

END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `buscarUsuario` (IN `_nombre` VARCHAR(50))  BEGIN
		SELECT JSON_OBJECT('idusuario',u.idusuario,
                      	'nombre',u.nombre,
                      	'correo',u.correo,
                      	'usuario',u.usuario,
                      	'rol',r.rol ) FROM usuario u INNER JOIN rol r ON u.rol=r.idrol AND nombre LIKE _nombre AND estatus = 1;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `cargarDetalleFacturaFiado` (IN `codCliente` INT)  BEGIN
 	
        	SELECT  DF.codproducto,p.descripcion,DF.cantidad,DF.precio_venta FROM factura f 
			INNER JOIN detallefactura DF
			ON 
			DF.nofactura = f.nofactura 
			INNER JOIN producto p 
			on p.codproducto = DF.codproducto
			WHERE f.id_tipoF = 3 and f.codcliente = codCliente AND f.estatus = 1;

	
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `cargarRegistros` (IN `codCliente` INT)  BEGIN 
	SELECT DF.codproducto,DF.cantidad,DF.precio_venta FROM detallefactura DF
                        INNER JOIN factura f 
                        ON
                        DF.nofactura = f.nofactura
                        INNER JOIN cliente c
                        ON
                        c.idcliente = f.codcliente
                        WHERE (f.id_tipoF = 3 AND f.codcliente = codCliente AND f.estatus = 1);
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `dataDashboard` ()  BEGIN
    	DECLARE usuarios int;
        DECLARE clientes int;
        DECLARE proveedores int;
        DECLARE productos DECIMAL(10,2);
        DECLARE ventas int;
        
        SELECT COUNT(*) INTO usuarios FROM usuario WHERE estatus != 10;
        SELECT COUNT(*) INTO clientes FROM cliente WHERE estatus != 10;
        SELECT COUNT(*) INTO proveedores FROM proveedor WHERE  estatus != 10;
        SELECT COUNT(*) INTO productos FROM producto WHERE estatus != 10;
        SELECT COUNT(*) INTO ventas FROM factura WHERE fecha > CURDATE() AND estatus != 10;
        
        SELECT usuarios, clientes,proveedores,productos,ventas;
    END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `del_detalle_temp` (`id_detalle` INT, `token` VARCHAR(50))  BEGIN
    	DELETE FROM detalle_temp WHERE correlativo = id_detalle;
        
        SELECT tmp.correlativo, tmp.codproducto, p.descripcion, tmp.cantidad, tmp.precio_venta FROM detalle_temp tmp
        INNER JOIN producto p 
        ON tmp.codproducto = p.codproducto
        WHERE tmp.token_user = token;
    END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `eliminarCliente` (IN `_nit` INT(11))  BEGIN
	UPDATE cliente SET estatus= 0 WHERE nit=_nit;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `eliminarProducto` (IN `_idproducto` INT(11))  BEGIN
	UPDATE producto SET estatus= 0 WHERE idproducto=_idproducto;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `eliminarProveedor` (IN `_codProveedor` INT(11))  BEGIN
	UPDATE proveedor SET estatus= 0 WHERE codproveedor=_codProveedor;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `eliminarUsuario` (IN `_idusuario` INT(11))  BEGIN
    	UPDATE usuario SET estatus= 0 WHERE idusuario = _idusuario;
    END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `extraerIVA` ()  BEGIN
		SELECT iva FROM configuracion ;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `ingresarCliente` (IN `_nit` INT(11), IN `_nombre` VARCHAR(80), IN `_telefono` INT(11), IN `_direccion` TEXT)  BEGIN
	INSERT INTO cliente(nit,nombre,telefono,direccion) 
    		VALUES(_nit,_nombre,_telefono,_direccion);
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `ingresarProducto` (IN `_descripcion` VARCHAR(100), IN `_proveedor` INT(11), IN `_precio` DECIMAL(10,2), IN `_existencia` INT(11), IN `_foto` TEXT, IN `_usuario_id` INT(11))  BEGIN
	INSERT INTO producto( descripcion, proveedor, precio,existencia,foto,usuario_id)
    		VALUES(_descripcion,_proveedor,_precio,_existencia,_foto,_usuario_id);
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `ingresarProveedor` (IN `_proveedor` VARCHAR(100), IN `_contacto` VARCHAR(100), IN `_telefono` BIGINT(11), IN `_direccion` TEXT, IN `_usuarioid` INT)  BEGIN
	INSERT into proveedor(proveedor,contacto,telefono,direccion,usuario_id)VALUES(_proveedor,_contacto,_telefono,_direccion,_usuarioid);
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `insertarUsuario` (IN `_nombre` VARCHAR(50), IN `_correo` VARCHAR(100), IN `_usuario` VARCHAR(15), IN `_clave` VARCHAR(100), IN `_rol` INT(11))  BEGIN
	INSERT INTO usuario (nombre, correo, usuario,clave,rol) VALUES(_nombre,_correo, _usuario,_clave,_rol);
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `listarCliente` ()  BEGIN

	SELECT JSON_OBJECT('nit',c.nit,
                      	'nombre', c.nombre,
                      	'telefono',c.telefono,
                      	'direccion',c.direccion) FROM cliente c WHERE estatus = 1 ORDER BY idcliente DESC;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `listarProducto` ()  BEGIN

	SELECT JSON_OBJECT('codproducto',p.codproducto,
                      	'descripcion', p.descripcion,
                      	'proveedor',pr.proveedor,
                      	'precio',p.precio,
                      	'existencia',p.existencia,
                      	'foto',p.foto) FROM producto p INNER JOIN  proveedor pr on p.proveedor=pr.codproveedor WHERE p.estatus=1;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `listarProveedor` ()  BEGIN

	SELECT JSON_OBJECT('codproveedor',p.codproveedor,
                      	'contacto', p.contacto,
                      	'direccion',p.direccion,
                      	'proveedor',p.proveedor,
                      	'telefono',p.telefono) FROM proveedor p
                        WHERE estatus=1 ORDER BY p.codproveedor DESC;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `listarUsuario` ()  BEGIN

	SELECT JSON_OBJECT('idusuario',u.idusuario,
                      	'nombre', u.nombre,
                      	'correo',u.correo,
                      	'usuario',u.usuario,
                      	'rol',r.rol) FROM usuario u INNER JOIN rol r on u.rol = r.idrol WHERE u.estatus=1 ;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `procesar_venta` (IN `cod_usuario` INT, IN `cod_cliente` INT, IN `token` VARCHAR(50), IN `tipoF` INT)  BEGIN
    	DECLARE factura INT;
        DECLARE registros INT;
        
        DECLARE total DECIMAL(10,2);
       	DECLARE nueva_existencia DECIMAL(10,2);
        DECLARE existencia_actual DECIMAL(10,2);
        DECLARE tmp_cod_producto int;
        DECLARE tmp_cant_producto DECIMAL(10,2);
        DECLARE a int;
        SET a = 1;
        
        CREATE TEMPORARY TABLE tbl_tmp_tokenuser(
        		id BIGINT NOT NULL AUTO_INCREMENT KEY,
            	cod_prod BIGINT,
            	cant_prod DECIMAL(10,2)
        );
        
        SET registros =(SELECT COUNT(*) FROM detalle_temp WHERE token_user = token);
        
        IF registros > 0 THEN
        	INSERT INTO tbl_tmp_tokenuser(cod_prod,cant_prod) SELECT codproducto,cantidad FROM detalle_temp WHERE 					token_user = token; 
            
            INSERT INTO factura(usuario,codcliente,id_tipoF) VALUES(cod_usuario,cod_cliente,tipoF);
            SET factura = LAST_INSERT_ID();
            
            INSERT INTO detallefactura(nofactura,codproducto,cantidad,precio_venta) SELECT(factura) as 										nofactura,codproducto,cantidad,precio_venta FROM detalle_temp WHERE token_user = token;
            
            WHILE a <= registros DO
              SELECT cod_prod,cant_prod INTO tmp_cod_producto, tmp_cant_producto FROM tbl_tmp_tokenuser WHERE id = a;
              SELECT existencia INTO existencia_actual FROM producto WHERE codproducto = tmp_cod_producto;
              
              SET nueva_existencia = existencia_actual - tmp_cant_producto;
              UPDATE producto SET existencia = nueva_existencia WHERE codproducto = tmp_cod_producto;
              
              SET a = a+1;
            END WHILE;
            
            SET total = (SELECT SUM(cantidad*precio_venta) FROM detalle_temp WHERE token_user = token);
            UPDATE factura SET totalfactura = total WHERE nofactura = factura;
            
            DELETE FROM detalle_temp WHERE token_user = token;
          
            TRUNCATE TABLE tbl_tmp_tokenuser;
            SELECT * FROM factura WHERE nofactura = factura;
        ELSE
        	SELECT 0;
        END IF;
    END$$

DELIMITER ;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `cliente`
--

CREATE TABLE `cliente` (
  `idcliente` int(11) NOT NULL,
  `nit` int(11) DEFAULT NULL,
  `idtipo` int(11) NOT NULL,
  `nombre` varchar(80) DEFAULT NULL,
  `telefono` int(11) DEFAULT NULL,
  `direccion` text DEFAULT NULL,
  `estatus` int(11) NOT NULL DEFAULT 1,
  `date_add` datetime NOT NULL DEFAULT current_timestamp(),
  `usuario_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Volcado de datos para la tabla `cliente`
--

INSERT INTO `cliente` (`idcliente`, `nit`, `idtipo`, `nombre`, `telefono`, `direccion`, `estatus`, `date_add`, `usuario_id`) VALUES
(12, 1102412622, 1, 'klever pardo soto', 989574272, 'libre ecuador y escocia', 1, '2020-07-20 21:42:51', 30),
(13, 1102435052, 2, 'carlota Jimenez', 23767415, 'Japon e iran', 1, '2020-07-20 21:43:52', 30),
(14, 1718004805, 2, 'valeria pardo jimenez', 985623145, 'ciudad nueva', 1, '2020-07-20 21:49:51', 30),
(15, 999999999, 1, 'SN', 1119999999, 'SN', 1, '2020-07-21 17:20:16', 30),
(16, 123456789, 2, 'lukas arrieta', 231567895, 'pampas ', 1, '2020-07-26 23:33:25', 30),
(18, 12345645, 2, 'Wilson', 985745123, 'Libre Ecuador', 1, '2020-07-30 03:02:53', 30),
(22, 1234569856, 2, 'Jose Castro', 986656, 'Av. De la prensa N58-96 y Vaca  de castro', 1, '2021-06-10 22:44:12', 32);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `configuracion`
--

CREATE TABLE `configuracion` (
  `id` bigint(20) NOT NULL,
  `nit` varchar(20) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `razon_social` varchar(100) NOT NULL,
  `telefono` bigint(20) NOT NULL,
  `email` varchar(200) NOT NULL,
  `direccion` text NOT NULL,
  `iva` decimal(10,0) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Volcado de datos para la tabla `configuracion`
--

INSERT INTO `configuracion` (`id`, `nit`, `nombre`, `razon_social`, `telefono`, `email`, `direccion`, `iva`) VALUES
(1, '1102412622', 'klever pardo', '', 9089898, 'klver@gmail.com', 'libre ecuador y escocia', '12');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `detallefactura`
--

CREATE TABLE `detallefactura` (
  `correlativo` bigint(11) NOT NULL,
  `nofactura` bigint(11) DEFAULT NULL,
  `codproducto` int(11) DEFAULT NULL,
  `cantidad` decimal(10,2) DEFAULT NULL,
  `precio_venta` decimal(10,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Volcado de datos para la tabla `detallefactura`
--

INSERT INTO `detallefactura` (`correlativo`, `nofactura`, `codproducto`, `cantidad`, `precio_venta`) VALUES
(790, 112, 132, '1.00', '1.75'),
(791, 113, 127, '1.00', '1.50'),
(792, 114, 127, '1.00', '1.50'),
(793, 115, 127, '2.00', '1.50'),
(794, 116, 127, '1.00', '1.50'),
(795, 117, 127, '1.00', '1.50'),
(796, 118, 127, '1.00', '1.50'),
(797, 119, 127, '1.00', '1.50'),
(798, 120, 127, '1.00', '1.50'),
(799, 121, 127, '2.00', '1.50'),
(800, 122, 127, '1.00', '1.50'),
(801, 123, 127, '5.00', '1.50'),
(802, 124, 127, '1.00', '1.50'),
(803, 125, 127, '3.00', '1.50'),
(804, 126, 127, '2.00', '1.50'),
(805, 127, 127, '3.00', '1.50'),
(806, 128, 127, '2.00', '1.50'),
(807, 129, 127, '3.00', '1.50'),
(808, 130, 127, '4.00', '1.50'),
(809, 130, 127, '1.00', '1.50'),
(810, 131, 127, '2.00', '1.50'),
(811, 132, 127, '2.00', '1.50'),
(812, 133, 127, '2.00', '1.50'),
(813, 134, 127, '1.00', '1.50'),
(814, 135, 127, '4.00', '1.50'),
(815, 136, 127, '1.00', '1.50'),
(816, 137, 131, '2.00', '1.75'),
(817, 138, 131, '1.00', '1.75'),
(818, 139, 127, '3.00', '1.50'),
(819, 140, 131, '1.00', '1.75'),
(820, 141, 127, '3.00', '1.50'),
(821, 142, 125, '3.00', '1.60'),
(822, 143, 127, '3.00', '1.50'),
(823, 144, 125, '2.00', '1.60'),
(824, 145, 127, '2.00', '1.50'),
(825, 146, 127, '10.00', '1.50'),
(826, 147, 131, '12.00', '1.75'),
(827, 148, 127, '2.00', '1.50'),
(828, 149, 127, '2.00', '1.50'),
(829, 150, 127, '2.00', '1.50'),
(830, 151, 127, '12.00', '1.50'),
(831, 152, 127, '6.00', '1.50'),
(832, 153, 127, '12.00', '1.50'),
(833, 154, 127, '1.00', '1.50'),
(834, 155, 131, '1.00', '1.75'),
(835, 156, 129, '1.00', '1.00'),
(836, 157, 127, '2.00', '1.50'),
(837, 158, 127, '12.00', '1.50'),
(838, 159, 128, '1.00', '1.30'),
(839, 160, 127, '1.00', '1.50'),
(840, 161, 131, '2.00', '1.75'),
(841, 162, 128, '3.00', '1.30'),
(842, 163, 128, '3.00', '1.30'),
(843, 164, 127, '2.00', '1.50'),
(844, 165, 128, '6.00', '1.30'),
(845, 166, 130, '1.00', '1.00'),
(846, 167, 127, '2.00', '1.50'),
(847, 168, 127, '3.00', '1.50'),
(848, 169, 127, '2.00', '1.50'),
(849, 170, 127, '1.00', '1.50'),
(850, 170, 131, '1.00', '1.75'),
(852, 171, 130, '2.00', '1.00'),
(853, 172, 127, '1.00', '1.50'),
(854, 173, 131, '2.00', '1.75'),
(855, 174, 127, '2.00', '1.50'),
(856, 175, 131, '1.00', '1.75'),
(857, 176, 127, '2.00', '1.50'),
(858, 177, 131, '1.00', '1.75'),
(859, 178, 127, '2.00', '1.50'),
(860, 178, 131, '1.00', '1.75'),
(862, 179, 127, '2.00', '1.50'),
(863, 180, 127, '1.00', '1.50'),
(864, 181, 127, '2.00', '1.50'),
(865, 182, 127, '4.00', '1.50'),
(866, 183, 127, '8.00', '1.50'),
(867, 184, 127, '1.00', '1.50'),
(868, 185, 136, '1.00', '2.00'),
(869, 185, 126, '1.00', '1.60'),
(871, 186, 127, '4.00', '1.50'),
(872, 187, 127, '2.00', '1.50'),
(873, 188, 127, '2.00', '1.50'),
(874, 188, 135, '3.00', '1.00'),
(876, 189, 127, '3.00', '1.50'),
(877, 190, 135, '1.00', '1.00'),
(878, 191, 128, '1.00', '1.30'),
(879, 192, 127, '2.00', '1.50'),
(880, 193, 131, '2.00', '1.75'),
(881, 194, 133, '1.00', '0.50'),
(882, 195, 131, '2.00', '1.75'),
(883, 196, 128, '2.00', '1.30'),
(884, 197, 128, '2.00', '1.30'),
(885, 198, 128, '6.00', '1.30'),
(886, 199, 128, '6.00', '1.30'),
(887, 200, 131, '2.00', '1.75'),
(888, 201, 131, '2.00', '1.75'),
(889, 202, 128, '6.00', '1.30'),
(890, 203, 126, '2.00', '1.60'),
(891, 204, 125, '1.00', '1.60'),
(892, 205, 126, '6.00', '1.60'),
(893, 206, 137, '12.00', '1.60'),
(894, 207, 127, '1.00', '1.50'),
(895, 208, 127, '1.00', '1.50'),
(896, 209, 131, '1.00', '1.75'),
(897, 210, 127, '6.00', '1.50'),
(898, 211, 125, '3.00', '1.60'),
(899, 212, 127, '6.00', '1.50'),
(900, 213, 127, '4.00', '1.50'),
(901, 214, 127, '2.00', '1.50'),
(902, 214, 131, '1.00', '1.75'),
(904, 215, 131, '1.00', '1.75'),
(905, 216, 127, '2.00', '1.50'),
(906, 217, 129, '1.00', '1.00'),
(907, 218, 131, '1.00', '1.75'),
(908, 219, 134, '6.00', '1.00'),
(909, 220, 127, '1.00', '1.50'),
(910, 221, 127, '4.00', '1.50'),
(911, 221, 125, '2.00', '1.60'),
(913, 222, 127, '6.00', '1.50'),
(914, 223, 127, '3.00', '1.50'),
(915, 224, 127, '3.00', '1.50'),
(916, 225, 127, '6.00', '1.50'),
(917, 226, 127, '4.00', '1.50'),
(918, 227, 127, '3.00', '1.50'),
(919, 228, 127, '6.00', '1.50'),
(920, 229, 127, '3.00', '1.50'),
(921, 230, 127, '3.00', '1.50'),
(922, 231, 127, '4.00', '1.50'),
(923, 232, 131, '3.00', '1.75'),
(924, 233, 131, '14.00', '1.75'),
(925, 234, 127, '1.00', '1.50'),
(926, 234, 125, '12.00', '1.60'),
(928, 235, 125, '12.00', '1.60'),
(929, 236, 131, '1.00', '1.75'),
(930, 237, 131, '1.00', '1.75'),
(931, 238, 127, '2.00', '1.50'),
(932, 239, 127, '6.00', '1.50'),
(933, 239, 131, '3.00', '1.75'),
(935, 240, 127, '4.00', '1.50'),
(936, 240, 131, '4.00', '1.75'),
(937, 241, 138, '6.00', '1.00'),
(938, 242, 138, '2.00', '1.00'),
(939, 243, 127, '4.00', '1.50'),
(940, 244, 131, '1.00', '1.75'),
(941, 245, 127, '2.00', '1.50'),
(942, 246, 142, '6.00', '1.50'),
(943, 247, 128, '1.00', '1.30'),
(944, 247, 142, '1.00', '1.50'),
(945, 248, 128, '1.00', '1.30'),
(946, 249, 128, '1.00', '1.30'),
(947, 250, 125, '1.00', '1.60'),
(948, 250, 127, '1.00', '1.50'),
(950, 251, 127, '1.00', '1.50'),
(951, 251, 128, '1.00', '1.30'),
(952, 251, 136, '1.00', '2.00'),
(953, 252, 126, '2.00', '1.60'),
(954, 252, 125, '1.00', '1.60'),
(955, 252, 129, '1.00', '1.00'),
(956, 252, 127, '1.00', '1.50'),
(960, 253, 128, '1.00', '1.30'),
(961, 253, 125, '1.00', '1.60'),
(962, 253, 127, '1.00', '1.50'),
(963, 253, 127, '1.00', '1.50'),
(964, 253, 128, '1.00', '1.30'),
(965, 253, 136, '1.00', '2.00'),
(966, 253, 126, '2.00', '1.60'),
(967, 253, 125, '1.00', '1.60'),
(968, 253, 129, '1.00', '1.00'),
(969, 253, 127, '1.00', '1.50'),
(970, 253, 128, '1.00', '1.30'),
(971, 253, 125, '1.00', '1.60'),
(972, 253, 127, '1.00', '1.50'),
(973, 253, 127, '1.00', '1.50'),
(974, 253, 128, '1.00', '1.30'),
(975, 253, 136, '1.00', '2.00'),
(976, 253, 126, '2.00', '1.60'),
(977, 253, 125, '1.00', '1.60'),
(978, 253, 129, '1.00', '1.00'),
(979, 253, 127, '1.00', '1.50'),
(991, 254, 139, '1.00', '20.00'),
(992, 255, 131, '1.00', '1.75'),
(993, 255, 129, '1.00', '1.00'),
(994, 255, 141, '1.00', '15.00'),
(995, 256, 139, '1.00', '20.00'),
(996, 256, 131, '1.00', '1.75'),
(997, 256, 129, '1.00', '1.00'),
(998, 256, 141, '1.00', '15.00'),
(1002, 257, 129, '1.00', '1.00'),
(1003, 258, 129, '1.00', '1.00'),
(1004, 259, 129, '1.00', '1.00'),
(1005, 260, 128, '1.00', '1.30'),
(1006, 261, 128, '1.00', '1.30'),
(1007, 262, 125, '1.00', '1.60'),
(1008, 263, 126, '1.00', '1.60'),
(1009, 264, 126, '1.00', '1.60'),
(1010, 265, 125, '1.00', '1.60'),
(1011, 266, 125, '1.00', '1.60'),
(1012, 267, 125, '1.00', '1.60'),
(1013, 268, 125, '1.00', '1.60'),
(1014, 269, 125, '1.00', '1.60'),
(1015, 269, 125, '1.00', '1.60'),
(1017, 270, 125, '1.00', '1.60'),
(1018, 271, 146, '1.00', '0.50'),
(1019, 271, 147, '1.00', '1.00'),
(1020, 271, 148, '1.00', '2.00'),
(1021, 271, 149, '10.00', '0.05'),
(1022, 271, 150, '1.00', '1.00'),
(1023, 271, 151, '1.00', '0.35'),
(1024, 271, 150, '1.00', '1.00'),
(1025, 271, 152, '1.00', '0.20'),
(1026, 271, 153, '1.00', '0.05'),
(1027, 271, 154, '2.00', '0.05'),
(1028, 271, 150, '1.00', '1.00'),
(1029, 271, 158, '1.00', '1.00'),
(1030, 271, 155, '1.00', '3.00'),
(1031, 271, 149, '10.00', '0.05'),
(1032, 271, 158, '1.00', '1.00'),
(1033, 271, 146, '1.00', '0.50'),
(1034, 271, 147, '1.00', '1.00'),
(1035, 271, 160, '1.00', '0.65'),
(1036, 271, 162, '1.00', '0.25'),
(1037, 271, 156, '1.00', '0.15'),
(1038, 271, 157, '1.00', '1.00'),
(1039, 271, 163, '1.00', '0.15'),
(1040, 271, 158, '1.00', '1.00'),
(1041, 271, 157, '1.00', '1.00'),
(1042, 271, 147, '1.00', '1.00'),
(1043, 271, 158, '1.00', '1.00'),
(1044, 271, 158, '1.00', '1.00'),
(1045, 271, 147, '1.00', '1.00'),
(1046, 271, 159, '2.00', '1.25'),
(1047, 271, 150, '1.00', '1.00'),
(1049, 272, 158, '1.00', '1.00'),
(1050, 272, 149, '10.00', '0.05'),
(1051, 273, 164, '1.00', '0.30'),
(1052, 273, 152, '1.00', '0.20'),
(1053, 273, 156, '1.00', '0.15'),
(1054, 273, 166, '1.00', '1.50'),
(1055, 273, 167, '1.00', '0.20'),
(1056, 273, 147, '1.00', '1.00'),
(1057, 273, 158, '1.00', '1.00'),
(1058, 273, 165, '10.00', '0.05'),
(1066, 274, 157, '1.00', '1.00'),
(1067, 275, 146, '1.00', '0.50'),
(1068, 275, 147, '1.00', '1.00'),
(1069, 275, 148, '1.00', '2.00'),
(1070, 275, 149, '10.00', '0.05'),
(1071, 275, 150, '1.00', '1.00'),
(1072, 275, 151, '1.00', '0.35'),
(1073, 275, 150, '1.00', '1.00'),
(1074, 275, 152, '1.00', '0.20'),
(1075, 275, 153, '1.00', '0.05'),
(1076, 275, 154, '2.00', '0.05'),
(1077, 275, 150, '1.00', '1.00'),
(1078, 275, 158, '1.00', '1.00'),
(1079, 275, 155, '1.00', '3.00'),
(1080, 275, 149, '10.00', '0.05'),
(1081, 275, 158, '1.00', '1.00'),
(1082, 275, 146, '1.00', '0.50'),
(1083, 275, 147, '1.00', '1.00'),
(1084, 275, 160, '1.00', '0.65'),
(1085, 275, 162, '1.00', '0.25'),
(1086, 275, 156, '1.00', '0.15'),
(1087, 275, 157, '1.00', '1.00'),
(1088, 275, 163, '1.00', '0.15'),
(1089, 275, 158, '1.00', '1.00'),
(1090, 275, 157, '1.00', '1.00'),
(1091, 275, 147, '1.00', '1.00'),
(1092, 275, 158, '1.00', '1.00'),
(1093, 275, 158, '1.00', '1.00'),
(1094, 275, 147, '1.00', '1.00'),
(1095, 275, 159, '2.00', '1.25'),
(1096, 275, 150, '1.00', '1.00'),
(1097, 275, 158, '1.00', '1.00'),
(1098, 275, 149, '10.00', '0.05'),
(1099, 275, 164, '1.00', '0.30'),
(1100, 275, 152, '1.00', '0.20'),
(1101, 275, 156, '1.00', '0.15'),
(1102, 275, 166, '1.00', '1.50'),
(1103, 275, 167, '1.00', '0.20'),
(1104, 275, 147, '1.00', '1.00'),
(1105, 275, 158, '1.00', '1.00'),
(1106, 275, 165, '10.00', '0.05'),
(1107, 275, 157, '1.00', '1.00'),
(1108, 275, 146, '1.00', '0.50'),
(1109, 275, 147, '1.00', '1.00'),
(1110, 275, 148, '1.00', '2.00'),
(1111, 275, 149, '10.00', '0.05'),
(1112, 275, 150, '1.00', '1.00'),
(1113, 275, 151, '1.00', '0.35'),
(1114, 275, 150, '1.00', '1.00'),
(1115, 275, 152, '1.00', '0.20'),
(1116, 275, 153, '1.00', '0.05'),
(1117, 275, 154, '2.00', '0.05'),
(1118, 275, 150, '1.00', '1.00'),
(1119, 275, 158, '1.00', '1.00'),
(1120, 275, 155, '1.00', '3.00'),
(1121, 275, 149, '10.00', '0.05'),
(1122, 275, 158, '1.00', '1.00'),
(1123, 275, 146, '1.00', '0.50'),
(1124, 275, 147, '1.00', '1.00'),
(1125, 275, 160, '1.00', '0.65'),
(1126, 275, 162, '1.00', '0.25'),
(1127, 275, 156, '1.00', '0.15'),
(1128, 275, 157, '1.00', '1.00'),
(1129, 275, 163, '1.00', '0.15'),
(1130, 275, 158, '1.00', '1.00'),
(1131, 275, 157, '1.00', '1.00'),
(1132, 275, 147, '1.00', '1.00'),
(1133, 275, 158, '1.00', '1.00'),
(1134, 275, 158, '1.00', '1.00'),
(1135, 275, 147, '1.00', '1.00'),
(1136, 275, 159, '2.00', '1.25'),
(1137, 275, 150, '1.00', '1.00'),
(1138, 275, 158, '1.00', '1.00'),
(1139, 275, 149, '10.00', '0.05'),
(1140, 275, 164, '1.00', '0.30'),
(1141, 275, 152, '1.00', '0.20'),
(1142, 275, 156, '1.00', '0.15'),
(1143, 275, 166, '1.00', '1.50'),
(1144, 275, 167, '1.00', '0.20'),
(1145, 275, 147, '1.00', '1.00'),
(1146, 275, 158, '1.00', '1.00'),
(1147, 275, 165, '10.00', '0.05'),
(1148, 275, 157, '1.00', '1.00'),
(1149, 275, 125, '1.00', '1.60'),
(1150, 275, 125, '1.00', '1.60'),
(1151, 275, 156, '1.00', '0.15'),
(1194, 276, 146, '1.00', '0.50'),
(1195, 276, 147, '1.00', '1.00'),
(1196, 276, 148, '1.00', '2.00'),
(1197, 276, 149, '10.00', '0.05'),
(1198, 276, 150, '1.00', '1.00'),
(1199, 276, 151, '1.00', '0.35'),
(1200, 276, 150, '1.00', '1.00'),
(1201, 276, 152, '1.00', '0.20'),
(1202, 276, 153, '1.00', '0.05'),
(1203, 276, 154, '2.00', '0.05'),
(1204, 276, 150, '1.00', '1.00'),
(1205, 276, 158, '1.00', '1.00'),
(1206, 276, 155, '1.00', '3.00'),
(1207, 276, 149, '10.00', '0.05'),
(1208, 276, 158, '1.00', '1.00'),
(1209, 276, 146, '1.00', '0.50'),
(1210, 276, 147, '1.00', '1.00'),
(1211, 276, 160, '1.00', '0.65'),
(1212, 276, 162, '1.00', '0.25'),
(1213, 276, 156, '1.00', '0.15'),
(1214, 276, 157, '1.00', '1.00'),
(1215, 276, 163, '1.00', '0.15'),
(1216, 276, 158, '1.00', '1.00'),
(1217, 276, 157, '1.00', '1.00'),
(1218, 276, 147, '1.00', '1.00'),
(1219, 276, 158, '1.00', '1.00'),
(1220, 276, 158, '1.00', '1.00'),
(1221, 276, 147, '1.00', '1.00'),
(1222, 276, 159, '2.00', '1.25'),
(1223, 276, 150, '1.00', '1.00'),
(1224, 276, 158, '1.00', '1.00'),
(1225, 276, 149, '10.00', '0.05'),
(1226, 276, 164, '1.00', '0.30'),
(1227, 276, 152, '1.00', '0.20'),
(1228, 276, 156, '1.00', '0.15'),
(1229, 276, 166, '1.00', '1.50'),
(1230, 276, 167, '1.00', '0.20'),
(1231, 276, 147, '1.00', '1.00'),
(1232, 276, 158, '1.00', '1.00'),
(1233, 276, 165, '10.00', '0.05'),
(1234, 276, 157, '1.00', '1.00'),
(1235, 276, 146, '1.00', '0.50'),
(1236, 276, 147, '1.00', '1.00'),
(1237, 276, 148, '1.00', '2.00'),
(1238, 276, 149, '10.00', '0.05'),
(1239, 276, 150, '1.00', '1.00'),
(1240, 276, 151, '1.00', '0.35'),
(1241, 276, 150, '1.00', '1.00'),
(1242, 276, 152, '1.00', '0.20'),
(1243, 276, 153, '1.00', '0.05'),
(1244, 276, 154, '2.00', '0.05'),
(1245, 276, 150, '1.00', '1.00'),
(1246, 276, 158, '1.00', '1.00'),
(1247, 276, 155, '1.00', '3.00'),
(1248, 276, 149, '10.00', '0.05'),
(1249, 276, 158, '1.00', '1.00'),
(1250, 276, 146, '1.00', '0.50'),
(1251, 276, 147, '1.00', '1.00'),
(1252, 276, 160, '1.00', '0.65'),
(1253, 276, 162, '1.00', '0.25'),
(1254, 276, 156, '1.00', '0.15'),
(1255, 276, 157, '1.00', '1.00'),
(1256, 276, 163, '1.00', '0.15'),
(1257, 276, 158, '1.00', '1.00'),
(1258, 276, 157, '1.00', '1.00'),
(1259, 276, 147, '1.00', '1.00'),
(1260, 276, 158, '1.00', '1.00'),
(1261, 276, 158, '1.00', '1.00'),
(1262, 276, 147, '1.00', '1.00'),
(1263, 276, 159, '2.00', '1.25'),
(1264, 276, 150, '1.00', '1.00'),
(1265, 276, 158, '1.00', '1.00'),
(1266, 276, 149, '10.00', '0.05'),
(1267, 276, 164, '1.00', '0.30'),
(1268, 276, 152, '1.00', '0.20'),
(1269, 276, 156, '1.00', '0.15'),
(1270, 276, 166, '1.00', '1.50'),
(1271, 276, 167, '1.00', '0.20'),
(1272, 276, 147, '1.00', '1.00'),
(1273, 276, 158, '1.00', '1.00'),
(1274, 276, 165, '10.00', '0.05'),
(1275, 276, 157, '1.00', '1.00'),
(1276, 276, 125, '1.00', '1.60'),
(1277, 276, 125, '1.00', '1.60'),
(1278, 276, 156, '1.00', '0.15'),
(1321, 277, 164, '1.50', '0.30'),
(1322, 277, 153, '1.00', '0.05'),
(1323, 277, 152, '1.00', '0.20'),
(1324, 277, 168, '1.00', '0.30'),
(1325, 277, 162, '1.00', '0.25'),
(1389, 279, 147, '1.00', '1.00'),
(1390, 279, 146, '1.00', '0.50'),
(1391, 279, 158, '1.00', '1.00'),
(1392, 279, 158, '1.00', '1.00'),
(1393, 279, 169, '1.00', '0.95');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `detalle_temp`
--

CREATE TABLE `detalle_temp` (
  `correlativo` int(11) NOT NULL,
  `token_user` varchar(50) NOT NULL,
  `codproducto` int(11) NOT NULL,
  `cantidad` decimal(10,2) NOT NULL,
  `precio_venta` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Volcado de datos para la tabla `detalle_temp`
--

INSERT INTO `detalle_temp` (`correlativo`, `token_user`, `codproducto`, `cantidad`, `precio_venta`) VALUES
(2586, 'd41d8cd98f00b204e9800998ecf8427e', 127, '1.00', '1.50'),
(2587, 'd41d8cd98f00b204e9800998ecf8427e', 127, '1.00', '1.50'),
(2611, 'd41d8cd98f00b204e9800998ecf8427e', 127, '3.00', '1.50'),
(2612, 'd41d8cd98f00b204e9800998ecf8427e', 127, '3.00', '1.50'),
(2613, 'd41d8cd98f00b204e9800998ecf8427e', 127, '3.00', '1.50');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `entradas`
--

CREATE TABLE `entradas` (
  `correlativo` int(11) NOT NULL,
  `codproducto` int(11) NOT NULL,
  `fecha` datetime NOT NULL DEFAULT current_timestamp(),
  `cantidad` decimal(10,2) NOT NULL,
  `precio` decimal(10,2) NOT NULL,
  `usuario_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Volcado de datos para la tabla `entradas`
--

INSERT INTO `entradas` (`correlativo`, `codproducto`, `fecha`, `cantidad`, `precio`, `usuario_id`) VALUES
(147, 125, '2020-11-09 17:36:19', '12.00', '1.60', 32),
(148, 126, '2020-11-09 17:36:55', '12.00', '1.60', 32),
(149, 127, '2020-11-09 17:37:47', '120.00', '1.50', 32),
(150, 128, '2020-11-09 17:38:31', '24.00', '1.30', 32),
(151, 129, '2020-11-09 17:39:10', '6.00', '1.00', 32),
(152, 130, '2020-11-09 17:41:34', '6.00', '1.00', 32),
(153, 131, '2020-11-09 17:42:37', '36.00', '1.75', 32),
(154, 132, '2020-11-09 17:45:27', '6.00', '1.75', 32),
(155, 133, '2020-11-09 17:46:04', '12.00', '0.50', 32),
(156, 128, '2020-11-09 17:47:46', '12.00', '1.30', 32),
(157, 134, '2020-11-09 17:48:44', '6.00', '1.00', 32),
(158, 135, '2020-11-09 17:49:23', '6.00', '1.00', 32),
(159, 136, '2020-11-09 17:50:15', '12.00', '2.00', 32),
(160, 127, '2020-11-12 20:36:33', '3.00', '1.50', 32),
(161, 127, '2020-11-13 12:43:23', '1.00', '1.50', 32),
(162, 127, '2020-11-16 15:24:48', '60.00', '1.50', 32),
(163, 137, '2020-11-16 15:25:28', '12.00', '1.60', 32),
(164, 127, '2020-11-23 10:41:24', '72.00', '1.50', 32),
(165, 131, '2020-11-23 10:41:58', '24.00', '1.75', 32),
(166, 125, '2020-11-23 10:42:34', '36.00', '1.60', 32),
(167, 128, '2020-11-30 22:48:35', '12.00', '1.30', 32),
(168, 134, '2020-11-30 22:49:23', '6.00', '1.00', 32),
(169, 138, '2020-11-30 22:50:43', '12.00', '1.00', 32),
(170, 131, '2020-11-30 22:51:36', '36.00', '1.75', 32),
(171, 127, '2020-11-30 22:52:02', '120.00', '1.50', 32),
(172, 136, '2020-11-30 22:52:27', '6.00', '2.00', 32),
(173, 132, '2020-11-30 22:52:46', '6.00', '1.75', 32),
(174, 126, '2020-11-30 22:53:15', '12.00', '1.60', 32),
(175, 139, '2021-01-06 15:55:36', '2.00', '20.00', 32),
(176, 140, '2021-01-06 15:56:25', '2.00', '15.00', 32),
(177, 141, '2021-01-06 15:56:50', '2.00', '15.00', 32),
(178, 142, '2021-01-06 15:57:20', '24.00', '1.50', 32),
(179, 143, '2021-01-06 15:57:49', '1.00', '15.00', 32),
(180, 144, '2021-01-06 15:58:11', '1.00', '10.00', 32),
(181, 145, '2021-01-06 15:58:28', '1.00', '10.00', 32),
(182, 144, '2021-06-08 23:55:12', '12.00', '1.50', 32),
(183, 143, '2021-06-08 23:55:49', '15.00', '12.50', 32),
(184, 146, '2021-06-10 23:01:11', '54.00', '0.50', 32),
(185, 147, '2021-06-10 23:02:00', '36.00', '1.00', 32),
(186, 148, '2021-06-10 23:02:32', '60.00', '2.00', 32),
(187, 149, '2021-06-10 23:02:52', '200.00', '0.05', 32),
(188, 150, '2021-06-10 23:03:37', '48.00', '1.00', 32),
(189, 151, '2021-06-10 23:04:33', '60.00', '0.35', 32),
(190, 152, '2021-06-10 23:05:19', '60.00', '0.20', 32),
(191, 153, '2021-06-10 23:06:02', '60.00', '0.05', 32),
(192, 154, '2021-06-10 23:06:28', '60.00', '0.05', 32),
(193, 155, '2021-06-10 23:07:05', '7.00', '3.00', 32),
(194, 156, '2021-06-10 23:07:43', '60.00', '0.15', 32),
(195, 157, '2021-06-10 23:08:14', '60.00', '1.00', 32),
(196, 158, '2021-06-10 23:09:22', '100.00', '1.00', 32),
(197, 159, '2021-06-10 23:09:50', '560.00', '1.25', 32),
(198, 160, '2021-06-10 23:13:59', '50.00', '0.65', 32),
(199, 161, '2021-06-10 23:14:19', '3.00', '2.50', 32),
(200, 162, '2021-06-10 23:16:18', '10.00', '0.25', 32),
(201, 163, '2021-06-10 23:17:30', '60.00', '0.15', 32),
(202, 164, '2021-06-12 16:31:08', '100.00', '0.30', 32),
(203, 165, '2021-06-12 16:31:42', '100.00', '0.05', 32),
(204, 166, '2021-06-12 16:33:18', '48.00', '1.50', 32),
(205, 167, '2021-06-12 16:34:06', '100.00', '0.20', 32),
(206, 168, '2021-06-12 22:55:35', '100.00', '0.30', 32),
(207, 169, '2021-06-13 23:48:17', '100.00', '0.95', 32);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `factura`
--

CREATE TABLE `factura` (
  `nofactura` bigint(11) NOT NULL,
  `fecha` datetime NOT NULL DEFAULT current_timestamp(),
  `usuario` int(11) DEFAULT NULL,
  `codcliente` int(11) DEFAULT NULL,
  `totalfactura` decimal(10,2) DEFAULT NULL,
  `estatus` int(11) NOT NULL DEFAULT 1,
  `id_tipoF` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Volcado de datos para la tabla `factura`
--

INSERT INTO `factura` (`nofactura`, `fecha`, `usuario`, `codcliente`, `totalfactura`, `estatus`, `id_tipoF`) VALUES
(78, '2020-07-27 22:37:11', 30, 14, '37.20', 2, 1),
(79, '2020-07-27 22:37:44', 30, 14, '5.10', 1, 1),
(80, '2020-07-27 23:02:47', 30, 14, '5049.00', 1, 1),
(81, '2020-07-29 00:30:42', 30, 13, '2.25', 1, 1),
(82, '2020-07-29 00:31:48', 30, 14, '5.10', 0, 3),
(83, '2020-07-29 00:33:07', 30, 14, '5.10', 1, 1),
(84, '2020-07-29 00:34:27', 30, 14, '5052.60', 0, 3),
(85, '2020-07-29 01:50:41', 30, 14, '78.60', 0, 3),
(86, '2020-07-29 01:51:40', 30, 14, '78.60', 1, 1),
(87, '2020-07-29 02:04:38', 30, 14, '242.00', 0, 3),
(88, '2020-07-29 02:05:48', 30, 14, '484.00', 0, 3),
(89, '2020-07-29 02:06:36', 30, 14, '0.30', 0, 3),
(90, '2020-07-29 02:41:12', 30, 14, '726.30', 1, 1),
(91, '2020-07-29 02:43:48', 30, 14, '22.00', 0, 3),
(92, '2020-07-29 02:44:46', 30, 14, '22.00', 1, 1),
(93, '2020-07-29 02:45:51', 30, 14, '22.00', 0, 3),
(94, '2020-07-29 02:46:16', 30, 14, '22.00', 1, 1),
(95, '2020-07-29 02:49:50', 30, 14, '22.00', 0, 3),
(96, '2020-07-29 02:50:25', 30, 14, '22.00', 1, 1),
(97, '2020-07-29 02:51:02', 30, 14, '22.00', 0, 3),
(98, '2020-07-29 02:56:22', 30, 14, '22.00', 1, 1),
(99, '2020-07-29 02:57:01', 30, 14, '22.00', 0, 3),
(100, '2020-07-29 03:47:22', 30, 14, '22.00', 1, 1),
(101, '2020-07-29 03:52:33', 30, 13, '22.00', 0, 3),
(102, '2020-07-29 03:53:03', 30, 14, '5016.00', 0, 3),
(103, '2020-07-29 03:53:25', 30, 16, '4628.25', 0, 3),
(104, '2020-07-29 03:54:27', 30, 13, '22.00', 2, 1),
(105, '2020-07-29 04:00:50', 30, 14, '5016.00', 1, 1),
(106, '2020-07-29 05:02:00', 30, 12, '2508.00', 1, 1),
(107, '2020-07-29 05:14:14', 30, 13, '209.00', 1, 1),
(108, '2020-07-29 05:17:26', 30, 13, '209.00', 1, 1),
(109, '2020-07-30 02:57:36', 30, 18, '119.36', 0, 3),
(110, '2020-07-30 03:54:50', 30, 18, '178.56', 0, 3),
(111, '2020-07-30 03:58:17', 30, 14, '0.15', 1, 3),
(112, '2020-11-09 20:02:19', 32, 15, '1.75', 1, 1),
(113, '2020-11-09 20:23:10', 32, 15, '1.50', 1, 1),
(114, '2020-11-09 20:33:47', 32, 15, '1.50', 1, 1),
(115, '2020-11-09 21:10:31', 32, 15, '3.00', 1, 1),
(116, '2020-11-09 21:27:08', 32, 15, '1.50', 1, 1),
(117, '2020-11-09 21:47:21', 32, 15, '1.50', 1, 1),
(118, '2020-11-09 22:16:35', 32, 15, '1.50', 1, 1),
(119, '2020-11-09 22:25:52', 32, 15, '1.50', 1, 1),
(120, '2020-11-09 22:57:50', 32, 15, '1.50', 1, 1),
(121, '2020-11-10 00:25:20', 32, 15, '3.00', 1, 1),
(122, '2020-11-12 11:45:01', 32, 15, '1.50', 1, 1),
(123, '2020-11-12 19:55:59', 32, 15, '7.50', 1, 1),
(124, '2020-11-12 20:00:21', 32, 15, '1.50', 1, 1),
(125, '2020-11-12 20:34:32', 32, 15, '4.50', 2, 1),
(126, '2020-11-12 20:35:50', 32, 15, '3.00', 1, 1),
(127, '2020-11-12 21:33:10', 32, 15, '4.50', 1, 1),
(128, '2020-11-12 22:23:50', 32, 15, '3.00', 1, 1),
(129, '2020-11-12 22:29:32', 32, 15, '4.50', 1, 1),
(130, '2020-11-12 23:20:14', 32, 15, '7.50', 1, 1),
(131, '2020-11-13 09:11:09', 32, 15, '3.00', 1, 1),
(132, '2020-11-13 09:14:25', 32, 15, '3.00', 1, 1),
(133, '2020-11-13 10:57:02', 32, 15, '3.00', 1, 1),
(134, '2020-11-13 11:30:44', 32, 15, '1.50', 1, 1),
(135, '2020-11-13 11:47:51', 32, 15, '6.00', 1, 1),
(136, '2020-11-13 12:42:01', 32, 15, '1.50', 2, 1),
(137, '2020-11-13 12:44:32', 32, 15, '3.50', 1, 1),
(138, '2020-11-13 13:00:14', 32, 15, '1.75', 1, 1),
(139, '2020-11-13 13:23:30', 32, 15, '4.50', 1, 1),
(140, '2020-11-13 13:23:48', 32, 15, '1.75', 1, 1),
(141, '2020-11-13 18:53:00', 32, 15, '4.50', 1, 1),
(142, '2020-11-14 16:09:54', 32, 15, '4.80', 1, 1),
(143, '2020-11-14 17:08:54', 32, 15, '4.50', 1, 1),
(144, '2020-11-14 18:23:03', 32, 15, '3.20', 1, 1),
(145, '2020-11-14 19:27:38', 32, 15, '3.00', 1, 1),
(146, '2020-11-14 19:50:48', 32, 15, '15.00', 1, 1),
(147, '2020-11-14 20:11:48', 32, 15, '21.00', 1, 1),
(148, '2020-11-14 20:12:00', 32, 15, '3.00', 1, 1),
(149, '2020-11-14 20:32:25', 32, 15, '3.00', 1, 1),
(150, '2020-11-14 21:15:58', 32, 15, '3.00', 1, 1),
(151, '2020-11-14 21:16:11', 32, 15, '18.00', 1, 1),
(152, '2020-11-14 21:59:34', 32, 15, '9.00', 1, 1),
(153, '2020-11-15 09:45:35', 32, 15, '18.00', 2, 1),
(154, '2020-11-15 09:45:50', 32, 15, '1.50', 1, 1),
(155, '2020-11-15 09:49:57', 32, 15, '1.75', 1, 1),
(156, '2020-11-15 09:51:28', 32, 15, '1.00', 1, 1),
(157, '2020-11-15 10:43:05', 32, 15, '3.00', 1, 1),
(158, '2020-11-15 10:43:46', 32, 15, '18.00', 1, 1),
(159, '2020-11-15 10:46:59', 32, 15, '1.30', 1, 1),
(160, '2020-11-15 11:16:11', 32, 15, '1.50', 1, 1),
(161, '2020-11-15 16:03:57', 32, 15, '3.50', 1, 1),
(162, '2020-11-15 21:14:15', 32, 15, '3.90', 1, 1),
(163, '2020-11-15 22:06:41', 32, 15, '3.90', 1, 1),
(164, '2020-11-16 17:49:07', 32, 15, '3.00', 1, 1),
(165, '2020-11-16 17:54:44', 32, 15, '7.80', 1, 1),
(166, '2020-11-16 21:01:56', 32, 15, '1.00', 1, 1),
(167, '2020-11-18 18:29:31', 32, 15, '3.00', 1, 1),
(168, '2020-11-18 22:31:45', 32, 15, '4.50', 1, 1),
(169, '2020-11-19 10:55:47', 32, 15, '3.00', 1, 1),
(170, '2020-11-19 11:59:34', 32, 15, '3.25', 1, 1),
(171, '2020-11-19 20:53:07', 32, 15, '2.00', 1, 1),
(172, '2020-11-20 09:52:25', 32, 15, '1.50', 1, 1),
(173, '2020-11-20 17:02:14', 32, 15, '3.50', 1, 1),
(174, '2020-11-20 17:13:16', 32, 15, '3.00', 1, 1),
(175, '2020-11-20 17:15:37', 32, 15, '1.75', 1, 1),
(176, '2020-11-20 17:34:59', 32, 15, '3.00', 1, 1),
(177, '2020-11-20 17:42:44', 32, 15, '1.75', 1, 1),
(178, '2020-11-20 18:16:09', 32, 15, '4.75', 1, 1),
(179, '2020-11-20 18:35:26', 32, 15, '3.00', 1, 1),
(180, '2020-11-20 19:44:53', 32, 15, '1.50', 1, 1),
(181, '2020-11-20 20:05:45', 32, 15, '3.00', 1, 1),
(182, '2020-11-20 21:46:47', 32, 15, '6.00', 1, 1),
(183, '2020-11-21 09:17:09', 32, 15, '12.00', 1, 1),
(184, '2020-11-21 12:09:50', 32, 15, '1.50', 1, 1),
(185, '2020-11-21 12:33:40', 32, 15, '3.60', 1, 1),
(186, '2020-11-21 13:10:01', 32, 15, '6.00', 1, 1),
(187, '2020-11-21 14:04:51', 32, 15, '3.00', 1, 1),
(188, '2020-11-21 14:46:14', 32, 15, '6.00', 1, 1),
(189, '2020-11-21 18:23:00', 32, 15, '4.50', 1, 1),
(190, '2020-11-22 11:04:28', 32, 15, '1.00', 1, 1),
(191, '2020-11-22 11:05:55', 32, 15, '1.30', 1, 1),
(192, '2020-11-22 11:08:23', 32, 15, '3.00', 1, 1),
(193, '2020-11-22 12:00:09', 32, 15, '3.50', 1, 1),
(194, '2020-11-22 12:26:50', 32, 15, '0.50', 1, 1),
(195, '2020-11-22 12:33:13', 32, 15, '3.50', 1, 1),
(196, '2020-11-22 12:34:48', 32, 15, '2.60', 1, 1),
(197, '2020-11-22 12:55:06', 32, 15, '2.60', 1, 1),
(198, '2020-11-22 14:55:25', 32, 15, '7.80', 1, 1),
(199, '2020-11-22 15:40:08', 32, 15, '7.80', 1, 1),
(200, '2020-11-22 15:50:47', 32, 15, '3.50', 1, 1),
(201, '2020-11-22 16:54:16', 32, 15, '3.50', 1, 1),
(202, '2020-11-22 16:56:46', 32, 15, '7.80', 1, 1),
(203, '2020-11-22 17:23:26', 32, 15, '3.20', 1, 1),
(204, '2020-11-22 18:00:42', 32, 15, '1.60', 1, 1),
(205, '2020-11-22 18:09:22', 32, 15, '9.60', 1, 1),
(206, '2020-11-22 19:32:45', 32, 15, '19.20', 1, 1),
(207, '2020-11-23 17:24:30', 32, 15, '1.50', 1, 1),
(208, '2020-11-23 21:27:37', 32, 15, '1.50', 1, 1),
(209, '2020-11-25 06:55:47', 32, 15, '1.75', 1, 1),
(210, '2020-11-25 15:54:58', 32, 15, '9.00', 1, 1),
(211, '2020-11-25 16:11:05', 32, 15, '4.80', 1, 1),
(212, '2020-11-25 18:10:53', 32, 15, '9.00', 1, 1),
(213, '2020-11-25 18:12:17', 32, 15, '6.00', 1, 1),
(214, '2020-11-25 20:23:27', 32, 15, '4.75', 1, 1),
(215, '2020-11-25 20:23:41', 32, 15, '1.75', 1, 1),
(216, '2020-11-25 20:39:43', 32, 15, '3.00', 1, 1),
(217, '2020-11-26 09:08:06', 32, 15, '1.00', 1, 1),
(218, '2020-11-26 21:09:17', 32, 15, '1.75', 1, 1),
(219, '2020-11-27 10:06:32', 32, 15, '6.00', 1, 1),
(220, '2020-11-27 18:29:08', 32, 15, '1.50', 1, 1),
(221, '2020-11-27 19:19:01', 32, 15, '9.20', 1, 1),
(222, '2020-11-27 19:48:28', 32, 15, '9.00', 1, 1),
(223, '2020-11-27 20:29:32', 32, 15, '4.50', 1, 1),
(224, '2020-11-28 13:48:34', 32, 15, '4.50', 1, 1),
(225, '2020-11-28 14:04:34', 32, 15, '9.00', 1, 1),
(226, '2020-11-28 14:26:31', 32, 15, '6.00', 1, 1),
(227, '2020-11-28 15:50:28', 32, 15, '4.50', 1, 1),
(228, '2020-11-28 16:22:52', 32, 15, '9.00', 1, 1),
(229, '2020-11-29 01:00:52', 32, 15, '4.50', 1, 1),
(230, '2020-11-29 01:43:13', 32, 15, '4.50', 1, 1),
(231, '2020-11-29 01:44:48', 32, 15, '6.00', 1, 1),
(232, '2020-11-29 01:50:04', 32, 15, '5.25', 1, 1),
(233, '2020-11-29 07:30:05', 32, 15, '24.50', 1, 1),
(234, '2020-11-29 20:45:08', 32, 15, '20.70', 1, 1),
(235, '2020-11-29 22:19:13', 32, 15, '19.20', 1, 1),
(236, '2020-11-30 02:17:23', 32, 15, '1.75', 1, 1),
(237, '2020-12-02 02:56:43', 32, 15, '1.75', 1, 1),
(238, '2020-12-03 04:10:00', 32, 15, '3.00', 1, 1),
(239, '2020-12-04 01:54:50', 32, 15, '14.25', 1, 1),
(240, '2020-12-04 03:22:33', 32, 15, '13.00', 1, 1),
(241, '2020-12-04 14:37:18', 32, 15, '6.00', 1, 1),
(242, '2020-12-04 16:01:17', 32, 15, '2.00', 1, 1),
(243, '2020-12-04 20:48:02', 32, 15, '6.00', 1, 1),
(244, '2020-12-04 20:49:31', 32, 15, '1.75', 1, 1),
(245, '2020-12-05 02:13:31', 32, 15, '3.00', 1, 1),
(246, '2021-01-10 23:50:53', 32, 15, '9.00', 1, 1),
(247, '2021-06-07 00:34:41', 32, 15, '2.80', 1, 1),
(248, '2021-06-08 20:23:15', 32, 15, '1.30', 1, 3),
(249, '2021-06-08 20:26:35', 32, 16, '1.30', 0, 3),
(250, '2021-06-08 20:28:45', 32, 16, '3.10', 0, 3),
(251, '2021-06-08 20:37:24', 32, 16, '4.80', 0, 3),
(252, '2021-06-09 00:19:10', 32, 16, '7.30', 0, 3),
(253, '2021-06-09 00:28:00', 32, 16, '33.00', 1, 1),
(254, '2021-06-09 00:29:46', 32, 16, '20.00', 0, 3),
(255, '2021-06-09 00:35:21', 32, 16, '17.75', 0, 3),
(256, '2021-06-09 00:38:48', 32, 16, '37.75', 1, 1),
(257, '2021-06-09 00:40:25', 32, 15, '1.00', 1, 1),
(258, '2021-06-09 00:40:56', 32, 16, '1.00', 0, 3),
(259, '2021-06-09 00:42:29', 32, 16, '1.00', 1, 1),
(260, '2021-06-10 17:26:29', 32, 16, '1.30', 0, 3),
(261, '2021-06-10 18:44:57', 32, 16, '1.30', 1, 1),
(262, '2021-06-10 20:59:36', 32, 15, '1.60', 1, 1),
(263, '2021-06-10 21:00:30', 32, 16, '1.60', 0, 3),
(264, '2021-06-10 21:01:06', 32, 16, '1.60', 1, 1),
(265, '2021-06-10 21:35:37', 32, 16, '1.60', 1, 1),
(266, '2021-06-10 21:36:11', 32, 16, '1.60', 0, 3),
(267, '2021-06-10 21:36:41', 32, 16, '1.60', 0, 3),
(268, '2021-06-10 21:37:00', 32, 16, '1.60', 1, 1),
(269, '2021-06-10 21:37:12', 32, 16, '3.20', 1, 1),
(270, '2021-06-10 22:51:24', 32, 15, '1.60', 1, 1),
(271, '2021-06-10 23:19:02', 32, 22, '26.40', 1, 3),
(272, '2021-06-11 01:46:12', 32, 22, '1.50', 1, 3),
(273, '2021-06-12 16:37:55', 32, 22, '4.85', 1, 3),
(274, '2021-06-12 16:48:25', 32, 22, '1.00', 1, 3),
(275, '2021-06-12 20:03:19', 32, 18, '70.85', 0, 3),
(276, '2021-06-12 20:04:48', 32, 18, '70.85', 1, 1),
(277, '2021-06-12 22:56:01', 32, 22, '1.25', 1, 3),
(279, '2021-06-13 23:48:29', 32, 22, '4.45', 1, 3);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `producto`
--

CREATE TABLE `producto` (
  `codproducto` int(11) NOT NULL,
  `descripcion` varchar(100) DEFAULT NULL,
  `proveedor` int(11) DEFAULT NULL,
  `precio` decimal(10,2) DEFAULT NULL,
  `existencia` decimal(10,2) DEFAULT NULL,
  `date_add` datetime NOT NULL DEFAULT current_timestamp(),
  `usuario_id` int(11) NOT NULL,
  `estatus` int(11) NOT NULL DEFAULT 1,
  `foto` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Volcado de datos para la tabla `producto`
--

INSERT INTO `producto` (`codproducto`, `descripcion`, `proveedor`, `precio`, `existencia`, `date_add`, `usuario_id`, `estatus`, `foto`) VALUES
(125, 'Club 550cc 12 verde', 38, '1.60', '-16.00', '2020-11-09 17:36:19', 32, 1, 'img_producto.png'),
(126, 'CLUB Platino 550cc ', 38, '1.60', '4.00', '2020-11-09 17:36:55', 32, 1, 'img_producto.png'),
(127, 'Nuestra Siembra 1000cc ', 38, '1.50', '93.00', '2020-11-09 17:37:47', 32, 1, 'img_producto.png'),
(128, 'Pilsener 600cc RB', 38, '1.30', '2.00', '2020-11-09 17:38:31', 32, 1, 'img_producto.png'),
(129, 'PONY 1000cc ', 38, '1.00', '-7.00', '2020-11-09 17:39:10', 32, 1, 'img_producto.png'),
(130, 'BUDWEISER 269cc ', 38, '1.00', '3.00', '2020-11-09 17:41:34', 32, 1, 'img_producto.png'),
(131, 'Pilsener Light 1000cc', 38, '1.75', '27.00', '2020-11-09 17:42:37', 32, 1, 'img_producto.png'),
(132, 'STELLA ARTOIS 330cc', 38, '1.75', '6.00', '2020-11-09 17:45:27', 32, 0, 'img_producto.png'),
(133, 'Nutrimalta 550cc ', 38, '0.50', '11.00', '2020-11-09 17:46:04', 32, 1, 'img_producto.png'),
(134, 'Pilsener Light 355cc LATA', 38, '1.00', '6.00', '2020-11-09 17:48:44', 32, 1, 'img_producto.png'),
(135, 'Pilsener 355cc LATA', 38, '1.00', '2.00', '2020-11-09 17:49:23', 32, 1, 'img_producto.png'),
(136, 'CORONA EXTRA 355cc ', 38, '2.00', '14.00', '2020-11-09 17:50:15', 32, 1, 'img_producto.png'),
(137, 'CLUB Roja 550cc', 38, '1.60', '0.00', '2020-11-16 15:25:28', 32, 1, 'img_producto.png'),
(138, 'Pilsener 269cc Lata', 38, '1.00', '4.00', '2020-11-30 22:50:43', 32, 1, 'img_producto.png'),
(139, 'Whisky Something 750ml', 44, '20.00', '0.00', '2021-01-06 15:55:36', 32, 1, 'img_producto.png'),
(140, 'Aguardiente Antioqueño Azul 750ml', 44, '15.00', '2.00', '2021-01-06 15:56:25', 32, 1, 'img_producto.png'),
(141, 'Whisky Jhon Morris 1lt', 44, '15.00', '0.00', '2021-01-06 15:56:50', 32, 1, 'img_producto.png'),
(142, 'Cerveza Stella ARTOIS 330ml', 44, '1.50', '17.00', '2021-01-06 15:57:20', 32, 1, 'img_producto.png'),
(143, 'Ron San Miguel Black', 44, '12.66', '16.00', '2021-01-06 15:57:49', 32, 1, 'img_producto.png'),
(144, 'Ron San Miguel Silver 750ml', 44, '2.15', '13.00', '2021-01-06 15:58:11', 32, 1, 'img_producto.png'),
(145, 'Ron San Miguel Gold', 44, '10.00', '1.00', '2021-01-06 15:58:28', 32, 1, 'img_producto.png'),
(146, 'Azucar de 1/2 Kg', 28, '0.50', '40.00', '2021-06-10 23:01:11', 32, 1, 'img_producto.png'),
(147, 'Cafe Oro de 25gr', 28, '1.00', '4.00', '2021-06-10 23:02:00', 32, 1, 'img_producto.png'),
(148, 'Queso Suave ', 36, '2.00', '54.00', '2021-06-10 23:02:32', 32, 1, 'img_producto.png'),
(149, 'Verde ', 31, '0.05', '20.00', '2021-06-10 23:02:52', 32, 1, 'img_producto.png'),
(150, 'Aceite Palma de Oro de 450ml', 28, '1.00', '24.00', '2021-06-10 23:03:37', 32, 1, 'img_producto.png'),
(151, 'Sal- Crisal yodada de 1/2 Kg', 28, '0.35', '54.00', '2021-06-10 23:04:33', 32, 1, 'img_producto.png'),
(152, 'Criollita en polvo - 7.5gr', 28, '0.20', '46.00', '2021-06-10 23:05:19', 32, 1, 'img_producto.png'),
(153, 'Comino en polvo - 3gr', 28, '0.05', '52.00', '2021-06-10 23:06:02', 32, 1, 'img_producto.png'),
(154, 'Hierva- Cilantro', 31, '0.05', '48.00', '2021-06-10 23:06:28', 32, 1, 'img_producto.png'),
(155, 'GAS- tanque de gas amarillo', 28, '3.00', '1.00', '2021-06-10 23:07:05', 32, 1, 'img_producto.png'),
(156, 'Cebolla colorada- morada', 31, '0.15', '46.00', '2021-06-10 23:07:43', 32, 1, 'img_producto.png'),
(157, 'Detergente Gol de limon - 500gr ', 28, '1.00', '42.00', '2021-06-10 23:08:14', 32, 1, 'img_producto.png'),
(158, 'Huevos camperos 7X1.00', 39, '1.00', '55.00', '2021-06-10 23:09:22', 32, 1, 'img_producto.png'),
(159, 'Pollo Anderson ', 27, '1.25', '548.00', '2021-06-10 23:09:50', 32, 1, 'img_producto.png'),
(160, 'Queso duro x 1/4 ', 36, '0.65', '44.00', '2021-06-10 23:13:59', 32, 1, 'img_producto.png'),
(161, 'queso duro x 1Lb', 36, '2.50', '3.00', '2021-06-10 23:14:19', 32, 1, 'img_producto.png'),
(162, 'Leche entera  x 1/4 200ml', 28, '0.25', '2.00', '2021-06-10 23:16:18', 32, 1, 'img_producto.png'),
(163, 'Huevos camperos x 1 und', 39, '0.15', '54.00', '2021-06-10 23:17:30', 32, 1, 'img_producto.png'),
(164, 'Papa x 1Lb', 31, '0.30', '91.00', '2021-06-12 16:31:08', 32, 1, 'img_producto.png'),
(165, 'Maduro ', 31, '0.05', '40.00', '2021-06-12 16:31:42', 32, 1, 'img_producto.png'),
(166, 'Atun Isabel x 104gr', 28, '1.50', '42.00', '2021-06-12 16:33:18', 32, 1, 'img_producto.png'),
(167, 'Cebolla colorada- morada x 20ctvs', 40, '0.20', '94.00', '2021-06-12 16:34:06', 32, 1, 'img_producto.png'),
(168, 'Fideo Fino x 1/4 de 100gr', 28, '0.30', '98.00', '2021-06-12 22:55:35', 32, 1, 'img_producto.png'),
(169, 'Sardina Real -salsa roja ', 28, '0.95', '99.00', '2021-06-13 23:48:17', 32, 1, 'img_producto.png');

--
-- Disparadores `producto`
--
DELIMITER $$
CREATE TRIGGER `entradas_A_I` AFTER INSERT ON `producto` FOR EACH ROW BEGIN
	INSERT INTO entradas(codproducto,cantidad,precio,usuario_id)
    VALUES(new.codproducto,new.existencia,new.precio,new.usuario_id);
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `proveedor`
--

CREATE TABLE `proveedor` (
  `codproveedor` int(11) NOT NULL,
  `proveedor` varchar(100) DEFAULT NULL,
  `contacto` varchar(100) DEFAULT NULL,
  `telefono` bigint(11) DEFAULT NULL,
  `direccion` text DEFAULT NULL,
  `date_add` datetime NOT NULL DEFAULT current_timestamp(),
  `usuario_id` int(11) NOT NULL,
  `estatus` int(11) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Volcado de datos para la tabla `proveedor`
--

INSERT INTO `proveedor` (`codproveedor`, `proveedor`, `contacto`, `telefono`, `direccion`, `date_add`, `usuario_id`, `estatus`) VALUES
(18, 'Pepsi-Cola', 'Rodrigo Lara bonilla', 989574272, 'escocia', '2020-07-08 15:01:44', 30, 0),
(19, 'BIG-COLA', 'Rosa Bonilla', 9876545678, 'Palmas y Barcelona', '2020-07-08 15:24:19', 7, 0),
(20, 'TONY', 'Carlos Zambrano', 987676547, 'Ciudad Nueva', '2020-07-09 16:06:24', 30, 0),
(21, 'unilever', 'sara', 98796890, '29 de mayo y tsachila', '2020-07-09 16:30:23', 30, 0),
(22, 'Adelca', 'juan zambrano', 987657890, 'chone y quevedo', '2020-07-09 18:18:40', 30, 0),
(23, 'Coca Cola', 'Julio Santillan', 9895555, 'libre ecuador y escocia', '2020-07-29 23:17:30', 30, 1),
(24, 'Pepsi-Cola', 'Carlos Arevalo', 945120356, 'Plan de vivienda', '2020-07-29 23:17:57', 30, 1),
(25, 'TONY', 'Jorge Ruales', 941235216, '29 de mayo y tsachila', '2020-07-29 23:18:21', 30, 1),
(26, 'unilever', 'Sara Cevallos', 941365213, 'SN', '2020-07-29 23:18:47', 30, 1),
(27, 'Pollero', 'Manuel Lopez', 945123695, 'Las palmas', '2020-07-29 23:19:33', 30, 1),
(28, 'SUPER-SAV', 'Bladimir Samaniego', 978416352, 'Ambato y Cuenca', '2020-07-29 23:20:19', 30, 1),
(29, 'Carnicero', 'El Capo', 915423698, 'Ambato y Cuenca', '2020-07-29 23:32:33', 30, 1),
(30, 'REY LECHE', 'Jorge Cruz', 987451263, 'Ambato', '2020-07-29 23:33:53', 30, 1),
(31, 'Verduras', 'Verduleria al costo', 984512635, 'Av.Esmeraldas', '2020-07-30 00:14:11', 30, 1),
(32, 'SU-PAN', 'Julio jimenez', 987451749, 'UCOM 2', '2020-07-30 00:24:32', 30, 1),
(33, 'Cigarrillos', 'Alex Salazar', 985637801, 'Santa Martha', '2020-07-30 00:25:40', 30, 1),
(34, 'Pinguino', 'Krishna Roman', 941649713, 'MOP', '2020-07-30 00:27:55', 30, 1),
(35, 'Arrocero', 'Jose ', 987586941, 'libre ecuador y escocia', '2020-07-30 00:30:48', 30, 1),
(36, 'Qucero ', 'Jose Almache', 945126497, 'Toachi', '2020-07-30 00:32:44', 30, 1),
(37, 'PRONACA', 'Alberto ', 978452365, 'libre ecuador y escocia', '2020-07-30 00:40:07', 30, 1),
(38, 'Cerveceria Nacional', 'Juanita de arco', 986452369, 'Colombia', '2020-07-30 00:42:36', 30, 1),
(39, 'Huevos-Camperos', 'Lukas ', 9864163, 'SN', '2020-07-30 00:48:50', 30, 1),
(40, 'Aguatero- Rocio', 'Rodrigo', 987456352, 'Heriberto maldonado', '2020-07-30 01:00:44', 30, 1),
(41, 'ILE', 'javier', 984562456, 'SN', '2020-07-30 01:05:15', 30, 1),
(42, 'BIG-COLA', 'Luisa', 987463588, 'SN', '2020-07-30 01:10:19', 30, 1),
(43, 'Panadero', 'Jose Luis', 963741852, 'SN', '2020-07-30 02:13:55', 30, 1),
(44, 'DISLICOR', 'Daniel G', 968645944, 'libre ecuador y escocia', '2021-01-06 15:43:07', 32, 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `rol`
--

CREATE TABLE `rol` (
  `idrol` int(11) NOT NULL,
  `rol` varchar(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Volcado de datos para la tabla `rol`
--

INSERT INTO `rol` (`idrol`, `rol`) VALUES
(1, 'administrador'),
(2, 'vendedor');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `saldo_factura_cliente`
--

CREATE TABLE `saldo_factura_cliente` (
  `idsaldo` bigint(11) NOT NULL,
  `idcliente` int(11) NOT NULL,
  `saldoFactura` decimal(10,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_spanish_ci;

--
-- Volcado de datos para la tabla `saldo_factura_cliente`
--

INSERT INTO `saldo_factura_cliente` (`idsaldo`, `idcliente`, `saldoFactura`) VALUES
(1, 14, '0.00'),
(2, 13, '0.00'),
(3, 16, '0.00'),
(4, 18, '58.85'),
(5, 22, '47.05');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tipo_cliente`
--

CREATE TABLE `tipo_cliente` (
  `idtipo` int(11) NOT NULL,
  `tipo` varchar(50) CHARACTER SET latin1 NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_spanish_ci;

--
-- Volcado de datos para la tabla `tipo_cliente`
--

INSERT INTO `tipo_cliente` (`idtipo`, `tipo`) VALUES
(1, 'comprador'),
(2, 'Fiador');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tipo_factura`
--

CREATE TABLE `tipo_factura` (
  `id_tipoF` int(11) NOT NULL,
  `tipoFact` text COLLATE utf8_spanish_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_spanish_ci;

--
-- Volcado de datos para la tabla `tipo_factura`
--

INSERT INTO `tipo_factura` (`id_tipoF`, `tipoFact`) VALUES
(1, 'compra'),
(3, 'fiado');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuario`
--

CREATE TABLE `usuario` (
  `idusuario` int(11) NOT NULL,
  `nombre` varchar(50) DEFAULT NULL,
  `correo` varchar(100) DEFAULT NULL,
  `usuario` varchar(15) DEFAULT NULL,
  `clave` varchar(100) DEFAULT NULL,
  `rol` int(11) DEFAULT NULL,
  `estatus` int(11) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Volcado de datos para la tabla `usuario`
--

INSERT INTO `usuario` (`idusuario`, `nombre`, `correo`, `usuario`, `clave`, `rol`, `estatus`) VALUES
(7, 'klever pardo', 'klever@gmail.es', 'kleve22', '7647966b7343c29048673252e490f736', 2, 1),
(11, 'juan ', 'juan@hotmail.com', 'juan22', 'a94652aa97c7211ba8954dd15a3cf838', 1, 0),
(14, 'carlos ', 'carlos@gmail.com', 'carlos99', '81dc9bdb52d04dc20036dbd8313ed055', 2, 1),
(15, 'cristian castro', 'cristian@gmail.com', 'crist_33', '81dc9bdb52d04dc20036dbd8313ed055', 1, 1),
(16, 'valeria ', 'valeria@gmail.com', 'valeria12', '202cb962ac59075b964b07152d234b70', 2, 1),
(17, 'alex salazar', 'alexsalazar@gmail.com', 'alez33', '202cb962ac59075b964b07152d234b70', 1, 1),
(18, 'klever pardo', 'kleverp@gmail.com', 'klever99', '202cb962ac59075b964b07152d234b70', 2, 0),
(19, 'patricia', 'paty@hotmail.es', 'paty_88', '202cb962ac59075b964b07152d234b70', 2, 1),
(20, 'marco alban', 'marco454@gmail.com', 'marco4', '202cb962ac59075b964b07152d234b70', 2, 1),
(21, 'brandon  moreira', 'brandon43@gmail.com', 'brandon22', 'f5cf002a27d9ced62a3420f4c336296f', 2, 1),
(22, 'gabriel zambrano', 'gabriel@gmail.com', 'gabriel77', 'dd420d56906ac2935a39361d23e89a5f', 2, 0),
(23, 'israel flores murillo', 'isra@outlook.com', 'isra12', '59ad9522ca8f3b84078b2d6acc394330', 2, 1),
(24, 'ronaldo flores', 'ronaldo99@hotmail.es', 'ronaldo_99', '6157e4932d6f619c65501f5e65c0af67', 2, 1),
(25, 'cristian guadalupe', 'cristhian@gmail.com', 'cristian99', 'd5b69176c120ed46c62bd78eeb418f80', 2, 1),
(26, 'lady jumbo', 'lady78@gmail.com', 'lady78', '1729bc477f7b098b508c1e99269c74a1', 1, 1),
(27, 'lourdes guaman', 'lou89@gmail.com', 'lou88', 'b301f022e80444c97566f911ad0f28da', 2, 0),
(28, 'lukas alban', 'lukas88@hotmail.es', 'lukas78', '7647966b7343c29048673252e490f736', 2, 1),
(29, 'carmen venabides', 'carmen@hotmail.es', 'carmen21', 'c20ad4d76fe97759aa27a0c99bff6710', 2, 1),
(30, 'rosa', 'rosa@gmail.com', 'rosa12', '7647966b7343c29048673252e490f736', 1, 1),
(31, 'marlon', 'marlon@gmail.com', 'marlon22', '89', 1, 1),
(32, 'Klever Esvin Pardo Jimenez', 'kleverpardo747@gmail.com', 'klever747', '0b6958e0bfc6c5c39cb8db44c27f6a99', 1, 1);

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `cliente`
--
ALTER TABLE `cliente`
  ADD PRIMARY KEY (`idcliente`),
  ADD KEY `usuario_id` (`usuario_id`),
  ADD KEY `idtipo` (`idtipo`);

--
-- Indices de la tabla `configuracion`
--
ALTER TABLE `configuracion`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `detallefactura`
--
ALTER TABLE `detallefactura`
  ADD PRIMARY KEY (`correlativo`),
  ADD KEY `codproducto` (`codproducto`),
  ADD KEY `nofactura` (`nofactura`);

--
-- Indices de la tabla `detalle_temp`
--
ALTER TABLE `detalle_temp`
  ADD PRIMARY KEY (`correlativo`),
  ADD KEY `nofactura` (`token_user`),
  ADD KEY `codproducto` (`codproducto`);

--
-- Indices de la tabla `entradas`
--
ALTER TABLE `entradas`
  ADD PRIMARY KEY (`correlativo`),
  ADD KEY `codproducto` (`codproducto`),
  ADD KEY `usuario_id` (`usuario_id`);

--
-- Indices de la tabla `factura`
--
ALTER TABLE `factura`
  ADD PRIMARY KEY (`nofactura`),
  ADD KEY `usuario` (`usuario`),
  ADD KEY `codcliente` (`codcliente`),
  ADD KEY `id_tipoF` (`id_tipoF`);

--
-- Indices de la tabla `producto`
--
ALTER TABLE `producto`
  ADD PRIMARY KEY (`codproducto`),
  ADD KEY `proveedor` (`proveedor`),
  ADD KEY `usuario_id` (`usuario_id`);

--
-- Indices de la tabla `proveedor`
--
ALTER TABLE `proveedor`
  ADD PRIMARY KEY (`codproveedor`),
  ADD KEY `usuario_id` (`usuario_id`);

--
-- Indices de la tabla `rol`
--
ALTER TABLE `rol`
  ADD PRIMARY KEY (`idrol`);

--
-- Indices de la tabla `saldo_factura_cliente`
--
ALTER TABLE `saldo_factura_cliente`
  ADD PRIMARY KEY (`idsaldo`),
  ADD KEY `idcliente` (`idcliente`);

--
-- Indices de la tabla `tipo_cliente`
--
ALTER TABLE `tipo_cliente`
  ADD PRIMARY KEY (`idtipo`);

--
-- Indices de la tabla `tipo_factura`
--
ALTER TABLE `tipo_factura`
  ADD PRIMARY KEY (`id_tipoF`);

--
-- Indices de la tabla `usuario`
--
ALTER TABLE `usuario`
  ADD PRIMARY KEY (`idusuario`),
  ADD KEY `rol` (`rol`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `cliente`
--
ALTER TABLE `cliente`
  MODIFY `idcliente` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- AUTO_INCREMENT de la tabla `configuracion`
--
ALTER TABLE `configuracion`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `detallefactura`
--
ALTER TABLE `detallefactura`
  MODIFY `correlativo` bigint(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1394;

--
-- AUTO_INCREMENT de la tabla `detalle_temp`
--
ALTER TABLE `detalle_temp`
  MODIFY `correlativo` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4660;

--
-- AUTO_INCREMENT de la tabla `entradas`
--
ALTER TABLE `entradas`
  MODIFY `correlativo` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=208;

--
-- AUTO_INCREMENT de la tabla `factura`
--
ALTER TABLE `factura`
  MODIFY `nofactura` bigint(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=280;

--
-- AUTO_INCREMENT de la tabla `producto`
--
ALTER TABLE `producto`
  MODIFY `codproducto` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=170;

--
-- AUTO_INCREMENT de la tabla `proveedor`
--
ALTER TABLE `proveedor`
  MODIFY `codproveedor` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=45;

--
-- AUTO_INCREMENT de la tabla `rol`
--
ALTER TABLE `rol`
  MODIFY `idrol` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `saldo_factura_cliente`
--
ALTER TABLE `saldo_factura_cliente`
  MODIFY `idsaldo` bigint(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT de la tabla `tipo_cliente`
--
ALTER TABLE `tipo_cliente`
  MODIFY `idtipo` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `tipo_factura`
--
ALTER TABLE `tipo_factura`
  MODIFY `id_tipoF` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `usuario`
--
ALTER TABLE `usuario`
  MODIFY `idusuario` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=33;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `cliente`
--
ALTER TABLE `cliente`
  ADD CONSTRAINT `cliente_ibfk_1` FOREIGN KEY (`usuario_id`) REFERENCES `usuario` (`idusuario`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `cliente_ibfk_2` FOREIGN KEY (`idtipo`) REFERENCES `tipo_cliente` (`idtipo`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `detallefactura`
--
ALTER TABLE `detallefactura`
  ADD CONSTRAINT `detallefactura_ibfk_1` FOREIGN KEY (`nofactura`) REFERENCES `factura` (`nofactura`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `detallefactura_ibfk_2` FOREIGN KEY (`codproducto`) REFERENCES `producto` (`codproducto`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `detalle_temp`
--
ALTER TABLE `detalle_temp`
  ADD CONSTRAINT `detalle_temp_ibfk_2` FOREIGN KEY (`codproducto`) REFERENCES `producto` (`codproducto`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `entradas`
--
ALTER TABLE `entradas`
  ADD CONSTRAINT `entradas_ibfk_1` FOREIGN KEY (`codproducto`) REFERENCES `producto` (`codproducto`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `factura`
--
ALTER TABLE `factura`
  ADD CONSTRAINT `factura_ibfk_1` FOREIGN KEY (`usuario`) REFERENCES `usuario` (`idusuario`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `factura_ibfk_2` FOREIGN KEY (`codcliente`) REFERENCES `cliente` (`idcliente`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `factura_ibfk_3` FOREIGN KEY (`id_tipoF`) REFERENCES `tipo_factura` (`id_tipoF`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `producto`
--
ALTER TABLE `producto`
  ADD CONSTRAINT `producto_ibfk_1` FOREIGN KEY (`proveedor`) REFERENCES `proveedor` (`codproveedor`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `producto_ibfk_2` FOREIGN KEY (`usuario_id`) REFERENCES `usuario` (`idusuario`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `proveedor`
--
ALTER TABLE `proveedor`
  ADD CONSTRAINT `proveedor_ibfk_1` FOREIGN KEY (`usuario_id`) REFERENCES `usuario` (`idusuario`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `saldo_factura_cliente`
--
ALTER TABLE `saldo_factura_cliente`
  ADD CONSTRAINT `saldo_factura_cliente_ibfk_1` FOREIGN KEY (`idcliente`) REFERENCES `cliente` (`idcliente`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `usuario`
--
ALTER TABLE `usuario`
  ADD CONSTRAINT `usuario_ibfk_1` FOREIGN KEY (`rol`) REFERENCES `rol` (`idrol`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
