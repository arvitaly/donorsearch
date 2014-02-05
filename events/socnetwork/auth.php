<?php
    $sn = isset($parameters['sn']) && $parameters['sn'] ? $parameters['sn'] : false;
    if ( ! $sn )
    {
        throw new Exception('Not sn');
    }
    
    if ( ! class_exists( $sn ) )
    {
        throw new Exception('Not sn');
    }
        $user_id = $sn::auth();
     
        $user=User::get();
        
        if ( $user )
        {
		  if ( isset($_POST['mode']) && $_POST['mode'] == 'delold' )
          {
            mysql_query('delete from accounts where sn_' . $_POST['sn'] . '="' . mysql_real_escape_string( $user_id ) . '"');
          }            
	      $t = mysql_fetch_one('select * from accounts where sn_' . $sn . '="' . mysql_real_escape_string( $user_id ) . '" limit 0,1' );
          
          if ( ! $t || $t->id == $user->id )
          {
			$result = mysql_query( 'update accounts set sn_' . $_POST['sn'] . '="' . mysql_real_escape_string( $user_id ) . '" where id=' . $user->id ) ;
            if ( $result === false )
            {
                throw new Exception404('Error with update');
            }            
            Page::go('/donor');
          }
            echo 'if (confirm("Этот контакт уже привязан к другой анкете, вы хотите удалить старую анкету и привязать контакт к новой?"))
            {
                actions.triggerOnServer("socnetwork.auth",[{ "name":"sn","value":"' . $parameters['sn'] . '"},{"name":"mode","value": "delold"}]);

            }
            else
            {
            }' ;
            exit ;
        }
        else
        {
            if ( $user = Users::getBySocnetwork( $sn, $user_id ) )
            {
    		  setcookie( 'vhrdfgdf5uh56dddu', md5( $user->id . '4g6' ), time() + 86400 * 10 * 365 ) ;
              Page::go('/donor');           
            }
            else
            {
                $_SESSION['auth'] = array( 'sn' => $sn, 'user_id' => $user_id ) ;            
                Page::go('/reg');
            }            
        }
    
