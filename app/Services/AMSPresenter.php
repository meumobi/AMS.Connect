<?php

namespace App\Services;

use Log;
use DateTime;
use Kreait\Firebase\Factory;
use Kreait\Firebase\ServiceAccount;

class AMSPresenter
{
  const MODE_ECHO = "echo";
  const MODE_ATTACH = "attach";
  const MODE_PUBLISH = "publish";
  const MODE_PREVIEW = "preview";
  const MODE_CONSOLE = "console";
  const MODE_CONSOLE_LIGHT = "console-light";
  const MODE_PREVIEW_LIGHT = "preview-light";
  
  protected $_dateFormat;
  
  public function __construct()
  {
    Log::debug('Presenter initialized: '.get_class($this));
  }
  
  protected function convertDate($date)
  {
    $date = DateTime::createFromFormat($this->_dateFormat, $date);
    
    return $date->format('Y-m-d');
  }
  
  protected function echoCsv($file)
  {
    if (($handle = fopen($file, "r")) !== false) {
      while (($data = fgetcsv($handle)) !== false) {
        echo implode(",", $data);
        echo "<br />";
      }
      fclose($handle);
    }
    Log::debug('CSV echoed successfully', ['file'=>$file]);
  }
  
  /*
  Output the file to the browser (for open/save)
  */
  protected function attachCsv($file)
  {
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename='.basename($file));
    header('Content-Length: ' . filesize($file));
    readfile($file);
    Log::debug('CSV attached successfully', ['file'=>$file]);
  }
  
  protected function pushToFirebase($records){
    Log::info('Number of Raws: '. sizeof($records));
    
    $serviceAccount = ServiceAccount::fromJsonFile(__DIR__.'/'.env("FIREBASE_CONFIG_PATH"));
    
    $firebase = (new Factory)
    ->withServiceAccount($serviceAccount)
    ->withDatabaseUri('https://ams-report.firebaseio.com')
    ->create();
    
    $database = $firebase->getDatabase();
    $structuresData = $this->structureData($records);
    
    foreach($structuresData as $key => $value) {
      $path = implode('/', ['reports', strtolower($key)]);
      
      $database
      ->getReference($path)
      ->set($value);
    }
    
    header('Content-Type: text/plain');
    /*
    echo "Data published successfully! \n";
    */
  } 
  
  protected function previewTreeToPublish($records)
  {
    header('Content-Type: text/plain');
    echo "Preview data ready to publish: \n";
    echo "Total 'impressions prise': "; 
    
    var_dump(array_reduce($records, 
    function ($sum, $record) {
      $sum += $record["impressions prises"];
      
      return $sum;
    })
  );
  
  echo "Total 'revenue': "; 
  
  var_dump(array_reduce($records, 
  function ($sum, $record) {
    $sum += $record["revenu"];
    
    return $sum;
  })
);

$structuresData = $this->structureData($records);
print_r($this->countLines($structuresData));

$this->printRecords($structuresData);
}

/*
Remove invalid characters on firebase as child key: ".$#[]"
*/
private function cleanKey($key) {
  return preg_replace('/[\.$#\[\]]/', '', $key);
}

private function structureData($records)
{
  $result = [];
  
  foreach($records as $raw) {
    $key = $raw["date"] . "_" . $raw["site"] . "_" . $raw["partenaire"];
    
    $key = $this->cleanKey($key);
    
    if (!array_key_exists($key, $result)) {
      $result[$key] = array(
        "date" => $raw["date"],
        "group" =>  strtolower($raw["site"] . "_" . $raw["date"]),
      );
    }
    
    $result[$key]["raws"][$raw["uid"]] = $raw;
  }
  
  return $result;
}

private function printRecords($records)
{
  var_dump($records);
}

private function countLines($records) {
  $lineCounts = array();
  
  foreach($records as $key => $group) {
    $lineCounts[$key] = count($group['raws']);
  }
  
  return $lineCounts;
}

protected function getCorrelatedFields($key)
{
  $correlationTable = CorrelationTable::getInstance();
  $row = $correlationTable->getRow($key);
  
  if (empty($row)) {
    $row['site'] = 'Unknown';
    $row['partenaire'] = 'Unknown';
    $row['emplacement'] = 'Unknown';
    $row['format'] = 'Unknown';
    Log::warning('Correlation Key Not Found', ['key' => (string)$key]);
  }
  
  return $row;
}

protected function getAdServingFields($key, $date)
{
  $adServingTable = AdServingTable::getInstance();
  $dateTime = (new DateTime)->createFromFormat('Y-m-d', $date);
  
  $row = $adServingTable->getRow($key . $dateTime->format('d/m/Y'));
  if (empty($row)) {
    $row['Impressions Envoyees'] = 'Unknown';
    /*
      Obsolet since header bidding migration
    */
    //Log::warning('AdServing Key Not Found', ['key' => (string)$key, 'date' => $date]);
  }
  
  return $row;
}

protected function getAdMarginFields($array)
{
  $date = $array['date'];
  $key = $array['site'] . $array['inventaire'];
  
  $adMarginTable = AdMarginTable::getInstance();
  $dateTime = (new DateTime)->createFromFormat('Y-m-d', $date);
  
  $row = $adMarginTable->getRow($key . $dateTime->format('d/m/Y'));
  if (empty($row)) {
    Log::warning('AdMargin Key Not Found', ['site' => $array['site'], 'inventaire' => $array['inventaire'], 'date' => $date]);
    
    $row['marge'] = 'Unknown';
    $row['revenu net'] = 'Unknown';
  } else {
    $row['revenu net'] = (float)$row['marge'] / 100 * $array['revenu'];
  }
  
  return $row;
}

protected function getUID($date, $key)
{
  $uid = $this->cleanKey($date . '_' . $key);
  $row = array('uid' => $uid);
  
  return $row;
}

protected function addFields($array)
{
  $array += $this->getCorrelatedFields($array['key']);
  $array += $this->getAdServingFields($array['key'], $array['date']);
  $array += $this->getAdMarginFields($array);
  $array += array('impressions facturables' => 'Unknown');
  $array += array('campagne' => 'Unknown');
  $array += array('annonceur' => 'Unknown');
  $array += $this->getUID($array['date'], $array['key']);
  
  return $array;
}

protected function consoleLog($records) {
  Log::info('Number of Raws: '. sizeof($records));
  
  foreach ($records as $record) {
    Log::info('Computed Raw (AMS Format)', [$record]);
  }
}

protected function presentAsMode($strTempFile, $records, $mode)
{
  switch ($mode) {
    case self::MODE_ATTACH:
    $this->attachCsv($strTempFile);
    break;
    case self::MODE_PUBLISH:
    $this->pushToFirebase($records);
    break;
    case self::MODE_PREVIEW:
    $this->previewTreeToPublish($records);
    break;
    case self::MODE_CONSOLE:
    $this->consoleLog($records);
    break;
    case self::MODE_PREVIEW_LIGHT:
    $this->previewTreeToPublish(array_slice($records, 0, 3));
    break;
    case self::MODE_CONSOLE_LIGHT:
    $this->consoleLog(array_slice($records, 0, 3));
    break;
    default:
    $this->echoCsv($strTempFile);
  }
}

}
