<?php

namespace App\Services;

use Log;

class AMSService
{
    public function __construct()
    {
        Log::info('Service initialized: '.get_class($this));
    }

    public static function loadService($providerName)
    {
        $providerName = strtolower($providerName);
        $class = 'App\\Services\\'.$providerName. '\\' . ucfirst($providerName).'Service';
        if (class_exists($class)) {
            $instance =  new $class;
            return $instance;
        }
        Log::warning('Class '. $class .' Not Found', ['class'=>$class]);
        return null;
    }

    protected function getParameter($params, $key, $defaultValue = null)
    {
        return (isset($params[$key]) && $params[$key])
          ? $params[$key]
          : $defaultValue;
    }
}
