<?php
    class DBException extends Exception{}
	class DB
	{
		private static $_handle ;
		public static function init( $host, $login, $password, $port, $dbname, $charset )
		{
			self::$_handle = new mysqli( $host, $login, $password, $dbname, $port ) ;
			if ( self::$_handle->connect_error )
			{
				throw new DBException('Invalid connect');
			}
            if ( self::$_handle->set_charset( $charset ) === false )
            {
                throw new DBException('Invalid set charset');
            }
		}
		public static function query( $sql )
		{
			$result = self::$_handle->query( $sql );
            if ( $result === false )
            {
                throw new DBException('Invalid query - ' . $sql . ' error - ' . self::$_handle->error );
            }
            return new DBQuery( $result );
        }
	}
    class DBQuery
    {
        private $_handle;
        public function __construct( $result )
        {
            $this->_handle = $result;
        }
        public function fetchArray()
        {
            $rows = array();
            while ( $row = $this->_handle->fetch_array(MYSQLI_ASSOC) )
            {
                $rows[]=(object)$row;
            }
            if ( ! $rows )
            {
                return null;
            }
            return $rows;
        }
        public function fetchOne()
        {
            $rows = $this->_handle->fetch_array(MYSQLI_ASSOC);
            if ( ! $rows )
            {
                return null;
            }            
            return (object)$rows;
        }        
    }