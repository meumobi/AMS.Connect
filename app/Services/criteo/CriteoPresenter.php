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

    public function present($data, $format, $echo = true)
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
        try {
            foreach ($data as $line) {
                $array = $this->mapping($line);
                $array = $array + $this->getCorrelatedFields($array['key']);
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
            if (strpos($exception->getMessage(), 'Undefined index:') !== false) {
                Log::error('Mapping Error, field does not exists', ['exception'=>$exception->getMessage()]);
                echo 'Mapping Error, field does not exists '.$exception->getMessage();
                return false;
            }
            throw $exception;
        } finally {
            fclose($tempFile);
        }
        
        $echo ? $this->echoCsv($strTempFile) : $this->attachCsv($strTempFile);
        
        // Delete the temp file
        unlink($strTempFile);
        Log::info('Temporary file deleted', ['file'=>$strTempFile]);
    }
    
    private function mapping($line)
    {
        $array = array(
            "date" => $this->convertDate($line["date"]),
            "impressions reçues" => $line["totalImpression"],
            "impressions prises" => $line["impression"],
            "revenu" => $line["revenue"]["value"],
            "key" => $line["placementId"],
            "inventaire" => "AdNetwork Fill",
			"cpm" => 0
        );
		
		if ((float)$array["impressions prises"]) 
		{
			$array["cpm"] = ((float)$array["revenu"]/(float)$array["impressions prises"]) * 1000;
		}
        
        return $array;
    }
}
