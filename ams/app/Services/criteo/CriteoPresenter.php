<?php


namespace App\Services\criteo;

use App\Services\AMSPresenterInterface;

class CriteoPresenter implements AMSPresenterInterface
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
