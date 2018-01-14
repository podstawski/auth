<?php


class payController extends Controller {
    
    protected function user() {
        $auth=Bootstrap::$main->session('auth');
        if (!isset($auth['id']) || !$auth['id']) $this->error(9);
        return $auth;
    }
    
    protected function signature($pin,$id,$data) {
        $sign=
            $pin.
            $id.
            $data['operation_number'].
            $data['operation_type'].
            $data['operation_status'].
            $data['operation_amount'].
            $data['operation_currency'].
            $data['operation_withdrawal_amount'].
            $data['operation_commission_amount'].
            $data['is_completed'].
            $data['operation_original_amount'].
            $data['operation_original_currency'].
            $data['operation_datetime'].
            $data['operation_related_number'].
            $data['control'].
            $data['description'].
            $data['email'].
            $data['p_info'].
            $data['p_email'].
            $data['credit_card_issuer_identification_number'].
            $data['credit_card_masked_number'].
            $data['credit_card_brand_codename'].
            $data['credit_card_brand_code'].
            $data['credit_card_id'].
            $data['channel'].
            $data['channel_country'].
            $data['geoip_country'];
            
            return hash('sha256', $sign);
    }
    
    public function post_dotpay() {
        
        Bootstrap::$main->log('payment',[$this->id,$this->data]);
        
        $config=Bootstrap::$main->getConfig('dotpay');
        
        $eventdata=new eventModel($this->id);
        $event=$eventdata->get();
        
        $id=isset($event['dotpay'])?$event['dotpay']:'-';
        
        if (isset($config[$id]) && isset($config[$id]['pin'])) {            
            if (isset($this->data['signature']) && $this->data['signature']==@$this->signature($config[$id]['pin'],$id,$this->data) && isset($this->data['operation_status']) && $this->data['operation_status']=='completed') {
                $this->grant(base64_decode($this->data['control']),$this->id);
            } else {
                Bootstrap::$main->log('payment',[$this->id,'Signature',@$this->signature($config[$id]['pin'],$id,$this->data)]);
            }
        }
        
        
        die('OK');
    }
    
    public function get_dotpay() {
             
        $auth=$this->user();
        if (!$this->id) $this->error(5);
        $eventdata=new eventModel($this->id);
        $event=$eventdata->get();
        $amount=isset($_GET['amount'])?$_GET['amount']:0;
        $amount_pure=preg_replace('/[^0-9\.]+/','',$amount)+0;
            
        if (!isset($event['event'])) $this->error(6);
        
        if (preg_replace('/[^0-9\.]+/','',$event['price_online'])!=$amount_pure &&
            preg_replace('/[^0-9\.]+/','',$event['price_offline'])!=$amount_pure) $this->error(13);
        
        if ($amount_pure==0) return $this->grant($auth['id'],$this->id);
        
       
        $form['action']='https://ssl.dotpay.pl/t2/';
        $form['form'] = [
            'api_version' => 'dev',
            'id' => $event['dotpay'],
            'amount' => $amount_pure,
            'currency' => 'PLN',
            'firstname' => $auth['first_name'],
            'lastname' => $auth['last_name'],
            'email' => $auth['email'],
            'type' => '0',
            'url' => 'http://'.$_SERVER['HTTP_HOST'].'/close.html',
            'urlc' => 'http://'.$_SERVER['HTTP_HOST'].'/pay/dotpay/'.$this->id,
            'description' => $event['title'],
            'control' => base64_encode($auth['id']),
        ];
        
        return $form;
    }
    
    protected function grant($user_id,$event_id) {
        
        $eventdata=new eventModel($event_id);
        $event=$eventdata->get();
        if (!isset($event['event'])) $this->error(6);
        
        $userdata=new userModel($user_id);
        $user=$userdata->data();
        
        
        if (array_search($user['email'],$event['users'])===false) {
            Bootstrap::$main->log('payment',['grant',$user['email'],$event_id,$event['event']]);
            $event['users'][]=$user['email'];
            $eventdata->save($event);
        }
        
        return true;
        
    }
}
