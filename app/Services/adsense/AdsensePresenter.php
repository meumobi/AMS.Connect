<?php


namespace App\Services\adsense;

use App\Services\AMSPresenterInterface;

class AdsensePresenter implements AMSPresenterInterface
{

    public function __construct()
    {
        error_log('AdsensePresenter Constructed');
    }

    public function present($data)
    {
        error_log('AdsensePresenter Presented');
        echo json_encode($data);
    }
}
