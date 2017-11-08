<?php
    $_SERVER['backend_start']=microtime(true);
    include __DIR__.'/../backend/include/all.php';
    
    
    
    allow_origin(['webkameleon.com','homeo24.eu']);
    
    autoload([__DIR__.'/../app/classes',
              __DIR__.'/../app/models',
              __DIR__.'/../app/controllers'
    ]);
    
    $config=json_config(__DIR__.'/../app/configs/application.json');
    $method=http_method();
    
    require_once '/opt/google/google-api-php-client/vendor/autoload.php';
    
    $bootstrap = new Bootstrap($config);
    register_shutdown_function(function () {
        @Bootstrap::$main->closeConn();   
    });
    
    
    $bootstrap->run(strtolower($method));
    