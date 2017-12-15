<?php


class youtubeController extends Controller {
    private $client,$service,$me,$myid;

    public function init() {
        $auth=Bootstrap::$main->session('auth');
        if (!isset($auth['id']) || !$auth['id']) $this->error(9);
        
        $this->me=strtolower($auth['email']);
        $this->myid=$auth['id'];
        $user=new userModel($auth['id']);
        $scopes=$user->scopes();
            
        
        $this->client=new Google_Client();
        $this->client->setAuthConfig(__DIR__.'/../configs/client_credentials.json');
        $this->service = new Google_Service_YouTube($this->client);
        $token=$user->token();
        if (in_array('youtube',$scopes) && $token) $this->client->setAccessToken($token);
        
    }
    
    public function get_event() {
        if (!$this->id) $this->error(5);
        
        $eventdata=new eventModel($this->id);
        $event=$eventdata->get();
        
        if (!isset($event['event'])) $this->error(6);
        $id=$event['event'];
        
        if (!$this->client->getAccessToken()) $this->error(12);
        
        $_event=$this->_get_event($id);
        
        if (!isset($_event->items) || count($_event->items)==0) $this->error(6);
        
        return ['yt'=>$_event->items[0],'data'=>$event];
    }
    
    protected function enableEmedded($id) {
        
        
        $listBroadcasts = $this->service->liveBroadcasts->listLiveBroadcasts('contentDetails',['id' => $id]);
        
        $broadcast = $listBroadcasts[0];
        $contentDetails = $broadcast->contentDetails;
        $contentDetails->enableEmbed=true;
        $broadcast->setContentDetails($contentDetails);

        //mydie($broadcast);
        
        $this->service->liveBroadcasts->update('contentDetails', $broadcast);            
                
    }
    
