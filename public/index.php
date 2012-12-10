<?php
try
{
    ob_start();

    chdir('..');
    require 'conf';
    
    require 'classes.php';
    
    $headers = getallheaders();
    

    
    
    if ( isset( $headers['Request-Type'] ) && $headers['Request-Type'] == 'event' )
    {
        Request::setType( Request::TYPE_EVENT );
        require 'event.php';
    }
    elseif ( isset( $headers['Request-Type'] ) && $headers['Request-Type'] == 'method' )
    {
        Request::setType( Request::TYPE_METHOD );
        require 'method.php';        
    }
    else
    {
        Request::setType( Request::TYPE_PAGE );
        require 'page.php';
    }
}
catch(Exception $exception)
{
    jsconsole($exception, 1);
}