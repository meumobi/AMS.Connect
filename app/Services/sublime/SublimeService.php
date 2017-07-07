<?php

namespace App\Services\sublime;

use App\Services\AMSService;
use App\Services\AMSServiceInterface;
use Log;
use DateTime;

require('config.php');

class SublimeService extends AMSService implements AMSServiceInterface
{

    public function __construct()
    {
        parent::__construct();
        $this->presenter = new SublimePresenter;
    }

    public function perform(array $params)
    {
        $configData = config('AMS.provider');
      
        $startDate = $this->getParameter($params, 'start');
        $endDate = $this->getParameter($params, 'end');
        $mode = $this->getParameter($params, 'mode');

        $timestamp = time();
        $urlData = [
            'api-key' => base64_encode($configData['apiKey']),
            'timestamp' => $timestamp,
            'hash' => base64_encode(password_hash($timestamp . $configData['apiSecret'], PASSWORD_BCRYPT))
        ];

        list($responses, $errors) = $this->batchCall($configData['url'], http_build_query($urlData), $startDate, $endDate);
        if (!empty($errors)) {
            echo 'cURL Error :' . $errors[0];
            return;
        }

        $this->presenter->present($responses, $configData['date_format'], $mode);
    }

    protected function batchCall($url, $queryString, $startDate, $endDate)
    {
        $basicCurlOptions = [
            CURLOPT_HEADER => 0,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "GET",
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTPHEADER => [
                "cache-control: no-cache",
                "Accept: application/json",
            ],
            CURLINFO_HEADER_OUT => true,
        ];
        $arrCurl = [];
        $responses = [];
        $errors = [];
        $running = null;
        $multiCurl = curl_multi_init();
        while ($endDate > $startDate) {
            $requestUrl = $url.$startDate->format('Y-m-d').'?'.$queryString;
            $curl = curl_init($requestUrl);
            curl_setopt_array($curl, $basicCurlOptions);
            $arrCurl[$startDate->format('Y-m-d')] = $curl;
            curl_multi_add_handle($multiCurl, $curl);
            Log::info('Request for '.$startDate->format('Y-m-d').' enqueued', ['url'=>$requestUrl]);
            $startDate->modify('+ 1 day');
        }

        do {
            curl_multi_exec($multiCurl, $running);
        } while ($running > 0);

        foreach ($arrCurl as $date => $curl) {
            $response = json_decode(curl_multi_getcontent($curl), true);
            
            $errNum = curl_errno($curl);
            $err = $errNum
                ? '#'.$errNum.' => '.curl_error($curl)
                : isset($response['error'])
                    ? $response['error']
                    : null;
            $curlInfo = curl_getinfo($curl);
        
            $requestData = [
                'headers' => $curlInfo['request_header'],
                'requestTime' => $curlInfo['total_time'],
                'requestSize' => $curlInfo['request_size'],
                'httpCode' => $curlInfo['http_code'],
            ];
            Log::info('Request finished', $requestData);

            if ($err) {
                Log::warning('Request Error', ['error' => $err]);
                $errors[] = $err;
            } else {
                $response = $this->addDateField($response, $date);
                $responses = array_merge($responses, $response);
            }

            curl_multi_remove_handle($multiCurl, $curl);
            curl_close($curl);
        }

        curl_multi_close($multiCurl);

        return [$responses, $errors];
    }

    private function addDateField($items, $date)
    {
        return array_map(
            function ($item) use ($date) {
                $item['date'] = $date;
                return $item;
            },
            $items
        );
    }
}
