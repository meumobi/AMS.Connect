<?php


namespace App\Services\adsense;

use App\Services\AMSPresenter;
use App\Services\AMSPresenterInterface;
use Log;
use DateTime;
use ErrorException;

class AdsensePresenter extends AMSPresenter implements AMSPresenterInterface
{

    public function __construct()
    {
        parent::__construct();
    }

    public function present($data)
    {
        error_log('AdsensePresenter Presented');
        echo json_encode($data);
    }
}
