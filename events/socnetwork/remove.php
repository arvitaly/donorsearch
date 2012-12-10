<?php

    $user = User::get();
    if ( ! $user )
    {
        Page::go('/auth');
    }
    
    $sn = isset($_POST['sn']) && $_POST['sn'] && class_exists($_POST['sn']) ? $_POST['sn'] : false;
    
    
    if ( ! $sn )
    {
        throw new Exception ('Error socnetwork');
    }
    $sns = array_keys( $socnetworks);
    array_walk( $sns, function(&$i,$a){ $i = 'sn_' . $i; } );
    $sns = implode( ',', $sns );
    
    
    
    mysql_query('update accounts set sn_' . $sn . '="", timeOnline_' . $sn . '=0 where id=' . $user->id . ' and concat(' . $sns . ')<>sn_' . $sn . '' ) or die(mysql_error());
    if ( mysql_affected_rows() > 0 )
    {
        Page::go('/donor');
    }
    
    throw new Exception ('Last socnetwork');