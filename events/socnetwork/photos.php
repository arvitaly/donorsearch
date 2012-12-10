<?php
    $user = User::get();
    if ( ! $user )
    {
        Page::go('/auth');
    }
    ob_start();
    $_SESSION['photos'] = array();
    
    $soc_ava = array();
    $soc_may = array();
    
    foreach( $socnetworks as $name => $s )
    {
        $n = 'sn_' . $name;
        if ( $user->$n )
        {
            $soc_ava[$name] = $s;
        }
        else
        {
            $soc_may[$name] = $s;
        }
    }    
?>
<style>
    .hh
    {
        border:2px white solid;
    }
    .hh:hover
    {
        
        border:2px #CCC solid;
    }
</style>
<?php
echo '<table border="0" cellpadding="5"><tr>';
    foreach( $soc_ava as $name => $s )
    {
        try
        {
            $n = 'sn_' . $name;
            $info = $name::getInfo( $user->$n );
            
            if ( $info && $info->photo )
            {
                $_SESSION['photos'][$name] = $info->photo;
                
                echo '<td style="vertical-align:top;text-align:center;"><a href="#" onclick="getnewphoto(\'' . $info->photo . '\',\'' . $name . '\');return false;">' . $name::$TITLE . '<br /><br /><img class="hh img-rounded" src="' . $info->photo . ( strpos( $info->photo, '?' ) !== false  ? '&' : '?') . rand(1,534534) . '" /></a></td>';
            }    
        }
        catch(Exception404 $exception)
        {
            
        }
        catch(Exception $exception)
        {
            
        }
    }
echo '</tr></table>';
    if ( count($_SESSION['photos']) == 0 )
    {
        echo '<p>У вас нет больше доступных фото в подключенных соц. сетях</p>';
    }
    $content = ob_get_contents();