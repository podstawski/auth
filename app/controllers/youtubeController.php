<?php


class youtubeController extends Controller {
    private $client,$service,$me,$myid;

    public function init() {
        $auth=Bootstrap::$main->session('auth');
        if (!isset($auth['id']) || !$auth['id']) $this->error('9');
        
        $this->me=strtolower($auth['email']);
        $this->myid=$auth['id'];
        $user=new userModel($auth['id']);
        $scopes=$user->scopes();
        
        if (!in_array('youtube',$scopes)) $this->error('8');
    
        
        $this->client=new Google_Client();
        $this->client->setAuthConfig(__DIR__.'/../configs/client_credentials.json');
        $this->service = new Google_Service_YouTube($this->client);
        $this->client->setAccessToken($user->token());
        
    }
    
    public function get_event() {
        $id=$this->id;
        if (!$id) $this->error(2);
        $event=$this->_get_event($id);
        if (!isset($event->items) || count($event->items)==0) $this->error(2);
        
        
        
        
        $eventdata=new eventModel($id);
        return ['yt'=>$event->items[0],'data'=>$eventdata->get()];
    }
    
    public function post_event() {
        $id=$this->id;
        if (!$id) $this->error(2);
        $event=$this->_get_event($id);
        if (!isset($event->items) || count($event->items)==0) $this->error(2);
        
        $eventdata=new eventModel($id);
        
        if (isset($this->data['users'])) {
            $users=strtolower($this->data['users']);
            $users=preg_replace("/[ ,;\r\t]/","\n",$users);
            $users=trim(preg_replace("/[\n]+/","\n",$users));
            if (strlen($users)==0) $this->data['users']=[];
            else $this->data['users']=array_unique(explode("\n",$users));
            
            if (array_search($this->me,$this->data['users'])===false) $this->data['users'][]=$this->me;
        }
        
        $listResponse = $this->service->videos->listVideos('status', array('id' => $id));
        $video = $listResponse[0];
        $videoStatus = $video['status'];
        if ($videoStatus->privacyStatus != 'private') {
            $videoStatus->privacyStatus = 'private';
            $video->setStatus($videoStatus);
            $this->service->videos->update('status', $video);            
        }

        
        $this->data['author']=$this->myid;
        
        return ['yt'=>$event->items[0],'data'=>$eventdata->save($this->data)];
        
    }
    
    protected function _get_event($id) {
        return $this->service->liveBroadcasts->listLiveBroadcasts(
            'id,snippet',
            array(
                'id' => $id
            )
        );
    }
    
    public function get_start() {
        $id=$this->id;
        if (!$id) $this->error(2);
        $eventdata=new eventModel($id);
        
        if (array_search($this->me,$eventdata['users'])===false) $this->error(3);
        
        
        return '<iframe width="100%" height="315" src="http://www.youtube.com/embed/'.$id.'" frameborder="0" allowfullscreen></iframe>';
    }

    public function get_my_life_events() {
        
        /*
        $broadcastsResponse = $this->service->liveStreams->listLiveStreams('id,snippet', array(
        'mine' => 'true',
        ));
        */
        
        
        $broadcastsResponse = $this->service->liveBroadcasts->listLiveBroadcasts(
            'id,snippet',
            array(
                'id' => '7vD30NXPqck'
            )
        );
        
        
        mydie($broadcastsResponse);
    }

    
}