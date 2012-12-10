<?php
    
   try
    { 
        if ( ! isset( $_SESSION['auth'] ) )
        {
            Page::go("/auth");
        }
        if ( preg_match( '~^.{1,50}$~Usi', $_POST['first_name'] ) == 0 )
        {
            throw new ExceptionValid('Неправильно заполнено имя');
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
        if ( (string)(int)$_POST['bloodtype'] !== $_POST['bloodtype'] || $_POST['bloodtype'] < 1 )
        {
            throw new ExceptionValid('Неправильно заполнена группа крови');
        }
        if ( $_SESSION['reg']->photo !== $_POST['photo'] )
        {
            throw new Exception('Не совпадает фото');
        }
        
        $result = mysql_query('insert into accounts(sn_' . $_SESSION['auth']['sn'] . ',first_name, last_name,country, city, bloodtype,photo, time_reg) values("' . $_SESSION['auth']['user_id'] . '","' . mysql_real_escape_string( $_POST['first_name'] ) . '","' . mysql_real_escape_string( $_POST['last_name'] ) . '","' . mysql_real_escape_string( $_POST['country'] ) . '","' . mysql_real_escape_string( $_POST['city'] ) . '","' . mysql_real_escape_string( $_POST['bloodtype'] ) . '","' . mysql_real_escape_string($_POST['photo']) . '", ' . time() . ')');
        if ( $result === false || ! ($id = mysql_insert_id() ) )
        {
            throw new ExceptionValid('Регистрация не удалась, пожалуйста, проверьте поля или попробуйте снова');
        }
        
        setcookie('vhr5uh56u', md5( $id .'4g6' ), time() + 86400*10*365 );
        
        Page::go("/donor" );
        exit;            
    }
    catch(ExceptionValid $exception)
    {
        echo 'alert("' . $exception->getMessage() . '");$("[type=submit]").removeAttr("disabled");';exit;
    }
    catch( Exception $exception )
    {
        echo 'alert("Произошла непредвиденная ошибка"' . ');window.location.reload();$("[type=submit]").removeAttr("disabled");';
    }
    exit;