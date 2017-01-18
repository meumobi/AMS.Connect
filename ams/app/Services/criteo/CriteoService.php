<?php

namespace App\Services\criteo;

use App\Services\AMSService;
use App\Services\AMSServiceInterface;

require('config.php');

class CriteoService extends AMSService implements AMSServiceInterface
{

    public function __construct()
    {
        $this->presenter = new CriteoPresenter;
        error_log('AdsenseService constructed');
    }

    public function perform()
    {
			$url = "https://publishers.criteo.com/api/2.0/stats.json?apitoken=e36080a1-8f8b-44fb-9aef-1457f4223355&begindate=2017-01-17&enddate=2017-01-17";
			$curl = curl_init($url);
			echo 'Perform Request';
			
			curl_setopt($curl, CURLOPT_HEADER, 0);
			curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);

			/*
			curl_setopt_array($curl, array(
			  CURLOPT_URL => "https://publishers.criteo.com/api/2.0/stats.json?apitoken=e36080a1-8f8b-44fb-9aef-1457f4223355&begindate=2017-01-17&enddate=2017-01-17",
			  CURLOPT_RETURNTRANSFER => true,
			  CURLOPT_ENCODING => "",
			  CURLOPT_MAXREDIRS => 10,
			  CURLOPT_TIMEOUT => 30,
			  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
			  CURLOPT_CUSTOMREQUEST => "GET",
				CURLOPT_SSL_VERIFYHOST => 2,
				CURLOPT_SSL_VERIFYPEER => true,
			  CURLOPT_HTTPHEADER => array(
			    "cache-control: no-cache"
			  ),
			));
			*/

			$response = curl_exec($curl);
			$err = curl_error($curl);

			curl_close($curl);

			if ($err) {
			  echo "cURL Error #:" . $err;
			} else {
			  $this->presenter->present($response);
			}
				
				error_log('AdsenseService Performed');
        $data = config('AMS.provider');        
    }
}
