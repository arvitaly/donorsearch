<?php

	$starttime = microtime( true ) ;
	$_SERVER['REQUEST_TIME_FLOAT'] = $starttime ;
	ini_set( 'display_errors', true ) ;
	error_reporting( E_ALL ^ E_PARSE ) ;
	function myErrorHandler( $errno = null, $errstr = null, $errfile = null, $errline = null )
	{
		throw new ErrorException( $errstr, $errno, 0, $errfile, $errline ) ;
	}
	class MysqlException extends Exception
	{
		public function __construct( $errstr, $errno, $errfile, $errline, $query = null )
		{
			$this->message = 'Mysql error - ' . $errstr . ' on query - `' . $query . '`' ;
			$this->code = $errno ;
			$this->file = $errfile ;
			$this->line = $errline ;
		}
	}
	class ScriptException extends Exception
	{
        protected $codeline; 
		public function __construct( $errstr, $errno, $errfile, $errline, $codeline )
		{
			$this->message = $errstr ;
			$this->code = $errno ;
			$this->file = $errfile ;
			$this->line = $errline ;
            $this->codeline = $codeline;
		}
        public function getCodeLine()
        {
            return $this->codeline;
        }
	}
	class InternalException extends Exception
	{
		public function __construct( $errstr, $errno, $errfile, $errline )
		{
			$this->message = $errstr ;
			$this->code = $errno ;
			$this->file = $errfile ;
			$this->line = $errline ;
		}
	}
	class Exception404 extends Exception
	{
		public function __construct( $errstr )
		{
			$this->message = $errstr ;
			//echo $this->message ;
            if ( Request::TYPE_EVENT  || Request::TYPE_METHOD )
            {
                //alert($errstr);
                exit;
            }
			header( "HTTP/1.0 404 Not Found" ) ;
			header( "Status: 404 Not Found" ) ;
			require '404.html' ;
			exit ;
		}
	}

	set_error_handler( 'myErrorHandler' ) ;
	/*function __autoload($className)
	{
	require '../../kazan/data/' . str_replace('_', '/', $className) . '.php';
	}*/
	ini_set( "mysql.trace_mode", true ) ;
	function mysql_fetch_one( $sql )
	{
		$start_time = microtime( true ) ;
		jsconsole( $sql ) ;

		$result = mysql_query( $sql ) ;

		jsconsole( 'Count of result - ' . mysql_num_rows( $result ) ) ;


		$rows = mysql_fetch_assoc( $result ) ;


		if ( $rows )
		{
			$rows = ( object )$rows ;
		}
		mysql_free_result( $result ) ;

		jsconsole( 'Time of query execute ' . ( microtime( true ) - $start_time ) . ' s' ) ;
		elapsedtime() ;
		return $rows ;
	}
	function mysql_insert( $table, $parameters, $ignore = false )
	{
		$sql = "insert " . ( $ignore ? 'ignore' : '' ) . " into " . $table . "(`" . implode( "`,`", array_keys( $parameters ) ) . "`) values('" . implode( "','", array_values( $parameters ) ) . "')" ;
		if ( mysql_query( $sql ) === false )
		{
			throw new MysqlException( 'Error insert' ) ;
		}
		return mysql_insert_id() ;
	}
	function mysql_remove( $table, $parameters )
	{
		$sql = "delete from " . $table . " where " ;

		$p = array() ;
		foreach ( $parameters as $name => $parameter )
		{
			$p[] = $name . '="' . mysql_real_escape_string( $parameter ) . '"' ;
		}

		$sql .= implode( ' and ', $p );

		if ( mysql_query( $sql ) === false )
		{
			throw new MysqlException( 'Error remove' ) ;
		}
		return mysql_affected_rows() ;
	} 
    function mysql_execute( $sql )
    {
		if ( mysql_query( $sql ) === false )
		{
			throw new MysqlException( 'Error' ) ;
		}
		return mysql_affected_rows() ;        
    }
	function mysql_update( $table, $parameters, $where )
	{
		$sql = "update " . $table . " set " ;

		$p = array() ;
		foreach ( $parameters as $name => $parameter )
		{
			$p[] = $name . '="' . mysql_real_escape_string( $parameter ) . '"' ;
		}

		$sql .= implode( ',', $p ) . ' where ' . $where ;

		if ( mysql_query( $sql ) === false )
		{
			throw new MysqlException( 'Error update' ) ;
		}
		return mysql_affected_rows() ;
	}
	function mysql_fetch_list( $sql )
	{
	   
		$start_time = microtime( true ) ;
		jsconsole( $sql ) ;
        
		$result = mysql_query( $sql ) ;

		jsconsole( 'Count of result - ' . mysql_num_rows( $result ) ) ;
        
        
		if ( mysql_num_rows( $result ) )
		{

			$rows = array() ;
			while ( $row = mysql_fetch_assoc( $result ) )
			{
				$rows[] = ( object )$row ;
			}
		}
		else
		{
			$rows = false ;
		}
		mysql_free_result( $result ) ;
		jsconsole( 'Time of query execute ' . ( microtime( true ) - $start_time ) . ' s' ) ;
		elapsedtime() ;
		return $rows ;
	}

	function preg_match_return( $regexp, $str, $fields )
	{
		if ( ! preg_match( $regexp, $str, $matches ) )
		{
			return false ;
		}

		unset( $matches[0] ) ;

		if ( ! $matches )
		{
			if ( $fields )
			{
				throw new Exception( 'Matches and fields has another count' ) ;
			}
			return array() ;
		}
		return array_combine( $fields, $matches ) ;
	}
	function elapsedtime()
	{
	  
		jsconsole( ( microtime( true ) - ( class_exists( 'Request' ) && method_exists('Request','get') ? Request::get( 'REQUEST_TIME_FLOAT' ) : $_SERVER['REQUEST_TIME_FLOAT'] ) ) . ' s from start script' ) ;
	}
	function getTranslit( $name )
	{
		$name = html_entity_decode( $name ) ;

		$r = array(
			"а",
			"б",
			"в",
			"г",
			"д",
			"е",
			"ё",
			"ж",
			"з",
			"и",
			"й",
			"к",
			"л",
			"м",
			"н",
			"о",
			"п",
			"р",
			"с",
			"т",
			"у",
			"ф",
			"х",
			"ц",
			"ч",
			"ш",
			"щ",
			"ъ",
			"ы",
			"ь",
			"э",
			"ю",
			"я",
			"(",
			")",
			"-",
			".",
			",",
			"_",
			"\t",
			"\s",
			chr( 32 ),
			"?",
			"+",
			"\"",
			"'",
			"!",
			"&",
			"~",
			"*",
			"/",
			";",
			":",
			"“",
			"”",
			"№",
			'\\' ) ;
		$e = array(
			"a",
			"b",
			"v",
			"g",
			"d",
			"e",
			"e",
			"zh",
			"z",
			"i",
			"j",
			"k",
			"l",
			"m",
			"n",
			"o",
			"p",
			"r",
			"s",
			"t",
			"u",
			"f",
			"h",
			"c",
			"ch",
			"sh",
			"sch",
			"",
			"y",
			"",
			"e",
			"yu",
			"ya",
			"_",
			"_",
			"_",
			"_",
			"_",
			"_",
			"_",
			"_",
			"_",
			"_",
			"_plyus_",
			"",
			"",
			"",
			"_",
			"_",
			"_",
			"_",
			"_",
			"_",
			"_",
			"_",
			"nomer",
			'_' ) ;

		return trim( str_replace( "__", "_", str_replace( "__", "_", str_replace( "__", "_", str_replace( "__", "_", str_replace( "__", "_", str_replace( "__", "_", str_replace( $r, $e, mb_strtolower( $name, 'UTF-8' ) ) ) ) ) ) ) ), "_" ) ;
	}


	function jsconsole( $str )
	{
	   return;
		if ( ( isset( $_SERVER ) && isset( $_SERVER['HTTP_X_REQUESTED_WITH'] ) ) || ( class_exists( 'Request' ) && Request::get( 'Vika-Request-Type' ) ) )
		{

			echo 'console.log(' . json_encode( $str ) . ');' ;
		}
		else
		{
			echo '<script>console.log(' . json_encode( $str ) . ')</script>' ;
		}

	}
	function alert( $str )
	{
		echo 'alert("' . iconv( 'WINDOWS-1251', 'UTF-8', $str ) . '");' ;
	}
	ini_set( "mysql.default_port", 3306 ) ;
	ini_set( "mysql.default_host", DBHOST ) ;
	ini_set( "mysql.default_user", DBUSER ) ;
	ini_set( "mysql.default_password", DBPASSWORD ) ;


	mysql_set_charset( 'utf8' ) ;
	mysql_query( 'SET character_set_results = utf8' ) ;
	mysql_query( 'SET NAMES utf8' ) ;
	mysql_query( 'USE ' . DBDATANAME . '' ) ;
    
    require 'db.php';
    DB::init( DBHOST, DBUSER, DBPASSWORD, 3306, DBDATANAME, 'utf8' );