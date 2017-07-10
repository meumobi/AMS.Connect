<?php

namespace App\Services\adsense;

use App\Services\AMSPresenter;
use App\Services\AMSPresenterInterface;
use Log;
use DateTime;
use ErrorException;

class AdsensePresenter extends AMSPresenter implements AMSPresenterInterface
{

    public function __construct()
    {
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
        Log::info('Temporary file created', ['file'=>$strTempFile]);

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
                } else {
                    $records[$array['site']][$array['uid']] = $array;
                }
                
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
        Log::info('Temporary file deleted', ['file'=>$strTempFile]);
    }

    private function mapping($line)
    {
        $array = array(
        	"date" => $this->convertDate($line["DATE"]),
        	"impressions reçues" => $line["AD_REQUESTS"],
        	"impressions prises" => $line["MATCHED_AD_REQUESTS"],
            "revenu" => number_format(0.96 * (float)$line["EARNINGS"], 2, '.', ''),
        	"key" => $line["AD_UNIT_CODE"],
        	"inventaire" => "AdNetwork Fill"
        );
        
        return $array;
    }
}
