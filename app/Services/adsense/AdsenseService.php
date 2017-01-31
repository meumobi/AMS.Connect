<?php

namespace App\Services\adsense;

use App\Services\AMSService;
use App\Services\AMSServiceInterface;
use Google_Client;

require('config.php');

//TODO: Refactoring
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
            'endDate' => $endDate,
            'dimension'=> [
                'AD_CLIENT_ID', 'AD_UNIT_ID', 'AD_UNIT_NAME', 'DATE'
            ],
            'metric' => [
                'AD_REQUESTS', 'CLICKS', 'EARNINGS',
                'INDIVIDUAL_AD_IMPRESSIONS', 'INDIVIDUAL_AD_IMPRESSIONS_CTR', 'INDIVIDUAL_AD_IMPRESSIONS_RPM',
                'PAGE_VIEWS', 'PAGE_VIEWS_CTR', 'PAGE_VIEWS_RPM'
            ],
            'fields' => 'averages,endDate,headers,kind,rows,startDate,totalMatchedRows,totals,warnings'
        ];
        
        $url = $configData['url'] . '?';

        foreach ($urlData as $key => $data) {
            if (is_array($data)) {
                $url = array_reduce(
                    $data,
                    function ($url, $item) use ($key) {
                        $url .= '&'.http_build_query([$key => $item]);
                        return $url;
                    },
                    $url
                );
            } else {
                $url .= '&'.http_build_query([$key => $data]);
            }
        }

        list($response, $error) = $this->call($url);

        // list($response, $error) = $this->callLoginUrl($url);

        if ($error) {
            echo 'Request Error :' . $error;
            return;
        }

        $this->presenter->present($response, $configData['date_format']);
    }

    protected function call($url)
    {
        $configData = config('AMS.provider');
        $token = $configData['token'];
        $scope = $configData['scope'];
        $serviceAccountFile =  $configData['serviceAccountFile'];

        $client = new Google_Client();
        $client->setAuthConfig($serviceAccountFile);
        $client->addScope($scope);
        $client->setRedirectUri((!empty($_SERVER['HTTPS'])? 'https://' : 'http://') . $_SERVER['HTTP_HOST'].'/oauth/adsense');
        $client->setAccessType('offline');
        $client->setApprovalPrompt('force');
        $authUrl = $client->createAuthUrl();
        $client->setAccessToken($token);
        if ($client->isAccessTokenExpired()) {
            $client->refreshToken($token['refresh_token']);
        }

        $httpClient = $client->authorize();
        $response = $httpClient->get($url);
        $response = json_decode($response->getBody()->getContents(), true);

        //TODO: Handle the response errors;
        $error = isset($response['error'])
            ? $response['error']['message']
            : null;
        return [$response, $error];
    }

    protected function callLoginUrl($url)
    {
        $scope = config('AMS.provider.scope');
        $serviceAccountFile =  config('AMS.provider.serviceAccountFile');
        $client = new Google_Client();
        $client->setAuthConfig($serviceAccountFile);
        $client->addScope($scope);
        $client->setRedirectUri((!empty($_SERVER['HTTPS'])? 'https://' : 'http://') . $_SERVER['HTTP_HOST'].'/oauth/adsense');
        $client->setAccessType('offline');
        $client->setApprovalPrompt('force');
        $authUrl = $client->createAuthUrl();
        header('Location: ' . filter_var($authUrl, FILTER_SANITIZE_URL));
    }
}
