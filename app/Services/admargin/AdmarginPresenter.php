<?php

namespace App\Services\admargin;

use App\Services\AMSPresenter;
use App\Services\AMSPresenterInterface;
use Illuminate\Support\Facades\Storage;
use Log;
use DateTime;
use ErrorException;

/*
Inspired by https://gist.github.com/jakebathman/4fb8c55b13272eee9c88
*/
//TODO: Refactor
class AdmarginPresenter extends AMSPresenter implements AMSPresenterInterface
{
  
  public function __construct()
  {
    parent::__construct();
    $this->_dateFormat = 'Y-m-d';
  }
  
  public function present($data, $format, $echo = true){
    
  }
  
  public function format($data, $format, $toString = true)
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
      foreach ($data as $array) {
        //$array = $this->mapping($line);
        // $array = $this->addFields($array);
        
        if (empty($firstLineKeys)) {
          $firstLineKeys = array_keys($array);
          fputcsv($tempFile, $firstLineKeys);
          $firstLineKeys = array_flip($firstLineKeys);
        }
        
        /*
        Using array_merge is important to maintain the 
        order of keys acording to the first element
        */
        fputcsv($tempFile, array_merge($firstLineKeys, $array));
      }
    } catch (ErrorException $exception) {
      //Erasing the temp file when a error is catch
      unlink($strTempFile);
      if (strpos($exception->getMessage(), 'Undefined index:') !== false) {
        Log::error('Mapping Error, field does not exists', ['exception'=>$exception->getMessage()]);
        echo 'Mapping Error, field does not exists '.$exception->getMessage();                
        return false;
      }
      throw $exception;
    } finally {
      fclose($tempFile);
    }
    
    $fileData = file($strTempFile);
    array_shift($fileData);
    $formated = join("", $fileData);
    
    // Delete the temp file
    unlink($strTempFile);
    Log::debug('Temporary file deleted', ['file'=>$strTempFile]);
    
    return $formated;
  }
  
  private function mapping($line)
  {
    $array = array(
      "date" => $line['date'],
      "site" => $line['site'],
      "inventaire" => $line['inventaire'],
      "marge" => $line['taux de marge editeur'],
    );
    
    return $array;
  }
}
