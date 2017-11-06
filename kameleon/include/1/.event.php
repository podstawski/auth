<?php
	if (!$this->webtd['staticinclude'] ) {
		$webtd=new webtdModel($sid);
		$webtd->staticinclude=1;
		$webtd->save();
	}
	
	
	if (isset($_POST['_sid']) && $_POST['_sid']==$sid && isset($_POST['event'])) {
		$webtd=new webtdModel($sid);
		$webtd->costxt=$_POST['event'];
		$webtd->save();
	}
	
?>

<style>
	#eventSave input {
		width: 50%
	}
	#eventSave textarea {
		width: 50%;
		height: 300px;
	}
</style>

<form method="POST" id="eventSave">
	<input type="hidden" name="_sid" value="<?php echo $sid?>"/><br/>
	<input type="text" placeholder="event id" name="event" value="<?php echo $costxt?>"/><br/>
	<input type="text" placeholder="hangout id" name="hangout" value=""/><br/>
	<textarea name="users" placeholder="Dodaj uytkownikow"></textarea>
	<br/>
	<input type="button" value="go!" class="go"/>

</form>

<script>
	$('#eventSave input.go').click(function(){
		WebKameleonAuth.YoutubeSaveEvent($('#eventSave input[name="event"]').val(),{
			hangout:$('#eventSave input[name="hangout"]').val(),
			users:$('#eventSave textarea[name="users"]').val()
		},function(d){
			if (typeof(d.data)!='undefined' && typeof(d.data.hangout)!='undefined') {
				$('#eventSave').submit();
			}
		});
		return false;
	});
	<?php if($costxt):?>
	function WebKameleonAuthReady() {
		WebKameleonAuth.YoutubeGetEvent('<?php echo $costxt?>',function(d){
			
			if (typeof(d.data)!='undefined' && typeof(d.data.hangout)!='undefined') {
                $('#eventSave input[name="hangout"]').val(d.data.hangout);
				
            }
			
			if (typeof(d.data)!='undefined' && typeof(d.data.users)!='undefined') {
				$('#eventSave textarea[name="users"]').val(d.data.users.join("\n"));
			}
			
		});
	}
	<?php endif;?>
</script>