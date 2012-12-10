<?php
    if ( ! ( isset($_GET['id']) && $_GET['id'] > 0 ) )
    {
        exit;
    }
    ob_start();

    chdir('..');
    require 'conf';
    
    require 'classes.php';
    $params = array
    (
        'app_id=' . Mailru::APP_ID,
        'method=users.getInfo',
        'secure=1',
        'uid=' .$_GET['id'],
        'uids=' . $_GET['id']
        
    );
    
    sort( $params );
    
    $sid = md5( implode( '', $params ) . Mailru::APP_SECRET);
    
    $data = file_get_contents( 'http://www.appsmail.ru/platform/api?' . implode('&', $params ) . '&sig=' . $sid );
    
    
    if ( $data )
    {
        $data = json_decode($data);
        if ( isset($data[0]) )
        {
            if( isset( $data[0]->link ) )
            {
                header('Location: ' . $data[0]->link);
                exit;
            }
        }
    }
    throw new Exception404('Error');