<div class="row">
    <div class="col-md-9 ev-yt"></div>
    <div class="col-md-3 ev-ch"></div>
</div>

<?php if($costxt):?>

function WebKameleonAuthReady() {
    WebKameleonAuth.YoutubeStartEvent('<?php echo $costxt?>',function(d){
        concole.log(d);
    })
}


<?php endif; ?>