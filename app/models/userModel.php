<?php


const USERS_CACHE = __DIR__.'/../../users';

class userModel {
    protected $id,$dir,$token='token';
    
    public function __construct($id) {
        $this->dir=USERS_CACHE.'/'.$id;
        if (!file_exists($this->dir)) mkdir($this->dir,0700,true);
        if (Bootstrap::$main->session('referer')) {
            $this->token=preg_replace('~[\./#]~','_',Bootstrap::$main->session('referer'));
        }
    }
    
    public function scopes() {
        if (!file_exists($this->dir.'/scopes.json')) return [];
        return json_decode(file_get_contents($this->dir.'/scopes.json'),true);
    }
    
    public function storeToken($token,$scope) {
        $scopes=array_unique(array_merge($this->scopes(),explode(',',$scope)));
        file_put_contents($this->dir.'/scopes.json',json_encode($scopes));
        file_put_contents($this->dir.'/'.$this->token.'.json',json_encode($token));
    }
    
    public function token() {
        return json_decode(file_get_contents($this->dir.'/'.$this->token.'.json'),true);
    }
    
    public function data($d=null) {
        if ($d!=null) file_put_contents($this->dir.'/data.json',json_encode($d));
        return json_decode(file_get_contents($this->dir.'/data.json'),true);
    }

}
