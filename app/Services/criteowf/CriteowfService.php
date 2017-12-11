<?php

namespace App\Services\criteowf;

use App\Services\criteo\CriteoService;

class CriteowfService extends CriteoService 
{
    public function __construct()
    {     
        parent::__construct();
        require('config.php');
        $this->presenter = new CriteowfPresenter;
    }
}
