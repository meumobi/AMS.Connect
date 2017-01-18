<?php

namespace App\Services;

class AMSService
{
    public function __construct()
    {
    }

    public static function loadService($providerName)
    {
        $providerName = strtolower($providerName);
        $class = 'App\\Services\\'.$providerName. '\\' . ucfirst($providerName).'Service';
        if (class_exists($class)) {
            $instance =  new $class;
            return $instance;
        }
        //TODO: Log when the class is not found
        return null;
    }
}
