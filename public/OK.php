<?php
    ob_start();

    chdir('..');
    require 'conf';
    
    require 'classes.php';

try
{
    if ( isset( $_GET['auth'] ) )
    {
        Page::go('http://www.odnoklassniki.ru/oauth/authorize?client_id=' . OK::$APP_ID . '&scope=VALUABLE+ACCESS&response_type=code&redirect_uri=http://donorsearch.ru/OK.php');
        exit;
    }
    if ( ! isset( $_GET['code'] ) )
	{
        throw new Exception('');
    }
	$curl = curl_init( 'http://api.odnoklassniki.ru/oauth/token.do' ) ;
	curl_setopt( $curl, CURLOPT_POST, 1 ) ;
	curl_setopt( $curl, CURLOPT_POSTFIELDS, 'code=' . $_GET['code'] . '&redirect_uri=http://donorsearch.ru/OK.php&grant_type=authorization_code&client_id=' . OK::$APP_ID . '&client_secret=' . OK::$APP_SECRET ) ;
	curl_setopt( $curl, CURLOPT_RETURNTRANSFER, 1 ) ;
	$s = curl_exec( $curl ) ;
    if ( ! $s || curl_error($curl) )
    {
        throw new Exception('');
    }
	curl_close( $curl ) ;
    

    
	$auth = json_decode( $s, true ) ;

    
    if ( ! $auth || ! isset($auth['access_token']) )
    {
        throw new Exception('');
    }
    
	$curl = curl_init( 'http://api.odnoklassniki.ru/fb.do?access_token=' . $auth['access_token'] . '&application_key=' . OK::$APP_PRIVATE . '&method=users.getCurrentUser&sig=' . md5( 'application_key=' . OK::$APP_PRIVATE . 'method=users.getCurrentUser' . md5( $auth['access_token'] . OK::$APP_SECRET ) ) ) ;
	curl_setopt( $curl, CURLOPT_RETURNTRANSFER, 1 ) ;
	$s = curl_exec( $curl ) ;
    if ( ! $s || curl_error($curl) )
    {
        throw new Exception('');
    }    
	curl_close( $curl ) ;
	$user = json_decode( $s, true ) ;
    if ( ! $user )
    {
        throw new Exception('');
    }
    if ( ! ( isset($user['uid']) && $user['uid'] ) )
    {
        throw new Exception('');
    }

    $_SESSION['OK'] = $user;
    echo '<meta http-equiv="content-type" content="text/html; charset=utf-8" /><p>Пожалуйста, подождите, идет авторизация</p>';
    echo '<script>window.opener.OK.callback(true);window.opener.OK.callback = false;window.close();</script>';
}
catch(Exception $exception)
{
    //var_dump($exception);
    echo '<script>window.opener.OK.callback(false);window.opener.OK.callback = false;window.close();</script>';    
}