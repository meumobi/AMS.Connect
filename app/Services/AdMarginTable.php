<?php

namespace App\Services;

use ErrorException;
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
    Log::debug('AdMarginTable initialized');
  }
  
  public function getRow($key)
  {
    $row = array();

    if (isset($this->_tableData[$key])) {
      $row = $this->_tableData[$key];
    }
    
    Log::debug('AdMarginTable, getRow: ', $row);

    return $row;
  }

  public function getRevenuNetRow($margin, $revenue)
  {
    $row = array();
    Log::debug('Get \'revenu net\' row ', [
      'margin' => $margin, 
      'revenu' => $revenue,
      'Margin is numeric: ' => is_numeric(substr($margin, 0, -1)),
      'Revenue is numeric: ' => is_numeric($revenue)
      ]
    );
    try {
      if (is_numeric($revenue) && is_numeric(substr($margin, 0, -1))) {
        $row['revenu net'] = (float)$margin / 100 * $revenue;
      } else {
        $row['revenu net'] = 'Unknown';
      }
    } catch (ErrorException $exception) {
      Log::error('Compute \'revenu net\' field error', ['exception' => $exception->getMessage()]);
      $row['revenu net'] = 'Unknown';
    } finally {

      Log::debug('Get \'revenu net\' row ', $row);
      return $row;
    }
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
