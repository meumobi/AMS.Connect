<?php

namespace App\Services\rubicon;

use App\Services\AMSService;
use App\Services\AMSServiceInterface;
use Log;
use DateTime;

require('config.php');

class RubiconService extends AMSService implements AMSServiceInterface
{

    public function __construct()
    {
        parent::__construct();
        $this->presenter = new RubiconPresenter;
    }

    public function perform(array $params)
    {
        $configData = config('AMS.provider');
      
        $startDate = $this->getParameter($params, 'start')
            ? (new DateTime())->createFromFormat('Y-m-d', $this->getParameter($params, 'start'))
                 ->setTime(0, 0, 0)->format(DATE_W3C)
            : (new DateTime())->modify('-1 day')
                ->setTime(0, 0, 0)->format(DATE_W3C);
        $endDate = $this->getParameter($params, 'end')
            ? (new DateTime())->createFromFormat('Y-m-d', $this->getParameter($params, 'end'))
                 ->setTime(23, 59, 59)->format(DATE_W3C)
            : (new DateTime())->modify('-1 day')
                ->setTime(23, 59, 59)->format(DATE_W3C);

        $urlData = [
            'start' => $startDate,
            'end' => $endDate,
            'account' => 'publisher/14794',
            'dimensions' => 'date,site,site_id,zone,zone_id,size,size_id',
            'metrics' => 'ecpm,revenue,impressions,paid_impression'
        ];
        
        $url = $configData['url'] . '?' . http_build_query($urlData);
      
        list($response, $error) = $this->call($url, $configData['username'], $configData['password']);

        if ($error) {
            echo "cURL Error #:" . $error;
            return;
        }

        $this->presenter->present($response, $configData['date_format']);
        
        error_log('AdsenseService Performed');
    }

    protected function call($url, $user, $pass)
    {
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
                CURLOPT_HTTPHEADER => [
                    "cache-control: no-cache",
                    "Accept: application/json",
                    "Authorization: Basic " . base64_encode($user . ":" . $pass)
                ],
                CURLINFO_HEADER_OUT => true,
            ]
        );
        
        
        $response = curl_exec($curl);
        $err = curl_error($curl);
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
