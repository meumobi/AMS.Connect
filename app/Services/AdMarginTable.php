<?php

namespace App\Services;

use Illuminate\Support\Facades\Storage;
use Log;

class AdMarginTable
{
  
  private $_tableData;
  const FILE_NAME = "admargin.csv";  
  
  private function __construct()
  {
    $filePath = Storage::disk('public')->url(self::FILE_NAME);
    Log::info('Lines of AdMarginTable: ' . count(file($filePath)));
    
    $csv = array_map('str_getcsv', file($filePath));
    $header = array_map('strtolower', array_shift($csv));
    $this->_tableData = array_reduce(
      $csv,
      function ($data, $row) use ($header) {
        $row = array_combine($header, $row);
        $tableKey = $row['site'] . $row['inventaire'] . $row['date'];
        $data[$tableKey] = array('marge' => $row['taux de marge editeur']);
        return $data;
      },
      []
    );
    Log::info('AdMarginTable initialized');
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