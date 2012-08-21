<?php 
include('vconnector.class.php');
	

define('DB_NAME', 'dbname');

/** MySQL database username */
define('DB_USER', 'dbusername');

/** MySQL database password */
define('DB_PASSWORD', 'dbpassword');

/** MySQL hostname */
define('DB_HOST', 'xx.xxx.xxx.xxx');

define('DB_CHARSET', 'utf8');



$conexion = new vconnector( DB_USER, DB_PASSWORD, DB_NAME, DB_HOST, DB_CHARSET );

/*prueba insert*/
$res_insert = $conexion->insert('test','name,age',"'Andres',18");


/*prueba seleccionar*/
$SQL = "SELECT * FROM test";
$res_select = $conexion->select($SQL);
print_r($res_select);

/*prueba actualizar*/
$return_update = $conexion->update('test', 'name', "Pepe","age = 18,name ='Andres' ");

/*prueba Borrar*/
$res_delete = $conexion->delete('test', 'id=1');

/*Prueba SQL independiente*/
$res_delete = $conexion->execute('DELETE FROM test WHERE id IN (27,29,32)');

?>