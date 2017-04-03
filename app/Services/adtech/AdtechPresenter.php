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
                //$array = $this->addFields($array);
                
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
        // Log::info('Line mapped', $line);
        $array = array(
            "date" => $this->convertDate($line["par jour"]),
            "site" => $line["site web"],
            "emplacement" => $line["emplacement"],
            "position" => $line["position de l'emplacement"],
            "format" => $line["banner size to report"],
            "impressions envoyees" => $this->getImprEnvoyees($line),
            "impressions facturables" => $this->getImprFacturables($line),
            "impressions reçues" => "ND",
            "key" => "ND",
            "inventaire" => "Premium",
            "cpm" => $this->getCustomCpm($line),
            "discrepencies" => "ND",
            "fillRate" => "ND",
            "annonceur" => $line["annonceur"],
            "campagne" => $line["flight description"],
        );

        $array += $this->getImprPrises(
            $line["campaign flat fee"], 
            $array["impressions envoyees"], 
            $array["impressions facturables"]);

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

        if ($fee == 0) {
            if ($facturables == 0) {
                $val = $envoyees;
            } elseif ($envoyees > $facturables) {
                $val = $facturables;
            } elseif ($envoyees < $facturables) {
                $val = $envoyees;
            }
        }

        $row["impressions prises"] = $val;

        return $row;
    }
    
    private function getImprEnvoyees($line)
    {   
        $data = "FORFAIT";

        if ($line["campaign flat fee"] == 0) {
            $data = $line["imps. sans défaut"];
        }

        return $data;
    }

    private function getCustomCpm($line)
    {   
        $data = "FORFAIT";

        if ($line["campaign flat fee"] == 0) {
            $data = $line["campaign cpm"];
        }

        return $data;
    }

    private function calcDaysBetweenDates($start, $end)
    {
        $start = DateTime::createFromFormat('d/m/Y', $start);
        $end = DateTime::createFromFormat('d/m/Y', $end);

        $diff = $end->diff($start)->format("%a");

        return $diff;
    }
    
    private function getImprFacturables($line)
    {   
        $data = "FORFAIT";
        $days = 0;

        if ($line["campaign flat fee"] == 0) {
            $end = $line["date de fin du flight"];
            $start = $line["flight date de début"]; 
            $days = $this->calcDaysBetweenDates($start, $end);

            $data = number_format($line["campaign billable imps."] / $days, 2);
        }

        Log::info('Line mapped', [$line["campaign billable imps."], $days, $data]);
        return $data;
    }

    private function getRevenu($fee, $prises, $cpm)
    {   
        $row = [];
        $val = $fee;

        if ($fee == 0) {
            $val = (int)$prises * (int)$cpm / 1000;
        }
        
        $row["revenu"] = $val;

        return $row;
    }
}
