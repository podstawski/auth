<?php

class indexController extends Controller {
    
    public function get() {
        $lang=Error::lang();
        
        include(__DIR__.'/../views/index.phtml');
        die();
    }
}