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
	<input type="text" placeholder="youtube id" title="youtube id" name="event" value="<?php echo $costxt?>"/><br/>
	<input type="text" placeholder="hangout id" title="hangout id" name="hangout" value=""/><br/>
	<textarea name="users" placeholder="Dodaj uytkownikow"></textarea>
	<br/>
	<input type="text" placeholder="cena ONLINE" title="cena ONLINE" name="price_online" value=""/><br/>
	<input type="text" placeholder="cena OFFLINE" title="cena OFFLINE" name="price_offline" value=""/><br/>
	<input type="button" value="go!" class="go"/>

</form>

<script>
	function objectifyForm() {
		var formArray = $('#eventSave').serializeArray();
		var returnArray = {};
		for (var i = 0; i < formArray.length; i++){
			if (formArray[i]['name'].substr(0,1)=='_') continue;
			returnArray[formArray[i]['name']] = formArray[i]['value'];
		}
		return returnArray;
	}
	
	$('#eventSave input.go').click(function(){

		WebKameleonAuth.YoutubeSaveEvent($('#eventSave input[name="event"]').val(),objectifyForm(),function(d){
			if (typeof(d.data)!='undefined' && typeof(d.data.hangout)!='undefined') {
				$('#eventSave').submit();
			}
		});
		return false;
	});
	<?php if($costxt):?>
	function WebKameleonAuthReady() {
		WebKameleonAuth.YoutubeGetEvent('<?php echo $costxt?>',function(d){
			
			if (typeof(d.data)!='undefined') for (var k in d.data) {
			
				if (typeof(d.data[k])=='object') {
					$('#eventSave textarea[name="'+k+'"]').val(d.data[k].join("\n"));
                    
                } else {
					$('#eventSave input[name="'+k+'"]').val(d.data[k]);
				}
			}
		
			
		});
	}
	<?php endif;?>
</script>