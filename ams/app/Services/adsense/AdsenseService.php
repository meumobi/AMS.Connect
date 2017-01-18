<?php

namespace App\Services\adsense;

use App\Services\AMSService;
use App\Services\AMSServiceInterface;

require('config.php');

class AdsenseService extends AMSService implements AMSServiceInterface
{

    public function __construct()
    {
        $this->presenter = new AdsensePresenter;
        error_log('AdsenseService constructed');
    }

    public function perform()
    {
        error_log('AdsenseService Performed');
        $data = config('AMS.provider');
        
        $this->presenter->present($data);
    }
}
