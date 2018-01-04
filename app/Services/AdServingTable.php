<?php

namespace App\Services;

use Illuminate\Support\Facades\Storage;
use Log;

class AdServingTable
{
  
  private $_tableData;
  const FILE_NAME = "adserving.csv";  
  
  private function __construct()
  {
    $filePath = Storage::disk('public')->url(self::FILE_NAME);
    Log::info('Lines of AdservingTable: ' . count(file($filePath)));
    
    try {
      $csv = array_map('str_getcsv', file($filePath));
      $header = array_map('strtolower', array_shift($csv));
      $this->_tableData = array_reduce(
        $csv,
        function ($data, $row) use ($header) {
          $row = array_combine($header, $row);
          $tableKey = $row['key'] . array_shift($row);
          $data[$tableKey] = $row;
          return $data;
        },
        []
      );
      Log::debug('Adserving table initialized');
    } catch (ErrorException $exception) {
      Log::error('Can\'t initialize adserving table', ['exception'=>$exception->getMessage()]);
    } finally {} 
  }
  
  public function getRow($key)
  {
    if (isset($this->_tableData[$key])) {
      return $this->_tableData[$key];
    }
    
    return [];
  }
  
  public static function getInstance()
  {
    static $instance = null;
    if (null === $instance) {
      $instance = new static();
    }
    
    return $instance;
  }
  
  private function __clone()
  {
  }
  
  private function __wakeup()
  {
  }
}
