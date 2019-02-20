<?php


class googleController extends Controller {
    
    private static $scopes = [
        'youtube'=>'https://www.googleapis.com/auth/youtube',
        'drive'=>Google_Service_Drive::DRIVE
    ];

    public function get() {
        $scopes="openid profile email";
        $prompt = $this->_getParam('prompt')?:'none';
        $config=Bootstrap::$main->getConfig();
        
        
        $uri = $config['protocol'].'://' . $_SERVER['HTTP_HOST'] . Bootstrap::$main->getRoot() . 'google';
        $realm = $config['protocol'].'://' . $_SERVER['HTTP_HOST'] . Bootstrap::$main->getRoot() . 'google';
        
        if ($this->_getParam('redirect')) Bootstrap::$main->session('auth_redirect',$this->_getParam('redirect'));
        elseif (!Bootstrap::$main->session('auth_redirect')) mydie('redirect parameter missing','error');
	
        if (isset($_GET['state']) && $_GET['state']==Bootstrap::$main->session('oauth2_state'))
        {
            if (isset($_GET['code']))
            {
                $data = array(
                    'code' => $_GET['code'],
                    'client_id' => $config['oauth2.client_id'],
                    'client_secret' => $config['oauth2.client_secret'],
                    'redirect_uri' => $uri,
                    'grant_type' => 'authorization_code'
                );
		
                $response=$this->req("https://accounts.google.com/o/oauth2/token",$data);
		
                $token=json_decode($response,true);

                if (isset($token['access_token']))
                {
                      
                    $auth = json_decode(file_get_contents('https://www.googleapis.com/oauth2/v1/userinfo?access_token='.$token['access_token']),true);
                    
		    
                    if (isset($auth['given_name'])) $auth['first_name'] = $auth['given_name'];
                    if (isset($auth['family_name'])) $auth['last_name'] = $auth['family_name'];
                    
		    
                    if (isset($auth['id']) && isset($auth['email']))
                    {
                        $email=$this->standarize_email($auth['email'],false);
			

            			$auth['email']=$email;
                        Bootstrap::$main->session('auth', $auth);

						$user=new userModel($auth['id']);
						$user->data($auth);
			
                        $this->redirect(Bootstrap::$main->session('auth_redirect'));
                        
                    }
                    else
                    {
                        if (isset($auth['error']))
                        {
                            Bootstrap::$main->session('error', $auth['error']['message']);
                        }
                        $this->redirect(Bootstrap::$main->session('auth_redirect'));
                    }
                    
                }               
                else
                {
                    $this->redirect(Bootstrap::$main->session('auth_redirect'));
                }
                
            }
            elseif (isset($_GET['error']) && $_GET['error']=='immediate_failed') {
		
                $this->redirect($uri.'?prompt=select_account');
            }
            else
            {
                $this->redirect(Bootstrap::$main->session('auth_redirect'));
            }
        }
        elseif (isset($_GET['state'])) {
            $this->redirect($uri);
        }
        else {        
        
            $state=md5(rand(90000,1000000).time());
            Bootstrap::$main->session('oauth2_state',$state);
	    
            $url='https://accounts.google.com/o/oauth2/auth?client_id='.urlencode($config['oauth2.client_id']);
            $url.='&response_type=code';
            $url.='&scope='.urlencode($scopes);
            $url.='&redirect_uri='.urlencode($uri);
            $url.='&openid.realm='.urlencode($realm);
            $url.='&state='.$state;
            $url.='&prompt='.$prompt;
            //$url.='&access_type=offline';
            
	    //mydie("<a href='$url'>$url</a>");
            $this->redirect($url);
        }
        
        return [];
    }
    
    public function get_auth () {
        return Bootstrap::$main->session('auth');
    }

    public function get_logout() {
        return Bootstrap::$main->logout();
    }
    
    protected function standarize_email($email,$error=true)
    {
        $email=mb_convert_case($email,MB_CASE_LOWER);
        $email=str_replace(' ','',$email);
        if ($error && !preg_match('/^[^@]+@.+\..+$/',$email)) {
            return $this->error(4);
        }	
	
        return $email;
    }
    
    protected function require_user() {
        $auth=Bootstrap::$main->session('auth');
        if (!isset($auth['id']) || !$auth['id']) $this->error(9);
        return $auth;
    }
    
    public function get_scopes() {
        $auth=$this->require_user();
 
        $user=new userModel($auth['id']);
        return ['scopes'=>$user->scopes()];
    }
    

	
    public function get_scope() {
        $auth=$this->require_user();
		
		if (isset($_GET['redirect'])) Bootstrap::$main->session('scope_redirect',$_GET['redirect']);
        
        if ($this->id) {
            Bootstrap::$main->session('scope',$this->id);
            Header('Location:'.str_replace('/'.$this->id,'',$_SERVER['REQUEST_URI']));
            die();
        }
        
        if (!Bootstrap::$main->session('scope')) return $this->error(3);
        
        $user=new userModel($auth['id']);
        
        $scopes=[];
        foreach (explode(',',Bootstrap::$main->session('scope')) AS $scope) {
            if (isset($this::$scopes[$scope])) $scopes=array_unique(array_merge($scopes,explode(',',$this::$scopes[$scope])));
        }
        $scopes=implode(',',$scopes);
        
        $client = new Google_Client();
        
        $client->setAuthConfig(__DIR__.'/../configs/client_credentials.json');
        $client->addScope($scopes);
        $client->setAccessType("offline");
        $client->setIncludeGrantedScopes(true); 
        $redirect_uri = 'https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
        if ($pos=strpos($redirect_uri,'?')) $redirect_uri=substr($redirect_uri,0,$pos);
        $client->setRedirectUri($redirect_uri);
        
        $client->setApprovalPrompt('force');

        
        if (isset($_GET['code'])) {
            $client->authenticate($_GET['code']);
            $token=$client->getAccessToken();
            $user->storeToken($token,Bootstrap::$main->session('scope'));
			$redir=Bootstrap::$main->session('scope_redirect');
			if ($redir) Header('Location:'.$redir);
        } else {

            $authUrl = $client->createAuthUrl();
            Header('Location: '.filter_var($authUrl,FILTER_SANITIZE_URL));
            die();
        }
        
        
      
        return true;
    }
	
	public function post_init() {
		foreach ($this->data AS $k=>$v) {
			switch ($k) {
				case 'lang':
					Error::lang($v);
					break;
				case 'referer':
					Bootstrap::$main->session('referer',$v);
					break;
			}
		}
		
		return true;
	}

}
