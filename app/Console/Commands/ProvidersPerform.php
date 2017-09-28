<?php

namespace App\Console\Commands;

use App\Jobs\DailyReportsPublishing;
use DateTime;
use Illuminate\Console\Command;

class ProvidersPerform extends Command
{
    
  /**
    * The name and signature of the console command.
    *
    * @var string
    */
  protected $signature = 'providers:perform {provider?} {--update-adserving} {--mode=console} {--date=}';

  /**
    * The console command description.
    *
    * @var string
    */
  protected $description = 'Command description';

  private $providers = ["Rubicon", "Sublime", "Adsense", "Adtech", "Criteo", "Unplugged"];
  //private $providers = ["Rubicon"];

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
    $mode = $this->option('mode');
    $date = $this->option('date')
    ? (new DateTime)->createFromFormat('Y-m-d', $this->option('date'))
    : (new DateTime)->modify('-1 day');

    if ($updateAdserving) {
      dispatch(new DailyReportsPublishing('Adserving', $mode, $date));
    }

    if ($providerName) {
      $this->info('Commands: Perform ' . $providerName);
      dispatch(new DailyReportsPublishing($providerName, $mode, $date));
    } else {
      foreach ($this->providers as $providerName) {
        $this->info('Commands: Perform ' . $providerName);
        dispatch(new DailyReportsPublishing($providerName, $mode, $date));
      }
    }
  }
}
