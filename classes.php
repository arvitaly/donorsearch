<?php
    session_start();
    class ExceptionValid extends Exception{}
	class User
	{
		private static $_user = null ;
		public static function get()
		{
			if ( self::$_user !== null )
			{
				return self::$_user ;
			}

			if ( isset( $_COOKIE['vhr5uh56u'] ) && ( $uidmd5 = $_COOKIE['vhr5uh56u'] ) )
			{
			 
				$user = DB::Query( 'select a.*, c.name as countryname, ct.name as cityname from dsdata.accounts a left join countries c on c.id = a.country left join cities ct on ct.id = a.city where md5( concat(a.id,"4g6") ) = "' . mysql_real_escape_string( $uidmd5 ) . '"' )->fetchOne();

				return $user ;
			}
			return false ;
		}
	}
    class Users
    {
        public static function count()
        {
            return mysql_fetch_one('select count(*) as count from accounts')->count;
        }
        public static function getBySocnetwork( $sn, $id )
        {
       	    $user = DB::Query( 'select * from accounts where sn_' . $sn . '="' . mysql_real_escape_string( $id ) . '"' )->fetchOne();
            return $user;
        }
    }
    

    
    interface Socnetwork
    {

        public static function auth();
        public static function getInfo( $id );
        public static function getLinkById( $id );
    }
    class SocnetworkException extends Exception{};
    class Twitter implements Socnetwork
    {
        public static $TITLE;
        public static $APP_SECRET;
        public static $APP_ID;
        public static $APP_PRIVATE;        
 
        public static function auth()
        {
            if ( ! isset( $_SESSION['Twitter'] ) )
            {
                throw new SocnetworkException( 'Twitter::Invalid auth error' ) ;
            }
            return $_SESSION['Twitter'];
        }
        public static function getInfo( $id )
        {

             $user_ = json_decode( file_get_contents('https://api.twitter.com/1/users/show.json?id=' . $_SESSION['auth']['user_id']));
             
             $user = new stdClass;
             $user->id = $id;
             $user->first_name = $user_->name;
             $user->last_name = '';
             $user->photo = $user_->profile_image_url;
             return $user;
        }
        public static function getLinkById( $id )
        {
            return 'https://twitter.com/intent/user?user_id=' . $id;
            //return 'http://donorsearch.ru/twitterlink.php?id=' . $id;
        }
    }
    class OK implements Socnetwork
    {
        public static $TITLE;
        public static $APP_SECRET;
        public static $APP_ID;
        public static $APP_PRIVATE;        
       
        public static function auth()
        {
			if ( ! isset( $_SESSION['OK'] ) ||  ! isset($_SESSION['OK']['uid']) )
			{
                throw new SocnetworkException( 'OK::Invalid auth error' ) ;
            }
            return $_SESSION['OK']['uid'];
        }
        public static function getInfo( $id )
        {
            $user = new stdClass;
            
            if ( ! isset( $_SESSION['OK'] ) )
            {
                throw new SocnetworkException( 'OK::Invalid auth error' ) ;
            }
            
            $user->first_name = $_SESSION['OK']['first_name'];
            $user->last_name = $_SESSION['OK']['last_name'];
            $user->photo = $_SESSION['OK']['pic_2'];
            return $user;
        }
        public static function getLinkById( $id )
        {
            return 'http://www.odnoklassniki.ru/profile/' . $id;
        }
    }
    class Mailru implements Socnetwork
    {
        public static $TITLE;
        public static $APP_SECRET;
        public static $APP_ID;
        public static $APP_PRIVATE;        

        public static function auth()
        {
			if ( ! isset( $_COOKIE['mrc'] ) )
			{
				throw new SocnetworkException( 'MR::Invalid auth error' ) ;
			}        
            $a = explode('&', urldecode($_COOKIE['mrc']));
            
            $sid = substr( array_pop( $a ),4);
            ksort($a);
            
            if ( $sid != md5( implode('', $a ) . self::$APP_SECRET ) )
            {
                throw new SocnetworkException( 'MR::Invalid auth error' ) ;
            }
            
            
            foreach( $a as $p )
            {
                if ( substr($p,0,4) == 'vid=' )
                {
                    $user_id = substr( $p, 4);
                    break;                    
                }
            }
            return $user_id;            
        }
        public static function getInfo( $id )
        {
            $params = array
            (
                'app_id=' . self::$APP_ID,
                'method=users.getInfo',
                'secure=1',
                'uid=' . $id,
                'uids=' . $id
                
            );
            
            sort( $params );
            
            $sid = md5( implode( '', $params ) . self::$APP_SECRET);
            
            $data = file_get_contents( 'http://www.appsmail.ru/platform/api?' . implode('&', $params ) . '&sig=' . $sid );
            
            if ( ! isset( $data[0] ) )
            {
                throw new Exception404('Not found user') ;
            }
            
            $data = json_decode( $data );
            $data=$data[0];
            
            
            
            $user = new stdClass;
            $user->photo = $data->pic;
            if ( isset($data->first_name) )
            {
                $user->first_name = $data->first_name;    
            }
            if ( isset($data->last_name) )
            {
                $user->last_name = $data->last_name;
            }
            if ( isset($data->location) )
            {
                if ( isset($data->location->country ) && $data->location->country->name )
                {
                    $user->country = $data->location->country->name;
                }
                if ( isset($data->location->city ) && $data->location->city->name )
                {
                    $user->city = $data->location->city->name;
                }                
            }
            return $user;        
        }
        public static function getLinkById( $id )
        {
            return '/mailrulink.php?id=' . $id;
        }  
    }
    class Vkontakte  implements Socnetwork
    {
        public static $TITLE;
        public static $APP_SECRET;
        public static $APP_ID;
        public static $APP_PRIVATE;        

        public static function auth()
        {
			if ( ! isset( $_COOKIE['vk_app_' . self::$APP_ID] ) )
			{
				throw new SocnetworkException( 'VK::Invalid auth error' ) ;
			}
			$member = authOpenAPIMember() ;

			if ( $member === false )
			{
				throw new SocnetworkException( 'VK::Invalid auth error' ) ;
			}
            $user_id = $member['id'];
            return $user_id;            
        }
        public static function getLinkById( $id )
        {
            return 'http://vk.com/id' . $id ;
        }
        public static function getInfo( $id )
        {
			$VK = new vkapi( self::$APP_ID, self::$APP_SECRET ) ;


			$screen_names_v = $id;

			try
			{
				$response = $VK->api( 'users.get', array( 'uids' => $screen_names_v, 'fields' => 'uid, first_name, last_name, nickname, screen_name, sex, bdate, city, country,photo_100' ) ) ;


				$user = (object)$response['response'][0] ;
                
				if ( ! $user )
				{
					throw new SocnetworkException() ;
				}
                
                
                /*$VK = new vkapi( self::$APP_ID, self::$APP_SECRET ) ;
                $response = $VK->api( 'getCountries', array('cids'=> $user->country ) );

                var_dump($response);exit;
                
                $user->country = $response['response'][0]['name'];
                $response = $VK->api( 'getCities', array('cids'=> $user->city ) );
                
                $user->city = $response['response'][0]['name'];     */
                $user->photo = $user->photo_100; 
                unset($user->city);
                unset($user->country);
                return $user;                
                
			}
			catch ( exception $exception )
			{
				throw new SocnetworkException($exception->getMessage()) ;
			}              
        }
    }
	class Facebook implements Socnetwork
	{
        public static $TITLE;
        public static $APP_SECRET;
        public static $APP_ID;
        public static $APP_PRIVATE;	   

        public static function getLinkById( $id )
        {
            return 'http://facebook.com/' . $id ;
        }        
		public static function auth()
		{
			if ( ! isset( $_COOKIE['fbsr_' . self::$APP_ID] ) )
			{
				throw new SocnetworkException( 'FB::Invalid auth error' ) ;
			}
			$data = parse_signed_request( $_COOKIE['fbsr_' . self::$APP_ID], self::$APP_SECRET ) ;

			if ( ! $data )
			{
				throw new Exception( 'FB::Invalid auth error' ) ;
			}
			$user_id = $data['user_id'] ;
            return $user_id;
		}
        public static function getInfo( $id )
        {
            $user = file_get_contents('https://graph.facebook.com/' . $id );
			if ( ! $user )
			{
				throw new SocnetworkException('Not found user') ;
			}
            $user = json_decode( $user );
            
			if ( ! $user )
			{
				throw new SocnetworkException('Not found user') ;
			}
            return $user;         
        }
	}
    
    class Cities
    {
        public static function getByCountryAndName( $name, $country )
        {
            return mysql_fetch_one( 'select * from cities where name="' . $name . '" and country="' . $country . '"' );;    
        }   
        public static function searchByNameAndCountry( $name, $country = null )
        {
            $list = mysql_fetch_list('select id as value,name as title from cities where country="' . mysql_real_escape_string($name['country']) . '" and name like "%' . mysql_real_escape_string($name['q']) . '%" order by sort desc, name limit 0,10');

            
            if ( ! $list  )
            {
                $list = array();
            }   
            return $list;
        }              
    }
    class Countries
    {
        public static function searchByName( $name )
        {
            if ( is_array( $name ) )
            {
                $name = $name['q'];
            }
            $list = mysql_fetch_list('select id as value,name as title from dsdata.countries where name like "%' . mysql_real_escape_string($name) . '%" order by sort desc, name limit 0,10');

            
            if ( ! $list  )
            {
                $list = array();
            }   
            return $list;
        }        
        public static function getByName( $name )
        {
            return mysql_fetch_one( 'select * from countries where name="' . $name . '"' );
        }
    }
    
	function authOpenAPIMember()
	{
		$session = array() ;
		$member = false ;
		$valid_keys = array(
			'expire',
			'mid',
			'secret',
			'sid',
			'sig' ) ;
		$app_cookie = $_COOKIE['vk_app_' . Vkontakte::$APP_ID ] ;
		if ( $app_cookie )
		{
			$session_data = explode( '&', $app_cookie, 10 ) ;
			foreach ( $session_data as $pair )
			{
				list( $key, $value ) = explode( '=', $pair, 2 ) ;
				if ( empty( $key ) || empty( $value ) || ! in_array( $key, $valid_keys ) )
				{
					continue ;
				}
				$session[$key] = $value ;
			}
			foreach ( $valid_keys as $key )
			{
				if ( ! isset( $session[$key] ) ) return $member ;
			}
			ksort( $session ) ;

			$sign = '' ;
			foreach ( $session as $key => $value )
			{
				if ( $key != 'sig' )
				{
					$sign .= ( $key . '=' . $value ) ;
				}
			}
			$sign .=  Vkontakte::$APP_SECRET ;
			$sign = md5( $sign ) ;
			if ( $session['sig'] == $sign && $session['expire'] > time() )
			{
				$member = array(
					'id' => intval( $session['mid'] ),
					'secret' => $session['secret'],
					'sid' => $session['sid'] ) ;
			}
		}
		return $member ;
	}
	function parse_signed_request( $signed_request, $secret )
	{
		list( $encoded_sig, $payload ) = explode( '.', $signed_request, 2 ) ;

		// decode the data
		$sig = base64_url_decode( $encoded_sig ) ;
		$data = json_decode( base64_url_decode( $payload ), true ) ;

		// Adding the verification of the signed_request below
		$expected_sig = hash_hmac( 'sha256', $payload, $secret, $raw = true ) ;
		if ( $sig !== $expected_sig )
		{
			return false ;
		}

		return $data ;
	}
	function base64_url_decode( $input )
	{
		return base64_decode( strtr( $input, '-_', '+/' ) ) ;
	}

    class Page
    {
        public static function go( $url )
        {
            if ( Request::type() == Request::TYPE_EVENT )
            {
                echo 'isPageLoading=true;window.location.href="' . $url . '";';    
            }
            else
            {
                header('Location: ' . $url );
            }
            exit;
        }
    }
    class Request
    {
        const TYPE_PAGE = 0;
        const TYPE_EVENT = 1;
        const TYPE_METHOD = 2;
        private static $_type = 0;
        public static function type()
        {
            return self::$_type;
        }
        public static function setType( $type )
        {
            self::$_type = $type;
        }
    }    
        $bloodtypes = array('',
        'O(I) Rh+',
        'O(I) Rh-',
        'A(II) Rh+',
        'A(II) Rh-',
        'B(III) Rh+',
        'B(III) Rh-',
        'AB(IV) Rh+',
        'AB(IV) Rh-',
        'Группа крови неизвестна (донор на замену)'
        ); 
         
        $socnetworks = array('Vkontakte'=>array('title'=>'ВКонтакте'),'Facebook'=>array('title'=>'Facebook'),'Mailru'=>array('title'=>'Mail.ru'),'OK'=>array('title'=>'Одноклассники'),'Twitter'=>array('title'=>'Twitter'));//,);
	class vkapi
	{
		var $api_secret ;
		var $app_id ;
		var $api_url ;

		function vkapi( $app_id, $api_secret, $api_url = 'api.vk.com/api.php' )
		{
			$this->app_id = $app_id ;
			$this->api_secret = $api_secret ;
			if ( ! strstr( $api_url, 'http://' ) ) $api_url = 'http://' . $api_url ;
			$this->api_url = $api_url ;
		}

		function api( $method, $params = false )
		{
			if ( ! $params ) $params = array() ;
			$params['api_id'] = $this->app_id ;
			$params['v'] = '3.0' ;
			$params['method'] = $method ;
			$params['timestamp'] = time() ;
			$params['format'] = 'json' ;
			$params['random'] = rand( 0, 10000 ) ;
			ksort( $params ) ;
			$sig = '' ;
			foreach ( $params as $k => $v )
			{
				$sig .= $k . '=' . $v ;
			}
			$sig .= $this->api_secret ;
			$params['sig'] = md5( $sig ) ;
			$query = $this->api_url . '?' . $this->params( $params ) ;
			$res = file_get_contents( $query ) ;
			return json_decode( $res, true ) ;
		}

		function params( $params )
		{
			$pice = array() ;
			foreach ( $params as $k => $v )
			{
				$pice[] = $k . '=' . urlencode( $v ) ;
			}
			return implode( '&', $pice ) ;
		}
	}    
    require '../socnetwork_config';           