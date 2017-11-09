<?php

class Error {
    protected static $err = array(
        0  => ['en'=>'Class file not found','pl'=>'Klasa nie znaleziona'],
        1  => ['en'=>'No configuration file','pl'=>'Brak pliku konfiguracyjnego'],
        2  => ['en'=>'Unknown method','pl'=>'Nieznana metoda'],
        3  => ['en'=>'No scope','pl'=>'Nie podano zakresu'],
        4  => ['en'=>'Email format problem','pl'=>'Problem z formatem maila'],
        5  => ['en'=>'No event given','pl'=>'Nie podano identyfikatora transmisji'],
        6  => ['en'=>'Event not found','pl'=>'Nie znaleziono wydarzenia'],
        7  => ['en'=>'Please pay','pl'=>'Prosimy zapłacić'],
        8  => ['en'=>'Broadcast starts','pl'=>'Transmisja zaczyna się'],
        9  => ['en'=>'Please log in','pl'=>'Proszę się zalogować'],
        10 => ['en'=>'Not authorized','pl'=>'Nie masz dostępu'],
        11 => ['en'=>'No such user','pl'=>'Nie ma użytkownika'],
    );

    
    public static function lang ($lang=null) {
        if ($lang!=null) Bootstrap::$main->session('lang',$lang);
        return Bootstrap::$main->session('lang')?:'en';
    }
    public static function e($id)
    {
        $info='Unknown error';
        if (isset(self::$err[$id])) {
            $info=isset(self::$err[$id][self::lang()])?self::$err[$id][self::lang()] : self::$err[$id]['en'];
        }
        return array('number'=>$id,'info'=>$info);
    }
}
