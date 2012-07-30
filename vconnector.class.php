<?php
/**
 * VIANCH MYSQL CONNECTO Class, el ejemplo apenas está en desarrollo
 *
 * @author Victor Chavarro {@link http://www.vianch.com Victor Chavarro (victor@vianch.com)}
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * aunque el uso de la función nativa php mysql_connect esta obsoleto la utilizo para mostrar
 * la teoria básica de un conector a MySQL desde PHP (se recomienda el uso de  MySQLi o PDO_MySQL)
 */


class vconnector{

	/*Conexión a la base de datos*/
	private conexion;
	
	/**
	* conecta a la base de datos, recibe como parámetros obligatorios
	* el usuario de la base de datos, el password de la base de datos
	* el nombre de la base de datos que se va a usar, la dirección host de la basae de datos
	* y como parámetro opcional la codificación de los textos en la bse de datos, por defecto esta UTF-8
	* @param string $dbuser;
	* @param string $dbpassword;
	* @param string $dbname;
	* @param string $dbhost;
	* @param string $dbcharset;
	*
	* al dejar de usar la clase automáticamente cierra la conexión
	* @see __destruct()
	*/
	public function __construct($dbuser, $dbpassword, $dbname, $dbhost, $dbcharset = 'utf8'){
		
		$this->conexion =  mysql_connect($dbhost, $dbuser, $dbpassword);
		if (!$this->conexion) {
			die('ERROR!, No pudo conectarse: ' . mysql_error());
		}
		else{
			$dbseleccionada = mysql_select_db($dbname, $this->conexion);
			if (!$dbseleccionada) {
				$this->conexion = NULL;
				die ('ERROR!, No se puede usar la base de datos seleccionada : ' . mysql_error());
			}
			else{
				mysql_query("SET NAMES '$dbcharset'",$this->conexion);
			}
		}	
	}
	

	/**
	 *
	 * INSERTA EN UNA TABLA DETERMINADA
	 * se pasan por parámetros las columnas a insertar (string: columnas separadas por coma) y los valores
	 * que van en ellas (string: valores separados por coma)
	 * 
	 * @param string $table_name
	 * @param string $cols
	 * @param string $vals
	 */
	public function insert($table_name,$cols,$vals){
		$SQL_INSERT = "INSERT INTO $table_name ($cols) VALUES ($vals)";
		$answer = mysql_query($SQL_INSERT1,$conexion);
		if(!$answer){
			 die('INSERT no válida: ' . mysql_error());
		}
	}
	
    public function select($tables){

	
	}

	public function update(){

	}
	
	public function delete(){
	
	}
	
	public function __destruct(){
		mysql_close($this->conexion);
		
		//mysql_free_result($res_tyco);
	}
	

}
?>