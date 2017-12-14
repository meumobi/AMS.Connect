<?php

namespace App\Services\unplugged;

use App\Services\AMSService;
use App\Services\AMSServiceInterface;
use App\Lib\EmailReader;
use Log;
use DateTime;

require('config.php');

class UnpluggedService extends AMSService implements AMSServiceInterface
{
    
    public function __construct()
    {
        parent::__construct();
        $this->presenter = new UnpluggedPresenter;
    }
    
    public function perform(array $params)
    {
        $configData = config('AMS.provider');
        
        $email = $this->getParameter($params, 'email');
        $date = $this->getParameter($params, 'start')->modify('+ 1 day')->format('d-M-Y');
        $mode = $this->getParameter($params, 'mode');
        
        $delimiter = ",";
        
        list($response, $error) = $this->call($date, $email, $delimiter);
        
        
        if ($error) {
            Log::warning('Request Error', ['error' => $error]);
            return;
        }
        
        $this->presenter->present($response, $configData['date_format'], $mode);
        
        Log::debug(ucfirst($configData['name']) . ' Service Performed');
    }
    
    protected function callStub($date, $email_to, $delimiter)
    {
        $response = [];
        $error = false;
        
        /*
        From browser path=public/examples/...
        From cli path=examples/...
        */
        $strTempFile = "public/examples/unplugged.csv";
        $response = $this->getArrayFromCsvString(file_get_contents($strTempFile), $delimiter);
        
        return [$response, $error];
    }
    
    protected function call($date, $email_to, $delimiter)
    {
        $configData = config('AMS.provider');
        
        $email_to = ($email_to != null) ? $email_to : $configData['email_to'];
        
        Log::debug('Initializing Request', ['email' => $configData['email_username']]);
        
        $response = null;
        $error = false;
        
        $emailReader = new EmailReader();
        $emailReader->connect($configData['email_server'], $configData['email_username'], $configData['email_password']);
        //Change to filter by recipient
        $emails = $emailReader->searchEmails($email_to, $date);
        
        Log::debug('Request finished', ['Number of emails fetched' => count($emails)]);
        
        if (empty($emails)) {
            $error = 'No Emails Found';
        } else {
            /*
            put the newest emails on top
            */
            rsort($emails);
        }
        //TODO: Ensure this heuristic to work, check if will be always the first email and first attachment
        if (!$error) {
            //Get the first attachment for the first email
            $index = array_shift($emails);
            $attachments = $emailReader->getEmailAttachments($index);
            $firstAttachment = array_shift($attachments);
            $response = $this->getArrayFromCsvString($firstAttachment['attachment'], $delimiter);
            Log::debug('Email 1st attachment file: ', ['filename' => $firstAttachment['filename'], 'name' => $firstAttachment['name']]);
            $emailReader->close();
            
            if (empty($response)) {
                $error = 'No Data Found';
            }
            
            Log::debug('Request finished', ['response'=>$response]);
            
        }
        return [$response, $error];
    }
}