    protected function setVideoStatus($id,$author,$status='private') {
        
        $token=null;
        
        if ($author!=$this->myid) {
            $token=$this->client->getAccessToken();
            $user=new userModel($author);
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
        
        if (!$this->id) $this->error(5);
        $eventdata=new eventModel($this->id);
        $event=$eventdata->get();
        if (!isset($this->data['event'])) $this->error(6);
        
        $this->data['event']=end(explode('/',$this->data['event']));
        $pos=strpos($this->data['event'],'v=');
        if ($pos) $this->data['event']=substr($this->data['event'],$pos+2);
        $pos=strpos($this->data['event'],'&');
        if ($pos) $this->data['event']=substr($this->data['event'],0,$pos);
        
        $id=$this->data['event'];
        
        $_event=$this->_get_event($id);
        if (!isset($_event->items) || count($_event->items)==0) $this->error(6);
        
        
        
        if (isset($this->data['hangout'])) {
            $pos=strpos($this->data['hangout'],'?');
            if ($pos) $this->data['hangout']=substr($this->data['hangout'],0,$pos);
            $this->data['hangout']=end(explode('/',$this->data['hangout']));
        }
        
        if (isset($this->data['users'])) {
            $users=strtolower($this->data['users']);
            $users=preg_replace("/[ ,;\r\t]/","\n",$users);
            $users=trim(preg_replace("/[\n]+/","\n",$users));
            if (strlen($users)==0) $this->data['users']=[];
            else $this->data['users']=array_unique(explode("\n",$users));
            
            if (array_search($this->me,$this->data['users'])===false) $this->data['users'][]=$this->me;
        }
        
        if (isset($this->data['speakers'])) {
            $speakers=strtolower($this->data['speakers']);
            $speakers=preg_replace("/[ ,;\r\t]/","\n",$speakers);
            $speakers=trim(preg_replace("/[\n]+/","\n",$speakers));
            if (strlen($speakers)==0) $this->data['speakers']=[];
            else $this->data['speakers']=array_unique(explode("\n",$speakers));
        }
        
        $this->data['title']=$_event->items[0]->snippet->title;
        
        $this->data['author']=$this->myid;
        $data=$eventdata->save($this->data);
        //$this->enableEmedded($id);
        
        $user=new userModel($this->myid);
        $events=$user->events([$this->id => ['title'=>$this->data['title']]]);
        
        return ['yt'=>$_event->items[0],'data'=>$data,'events'=>$events];
        
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
        
        if ($author!=null && $token) {
            $this->client->setAccessToken($token);
        }
        
        return $ret;
    }
    
    public function get_start() {      
        if (!$this->id) $this->error(5);
        $eventdata=new eventModel($this->id);
        $event=$eventdata->get();
        
        if (!isset($event['event'])) $this->error(6);
        $id=$event['event'];
    
        $imtheauthor=$event['author']==$this->myid;
        $snippet=$this->_get_event($id,$imtheauthor?null:$event['author'])->items[0]->snippet;
        
        $price=$snippet->actualEndTime?$event['price_offline']:$event['price_online'];
    
        if (strlen(trim($price))) {
            if (!isset($event['users']) || array_search($this->me,$event['users'])===false) {
                $this->error(7,$price);
            }            
        } else {
            if (!isset($event['users']) || array_search($this->me,$event['users'])===false) {
                if (!isset($event['users'])) $event['users']=[];
                $event['users'][]=$this->me;
                $eventdata->save($event);
            }
        }

        
        if ($snippet->actualStartTime || $imtheauthor) {
            
            $may_watch=true;
            
            if ($event['referers']) {
                $may_watch=false;
                
                if (isset($_SERVER['HTTP_REFERER'])) foreach (explode(',',strtolower($event['referers'])) AS $referer) {
                    if (strstr(strtolower($_SERVER['HTTP_REFERER']),$referer)) $may_watch=true;
                }
            }
         
            if($may_watch) $this->setVideoStatus($id,$event['author'],'unlisted');
            
            $event['start']=time();
            $eventdata->save($event);
            
            $ret=['yt'=>$id,'chat'=>true,'close'=>$snippet->actualEndTime?true:false,'author'=>$imtheauthor];
            
            if(isset($event['notice'])) $ret['notice']=$event['notice'];
            
            if ($imtheauthor && !$snippet->actualEndTime) {
                $ret['hangout']=$event['hangout'];
                return $ret;
            }
            
            if ($snippet->actualEndTime) $ret['chat'] = false;
            elseif ( isset($event['speakers']) && array_search($this->me,$event['speakers'])!==false) $ret['hangout']=$event['hangout'];
            
            return $ret;
                
        }
        //mydie(date('d-m-Y H:i',strtotime($snippet->scheduledStartTime)));
        $this->error(8,$snippet->scheduledStartTime);
    }

    public function get_stop() {
        if (!$this->id) $this->error(5);
        
        $eventdata=new eventModel($this->id);
        $event=$eventdata->get();
        if (!isset($event['event'])) $this->error(6);
        $id=$event['event'];
        if (time()-$event['start']<5) return false;
        
        $snippet=$this->_get_event($id,$event['author']!=$this->myid?$event['author']:null)->items[0]->snippet;
        
        if ($snippet->actualEndTime) $this->setVideoStatus($id,$event['author'],'private');
        return true;
    }
    
    public function get_chat() {
        $lang=Error::lang();
        
        if (!$this->id) $this->error(5);
        $eventdata=new eventModel($this->id);
        $event=$eventdata->get();
        
        if (!isset($event['event'])) $this->error(6);
        $id=$event['event'];
    
        if (!isset($event['users']) || array_search($this->me,$event['users'])===false) {
            $this->error(7);
        }
        
        $speaker = isset($event['speakers']) && array_search($this->me,$event['speakers'])!==false;        
        $event_id=$this->id;
        include(__DIR__.'/../views/chat.phtml');
        die();
    }
    
    public function get_join(){
        if (!$this->id) $this->error(5);
        
        $eventdata=new eventModel($this->id);
        $event=$eventdata->get();
        if (!isset($event['event'])) $this->error(6);
        
        $queue=$eventdata->queue();
        
        
        if (!isset($queue['active'])) $queue['active']=[];
        if (!isset($queue['waiting'])) $queue['waiting']=[];
        if (!isset($queue['out'])) $queue['out']=[];
        
        if (isset($queue['active'][$this->myid])) return ['hangout'=>$event['hangout']];
        if (isset($queue['waiting'][$this->myid])) return true;
        
        $queue['waiting'][$this->myid]=Bootstrap::$main->session('auth');
        $eventdata->queue($queue);
        return true;
    }
    
    public function get_unjoin(){
        if (!$this->id) $this->error(5);
        
        $eventdata=new eventModel($this->id);
        $event=$eventdata->get();
        if (!isset($event['event'])) $this->error(6);
        
        $queue=$eventdata->queue();
        
        if (isset($queue['out'][$this->myid])) {
            unset($queue['out'][$this->myid]);
            $eventdata->queue($queue);
            return ['yt'=>$event['event']];
        }
        
        return true;
    }
    
    
    public function get_change() {
        if (!$this->id) $this->error(5);
        
        return Bootstrap::$main->session('yt.'.$this->id);
    }
    
    public function post_change() {
        if (!$this->id) $this->error(5);
        
        return Bootstrap::$main->session('yt.'.$this->id,$this->data);
    }
    
    public function get_guests() {
        if (!$this->id) $this->error(5);
        $eventdata=new eventModel($this->id);
        $event=$eventdata->get();
        if (!isset($event['event'])) $this->error(6);
        
        if (!isset($event['speakers']) || array_search($this->me,$event['speakers'])===false) {
            $this->error(10);
        }
        
        return ['guests'=>$eventdata->queue()];
        
    }
    
    public function post_guests(){
        if (!$this->id) $this->error(5);
        $eventdata=new eventModel($this->id);
        $event=$eventdata->get();
        if (!isset($event['event'])) $this->error(6);
        
        if (!isset($event['speakers']) || array_search($this->me,$event['speakers'])===false) {
            $this->error(10);
        }
        if (!isset($this->data['user'])) $this->error(11);
        $user=$this->data['user'];
        
        $queue=$eventdata->queue();
        if ($this->data['active'] && isset($queue['waiting'][$user])) {
            $queue['active'][$user] = $queue['waiting'][$user];
            unset($queue['waiting'][$user]);
        } 
        if (!$this->data['active'] && isset($queue['active'][$user])) {
            
            $queue['out'][$user] = $queue['active'][$user];
            unset($queue['active'][$user]);
        }
        return $eventdata->queue($queue);
    }
    
    public function get_events() {
		$user=new userModel($this->myid);
		return ['events'=>$user->events()];
	}
    
    public function post_events() {
		
        $event_id=$this->myid.'-'.time();
        
        $broadcastsResponse = $this->service->liveBroadcasts->listLiveBroadcasts(
            'id,snippet',
            array(
                'broadcastStatus' => 'upcoming'
            )
        );
        
        $last=false;
        
        foreach($broadcastsResponse->items AS $item) {
            if (!$last || strtotime($item->snippet->publishedAt) > strtotime($last))
                $last=$item->snippet->publishedAt;
        }
        
		return ['event'=>$event_id,'last'=>$last];
	}
    
    
    public function get_my_future_event() {
        $broadcastsResponse = $this->service->liveBroadcasts->listLiveBroadcasts(
            'id,snippet',
            array(
                'broadcastStatus' => 'upcoming'
            )
        );
        
        if (count($broadcastsResponse->items)==0) return ['event'=>false];
        
        if (!$this->id) return ['event'=>$broadcastsResponse->items[0]];
        
        $last=strtotime($this->id);
        
        foreach($broadcastsResponse->items AS $item) {
            if (strtotime($item->snippet->publishedAt) > $last)
                return ['event'=>$item];
        }
        
        return ['event'=>false];
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
                'mine' => 'true'
            )
        );
        
        
        mydie($broadcastsResponse);
    }

    
}