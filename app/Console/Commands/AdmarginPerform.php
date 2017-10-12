<?php

namespace App\Console\Commands;

use App\Jobs\DailyReportsPublishing;
use DateTime;
use Illuminate\Console\Command;

class AdmarginPerform extends Command
{
    
  /**
    * The name and signature of the console command.
    *
    * @var string
    */
  protected $signature = 'admargin:perform {--date=}';

  /**
    * The console command description.
    *
    * @var string
    */
  protected $description = 'Update AdMargin repository from daily email inputs';

  private $providerName = "Admargin";

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
