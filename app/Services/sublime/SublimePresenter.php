<?php


namespace App\Services\sublime;

use App\Services\AMSPresenter;
use App\Services\AMSPresenterInterface;
use DateTime;
use ErrorException;
use Log;

/*
Inspired by https://gist.github.com/jakebathman/4fb8c55b13272eee9c88
*/

class SublimePresenter extends AMSPresenter implements AMSPresenterInterface
{
  
  public function __construct()
  {
    $this->_dateFormat = 'Y-m-d';
    parent::__construct();
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
        $array = $this->addFields($array);
        
        /*
        overwrite fillRate for sublime, impressions reçues = impressions envoyées
        */
        
        if (!array_key_exists('impressions envoyees', $array)) {
          $array['impressions envoyees'] = 'NA';
        }
        $array['impressions reçues'] = $array['impressions envoyees'];
        //$extraRow = $this->getFillRate($array['impressions envoyees'], $array['impressions prises']);
        //$array['fillRate'] = $extraRow['fillRate'];
        
        
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
        Log::error('Mapping Error, field does not exists', ['exception'=>$exception->getTraceAsString()]);
        echo 'Mapping Error, field does not exists '.$exception->getMessage();
        return false;
      }
      unlink($strTempFile);
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
    $configData = config('AMS.provider');

    $array = array(
      "date" => $this->convertDate($line["date"]),
      "impressions reçues" => "NA",
      "impressions prises" => $line["impr"],
      "revenu" => $line["rev"],
      //"revenu" => 0.85 * (float)$line["revenue"],
      "key" => $line["zone"],
      'inventaire' => $configData['inventaire'],
      "partenaire" => ucfirst($configData['name'])
    );
    
    return $array;
  }
}