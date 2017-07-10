<?php

namespace App\Services\criteo;

use App\Services\AMSService;
use App\Services\AMSServiceInterface;
use Log;
use DateTime;

require('config.php');

class CriteoService extends AMSService implements AMSServiceInterface
{

    public function __construct()
    {
        parent::__construct();
        $this->presenter = new CriteoPresenter;
    }

    public function perform(array $params)
    {
        $configData = config('AMS.provider');

        $startDate = $this->getParameter($params, 'start')->format('Y-m-d');
        $endDate = $this->getParameter($params, 'end')->format('Y-m-d');
        $mode = $this->getParameter($params, 'mode');

        $urlData = [
            'apitoken' => $configData['token'],
            'begindate' => $startDate,
            'enddate' => $endDate
        ];

        $url = $configData['url'] . '?' . http_build_query($urlData);

        list($response, $error) = $this->call($url);

        if ($error) {
            echo 'cURL Error :' . $error;
            return;
        }

        $this->presenter->present($response, $configData['date_format'], $mode);

        error_log('AdsenseService Performed');
    }

    protected function call($url)
    {
        Log::info('Initializing Request', ['url' => $url]);

        $curl = curl_init();
        curl_setopt_array(
            $curl,
            [
                CURLOPT_URL => $url,
                CURLOPT_HEADER => 0,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_TIMEOUT => 30,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => "GET",
                CURLOPT_HTTPHEADER => ["cache-control: no-cache"],
                CURLINFO_HEADER_OUT => true,
            ]
        );
        $response = curl_exec($curl);
        $errNum = curl_errno($curl);
        $err = $errNum
            ? '#'.$errNum.' => '.curl_error($curl)
            : null;
        $curlInfo = curl_getinfo($curl);
        curl_close($curl);
        
        $requestData = [
            'headers' => $curlInfo['request_header'],
            'requestTime' => $curlInfo['total_time'],
            'requestSize' => $curlInfo['request_size'],
            'httpCode' => $curlInfo['http_code'],
        ];
        Log::info('Request finished', $requestData);

        if ($err) {
            Log::warning('Request Error', ['error' => $err]);
        }

        return [$response, $err];
    }
}
