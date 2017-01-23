<?php

namespace App\Services\rubicon;

use App\Services\AMSService;
use App\Services\AMSServiceInterface;
use DateTime;

require('config.php');

class RubiconService extends AMSService implements AMSServiceInterface
{

    public function __construct()
    {
        $this->presenter = new RubiconPresenter;
        error_log('AdsenseService constructed');
    }

    public function perform(array $params)
    {
        $configData = config('AMS.provider');
      
        $beginDate = $this->getParameter($params, 'beginDate', (new DateTime())->modify('-1 day')->format(DATE_W3C)); // 2016-10-25T23:59:59-0700
        $endDate = $this->getParameter($params, 'endDate', (new DateTime())->format(DATE_W3C));

		$urlData = [
            'start' => $beginDate,
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

		/*
		curl_setopt_array($curl, array(
		  CURLOPT_URL => "https://api.rubiconproject.com/analytics/v1/report/?account=publisher%2F14794&end=2016-10-25T23%3A59%3A59-07%3A00&dimensions=zone%2Csize%2Csize_id%2Csite_id%2Czone_id&metrics=revenue%2Cpaid_impression&start=2016-10-25T00%3A00%3A00-07%3A00",
		  CURLOPT_RETURNTRANSFER => true,
		  CURLOPT_ENCODING => "",
		  CURLOPT_MAXREDIRS => 10,
		  CURLOPT_TIMEOUT => 30,
		  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
		  CURLOPT_CUSTOMREQUEST => "GET",
		  CURLOPT_HTTPHEADER => array(
		    "accept: application/json",
		    "authorization: Basic MTEzNzgxOTdiYmQzYmVjNGIyMzIxNWEzMjE4NmQwNzliY2QyNGYzYTozMzUzYmJiMDNkNWQzNGU2NWUwZDk0YjliNmRjMGM3Zg==",
		    "cache-control: no-cache",
		    "postman-token: d1a33694-7b54-98f9-a40a-bd2b90c07de6"
		  ),
		));
		*/
		
	
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
					//"Authorization: Basic MTEzNzgxOTdiYmQzYmVjNGIyMzIxNWEzMjE4NmQwNzliY2QyNGYzYTozMzUzYmJiMDNkNWQzNGU2NWUwZDk0YjliNmRjMGM3Zg=="
				],
            ]
		);
		
        
        $response = curl_exec($curl);
        $err = curl_error($curl);

        curl_close($curl);

        return [$response, $err];
    }
}
