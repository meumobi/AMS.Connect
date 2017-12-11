<?php

namespace App\Services\adsense;

use App\Services\AMSService;
use App\Services\AMSServiceInterface;
use Google_Client;
use Log;

require('config.php');

class AdsenseService extends AMSService implements AMSServiceInterface {
  
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
    $mode = $this->getParameter($params, 'mode');
    
    $urlData = [
      'startDate' => $startDate,
      'endDate' => $endDate,
      'useTimezoneReporting' => 'true',
      'dimension'=> [
        'AD_UNIT_CODE', 'DATE'
      ],
      'metric' => [
        'AD_REQUESTS', 'MATCHED_AD_REQUESTS', 'EARNINGS', 'AD_REQUESTS_RPM'
      ],
      'fields' => 'averages,endDate,headers,kind,rows,startDate,totalMatchedRows,totals,warnings'
    ];
    
    $url = $this->createUrl($configData['url'], $urlData);
    
    //To call Google Oath to generate the access and refresh tokens
    if ($this->getParameter($params, 'callGoogleOAuth')) {
      $this->callLoginUrl();
    }
    
    list($response, $error) = $this->call($url);
    
    if ($error) {
      Log::warning('Request Error', ['error' => $error]);
      return;
    }
    
    $this->presenter->present($response, $configData['date_format'], $mode);

    Log::debug(ucfirst($configData['name']) . ' Service Performed');
  }
  
  protected function call($url)
  {
    $client = $this->getGoogleClient();
    $token = config('AMS.provider.token');
    
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
    
    //Manipulate data keys
    $data = [];
    if (!$error) {
      $keys = array_reduce(
        $response['headers'],
        function ($keys, $item) {
          $keys[] = $item['name'];
          return $keys;
        },
        []
      );
      $data = array_map(
        function ($row) use ($keys) {
          return array_combine($keys, $row);
        },
        $response['rows']
      );
    }
    
    return [$data, $error];
  }
  
  protected function callLoginUrl()
  {
    $client = $this->getGoogleClient();
    $authUrl = $client->createAuthUrl();
    header('Location: ' . filter_var($authUrl, FILTER_SANITIZE_URL));
  }
  
  public function getGoogleClient()
  {
    $scope = config('AMS.provider.scope');
    $serviceAccountFile =  config('AMS.provider.serviceAccountFile');
    $client = new Google_Client();
    $client->setAuthConfig($serviceAccountFile);
    $client->addScope($scope);
    $client->setRedirectUri(config('AMS.provider.redirectUri'));
    $client->setAccessType('offline');
    $client->setApprovalPrompt('force');
    
    return $client;
  }
  
  protected function createUrl($url, $urlData)
  {
    $url .= '?';
    
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
        continue;
      }
      $url .= '&'.http_build_query([$key => $data]);
    }
    
    return $url;
  }
}
