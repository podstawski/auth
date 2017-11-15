<?php
@include_once 'Error.php';
class Bootstrap extends Bootstrapbase {
    
    public function log($container,$data) {
        $logdir=__DIR__.'/../logs';
        if (!file_exists($logdir)) mkdir($logdir,0755);
        file_put_contents($logdir.'/'.$container.'.log',"\n\n".date('Y-m-d H:i:s')."\n".print_r($data,true),FILE_APPEND);
    }
}