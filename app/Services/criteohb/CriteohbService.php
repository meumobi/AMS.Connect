<?php

namespace App\Services\criteohb;

use App\Services\criteowf\CriteowfService;

class CriteohbService extends CriteowfService 
{
    public function __construct()
    {     
        parent::__construct();
        require('config.php');
        $this->presenter = new CriteohbPresenter;
    }
}
