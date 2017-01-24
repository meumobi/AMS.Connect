<?php

namespace App\Services\sublime;

use App\Services\AMSService;
use App\Services\AMSServiceInterface;
use DateTime;

require('config.php');

class SublimeService extends AMSService implements AMSServiceInterface
{

    public function __construct()
    {
        $this->presenter = new SublimePresenter;
        error_log('AdsenseService constructed');
    }

    public function perform(array $params)
    {
        $configData = config('AMS.provider');
      
        $date = $this->getParameter($params, 'start', (new DateTime())->modify('-1 day')->format('Y-m-d'));

		$timestamp = time();
		$urlData = [
            'api-key' => base64_encode($configData['apiKey']),
			'timestamp' => $timestamp,
			'hash' => base64_encode(password_hash($timestamp . $configData['apiSecret'], PASSWORD_BCRYPT))
        ];

        $url = $configData['url'] . $date . '?' . http_build_query($urlData);
      
	list($response, $error) = $this->call($url);

        if ($error) {
            echo "cURL Error #:" . $error;
            return;
        }

	echo $response;
	//$this->presenter->present($response, $configData['date_format']);
        
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
				CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTPHEADER => [
					"cache-control: no-cache",
					"Accept: application/json",
				],
            ]
		);
		      
        $response = curl_exec($curl);
        $err = curl_error($curl);

        curl_close($curl);

        return [$response, $err];
    }
}
