<?php
    if ( ! ( isset($_GET['id']) && $_GET['id'] > 0 ) )
    {
        exit;
    }
    ob_start();

    chdir('..');
    require 'conf';
    
    require 'classes.php';
        
        
    $data = json_decode( file_get_contents('https://api.twitter.com/1/users/show.json?id=' . $_GET['id']));
    Page::go('http://twitter.com/' . $data->screen_name);