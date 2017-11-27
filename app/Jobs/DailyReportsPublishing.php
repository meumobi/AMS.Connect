<?php

namespace App\Jobs;

use App\Jobs\Job;
use App\Services\AMSService;
use App\Services\sublime\SublimeService;

use Log;

class DailyReportsPublishing extends Job
{
  
  protected $providerName, $mode, $date;
  
  /**
  * Create a new job instance.
  *
  * @return void
  */
  public function __construct($providerName, $mode, $date)
  {
    $this->providerName = $providerName;
    $this->mode = $mode;
    $this->date = $date;
  }
  
  /**
  * Execute the job.
  *
  * @return void
  */
  public function handle()
  {
    $serviceHandler = AMSService::loadService($this->providerName);
    
    if ($this->date) {
      $params['start'] = clone $this->date;
      $params['start'] = $params['start']->setTime(0, 0, 0);
      $params['end'] = clone $this->date;
      $params['end'] = $params['end']->setTime(23, 59, 59);
    }
    
    $params['mode'] = $this->mode;
    Log::info('Job Handle: ' . $this->providerName, [$this->mode, $this->date, $params]);
    
    $serviceHandler->perform($params);
    sleep(5);
  }
}
