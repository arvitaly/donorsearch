    var socnetwork = {};
    
    socnetwork.auth = function( snname )
    {
        loading();
        sn = window[snname];
        sn.init( function(){
            sn.auth(function(data){
                loading();
                if ( data == false )
                {
                    alert('Не удалась авторизация в ' + snname);  
                    loaded();                 
                    return false;
                }
                
                $.ajax({
                    type:'POST',
                  url: window.location.href,
                  data: { 'event':'socnetwork.auth', sn:snname},
                  beforeSend: function ( xhr ) {
                    xhr.setRequestHeader('Request-Type', 'event');
                    return xhr;
                  },
                  success: function(data, textStatus, jqXHR)
                  {
                    try
                    {
                        eval(data); 
                        loaded();
                    }
                    catch(e)
                    {
                        console.log(e + data );
                    }                    
                  }
                });                
            });
        }); 
        return false;
    };
    
    var loading = function()
    {
        $('body').css('opacity',0.3);
    };
    var isPageLoading=false;
    var loaded = function()
    {
        if ( ! isPageLoading )
        $('body').css('opacity',1);
    };    
    
    var actions = {};
    
    actions.triggerOnServer = function(name,params,callback)
    {
        loading();
        var newparams = [];
        
        if ( params && ! params.length )
        {
            for( var i in params )
            {
                newparams.push( { 'name': i, 'value' : params[i] } );
            }
            params = newparams;
        }
        if ( ! params )
            params = [];
        params.push({'name' : 'event', 'value' : name});
        
        $.ajax({
            type:'POST',
          url: window.location.href,
          data: params,
          beforeSend: function ( xhr ) {
            xhr.setRequestHeader('Request-Type', 'event');
            return xhr;
          },
          success: function(data, textStatus, jqXHR)
          {
            try
            {
                
                if ( callback )
                {
                    loaded();
                    callback(data);
                    return;
                }
                else
                {
                    eval(data);
                    loaded();
                }
            }
            catch(e)
            {
                console.log(e + data );
            }                    
          }
        });
        return false;        
    };
    
    
    var Twitter = {};
    Twitter.isInit = false;
    Twitter.callback = false;    
    Twitter.init = function(callback)
    {
        if ( Twitter.isInit )
        {
            callback();
            return;
        }
        callback();
    } 
    Twitter.auth = function(callback)
    {
        Twitter.callback = callback;
        try
        {
            var win =window.open('http://donorsearch.ru/Twitter.php','Авторизация в Twitter','width=626, height=436');
            win.onunload = function()
            {
                loaded();
            }            
        }
        catch(e)
        {
            alert(e);
        }
        
    };       
    
    var OK = {};
    
    OK.isInit = false;
    OK.callback = false;
    OK.init = function(callback)
    {
        if ( OK.isInit )
        {
            callback();
            return;
        }
        callback();
    }
    OK.auth = function(callback)
    {
        OK.callback = callback;
        try
        {
            var win = window.open('http://donorsearch.ru/OK.php?auth','Авторизация в Одноклассниках','width=626, height=436');
            
            win.onunload = function()
            {
                loaded();
            }
            <?php /*ODKL.Oauth2($('[sn=OK]').get(0), "<?php echo OK::$APP_ID; ?>", 'VALUABLE ACCESS', 'http://donorsearch.ru/OK.php' ); */ ?>
    
        }
        catch(e)
        {
            alert(e);
        }
        
    };
    
    var Mailru = {};
    Mailru.isInit = false;
    Mailru.init = function( callback )
    {
        if ( Mailru.isInit )
        {
            callback();
            return;
        }
        
        mailru.loader.require('api', function()
        {
            mailru.connect.init(<?php echo Mailru::$APP_ID; ?>, '<?php echo Mailru::$APP_PRIVATE; ?>');
            Mailru.isInit = true;
            callback();
        });
    };
    Mailru.auth = function(callback)
    {
        mailru.events.listen(mailru.connect.events.login, function(session)
        {
            
            callback(true);
        });        
        mailru.connect.login(['widget','notifications']);
    };
    
    var Vkontakte = {};
    Vkontakte.isInit = false;
    
    Vkontakte.init = function( callback )
    {
        if ( Vkontakte.isInit )
        {
            callback();
            return;
        }
          window.vkAsyncInit = function() {
            VK.init({
              apiId: <?php echo Vkontakte::$APP_ID; ?>
            });
            callback();
          };
        
          setTimeout(function() {
            var el = document.createElement("script");
            el.type = "text/javascript";
            el.src = "http://vkontakte.ru/js/api/openapi.js";
            el.async = true;
            $('body').append('<div id="vk_api_transport"></div>');
            document.getElementById("vk_api_transport").appendChild(el);
          }, 0);       
    };
    Vkontakte.auth = function(callback)
    {
            VK.Auth.login(function(response){
            if ( ! response.session)
            {
                callback(false);
                return false;
            }
            });
            callback(true);
    };
    
    
    var Facebook = {};
    Facebook.isInit = false;
    Facebook.init = function( callback )
    {
        if ( Facebook.isInit )
        {
            callback();
            return;
        }        
          window.fbAsyncInit = function() {
            FB.init({
              appId      : <?php echo Facebook::$APP_ID; ?>, // App ID
              status     : true, // check login status
              cookie     : true, // enable cookies to allow the server to access the session
              xfbml      : true  // parse XFBML
            });
            Facebook.isInit = true;
            callback();
          };
          (function(d){
             var js, id = 'facebook-jssdk', ref = d.getElementsByTagName('script')[0];
             if (d.getElementById(id)) {return;}
             js = d.createElement('script'); js.id = id; js.async = true;
             js.src = "//connect.facebook.net/ru_RU/all.js";
             ref.parentNode.insertBefore(js, ref);
           }(document));
    };
    Facebook.auth = function(callback)
    {
        FB.login(function(response)
        {
            if ( ! response.authResponse)
            {
                callback(false);
                return false;
            }
            callback(true);
        },{'scope':'publish_stream,user_online_presence'});
    };
