<?php

namespace App\Services\adserving;

use App\Services\AMSService;
use App\Services\AMSServiceInterface;
use App\Lib\EmailReader;
use Log;
use DateTime;

require('config.php');

class AdservingService extends AMSService implements AMSServiceInterface
{

    public function __construct()
    {
        parent::__construct();
        $this->presenter = new AdservingPresenter;
    }

    public function perform(array $params)
    {
        $configData = config('AMS.provider');

        $email = $this->getParameter($params, 'email');

        $date = $this->getParameter($params, 'start')->modify('+ 1 day')->format('d-M-Y');

        list($response, $error) = $this->call($date, $email);
        if ($error) {
            echo 'Request Error :' . $error;
            return;
        }

        //Checking the PID Lock and Data Date
        $dataDate = $this->getDateOfData($response);
        $pidDate = $this->getDateOfPidLock();

        if ($pidDate >= $dataDate) {
            echo 'Request Error: This date was already imported';
            return;
        }

        //Getting the formated content to append in the file
        $formatedContent = $this->presenter->format($response, $configData['date_format']);
        if (!$formatedContent) {
            //Error on presenter
            return;
        }

        //Update the Adserving File
        $adservingFile = fopen(storage_path($configData['file_path']), 'a+');
        fwrite($adservingFile, $formatedContent);
        fclose($adservingFile);

        $this->updateLockFile($dataDate);

        echo json_encode(
            [
                'success' => true,
                'date' => $dataDate->format('d/m/Y'),
            ]
        );

        error_log('AdsenseService Performed');
    }

    private function getDateOfData($data)
    {
        if (!empty($data)) {
            return (new DateTime)->createFromFormat('d/m/Y', $data[0]['par jour'])
                ->setTime(0, 0, 0);
        }
        return null;
    }

    private function getDateOfPidLock()
    {
        $pidFile = config('AMS.provider.pid_lock_file');
        $pidDate = file_get_contents($pidFile);
        if ($pidDate) {
            return (new DateTime)->createFromFormat('d/m/Y', $pidDate)
                ->setTime(0, 0, 0);
        }
        return null;
    }

    private function updateLockFile($dataDate)
    {
        file_put_contents(
            config('AMS.provider.pid_lock_file'),
            $dataDate->format('d/m/Y')
        );
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
        //Get the first attachment for the first email
        $index = array_shift($emails);
        $attachments = $emailReader->getEmailAttachments($index);
        $firstAttachment = array_shift($attachments);
        $response = $this->getArrayFromCsvString($firstAttachment['attachment'], ';');

        $emailReader->close();
        if (empty($response)) {
            $error = 'No Data Found';
        }

        Log::info('Request finished', ['response'=>$response]);

        if ($error) {
            Log::warning('Request Error', ['error' => $error]);
        }

        return [$response, $error];
    }

    private function getArrayFromCsvString($csvString, $delimiter = ',')
    {
        $csvString = str_replace("\r\n", "\n", $csvString);
        $csvString = trim($csvString, "\n");
        $rowsArray = explode("\n", $csvString);
        $data = array_map(
            function ($row) use ($delimiter) {
                return str_getcsv($row, $delimiter);
            },
            $rowsArray
        );
        $header = array_map('strtolower', array_shift($data));
        $data = array_map(
            function ($row) use ($header) {
                return array_combine($header, $row);
            },
            $data
        );

        return $data;
    }
}
