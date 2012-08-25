<?php
/**
 * VIANCH MYSQL CONNECTO Class, el ejemplo apenas está en desarrollo
 *
 * @author Victor Chavarro {@link http://www.vianch.com Victor Chavarro (victor@vianch.com)}
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * Esta versión esta usando MySQLi para el manejo de la base de datos
 */


class vconnector{

	/*Conexión a la base de datos*/
	private $conexion;
	
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
		
		$this->conexion = mysqli_connect($dbhost, $dbuser, $dbpassword, $dbname);
	
		if (!$this->conexion) {
			 die('Connect Error (' . mysqli_connect_errno() . ') ' . mysqli_connect_error());
		}
		else{
				$stmt = mysqli_prepare($this->conexion, "SET NAMES '$dbcharset'");
				mysqli_stmt_execute($stmt);
				//echo "\n<br/>se conecto a la base de datos\n<br/>";
		}	
	}
	

	/**
	 *
	 * INSERTA EN UNA TABLA DETERMINADA
	 * se pasan por parámetros las columnas a insertar (string: columnas separadas por coma) y los valores
	 * que van en ellas (string: valores separados por coma)
	 * ejemplo 
	 * $table_name = 'table_user';
	 * $cols = 'id,name_user';
	 * $vals = "1,'juan'";
	 *
	 * SQL Generado: INSERT INTO table_user (id,name_user) VALUES (1, 'juan')
	 * 
	 * @param string $table_name
	 * @param string $cols
	 * @param string $vals
	 *
	 * @return true;
	 */
	public function insert($table_name,$cols,$vals){
		$SQL_INSERT = "INSERT INTO $table_name ($cols) VALUES ($vals)";
		$stmt = mysqli_prepare($this->conexion, $SQL_INSERT);
		/* Execute the statement */
		$answer = mysqli_stmt_execute($stmt);
		if(!$answer){
			 die('INSERT no válida: ' . mysqli_stmt_error($stmt));
		}
		else{
			/* close statement */
			mysqli_stmt_close($stmt);
			return true;
		}
		
		
		
	}
	
	/**
	 * Como realizar un select es diverso y dependiendo de como se quiera consultar
	 * esta función recibe por parametro el SQL que contiene el SELECT y opcional
	 * un debug para saber que esta pasando con el SQL
	 * la función retorna un array con toda la información de la consulta SQL, retorna
	 * array vacio si se genero algún error
	 * 
	 * @param string $SQL
	 * @param bool $debug
	 *
	 * @return array
	 * @see debuger() para ver como se retorna el debug del SQL
	 */
    public function select($SQL,$debug = false){
		$array_return = array();
		
		if ($result = mysqli_query($this->conexion, $SQL)) {
		    while ($row = mysqli_fetch_row($result)) {
		        $array_return[] = $row;
		    }
		    /* free result set */
		    mysqli_free_result($result);
		}
		
		/*debug para revisar si el sql que se pasa es el correcto y las respuestas del array*/
		if($debug){
			$this->debuger($SQL,$array_return);
		}
		
		return $array_return;
	}


	/**
	 * ACTUALIZA UNA TABLA DETERMINADA 
	 * Recibe por parametro la tabla a acutalizar, que columnas se van actualizar,
	 * cuales son los nuevos valores y las condiciones si tiene de que se va actualizar
	 * EJ: 
	 * $table = "user";
	 * $cols = "name,age,..."
	 * $vals = "carlos,30,..";
	 * $clauses = "ID = 1,..."
	 *
	 * SQL Generado: UPDATE user SET name = 'carlos', age = '30' WHERE 1=1 AND ID = 1;
	 *
	 * @see debuger() para ver como se retorna el debug del SQL
	 * 
	 * @param string $table
	 * @param string $cols
	 * @param string $vals
	 * @param string $clauses
	 *
	 * @return true;
	 */
	public function update($table, $cols, $vals, $clauses = '', $debug = false){
		$AND = '1=1 ';
		$columns = explode(',', $cols);
		$values = explode(',', $vals);
		if($clauses == ''){
			$clauses = array();
		}
		else{
			$clauses = explode(',', $clauses);
		}
		
		if((count($columns)) != (count($values))){
			return false;
		}
		else{
			$sets = '';
			$i=0;
			foreach ($columns as $column) {
				$sets .="$column = '".$values[$i]."', ";
				++$i;
			}
		
			if(count($clauses)>0){
				foreach ($clauses as $clause) {
					$AND .="AND $clause ";
				}	
			}
			

			$sets = substr($sets , 0, -2); //quita la ',' y el espacio final
			$AND = substr($AND , 0, -1); //quita el espacio final
			
			$SQL_UPDATE = "UPDATE $table SET $sets WHERE $AND";
		
			if($debug){
				$this->debuger($SQL_UPDATE,array());
			}
			
			
			$stmt = mysqli_prepare($this->conexion, $SQL_UPDATE);
			$answer = mysqli_stmt_execute($stmt);
			if(!$answer){
				 die('actualización no válida: ' . mysqli_stmt_error($stmt));
			}
			else{
				mysqli_stmt_close($stmt);
				return true;
			}
		}
		

	}
	
	/**
	 * BORRA UN REGISTRO DE LA BASE DE DATOS
	 * Recibe como parametros la tabla de donde va ser borrado el registro y 
     * las condiciones de borrado	
	 * 
	 * $table = "user"
	 * $clauses = "ID = 1"
	 *
	 * SQL GENERADO: DELETE FROM user WHERE 1=1 AND ID = 1
	 *
	 * @see debuger() para ver como se retorna el debug del SQL
	 * 
	 * @param string $table
	 * @param string $clauses
	 *
	 * @return true;
	 */
	public function delete($table, $clauses = '', $debug = false){
		$AND = '1=1 ';
		if($clauses == ''){
			$clauses = array();
		}
		else{
			$clauses = explode(',', $clauses);
		}
		
		if(count($clauses)>0){
			foreach ($clauses as $clause){
				$AND .="AND $clause ";
			}	
		}
		$SQL_DELETE = "DELETE FROM $table WHERE $AND";
		
		//debug del sql
		if($debug){
			$this->debuger($SQL_DELETE,array());
		}
		
		$stmt = mysqli_prepare($this->conexion, $SQL_DELETE);
		$answer = mysqli_stmt_execute($stmt);
	    if(!$answer){
			 die('actualización no válida: ' . mysqli_stmt_error($stmt));
	    }
		else{
			mysqli_stmt_close($stmt);
			return true;
		}
	}
	
	/**
	 * Ejecuta un query especial en la bse de datos 
	 * que no se pueda realizar con las funciones anteriores
	 * 
	 * @param string $SQL
	 * @param bool $debug
	 *
	 * @return sql_response
	 * @see debuger() para ver como se retorna el debug del SQL
	 * 
	 * */
	public function execute($SQL,$debug = false){
		if($debug){
			$this->debuger($SQL,array());
		}
		
		$stmt = mysqli_prepare($this->conexion, $SQL);
		$answer = mysqli_stmt_execute($stmt);
		if(!$answer){
			 die('ejecucion no válido: ' .  mysqli_stmt_error($stmt));
		}
		else{
			mysqli_stmt_close($stmt);
			return $answer;
		}
		
	}

	/**
	 *DEBUGER DE SQLS 
	 *
	 */
	public function debuger($SQL,$ANSWER){
		echo "\n<br/>SQL-> ".$SQL;
		echo "\n<br/>RETORNO: "; print_r($ANSWER);
	}
	
	
	/**
	 * destruye la conexión
	 */
	public function __destruct(){
		mysqli_close($this->conexion);
		//echo "\n<br/>se desconecto de la base de datos\n<br/>";
	}
	

}
?>