jQuery.fn.autocomplete = function( func ){
    var t = $(this);
    function ev( t )
    {
        if ( t.attr('lastvalue') != t.val() )
        {
            $('.autocomplete_list').hide();
            t.attr('lastvalue', t.val() );
            t.css('background-image','url(/loader.gif)');
            t.css('background-position','center center');
            
                var params = t.get(0).params ? t.get(0).params : {};
                params.method = t.attr('method');
                params.q = t.val();
            
                $.ajax({
                    type:'POST',
                  url: window.location.href,
                  dataType : 'json',
                  data: params ,
                  beforeSend: function ( xhr ) {
                    xhr.setRequestHeader('Request-Type', 'method');
                    return xhr;
                  },
                  success: function(data, textStatus, jqXHR)
                  {            
            
                        var html ='';
                        if ( data.length > 0 )
                        {
                            for( var i in data )
                            {
                                html+='<div value="' + data[i].value + '">' + data[i].title + '</div>';
                            }
                            $('div[for=' + t.attr('valuename') +']').html( html );
                            $('div[for=' + t.attr('valuename') +']>div').click(function(){
                                $('[name=' + t.attr('valuename') + ']').val( $(this).attr('value') );
                                t.val( $(this).html() );
                                $('div[for=' + t.attr('valuename') +']').css('display','none');
                                if (func)
                                func();
                            });
                            $('div[for=' + t.attr('valuename') +']').css('display','block');
                            
                        }
                        else
                        {
                            $('div[for=' + t.attr('valuename') +']').css('display','none');
                        }
                        t.css('background-image','none');
                
            }});
              
        }        
    }    
    
    t.attr('lastvalue', '~~~~~');
    
    t.css('background-repeat','no-repeat');
    t.css('background-position','right center');

    t.keyup(function(){
        
        t.trigger('realchange');
    });
    t.click(function(){
        t.trigger('realchange');   
    });
    t.change(function(){
        //t.trigger('realchange');
    });
    
    var timeout = 0;
    
    t.on('realchange',function(){
        if ( timeout )
        {
            clearTimeout(timeout);
        }
        timeout = setTimeout(function(){
           ev(t); 
        },300);
    });
    
    t.after('<input type="hidden" name="' + t.attr('valuename') + '" value="' + t.attr('valuestart') + '" />');
    t.after('<span style="display:' + t.css('display') + '" onclick="console.log($(\'[valuename=\'+ $(this).attr(\'for\') +\']\'));$(\'[valuename=\'+ $(this).attr(\'for\') +\']\').trigger(\'realchange\');" class="ard" for="' + t.attr('valuename') + '"></span>');
    t.after('<div class="autocomplete_list" for="' + t.attr('valuename') + '"></div>');
    
    
};
$(document).click(function(e){
   if ( $(e.target).parents('.autocomplete_list').length == 0 )
   {
        $('.autocomplete_list').css('display','none');
        $('[valuename]').attr('lastvalue','~~~~~~~~~');
   } 
});