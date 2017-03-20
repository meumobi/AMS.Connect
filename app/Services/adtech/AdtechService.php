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

        $date = $this->getParameter($params, 'start')->format('d-M-Y');
        
        list($response, $error) = $this->call($date);


        if ($error) {
            echo 'Request Error :' . $error;
            return;
        }

        $this->presenter->present($response, $configData['date_format']);

        error_log('AdsenseService Performed');
    }

    protected function call($date)
    {
        $configData = config('AMS.provider');

        Log::info('Initializing Request', ['email' => $configData['email_username']]);
        
        $response = null;
        $error = false;

        $emailReader = new EmailReader();
        $emailReader->connect($configData['email_server'], $configData['email_username'], $configData['email_password']);
        //Change to filter by recipient
        $emails = $emailReader->searchEmails(null, $date, 'Premium');

        if (empty($emails)) {
            $error = 'No Emails Found';
            $emails = [];
        }
        //TODO: Ensure this heuristic to work, check if will be always the first email and first attachment
        //Get the first attachment for the first email
        $index = array_shift($emails);
        $attachments = $emailReader->getEmailAttachments($index);
        $firstAttachment = array_shift($attachments);
        $response = $this->getArrayFromCsvString($firstAttachment['attachment'], ';');
        
        $emailReader->close();

        Log::info('Request finished', ['response'=>$response]);

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
            function($row) use ($delimiter) {
                return str_getcsv($row, $delimiter);
            }, 
            $rowsArray
        );
        $header = array_map('strtolower', array_shift($data));
        $data = array_map(
            function ($row) use ($header){
                return array_combine($header, $row);
            },
            $data
        );
        
        return $data;
    }
}
