<?php
    $scr = $this->mode==1 || !$this->mode;
?>
<?php if($scr):?>
<script>  
function WebKameleonAuthReady(u) {
    
<?php endif;?>

    WebKameleonAuth.GoogleInit({lang:'<?php echo $lang;?>',referer:'<?php echo $session['server']['nazwa'];?>'},function(){
        WebKameleonAuth.YoutubeStartEvent('.<?php echo $costxt?>','edi-<?php echo $sid?>');
    });
    
    
<?php if($scr):?>  
    
}

</script>

<?php endif; ?>