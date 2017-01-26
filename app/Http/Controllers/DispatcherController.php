<?php

namespace App\Http\Controllers;

use App\Services\AMSService;
use Log;
use DateTime;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class DispatcherController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(Request $request)
    {
        Log::info('Initializing Controller', ['url' => $request->fullUrl()]);
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
                    'start' => 'required_with:end|date_format:Y-m-d|before:today',
                    'end' => 'date_format:Y-m-d|after_or_equal:start|before:today'
                ]
            );
        } catch (ValidationException $exception) {
            //TODO: Handle the exception if needed
            Log::notice('Invalid Parameters', ['url'=>$request->fullUrl(), 'params'=>$request->all()]);
            throw $exception;
        }

        //TODO: Filter the request->all using request->only or request->except if needed
        $params = $request->all();
        //Converting the start and end parameters into DateTime objects
        $params['start'] = $request->has('start')
            ? (new DateTime)->createFromFormat('Y-m-d', $request->input('start'))
            : (new DateTime)->modify('-1 day');
        $params['start']->setTime(0, 0, 0);
        $params['end'] = $request->has('end')
            ? (new DateTime)->createFromFormat('Y-m-d', $request->input('end'))
            : (new DateTime)->modify('-1 day');
        $params['end']->setTime(23, 59, 59);

        $serviceHandler = AMSService::loadService($providerName);
        if (!$serviceHandler) {
            Log::notice('Inexistent Provider tried to be accessed', ['provider'=>$providerName]);
            echo 'The service provider \''.$providerName.'\' does not exists';
            return;
        }
        $serviceHandler->perform($params);
        Log::info('Service performed, finishing request', ['providerName'=>$providerName]);
    }
}
