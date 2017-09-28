<?php

namespace App\Console\Commands;

use App\Jobs\DailyReportsPublishing;
use DateTime;
use Illuminate\Console\Command;

class AdservingPerform extends Command
{
    
  /**
    * The name and signature of the console command.
    *
    * @var string
    */
  protected $signature = 'adserving:perform {--date=}';

  /**
    * The console command description.
    *
    * @var string
    */
  protected $description = 'Command description';

  private $providerName = "Adserving";

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
    $date = $this->option('date')
    ? (new DateTime)->createFromFormat('Y-m-d', $this->option('date'))
    : (new DateTime)->modify('-1 day');

    dispatch(new DailyReportsPublishing($this->providerName, null, $date));
  }
}
