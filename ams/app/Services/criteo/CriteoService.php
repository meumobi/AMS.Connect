<?php

namespace App\Services\criteo;

use App\Services\AMSService;
use App\Services\AMSServiceInterface;
use DateTime;

require('config.php');

class CriteoService extends AMSService implements AMSServiceInterface
{

    public function __construct()
    {
        $this->presenter = new CriteoPresenter;
        error_log('AdsenseService constructed');
    }

    public function perform(array $params)
    {
        $configData = config('AMS.provider');
      
        $beginDate = $this->getParameter($params, 'beginDate', (new DateTime())->format('Y-m-d'));
        $endDate = $this->getParameter($params, 'endDate', (new DateTime())->format('Y-m-d'));

        $urlData = [
            'apitoken' => $configData['token'],
            'begindate' => $beginDate,
            'enddate' => $endDate
        ];

        $url = $configData['url'] . '?' . http_build_query($urlData);
      
        list($response, $error) = $this->call($url);

        if ($error) {
            echo "cURL Error #:" . $error;
            return;
        }
        echo $response;
      //$this->presenter->present($response);
        
        error_log('AdsenseService Performed');
    }

    protected function call($url)
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
                CURLOPT_HTTPHEADER => ["cache-control: no-cache"],
            ]
        );
        $response = curl_exec($curl);
        $err = curl_error($curl);

        curl_close($curl);

        return [$response, $err];
    }
}
