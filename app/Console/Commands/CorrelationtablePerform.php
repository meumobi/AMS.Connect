<?php

namespace App\Console\Commands;

use App\Jobs\DailyReportsPublishing;
use DateTime;
use Illuminate\Console\Command;

class CorrelationtablePerform extends Command
{
    
  /**
    * The name and signature of the console command.
    *
    * @var string
    */
  protected $signature = 'correlationtable:perform';

  /**
    * The console command description.
    *
    * @var string
    */
  protected $description = 'Update Correlation Table repository';

  private $providerName = "Correlationtable";

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
    dispatch(new DailyReportsPublishing($this->providerName, null, null));
  }
}
