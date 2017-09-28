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

        $email = $this->getParameter($params, 'email');
        $date = $this->getParameter($params, 'start')->modify('+ 1 day')->format('d-M-Y');
        $mode = $this->getParameter($params, 'mode');

        list($response, $error) = $this->call($date, $email);


        if ($error) {
            echo 'Request Error: ' . $error;
            return;
        }

        $this->presenter->present($response, $configData['date_format'], $mode);

        error_log('AdtechService Performed');
    }

    protected function callStub($date, $email_to)
    {
        $response = [];
        $error = false;
        
        /*
            From browser path=public/examples/...
            From cli path=examples/...
        */
        $strTempFile = "public/examples/adtech.csv";
        $response = $this->getArrayFromCsvString(file_get_contents($strTempFile), ';');

        Log::info('Request finished', ['response'=>$response]);

        return [$response, $error];
    }

    protected function call($date, $email_to)
    {
        $configData = config('AMS.provider');

        $email_to = ($email_to != null) ? $email_to : $configData['email_to'];

        Log::info('Initializing Request', ['email' => $configData['email_username']]);
        
        $response = null;
        $error = false;

        $emailReader = new EmailReader();
        $emailReader->connect($configData['email_server'], $configData['email_username'], $configData['email_password']);
        //Change to filter by recipient
        $emails = $emailReader->searchEmails($email_to, $date);
        if (empty($emails)) {
            $error = 'No Emails Found';
            $emails = [];
        }
        //TODO: Ensure this heuristic to work, check if will be always the first email and first attachment
        if (!$error) {
            //Get the first attachment for the first email
            $index = array_shift($emails);
            $attachments = $emailReader->getEmailAttachments($index);
            $firstAttachment = array_shift($attachments);
            $response = $this->getArrayFromCsvString($firstAttachment['attachment'], ';');
            
            $emailReader->close();

            Log::info('Request finished', ['response'=>$response]);

            if (empty($response)) {
                $error = 'No Data Found';
            }
        }
        if ($error) {
            Log::warning('Request Error', ['error' => $error]);
        }

        return [$response, $error];
    }
}
