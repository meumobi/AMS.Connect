<?php

namespace App\Services;

use ErrorException;
use Log;

class AMSService
{
  public function __construct()
  {
    Log::debug('Service initialized: '.get_class($this));
  }
  
  public static function loadService($providerName)
  {
    $providerName = strtolower($providerName);
    $class = 'App\\Services\\'.$providerName. '\\' . ucfirst($providerName).'Service';
    if (class_exists($class)) {
      $instance =  new $class;
      return $instance;
    }
    Log::warning('Class '. $class .' Not Found', ['class'=>$class]);
    return null;
  }
  
  protected function getParameter($params, $key, $defaultValue = null)
  {
    return (isset($params[$key]) && $params[$key])
    ? $params[$key]
    : $defaultValue;
  }
  
  protected function getArrayFromCsvString($csvString, $delimiter = ',')
  {
    $data = [];
    
    try {
      $csvString = trim($csvString, "\r\n");
      $rowsArray = explode("\n", $csvString);
      
      $data = array_map(
        function ($row) use ($delimiter) {
          return str_getcsv($row, $delimiter);
        },
        $rowsArray
      );
      
      /*
      Set internal encoding of mb to utf-8
      to convert string that contains special characters
      http://stackoverflow.com/a/2516482
      */
      mb_internal_encoding('UTF-8');
      $header = array_map('mb_strtolower', array_shift($data));
      
      $data = array_map(
        function ($row) use ($header) {
          return array_combine($header, $row);
        },
        $data
      );
    } catch (ErrorException $exception) {
      Log::error('Can\'t convert csv to array', ['exception'=>$exception->getMessage()]);
      /*
        Return empty array if an error occurred
      */
      $data = [];
    } finally {
      return $data;
    }    
  }
}
