<?php

namespace App\Services\admargin;

use App\Services\AMSService;
use App\Services\AMSServiceInterface;
use App\Lib\EmailReader;
use Illuminate\Support\Facades\Storage;
use Log;
use DateTime;

require('config.php');

class AdmarginService extends AMSService implements AMSServiceInterface
{
  public function __construct()
  {
    parent::__construct();
    $this->presenter = new AdmarginPresenter;
  }
  
  public function perform(array $params)
  {
    $configData = config('AMS.provider');
    
    $email = $this->getParameter($params, 'email');
    
    $date = clone $this->getParameter($params, 'start');
    $date = $date->modify('+ 1 day')->format('d-M-Y');
    
    Log::debug('Looking for email on date: ' . $date);
    
    $delimiter = ",";
    
    list($response, $error) = $this->call($date, $email, $delimiter);
    
    if ($error) {
      Log::warning('Request Error', ['error' => $error]);
      return;
    }
    
    //Getting the formated content to append in the file
    $formatedContent = $this->presenter->format($response, $configData['date_format']);
    if (!$formatedContent) {
      //Error on presenter
      return;
    }
    
    //Update the Admargin File
    $filePath = Storage::disk('public')->url($configData['file_name']);
    Log::debug('File Path of Admargin file: ' . $filePath);    
    
    $admarginFile = fopen($filePath, 'a+');
    fwrite($admarginFile, $formatedContent);
    fclose($admarginFile);
    Log::info('Lines of AdmarginTable: ' . count(file($filePath)));
    
    Log::debug(ucfirst($configData['name']) . ' Service Performed');
  }
  
  protected function callStub($date, $email_to, $delimiter)
  {
    $response = [];
    $error = false;
    
    /*
    From browser path=examples/...
    From cli path=public/examples/...
    */
    $strTempFile = "public/examples/admargin.csv";
    $response = $this->getArrayFromCsvString(file_get_contents($strTempFile), $delimiter);
    
    Log::debug('Request finished', ['response'=>$response]);
    
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
    
    if (empty($emails)) {
      $error = 'No Emails Found';
      $emails = [];
    }
    //TODO: Ensure this heuristic to work, check if will be always the first email and first attachment
    //Get the first attachment for the first email
    $index = array_shift($emails);
    $attachments = $emailReader->getEmailAttachments($index);
    $firstAttachment = array_shift($attachments);
    $response = $this->getArrayFromCsvString($firstAttachment['attachment'], $delimiter);
    
    $emailReader->close();
    if (empty($response)) {
      $error = 'No Data Found';
    }
    
    Log::debug('Request finished', ['response'=>$response]);
    
    if ($error) {
      Log::warning('Request Error', ['error' => $error]);
    }
    
    return [$response, $error];
  }
}
