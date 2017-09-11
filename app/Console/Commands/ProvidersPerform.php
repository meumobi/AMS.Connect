<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Jobs\DailyReportsPublishing;

class ProvidersPerform extends Command
{
    
  /**
    * The name and signature of the console command.
    *
    * @var string
    */
  protected $signature = 'providers:perform {provider?} {--mode=console}';

  /**
    * The console command description.
    *
    * @var string
    */
  protected $description = 'Command description';

  private $providers = ["Adserving", "Rubicon", "Adsense", "Adtech", "Criteo", "Unplugged"];
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
    $mode = $this->option('mode');

    if ($providerName) {
      $this->info('Commands: Perform ' . $providerName);
      dispatch(new DailyReportsPublishing($providerName, $mode));
    } else {
      foreach ($this->providers as $providerName) {
        $this->info('Commands: Perform ' . $providerName);
        dispatch(new DailyReportsPublishing($providerName, $mode));
      }
    }
  }
}
