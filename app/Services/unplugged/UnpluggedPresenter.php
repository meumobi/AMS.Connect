<?php


namespace App\Services\unplugged;

use App\Services\AMSPresenter;
use App\Services\AMSPresenterInterface;
use Log;
use DateTime;
use ErrorException;

/*
Inspired by https://gist.github.com/jakebathman/4fb8c55b13272eee9c88
*/

class UnpluggedPresenter extends AMSPresenter implements AMSPresenterInterface
{
  
  public function __construct()
  {
    parent::__construct();
    $this->_dateFormat = 'Y-m-d';
  }
  
  public function present($data, $format, $mode = self::MODE_ECHO)
  {
    $this->_dateFormat = $format;
    
    // Passed a string, turn it into an array
    if (is_array($data) === false) {
      $data = json_decode($data, true);
    }
    
    
    $strTempFile = 'csvOutput' . date("U") . ".csv";
    $tempFile = fopen($strTempFile, "w+");
    Log::debug('Temporary file created', ['file'=>$strTempFile]);
    
    $firstLineKeys = false;
    $records = [];
    
    try {
      foreach ($data as $line) {
        $array = $this->mapping($line);
        $array += $this->getCorrelatedFields($array['key']);

        /*
          Check if marge OR revenu are not already provided on array (handle unplugged case)
        */
        if (!array_key_exists('marge', $array) || empty($array['marge'])) {
          //$array = array_merge($array, $this->getAdMarginFields($array));
          Log::debug('marge fields missing or empty, get value from AdMargin Table (AdMT)', ['key' => $array['key']]);
          $array = array_merge($array, $this->getAdMarginFields($array));
        } else {
          Log::debug('marge value provided on unplugged raw', ['key' => $array['key'] ,'marge' => $array['marge']]);
        }
        
        $array += $this->getUID($array['date'], $array['key']);
        $array += $this->getRevenuNet($array['marge'], $array['revenu']);
        
        if (empty($firstLineKeys)) {
          $firstLineKeys = array_keys($array);
          fputcsv($tempFile, $firstLineKeys);
          $firstLineKeys = array_flip($firstLineKeys);
        }
        
        array_push($records, $array);
        
        /*
        Using array_merge is important to maintain the 
        order of keys acording to the first element
        */
        fputcsv($tempFile, array_merge($firstLineKeys, $array));
      }
    } catch (ErrorException $exception) {
      if (strpos($exception->getMessage(), 'Undefined index:') !== false) {
        Log::error('Mapping Error, field does not exists', ['exception'=>$exception->getMessage(), $line]);
        echo 'Mapping Error, field does not exists '.$exception->getMessage();
        return false;
      }
      throw $exception;
    } finally {
      fclose($tempFile);
    }
    
    $this->presentAsMode($strTempFile, $records, $mode);
    
    // Delete the temp file
    unlink($strTempFile);
    Log::debug('Temporary file deleted', ['file'=>$strTempFile]);
  }
  
  private function mapping($line)
  {
    $array = array(
      "date" => $this->convertDate($line["date"]),
      "impressions reçues" => $line["impressions recues"],
      "key" => $line["key"],
      "inventaire" => $line["inventaire"],
      "annonceur" => empty($line["annonceur"]) ? "NA" : $line["annonceur"],
      "impressions envoyees" => $line["impressions envoyees"],
      "impressions prises" => $line["impressions prises"],
      "revenu" => floatval(str_replace(",",".",$line["revenu"])),
      "impressions facturables" => empty($line["impressions facturables"]) ? "NA" : $line["impressions facturables"],
      "campagne" => empty($line["campagne"]) ? "NA" : $line["campagne"],
      "marge" => $line["marge"]
    );
    
    return $array;
  }
}
