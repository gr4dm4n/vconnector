<?php
/**
 * VIANCH MYSQL CONNECTO Class, el ejemplo apenas está en desarrollo
 *
 * @author Victor Chavarro {@link http://www.vianch.com Victor Chavarro (victor@vianch.com)}
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * Esta versión esta usando MySQLi para el manejo de la base de datos
 */


class vconnector
{
	static private $instance = NULL;
	
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
	public function __construct($dbuser, $dbpassword, $dbname, $dbhost, $dbcharset = 'utf8')
	{
		
		$this->conexion = mysqli_connect($dbhost, $dbuser, $dbpassword, $dbname);
	
		if (!$this->conexion) 
			 die('Connect Error (' . mysqli_connect_errno() . ') ' . mysqli_connect_error());
		else
		{
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
	protected function insert($table_name,$cols,$vals,$debug = false)
	{
		$SQL_INSERT = "INSERT INTO $table_name ($cols) VALUES ($vals)";
		
		if($debug)
			$this->debuger($SQL_INSERT,array());
		
		
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
    protected function select($SQL,$debug = false)
    {
    	$array_return = array();
    	
    	if( strlen( $SQL ) > 0 )
    	{
    		if ($result = mysqli_query($this->conexion, $SQL)) 
    		{
    			while ($row = mysqli_fetch_row($result)) 
    			{
    				$array_return[] = $row;
    			}
    			/* free result set */
    			mysqli_free_result($result);
    		}
    		
    		/*debug para revisar si el sql que se pasa es el correcto y las respuestas del array*/
    		if($debug){
    			$this->debuger($SQL,$array_return);
    		}
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
	protected function update($table, $cols, $vals, $clauses = '', $debug = false)
	{	
		$AND = '1=1 ';
		$columns = explode(',', $cols);
		$values = explode(',', $vals);
		if($clauses == ''){
			$clauses = array();
		}
		else{
			$clauses = explode(',', $clauses);
		}
		
		if((count($columns)) != (count($values)))
		{
			if($debug)
				$this->debuger("No son iguales las columnas a los valores",array());
			return false;
		}
		else{
			$sets = '';
			$i=0;
			foreach ($columns as $column) {
				$sets .="$column = ".$values[$i].", ";
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
	protected function delete($table, $clauses = '', $debug = false){
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
	protected function execute($SQL,$debug = false){
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
			
			if($debug){
				$this->debuger($SQL,array("$answer"));
			}
			
			return $answer;
		}
		
	}
	
	
	/* HELPERS FUNCTIONS */
	
	/**
	 * 
	 * Compara dos fechas retorna true si la fecha limite es mayor a la de hoy
	 * falso si lo contrario
	 * 
	 * @return bool
	 */
	public function DateComapre($expirationDate, $debug = false)
	{
		$today = date("Y-m-d H:i:s");
		
		if($debug)
		{
			echo "Expiraton date: ".$expirationDate;
			echo "<br /> today: ".$today;
		}
		
		$today = strtotime($today);
		$expirationDate = strtotime($expirationDate);
		
		if($debug)
		{
			echo "<br /> expiration date UNIX: ".$expirationDate;
			echo "<br /> today Unix: ".$today;
		}
		
		if ($expirationDate > $today) {
			return true;
		} else {
			return false;
		}
	}
	
	
	/**
	 *
	 * Compara si la fecha actual con una fecha registro ya pasó una semana
	 * falso si lo contrario
	 *
	 * @return bool
	 */
	public function ElapsedDate($registerDate, $_daysAfter = 7, $debug = false)
	{
		$today = date("Y-m-d H:i:s");
	
		if($debug)
		{
			echo "register date: ".$registerDate;
			echo "<br /> today: ".$today;
		}
	
		$today = strtotime($today);
		$registerDate = strtotime($registerDate);
	
		if($debug)
		{
			echo "<br /> register date UNIX: ".$registerDate;
			echo "<br /> today Unix: ".$today;
		}
	
		$dateDifference  = ($today - $registerDate) / 86400;
		
		if($debug)
		{
			echo "<br /> difference: ".$dateDifference;
			echo "<br /> days after: ".$_daysAfter;
		}
		
		if ($dateDifference > $_daysAfter) 
			return true;
		else 
			return false;
	}

	
	/**
	 * 
	 * genera numero aleatorios diferentes, tantos como se indiquen
	 * 
	 * @param int $maximunNumberGenerated
	 * @return array
	 */
	public function GenerateRandomNumbers( $maxNumberGenerated, $minValue = 1, $maxValue = 20 )
	{
		//inicializo variables 
		$randomNumbers = array();
		$iterator = 0;
		
		//restrigen la cantidad de numeros generados al maximo posibles
		$maxNumberCanBeGenerated = ($maxValue - $minValue) + 1;
		if( $maxNumberGenerated > $maxNumberCanBeGenerated )
			$maxNumberGenerated = $maxNumberCanBeGenerated;
		
		//si minVaule es mayor a maxValue invierte los valores de maximo y minimo
		if($minValue > $maxValue)
		{
			$tempVal = $minValue;
			$minValue = $maxValue;
			$maxValue = $tempVal;
			unset($tempVal);
		}
		
		do 
		{
			$randonNumber = rand($minValue,$maxValue);
			if( !in_array($randonNumber, $randomNumbers) )
			{
				$randomNumbers[$iterator] = $randonNumber;
				++$iterator;
			}
				
		}
		while( $iterator != $maxNumberGenerated);
		
		return $randomNumbers;
	}

	public function CheckProbability($probability , $length)
	{
	   $rand = mt_rand(1, $length);
	   return $rand<=$probability*$length;
	}
	
	/**
	 * Connection alerts Function can help to struct outputs
	 * @param string $info_to_debug
	 * @param int $error_code
	 * @param string $status
	 * @param bool $print_json
	 * @param bool $print_on_screen
	 * @return array|bool|string
	 */
	protected function print_debug( $info_to_debug, $status = 'SUCCESS', $error_code = 0, $print_json = true, $print_on_screen = true )
	{
		$array_output = array(
				'error_code' => $error_code,
				'Status' => $status,
				'Debug_info' => $info_to_debug
		);
	
		if($print_on_screen){
			if($print_json){
				echo json_encode($array_output);
			}
			else{
				print_r($array_output);
			}
			return true;
		}
		else{
			if($print_json){
				return json_encode($array_output);
			}
			else{
				return $array_output;
			}
				
		}
	}
	

	/**
	 *DEBUGER DE SQLS
	 *
	 */
	private function debuger($SQL,$ANSWER){
		echo "\n<br/>SQL-> ".$SQL;
		echo "\n<br/>RETORNO: "; print_r($ANSWER);
	}
	
	 static public function getInstance($dbuser, $dbpassword, $dbname, $dbhost, $dbcharset = 'utf8') {
	    if (self::$instance == NULL) {
	      self::$instance = new vconnector($dbuser,$dbpassword,$dbname,$dbhost, $dbcharset);
	    }
	    return self::$instance;
	  }

	/**
	 * destruye la conexión
	 */
	function __destruct(){
		if($this->conexion)
		mysqli_close($this->conexion);
		//echo "\n<br/>se desconecto de la base de datos\n<br/>";
	}
	

}
