<?php

namespace App\Services;

use Log;

class AMSPresenter
{
    public function __construct()
    {
        Log::info('Presenter initialized: '.get_class($this));
    }


}
