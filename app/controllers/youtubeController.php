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
            
        
        $this->client=new Google_Client();
        $this->client->setAuthConfig(__DIR__.'/../configs/client_credentials.json');
        $this->service = new Google_Service_YouTube($this->client);
        if (in_array('youtube',$scopes)) $this->client->setAccessToken($user->token());
        
    }
    
    public function get_event() {
        $id=$this->id;
        if (!$id) $this->error(2);
        $event=$this->_get_event($id);
        if (!isset($event->items) || count($event->items)==0) $this->error(2);
          
        $eventdata=new eventModel($id);
        return ['yt'=>$event->items[0],'data'=>$eventdata->get()];
    }
    
    protected function enableEmedded($id) {
        
        
        $listBroadcasts = $this->service->liveBroadcasts->listLiveBroadcasts('contentDetails',['id' => $id]);
        
        $broadcast = $listBroadcasts[0];
        $contentDetails = $broadcast->contentDetails;
        $contentDetails->enableEmbed=true;
        $broadcast->setContentDetails($contentDetails);

        
        $this->service->liveBroadcasts->update('contentDetails', $broadcast);            
                
    }
    
    protected function setVideoStatus($id,$status='private') {
        
        $eventdata=new eventModel($id);
        $event=$eventdata->get();
        $token=null;
        
        
        if ($event['author']!=$this->myid) {
            $token=$this->client->getAccessToken();
            $user=new userModel($event['author']);
            $this->client->setAccessToken($user->token());
        }
 
        $listResponse = $this->service->videos->listVideos('status', array('id' => $id));
        $video = $listResponse[0];
        $videoStatus = $video['status'];
        
        if ($videoStatus->privacyStatus != $status) {
            $videoStatus->privacyStatus = $status;
            $video->setStatus($videoStatus);
            $this->service->videos->update('status', $video);            
        }
        
        if ($token!=null) $this->client->setAccessToken($token);
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
        
        $this->enableEmedded($id);
        
        $this->data['author']=$this->myid;
        
        return ['yt'=>$event->items[0],'data'=>$eventdata->save($this->data)];
        
    }
    
    protected function _get_event($id,$author=null) {
        
        if ($author!=null) {
            $token=$this->client->getAccessToken();
            $user=new userModel($author);
            $this->client->setAccessToken($user->token());
        }
        $ret = $this->service->liveBroadcasts->listLiveBroadcasts(
            'id,snippet',
            array(
                'id' => $id
            )
        );
        if ($author!=null) {
            $this->client->setAccessToken($token);
        }
        
        return $ret;
    }
    
    public function get_start() {
        $id=$this->id;
            
        if (!$id) $this->error(2);
        $eventdata=new eventModel($id);
        $event=$eventdata->get();
        
    
        if (array_search($this->me,$event['users'])===false) $this->error(3);

        
        $snippet=$this->_get_event($id,$event['author']!=$this->myid?$event['author']:null)->items[0]->snippet;
        
        
        if ($snippet->actualStartTime) {
            $this->setVideoStatus($id,'unlisted');
            
            
            $event['start']=time();
            $eventdata->save($event);
            
            return [
                'yt'=>'<iframe width="100%" height="300" src="http://www.youtube.com/embed/'.$id.'?autoplay=1" frameborder="0" allowfullscreen></iframe>',
                'chat'=>'<iframe width="100%" height="300" src="https://www.youtube.com/live_chat?v='.$id.'&embed_domain=ORIGIN" frameborder="0" allowfullscreen></iframe>'
            ];    
        }
        //mydie(date('d-m-Y H:i',strtotime($snippet->scheduledStartTime)));
        $this->error(5,$snippet->scheduledStartTime);
    }

    public function get_stop() {
        $id=$this->id;
        if (!$id) $this->error(2);
        
        $eventdata=new eventModel($id);
        $event=$eventdata->get();
        if (time()-$event['start']<5) return false;
        
        $snippet=$this->_get_event($id,$event['author']!=$this->myid?$event['author']:null)->items[0]->snippet;
        
        
        if ($snippet->actualEndTime) $this->setVideoStatus($id,'private');
        return true;
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