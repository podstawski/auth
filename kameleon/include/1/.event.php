<?php
	if (!$this->webtd['staticinclude'] ) {
		$webtd=new webtdModel($sid);
		$webtd->staticinclude=1;
		$webtd->save();
	}
	
	if (isset($_POST['_sid']) && $_POST['_sid']==$sid && isset($_POST['button'])) {
		$webtd=new webtdModel($sid);
		$webtd->costxt=$_POST['button'];
		$webtd->save();
	}
	
?>

<style>
	#eventSave {
		display: none;
		margin-top: 30px;
	}
	#eventSave input {
		width: 50%
	}
	#eventSave .error {
		border: solid 1px red;
	}
	#eventSave a.token {
		float: right;
		background-color: red;
	}
	#eventSave textarea {
		height: 50px;
	}
	#eventSave div textarea:focus {
		height: 300px;
	}
	
	#eventSave div {
	  position: relative;
	  margin: 0 0 30px 0;
	  font-family: impact;
	  font-size: 16px
	}
	#eventSave div input,#eventSave div textarea {
	  padding: 10px;
	  width: 50%;
	  transition: all 1s;
	  border: 2px solid #999;
	  font-size: 17px;
	  color: #666
	}
	#eventSave div label {
	  position: absolute;
	  left:0px;
	  top: 0;
	  line-height:15px;
	  transition: all 0.5s;
	  overflow: hidden;
	  color: #999;
	  white-space: nowrap;
	  z-index: 1;
	  opacity: 0;
	}
	#eventSave div input:focus + label,
	#eventSave div textarea:focus + label{
	  opacity: 1;
	  top: -18px;  
	}
	#eventSave div input:focus,
	#eventSave div textarea:focus{
	  outline: none;
	  border-color: rgba(82, 168, 236, 0.8);
	}	
	
	
	
</style>

<form method="POST" id="eventSave">
	<input type="hidden" name="_sid" value="<?php echo $sid?>"/>
	
	<div>
		<input type="text" placeholder="youtube id"  name="event" value=""/>
		<label for="event">Youtube id:</label>
	</div>
	<div>
		<input type="text" placeholder="hangout id" name="hangout" value=""/>
		<label for="hangout">Hangout id:</label>
	</div>
	<div>
		<textarea name="users" placeholder="Dodaj użytkowników, którzy mogą oglądać"></textarea>
		<label for="users">Dodaj użytkowników, którzy mogą oglądać:</label>
	</div>
	<div>
		<textarea name="speakers" placeholder="Dodaj użytkownikow, którzy występują"></textarea>
		<label for="speakers">Dodaj użytkowników, którzy występują:</label>
	</div>
	<div>
		<input type="text" placeholder="Cena w czasie zajęć" name="price_online" value=""/>
		<label for="price_online">Cena w czasie zajęć:</label>
	</div>
	<div>
		<input type="text" placeholder="Cena za dostęp ex post" name="price_offline" value=""/>
		<label for="price_offline">Cena za dostęp ex post:</label>
	</div>
	<div>
		<input type="text" placeholder="Klasa przycisku/linku" name="button" value=""/>
		<label for="button">Klasa przycisku/linku:</label>
	</div>
	<div>
		<input type="text" placeholder="Domeny odnośników"  name="referers" value="<?php echo str_replace('/','',preg_replace('~^http[s]*://~','',$session['server']['http_url']));?>"/>
		<label for="referers">Domeny odnośników:</label>
	</div>
	<div>
		<input type="text" placeholder="Identyfikator DOTPAY"  name="dotpay" value=""/>
		<label for="dotpay">Identyfikator Dotpay:</label>
	</div>
	<a class="btn token">Token</a>
	<a class="btn save">Zapisz</a>

</form>

<a class="authorlogin btn">Zaloguj!</a>

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
	
	$('#eventSave a.token').click(function(){
		WebKameleonAuth.GoogleToken('youtube',function(){
		});
	});
	
	$('#eventSave a.save').click(function(){

		var a=$(this);
		var t=a.text();
		a.text('...');
		$('#eventSave input').removeClass('error');
		WebKameleonAuth.YoutubeSaveEvent('edi-<?php echo $sid;?>',objectifyForm(),function(d){
			
			if (typeof(d.data)!='undefined' && typeof(d.data.event)!='undefined') {
				a.text(t);
				var starts=$('.'+d.data.button);
				
				if (d.data.button.length==0 || starts.length==0) {
                    a.text('Nie istnieją przyciski klasy '+d.data.button);
					$('#eventSave input[name="button"]').addClass('error');
                } else {
					$('#eventSave').submit();
				}
				
				
			}
			if (typeof(d.error)!='undefined') {
                a.text(d.error.info);
				setTimeout(function(){
					a.text(t)
				},5000);
            }
		});
		return false;
	});
	function WebKameleonAuthReady(u) {
		var ref=$('#eventSave input[name="referers"]');
		var rv=ref.val();
		if (rv.length>0) rv+=',';
		rv+=location.host;
		ref.val(rv);
		
		<?php include(__DIR__.'/event.php');?>
		
		
		if (u.id==null) return;
		$('.authorlogin').hide();
		$('#eventSave').fadeIn(500);
	
		WebKameleonAuth.YoutubeGetEvent('edi-<?php echo $sid?>',function(d){
			
			if (typeof(d.data)!='undefined') for (var k in d.data) {
			
				if (typeof(d.data[k])=='object') {
					$('#eventSave textarea[name="'+k+'"]').val(d.data[k].join("\n"));
                    
                } else {
					$('#eventSave input[name="'+k+'"]').val(d.data[k]);
				}
			} else {
				if (typeof(d.error)!='undefined') {
                    console.log(d.error.info);
                }
			}
		
		});		
		

	}
	
	$('.authorlogin').click(function(){
		WebKameleonAuth.GoogleAuth(WebKameleonAuthReady);
	});
	

</script>