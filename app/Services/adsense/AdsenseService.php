<?php

namespace App\Services\adsense;

use App\Services\AMSService;
use App\Services\AMSServiceInterface;
use Google_Client;

require('config.php');

class AdsenseService extends AMSService implements AMSServiceInterface
{

    public function __construct()
    {
        parent::__construct();
        $this->presenter = new AdsensePresenter;
    }

    public function perform(array $params)
    {
        $configData = config('AMS.provider');

        $startDate = $this->getParameter($params, 'start')->format('Y-m-d');
        $endDate = $this->getParameter($params, 'end')->format('Y-m-d');

        $urlData = [
            'startDate' => $startDate,
            'endDate' => $endDate
        ];

        $url = $configData['url'] . '?' . http_build_query($urlData);
        
        list($response, $error) = $this->call($url, $configData['scope'], $configData['serviceAccountFile']);

        if ($error) {
            echo 'cURL Error :' . $error;
            return;
        }

        $this->presenter->present($response, $configData['date_format']);
    }

    protected function call($url, $scope, $serviceAccountFile)
    {
        putenv('GOOGLE_APPLICATION_CREDENTIALS='.$serviceAccountFile);
        $client = new Google_Client();
        $client->useApplicationDefaultCredentials();
        $client->addScope($scope);
        
        $httpClient = $client->authorize();
        $response = $httpClient->get($url);
        $error = null;
        return [$response->getBody()->getContents(), $error];
    }
}
