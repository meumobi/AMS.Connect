<?php

namespace App\Services;

use Log;

class AMSPresenter
{
    protected $_dateFormat;
    
    public function __construct()
    {
        Log::info('Presenter initialized: '.get_class($this));
    }
}
