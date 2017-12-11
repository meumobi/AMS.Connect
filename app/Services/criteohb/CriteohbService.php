<?php

namespace App\Services\criteohb;

use App\Services\criteo\CriteoService;

class CriteohbService extends CriteoService 
{
    public function __construct()
    {     
        parent::__construct();
        require('config.php');
        $this->presenter = new CriteohbPresenter;
    }
}
