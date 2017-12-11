<?php


namespace App\Services\adtech;

use App\Services\AMSPresenter;
use App\Services\AMSPresenterInterface;
use Log;
use DateTime;
use ErrorException;

/*
Inspired by https://gist.github.com/jakebathman/4fb8c55b13272eee9c88
*/

class AdtechPresenter extends AMSPresenter implements AMSPresenterInterface
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
        $array += $this->getAdMarginFields($array);
        $array += $this->getUID($array['date'], $array['key']);
        
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
    $configData = config('AMS.provider');

    $array = array(
      "date" => $this->convertDate($line["par jour"]),
      "impressions reçues" => "ND",
      "key" => $line["emplacement"],
      "site" => $line["site web"],
      "emplacement" => $line["emplacement"],
      "position" => $line["position de l'emplacement"],
      "format" => $line["banner size to report"],
      "annonceur" => $line["annonceur"],
      "impressions envoyees" => $this->getImprEnvoyees($line),
      "impressions facturables" => $this->getImprFacturables($line), 
      "campagne" => $line["flight description"],
      "inventaire" => $configData['inventaire'],
      "partenaire" => ucfirst($configData['name'])
    );
    
    $array += $this->getImprPrises(
      $line["campaign flat fee"],
      $array["impressions envoyees"],
      $array["impressions facturables"]
    );
    
    $array += $this->getRevenu(
      $line["campaign flat fee"],
      $array["impressions prises"],
      $array["cpm"]);
      
      return $array;
    }
    
    private function getImprPrises($fee, $envoyees, $facturables)
    {   
      $row = [];
      $val = "FORFAIT";
      
      if (intval($fee) == 0) {
        if ($facturables == 0) {
          // ex on stub: 2017-03-24_AllezTFC_300x250_geoloc_ATF
          $val = $envoyees;
        } elseif ($envoyees > $facturables) {
          // ex on stub: 2017-03-24_RG_1997Media_300x600-ATF
          $val = $facturables;
        } elseif ($envoyees < $facturables) {
          // ex on stub: 2017-03-24_AllezLyon_320x50_MOB_ATF
          $val = $envoyees;
        }
      }
      
      $row["impressions prises"] = $val;
      
      return $row;
    }
    
    private function getImprEnvoyees($line)
    {   
      $data = "FORFAIT";
      
      if (intval($line["campaign flat fee"]) == 0) {
        $data = intval(preg_replace('/[^0-9]/', '', $line["imps. sans défaut"]));
      }
      
      return $data;
    }
    
    private function getCustomCpm($line)
    {   
      $data = "FORFAIT";
      
      if (intval($line["campaign flat fee"]) == 0) {
        $data = str_replace(",", ".", $line["campaign cpm"]);            
      }
      
      return $data;
    }
    
    private function calcDaysBetweenDates($start, $end)
    {
      $start = DateTime::createFromFormat('d/m/Y', $start);
      $end = DateTime::createFromFormat('d/m/Y', $end);
      
      $diff = $end->diff($start)->format("%a");
      
      return $diff + 1;
    }
    
    private function getImprFacturables($line)
    {   
      $data = "FORFAIT";
      $days = 1;
      
      if (intval($line["campaign flat fee"]) == 0) {
        $end = $line["date de fin du flight"];
        $start = $line["flight date de début"];
        /*
        If dates are "unknown" return default diff 1
        */
        if ($start != "unknown" && $end != "unknown") {
          $days = $this->calcDaysBetweenDates($start, $end);
        } else {
          $date = $line["par jour"];
          $key = $line["emplacement"];
          Log::info('Flight dates unknown', [$date, $key]);
        }     
        
        $data = intval($line["campaign billable imps."] / $days);
      }
      
      return $data;
    }
    
    private function getRevenu($fee, $prises, $cpm)
    {   
      $row = [];
      $val = $fee;
      
      if (intval($fee) == 0) {
        $val = (int)$prises * (float)$cpm / 1000;
      }
      
      $row["revenu"] = number_format((float)$val, 4, '.', '');
      
      return $row;
    }
  }
  