<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
$host = 'localhost';
$user = 'root';
$password = '';
$DB = 'facturacion';

$conexion = mysqli_connect($host, $user, $password, $DB);

if(!$conexion){
    echo "error en la conexion";
} 
?>
