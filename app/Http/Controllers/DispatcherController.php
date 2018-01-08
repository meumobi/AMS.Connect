<?php

namespace App\Http\Controllers;

use App\Services\AMSService;
use DateTime;
use Google_Client;
use Illuminate\Console\Command;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Validation\ValidationException;
use Log;

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
      
    public function perform(Request $request, $providerName = null)
    {
        try {
            $this->validate(
                $request,
                [
                    'date' => 'date_format:Y-m-d|before:today'
                ]
            );
        } catch (ValidationException $exception) {
            //TODO: Handle the exception if needed
            Log::notice('Invalid Parameters', ['url'=>$request->fullUrl(), 'params'=>$request->all()]);
            throw $exception;
        }

        if (!isset($providerName)) {
            $providerName = null;
        }
        //TODO: Filter the request->all using request->only or request->except if needed
        $params = $request->all();

        /*
            date could be setted on date or text (from slack slash command) param
        */

        $date = null;

        if ($request->has('date')) {
            $date = $request->input('date');
        } elseif ($request->has('text')) {
            $date = $request->input('text');
        } else {
            $date = (new DateTime)->modify('-1 day')->format('Y-m-d');
        }

        $params['date'] = $date;

        Log::debug('Date of reports: ' . $params['date']);
           
        $exitCode = Artisan::call('providers:perform', [
            'provider' => $providerName,
            '--update-correlationtable' => true,
            '--update-adserving' => true,
            '--update-admargin' => true,
            '--mode' => 'publish',
            '--date' => $params['date']
        ]);

        Log::debug(__CLASS__ . ', Artisan call: ', ['exit code' => $exitCode]);
        
        echo 'Command Performed, exit code: ' . $exitCode;
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

    public function handleAdsenseToken(Request $request)
    {
        $client = AMSService::loadService('adsense')->getGoogleClient();
        
        $data = $request->all();
        if ($request->has('code')) {
            $token = $client->fetchAccessTokenWithAuthCode($request->input('code'));
            $data['token'] = $token;
        }
        echo json_encode($data);
    }
}
