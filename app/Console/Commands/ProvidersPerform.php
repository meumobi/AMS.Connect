<?php

namespace App\Console\Commands;

use App\Jobs\DailyReportsPublishing;
use DateTime;
use Illuminate\Console\Command;
use Log;

class ProvidersPerform extends Command
{
    
  /**
    * The name and signature of the console command.
    *
    * @var string
    */
  protected $signature = 'providers:perform {provider?} {--update-correlationtable} {--update-adserving} {--update-admargin} {--mode=console} {--date=}';

  /**
    * The console command description.
    *
    * @var string
    */
  protected $description = 'Connect to providers to fetch reports and save them on AMS db';

  private $providers = ["Rubicon", "Sublime", "Adsense", "Adtech", "Criteowf", "Criteohb" , "Unplugged"];

  /**
    * Create a new command instance.
    *
    * @return void
    */
  public function __construct()
  {
      parent::__construct();
      
  }

  /**
    * Execute the console command.
    *
    * @return mixed
    */
  public function handle()
  {
    $providerName = $this->argument('provider');
    $updateAdserving = $this->option('update-adserving');
    $updateAdmargin = $this->option('update-admargin');
    $updateCorrelationtable = $this->option('update-correlationtable');
    $mode = $this->option('mode');
    $date = $this->option('date')
    ? (new DateTime)->createFromFormat('Y-m-d', $this->option('date'))
    : (new DateTime)->modify('-1 day');

    if ($updateAdserving) {
      dispatch(new DailyReportsPublishing('Adserving', $mode, $date));
    }

    if ($updateAdmargin) {
      dispatch(new DailyReportsPublishing('Admargin', $mode, $date));
    }

    if ($updateCorrelationtable) {
      dispatch(new DailyReportsPublishing('Correlationtable', $mode, $date));
    }

    if ($providerName) {
      dispatch(new DailyReportsPublishing($providerName, $mode, $date));
    } else {
      foreach ($this->providers as $providerName) {
        dispatch(new DailyReportsPublishing($providerName, $mode, $date));
      }
    }
  }
}
