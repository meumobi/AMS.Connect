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
        Log::info('Temporary file created', ['file'=>$strTempFile]);

        $firstLineKeys = false;
        $records = [];

        try {
            foreach ($data as $line) {
                $array = $this->mapping($line);
                $array += $this->getCorrelatedFields($array['key']);
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
        Log::info('Temporary file deleted', ['file'=>$strTempFile]);
    }
    
    private function mapping($line)
    {
        // Log::info('Line mapped', $line);
        $array = array(
            "date" => $this->convertDate($line["date"]),
            "impressions reçues" => $line["impressions recues"],
            "key" => $line["key"],
            "inventaire" => $line["inventaire"],
            //"fillRate" => "ND",
            //"cpm" => $this->getCustomCpm($line),
            //"site" => $line["site web"],
            //"emplacement" => $line["emplacement"],
            //"position" => $line["position de l'emplacement"],
            //"format" => $line["banner size to report"],
            "annonceur" => $line["annonceur"],
            "impressions envoyees" => $line["impressions envoyees"],
            "impressions prises" => $line["impressions prises"],
            "revenu" => floatval(str_replace(",",".",$line["revenu"])),
            //"discrepencies" => "ND",
            "impressions facturables" => "ND",     
            "campagne" => "ND",
        );
        
        return $array;
    }
}
