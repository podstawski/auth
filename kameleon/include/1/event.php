<a name="online"></a>
<div class="row">
    <div class="col-md-9 ev-yt">
    </div>
    <div class="col-md-3 ev-ch">
        
    </div>
</div>

<style>
    .ev-yt {
        color: red;
        text-align: center;
        height: 300px;
    }
    .ev-yt a{

        cursor: pointer;
    }
</style>
<?php if($costxt):?>

<script>
    
    
function WebKameleonAuthReady() {
    
    WebKameleonAuth.GoogleLang('<?php echo $lang;?>',function(){
        WebKameleonAuth.YoutubeStartEvent('.<?php echo $costxt?>','edi-<?php echo $sid?>');
    });
    
    
    
    
    return;
    $('.ev-yt').addClass('iframe-container').html('');
    WebKameleonAuth.YoutubeStartEvent('<?php echo $costxt?>',function(d){
        $('.ev-yt').removeClass('iframe-container');
        //console.log(d);
        if (typeof(d.yt)=='undefined') {
            if (typeof(d.error)!='undefined') {
                switch (d.error.number) {
                    case 7:
                        $('.ev-yt').html('<a class="pay btn">Material platny, prosimy o oplacenie.</a>');
                        break;
                    case 8:
                        var da=new Date(d.ctx);
                        var txt='Zaczynamy '+WebKameleonAuth.YoutubeDate('DD-MM-YYYY HH:mm',d.ctx);
                    
                        $('.ev-yt').html('<a class="refresh btn">'+txt+'</a>');
                        break;
                    case 9:
                        $('.ev-yt').html('<a class="signin btn">Prosimy się zalogować.</a>');
                        break;
                }
            }
            
            $('.ev-yt .signin').click(function(){
                WebKameleonAuth.GoogleAuth(WebKameleonAuthReady);
            });
            
            $('.ev-yt .refresh').click(WebKameleonAuthReady);
            
            return;
        }
        $('.ev-yt').html(d.yt);
        $('.ev-ch').html(d.chat.replace('ORIGIN',location.host));
        var w=$('.ev-yt').width();
        var h=Math.round((w*9)/16);
        $('.ev-yt iframe').height(h);
        $('.ev-ch iframe').height(h-100);
    
    })
}

</script>

<?php endif; ?>