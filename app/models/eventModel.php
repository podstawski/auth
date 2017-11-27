<?php


const EVENTS_CACHE = __DIR__.'/../../events';

class eventModel {
    protected $id,$dir;
    
    public function __construct($id) {
        $this->dir=EVENTS_CACHE.'/'.$id;
        if (!file_exists($this->dir)) mkdir($this->dir,0700,true); 
    }
    
    public function get() {
        if (!file_exists($this->dir.'/data.json')) return [];
        return json_decode(file_get_contents($this->dir.'/data.json'),true);
    }
    
    
    
    protected function _save($file,$data) {
        while (file_exists($this->dir.'/'.$file.'.lock')) {
                usleep(rand(50,1000));
        }
        touch($this->dir.'/'.$file.'.lock');
        file_put_contents($this->dir.'/'.$file,json_encode($data));
        unlink($this->dir.'/'.$file.'.lock');
    }
    
    public function save($data) {
        $data=array_merge($this->get(),$data);
        $this->_save('data.json',$data);
        return $data;
    }
    
    public function queue($data=null) {
        if ($data!=null) {
            $this->_save('queue.json',$data);
            return $data;
        }
        if (!file_exists($this->dir.'/queue.json')) return [];
        return json_decode(file_get_contents($this->dir.'/queue.json'),true);
    }
    

}
