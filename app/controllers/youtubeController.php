<?php


class youtubeController extends Controller {
    private $client,$service;

    public function init() {
        $auth=Bootstrap::$main->session('auth');
        if (!isset($auth['id']) || !$auth['id']) $this->error('9');
        
        $user=new userModel($auth['id']);
        $scopes=$user->scopes();
        
        if (!in_array('youtube',$scopes)) $this->error('8');
    
        
        $this->client=new Google_Client();
        $this->client->setAuthConfig(__DIR__.'/../configs/client_credentials.json');
        $this->service = new Google_Service_YouTube($this->client);
        $this->client->setAccessToken($user->token());
        
    }
    
    public function get() {
        
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
                'id' => 'weFb1c5FToc'
            )
        );
        
        
        mydie($broadcastsResponse);
    }

    
}