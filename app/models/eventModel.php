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
    
    public function save($data) {
        $data=array_merge($this->get(),$data);
        file_put_contents($this->dir.'/data.json',json_encode($data));
        return $data;
    }
    
    public function queue($data=null) {
        if ($data!=null) {
            file_put_contents($this->dir.'/queue.json',json_encode($data));
            return $data;
        }
        if (!file_exists($this->dir.'/queue.json')) return [];
        return json_decode(file_get_contents($this->dir.'/queue.json'),true);
    }
    

}
