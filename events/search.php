<?php
                $sql = ' from accounts a inner join countries c on c.id=a.country inner join cities ct on ct.id=city where opportunity <=' . time() . ' and bloodtype <> 0 ';
                
                if ( isset($_POST['country']) && $_POST['country'] )
                {
                    $sql .= ' and a.country ="' . mysql_real_escape_string($_POST['country']) . '"';
                }
                if ( isset($_POST['city']) && $_POST['city'] )
                {
                    $sql .= ' and a.city ="' . mysql_real_escape_string($_POST['city']) . '"';
                }

                if ( isset($_POST['bloodtype']) && $_POST['bloodtype'] && $_POST['bloodtype'] <> 9 )
                {
                    $sql .= ' and bloodtype ="' . mysql_real_escape_string($_POST['bloodtype']) . '"';
                }
                
                $sql .='  order by ' . ( $_POST['bloodtype'] ? ' if(bloodtype=9,999,000) desc, ' : '' ) . ' timeOnline desc ';
                
                if ( $_POST['limit'] == 0 )
                {
                    $count = mysql_fetch_one( 'select count(*) as count ' . $sql)->count;    
                }
                
                
                $list = mysql_fetch_list( 'select a.*, ct.name as city_name, c.name as country_name ' . $sql . ' limit ' . $_POST['limit'] . ',10 ' );
                if ( ! $list  )
                {
                    echo '';//$("#result").html("<p>Извините, по Вашему запросу ничего не найдено</p>");';
                    exit;
                }
                else
                {
                    ob_start();
                    foreach( $list as $user )
                    {
                        if ( $user->timeOnline > time() - 300 )
                        {
                            $user->isOnline = true;
                        }
                        else
                        {
                            $user->isOnline = false;
                        }
                        $user->bloodname = $bloodtypes[$user->bloodtype];
?>
<?php
if ( $_POST['limit'] == 0 )
{
    echo '<script>$("#result_count").html("' . $count . '");</script>';
}
?>
      <div class="row-fluid thumbnail" style="margin-top:10px;">
        <div class="span3" style="width:100px;height:100px;text-align: center;padding-top:13px;"><img src="<?php echo $user->photo; ?>" class="img-rounded"></div>
        <div class="span3"><h5 style="mar5gin-top:0px;"><?php echo $user->first_name; ?><br><?php echo $user->last_name; ?></h5><span style="font-size:<?php echo ( $user->bloodtype == 9 ? '1' : '2' ); ?>em;"><?php echo $user->bloodname; ?></span></div>
        <div class="span6">
          <div class="row-fluid" style="position:relative;top:5px;margin-left: 7px;">
            <div class="span4">
              <span style="font-size:1.1em;position:relative;top:4px;color:gray;">Расположение:</span>
            </div>   
            <div class="span8" style="margin-top: 5px;">
                <?php echo $user->country_name . ', ' . $user->city_name; ?>
            </div>
          </div>       
            <div class="span4">
              <span style="font-size:1.1em;position:relative;top:4px;color:gray;">Контакты:</span>
            </div>
            <div class="span8">
            <?php
                foreach( $socnetworks as $name => $s )
                {
                    $n = 'sn_' . $name;
                    if ( $user->$n )
                    {
                        echo '<a target="_blank" href="' . $name::getLinkById( $user->$n ) . '" class="ico"><img src="img/ico_' . $name . '.png" width="30" height="30" title="' . $name::TITLE . '"></a>&nbsp; ';
                    }
                }
            ?>
            </div>
            
            <?php
                if ( $user->show_phone )
                {
            ?>
            <div class="span4">
              <span style="font-size:1.1em;position:relative;top:4px;color:gray;">Телефон:</span>
            </div>
            <div class="span8"><?php echo $user->phone; ?></div>           
            <?php } ?>
            
            
            <br clear="all" />
            <p style="color: green;margin-top:10px;margin-left:3px;"><?php echo $user->comment; ?></p>
          </div> 
        </div>       
      </div>
<?php
                    }
                    $content = ob_get_contents();
                    ob_end_clean();
                    
                    //echo '$("#result").html("' . str_replace( array( "\n", "\r", '"' ), array( '\n', '\r', '\"' ), $content) . '");';
                    echo $content;
                }
                
                
?>