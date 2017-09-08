<?php

namespace App\Jobs;

use App\Jobs\Job;
use App\Services\AMSService;
use App\Services\sublime\SublimeService;
use DateTime;

use Log;

class DailyReportsPublishing extends Job
{
    
    protected $providerName, $mode;
    
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($providerName, $mode)
    {
        $this->providerName = $providerName;
        $this->mode = $mode;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $serviceHandler = AMSService::loadService($this->providerName);
        
        $params['start'] = (new DateTime)->modify('-1 day');
        $params['start']->setTime(0, 0, 0);
        $params['end'] = (new DateTime)->modify('-1 day');
        $params['end']->setTime(23, 59, 59);
        $params['mode'] = $this->mode;

        $serviceHandler->perform($params);
        sleep(5);
        
        //$this->provider->perform(["mode" => "preview"]);
        //$this->info('Jobs: handle');
        Log::info('Job Handle: ' . $this->providerName, []);
    }
}
