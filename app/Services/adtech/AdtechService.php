<?php

namespace App\Services\adtech;

use App\Services\AMSService;
use App\Services\AMSServiceInterface;
use App\Lib\EmailReader;
use Log;
use DateTime;

require('config.php');

class AdtechService extends AMSService implements AMSServiceInterface
{

    public function __construct()
    {
        parent::__construct();
        $this->presenter = new AdtechPresenter;
    }

    public function perform(array $params)
    {
        $configData = config('AMS.provider');

        $startDate = $this->getParameter($params, 'start')->format('Y-m-d');
        //$endDate = $this->getParameter($params, 'end')->format('Y-m-d');
        
        list($response, $error) = $this->call();

        echo json_encode($response);

        if ($error) {
            echo 'cURL Error :' . $error;
            return;
        }

        //$this->presenter->present($response, $configData['date_format']);

        error_log('AdsenseService Performed');
    }

    protected function call()
    {
        $configData = config('AMS.provider');

        Log::info('Initializing Request', ['email' => $configData['email_username']]);
        
        $response = $configData;
        $err = false;

        $emailReader = new EmailReader($configData['email_server'], $configData['email_username'], $configData['email_password']);
        $response = $emailReader->searchEmails(null, null, 'Premium');
        
        Log::info('Request finished', ['response'=>$response]);

        if ($err) {
            Log::warning('Request Error', ['error' => $err]);
        }

        return [$response, $err];
    }
}
