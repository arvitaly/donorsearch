<?php
    $method = isset($_POST['method']) && $_POST['method'] ? $_POST['method'] : false;
    
    if ( ! $method )
    {
        throw new Exception('Not method');
    }
    
    list( $class, $method ) = explode('.', $method );
    if ( ! class_exists($class) )
    {
        throw new Exception('Not found event');
    }

    unset($_POST['method']);
    
    $result = $class::$method( $_POST );
    
    echo json_encode($result);