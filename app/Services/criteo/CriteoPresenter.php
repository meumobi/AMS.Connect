<?php


namespace App\Services\criteo;

use App\Services\AMSPresenter;
use App\Services\AMSPresenterInterface;
use Log;
use DateTime;
use ErrorException;

/*
    Inspired by https://gist.github.com/jakebathman/4fb8c55b13272eee9c88
*/

class CriteoPresenter extends AMSPresenter implements AMSPresenterInterface
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
                $array = $this->addFields($array);
                
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
                Log::error('Mapping Error, field does not exists', ['exception'=>$exception->getMessage()]);
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
            "date" => $this->convertDate($line["date"]),
            "impressions reçues" => $line["totalImpression"],
            "impressions prises" => $line["impression"],
            "revenu" => number_format((float)$line["revenue"]["value"], 10, '.', ''),
            "key" => $line["placementId"],
            "inventaire" => $configData['inventaire'],
            "partenaire" => ucfirst($configData['name'])
        );
        
        return $array;
    }
}
