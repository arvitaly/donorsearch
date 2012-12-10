<?php
    $user = User::get();
    if ( ! $user )
    {
        Page::go('/auth');
    }
    try
    {
        if ( $_POST['phone'] && preg_match( '~^\+[0-9]{11,13}$~Usi', $_POST['phone'] ) == 0 )
        {
            throw new ExceptionValid('Неправильно заполнен телефон');
        }  
        
        if ( $_POST['opportunity'] && ! ( strtotime( $_POST['opportunity'] ) > 0 ) )
        {
            throw new ExceptionValid('Неправильно заполнена дата');
        }        
        
        if ( $_POST['email'] && ! filter_var( $_POST['email'] , FILTER_VALIDATE_EMAIL) )
        {
            throw new ExceptionValid('Неправильно заполнен email');
        }              
        if ( preg_match( '~^.{1,50}$~Usi', $_POST['first_name'] ) == 0 )
        {
            throw new ExceptionValid('Неправильно заполнено имя');
        }
        if ( isset( $_POST['show_phone'] ) )
        {
            $_POST['show_phone'] = 1;
        }
        else
        {
            $_POST['show_phone'] = 0;
        }
        if ( $_POST['comment'] && preg_match( '~^.{1,500}$~Usi', $_POST['comment'] ) == 0 )
        {
            throw new ExceptionValid('Неправильно заполнен комментарий');
        }        
        if ( preg_match( '~^.{1,50}$~Usi', $_POST['last_name'] ) == 0 )
        {
            throw new ExceptionValid('Неправильно заполнена фамилия');
        } 
        if ( ! mysql_fetch_one('select * from countries where id = "' . mysql_real_escape_string( $_POST['country'] ) . '"') )
        {
            throw new ExceptionValid('Неправильно заполнена страна');
        }
        if ( ! mysql_fetch_one('select * from cities where id = "' . mysql_real_escape_string( $_POST['city'] ) . '"') )
        {
            throw new ExceptionValid('Неправильно заполнен город');
        }        
        if ( (string)(int)$_POST['bloodtype'] !== $_POST['bloodtype'] )
        {
            throw new ExceptionValid('Неправильно заполнена группа крови');
        } 
        if ( $_POST['photo_sn'] && (! class_exists($_POST['photo_sn']) || ! isset($_SESSION['photos']) || ! isset($_SESSION['photos'][$_POST['photo_sn']]) ) )
        {
            throw new Exception('Произошла непредвиденная ошибка');
        }
        
        
        $_POST['phone'] = substr( $_POST['phone'], 1 );
        $result = mysql_query( 'update accounts set
            show_phone ="' . mysql_real_escape_string( $_POST['show_phone'] ) . '",
            email ="' . mysql_real_escape_string( $_POST['email'] ) . '",
            comment ="' . mysql_real_escape_string( strip_tags( $_POST['comment'] ) ) . '",
            opportunity ="' . mysql_real_escape_string( strtotime( $_POST['opportunity'] ) ) . '",
            phone ="' . mysql_real_escape_string( $_POST['phone'] ) . '",
            first_name ="' . mysql_real_escape_string( $_POST['first_name'] ) . '",
            last_name ="' . mysql_real_escape_string( $_POST['last_name'] ) . '",
            country ="' . mysql_real_escape_string( $_POST['country'] ) . '",
            city ="' . mysql_real_escape_string( $_POST['city'] ) . '",
            bloodtype ="' . mysql_real_escape_string( $_POST['bloodtype'] ) . '"
            ' . 
            ( $_POST['photo_sn'] ? ( ', photo="' . mysql_real_escape_string( $_SESSION['photos'][$_POST['photo_sn']] ) . '" ' ) : '' )
            . '
            where id = ' . $user->id . '
        ' );
        if ( $result === false )
        {
            throw new ExceptionValid('Сохранение не удалась, пожалуйста, проверьте поля или попробуйте снова');
        }
        
        setcookie('vhr5uh56u', md5( $user->id .'4g6' ), time() + 86400*10*365 );
        
        Page::go('/donor');
        exit;
    }
    catch(ExceptionValid $exception)
    {
        echo 'alert("' . $exception->getMessage() . '");$("[type=submit]").removeAttr("disabled");';exit;
    }
    catch( Exception $exception )
    {
        
        echo 'alert("Произошла непредвиденная ошибка");';
        Page::go('/donor');
    }
    exit;  