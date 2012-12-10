<?php
    ob_start();

    chdir('..');
    require 'conf';
    
    require 'classes.php';
    require 'twitter.php';
try
{      
	$TWAuth = new TwitterAuth( Twitter::$APP_ID, Twitter::$APP_SECRET, 'http://donorsearch.ru/Twitter.php' ) ;
	$TWAuth->text_support( false ) ;

	$oauth_token = array_key_exists( 'oauth_token', $_GET ) ? $_GET['oauth_token'] : false ;
	$oauth_verifier = array_key_exists( 'oauth_verifier', $_GET ) ? $_GET['oauth_verifier'] : false ;


	if ( ! $oauth_token && ! $oauth_verifier )
	{
		$TWAuth->request_token() ;
		$TWAuth->authorize() ;
	}
	else
	{
		// access_token и user_id
		$TWAuth->access_token( $oauth_token, $oauth_verifier ) ;

		// JSON-версия
		$user_data = $TWAuth->user_data( 'json' ) ;
		$user_data = json_decode( $user_data ) ;

		//echo '<pre>User data<br>' ;
		//print_r( $user_data ) ;
        if ( ! $user_data || ! isset($user_data->id) )
        {
            throw new Exception('');
        }
        $_SESSION['Twitter'] = $user_data->id;
        echo '<meta http-equiv="content-type" content="text/html; charset=utf-8" /><p>Пожалуйста, подождите, идет авторизация</p>';
        echo '<script>window.opener.Twitter.callback(true);window.close();</script>';        
		//echo '</pre>' ;
    //echo '<meta http-equiv="content-type" content="text/html; charset=utf-8" /><p>Пожалуйста, подождите, идет авторизация</p>';
    //echo '<script>window.opener.OK.callback(true);window.close();</script>';
		// XML-версия
		// $user_data = $TWAuth->user_data('xml');
	}
}
catch(Exception $exception)
{
    var_dump($exception);
    echo '<script>window.opener.OK.callback(false);window.close();</script>';    
}    