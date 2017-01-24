<?php

namespace App\Http\Controllers;

use App\Services\AMSService;

class DispatcherController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    public function index()
    {
        echo 'AMS.Connect Services';
    }
        
    public function call($providerName)
    {
        //TODO: Get the params from the Request url and pass to the handler
        $params = [];
        $serviceHandler = AMSService::loadService($providerName);
        if (!$serviceHandler) {
            echo 'The service provider \''.$providerName.'\' does not exists';
            return;
        }
        $serviceHandler->perform($params);
    }
}
