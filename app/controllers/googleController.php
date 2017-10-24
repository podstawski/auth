<?php


class googleController extends Controller {

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
			

            			//Bootstrap::$main->session('user',$data);
                        Bootstrap::$main->session('auth', $auth);
			
			
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
        
        return ['aaa'=>'kota'];
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

}