<?php
    $user = User::get();
    if ( ! $user )
    {
        Page::go('/auth');
    }
    $result = mysql_query('delete from accounts where id = ' . $user->id );
    if ( $result === false )
    {
        throw new Exception('Error with remove user');
    }
    Page::go('/');