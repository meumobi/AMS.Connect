<?php

namespace App\Services\adsense;

use App\Services\AMSService;
use App\Services\AMSServiceInterface;

require('config.php');

class AdsenseService extends AMSService implements AMSServiceInterface
{

    public function __construct()
    {
        parent::__construct();
        $this->presenter = new AdsensePresenter;
    }

    public function perform(Array $params)
    {
        $data = config('AMS.provider');
        
        $this->presenter->present($data);
    }
}
