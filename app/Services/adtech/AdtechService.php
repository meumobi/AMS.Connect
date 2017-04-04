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
        
        list($response, $error) = $this->call($date, $email);


        if ($error) {
            echo 'Request Error: ' . $error;
            return;
        }

        $this->presenter->present($response, $configData['date_format']);

        error_log('AdsenseService Performed');
    }

    protected function callStub($date, $email_to)
    {
        $response = null;
        $error = false;
        
        $strTempFile = "examples/adtech.csv";
        $response = $this->getArrayFromCsvString(file_get_contents($strTempFile), ';');

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

    private function getArrayFromCsvString($csvString, $delimiter = ',')
    {
        $csvString = trim($csvString, "\r\n");
        $rowsArray = explode("\r\n", $csvString);
        $data = array_map(
            function ($row) use ($delimiter) {
                return str_getcsv($row, $delimiter);
            },
            $rowsArray
        );
        /*
         Set internal encoding of mb to utf-8
         to convert string that contains special characters
         http://stackoverflow.com/a/2516482
        */
        mb_internal_encoding('UTF-8');
        $header = array_map('mb_strtolower', array_shift($data));
        $data = array_map(
            function ($row) use ($header) {
                return array_combine($header, $row);
            },
            $data
        );
        return $data;
    }
}
