<?php

namespace App\Http\Controllers;

use App\Services\AMSService;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

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
        
    public function call(Request $request, $providerName)
    {
        try {
            $this->validate(
                $request, 
                [
                    'start' => 'required_with:end|date_format:Y-m-d|before:tomorrow',
                    'end' => 'date_format:Y-m-d|after_or_equal:start|before:tomorrow'
                ]
            );
        }
        catch (ValidationException $exception){
            //TODO: Log and Handle the exception if needed
            throw $exception;
        }

        //TODO: Filter the request->all using request->only or request->except if needed
        $params = $request->all();
        $serviceHandler = AMSService::loadService($providerName);
        if (!$serviceHandler) {
            echo 'The service provider \''.$providerName.'\' does not exists';
            return;
        }
        $serviceHandler->perform($params);
    }
}
