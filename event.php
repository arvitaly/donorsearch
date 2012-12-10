<?php

    $event = isset($_POST['event']) && $_POST['event'] ? $_POST['event'] : false;
    
    if ( ! $event )
    {
        throw new Exception('Not event');
    }
    
    $file = 'events/' . str_replace('.','/', $_POST['event']) . '.php';
    if ( ! file_exists($file) )
    {
        throw new Exception('Not found event');
    }
    
    unset($_POST['event']);
    
    $parameters = $_POST;

    include( $file );