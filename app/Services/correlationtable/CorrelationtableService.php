<?php

namespace App\Services\correlationtable;

use App\Services\AMSService;
use App\Services\AMSServiceInterface;
use Illuminate\Support\Facades\Storage;
use Log;

require('config.php');

class CorrelationtableService extends AMSService implements AMSServiceInterface
{
    public function __construct()
    {
        parent::__construct();
    }

    public function perform(array $params)
    {
        $configData = config('AMS.provider');

        list($response, $error) = $this->call($configData['tdc_url']);
        if ($error) {
            echo 'Request Error :' . $error;
            return;
        }

        $filePath = Storage::disk('public')->url($configData['file_name']);
        Log::info('File Path of Correlation Table file: ' . $filePath);    

        $tdcFile = fopen($filePath, 'w+');

        //fputcsv($tdcFile, $response);
        fwrite($tdcFile, $response);
        fclose($tdcFile);
        Log::info('Lines of Correlation Table: ' . count(file($filePath)));

        error_log('CorrelationTableService Performed');
    }

    protected function callStub($url)
    {
        $response = [];
        $error = false;

        $configData = config('AMS.provider');
        
        /*
            From browser path=examples/...
            From cli path=public/examples/...
        */
        $strTempFile = "public/examples/" . $configData['file_name'];
        $response = file_get_contents($strTempFile);
        //$response = json_decode($data, true);

        Log::info('Request finished', ['response'=>$response]);

        return [$response, $error];
    }

    protected function call($url)
    {
        Log::info('Initializing Request', ['url' => $url]);

        $response = null;
        $error = false;

        $response = file_get_contents($url);

        if (empty($response)) {
            $error = 'No Data Found';
        }

        Log::info('Request finished', ['response'=>$response]);

        return [$response, $error];
    }
}
