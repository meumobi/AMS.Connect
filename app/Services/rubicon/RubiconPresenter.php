<?php


namespace App\Services\rubicon;

use App\Services\AMSPresenter;
use App\Services\AMSPresenterInterface;
use Log;
use DateTime;
use ErrorException;

/*
    Inspired by https://gist.github.com/jakebathman/4fb8c55b13272eee9c88
*/

class RubiconPresenter extends AMSPresenter implements AMSPresenterInterface
{

    public function __construct()
    {
        $this->_dateFormat = 'Y-m-d';
        $this->_data = [];
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
            $this->_data = array_reduce(
                $data["data"]["items"],
                function ($data, $line) {
                    $array = $this->mapping($line);
                    $array = $this->addFields($array);
                    $data[$array['date'] . $array['key']] = $array;
                    return $data;
                },
                []
            );
            
            foreach ($this->_data as $line) {
                $array = $this->adjustFields($line);
                if (empty($firstLineKeys)) {
                    $firstLineKeys = array_keys($array);
                    fputcsv($tempFile, $firstLineKeys);
                    $firstLineKeys = array_flip($firstLineKeys);
                } else {
                     $records[$array['site']][$array['date']][$array['partenaire']][$array['uid']] = $array;
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
            "date" => $this->convertDate($line["date"]),
            "impressions reçues" => $line["impressions"],
            "impressions prises" => $line["paid_impression"],
            "revenu" => number_format(0.85 * (float)$line["revenue"], 2, '.', ''),
            "key" => $line["zone_id"] . "-" . $line["size_id"],
            "inventaire" => "AMS Market Place",
            "partenaire" => "Rubicon"
        );

        return $array;
    }

    protected function adjustFields($array)
    {
        if ($this->hasToCheckAlternativeKey($array)) {
            
            $altKey = $this->getAlternateKey($array['key']);
            $dataIndex = $array['date'] . $altKey;
            if (isset($this->_data[$dataIndex]) && !empty($this->_data[$dataIndex])) {
                $altRow = $this->_data[$dataIndex];
                $array['impressions envoyees'] = $altRow['impressions reçues'] - $altRow['impressions prises'];
                Log::info('Rubicon, Adjusting fields for key', ['key'=>$array['key'], 'altKey'=>$altKey]);
                $altDiscrepencies = $this->getDiscrepencies(
                    $array['impressions envoyees'],
                    $array['impressions reçues']
                );
                $array['discrepencies'] = $altDiscrepencies['discrepencies'];
            } else {
                Log::info('No alternative key available on adserving', ['key'=>$array['key'], 'altKey'=>$altKey]);    
            }
        }
        return $array;
    }

    private function hasToCheckAlternativeKey($array)
    {
        $regexEndKeys = '/\-(2|15)$/';
        if ((!$array['impressions envoyees'] || $array['impressions envoyees'] == 'NA')
            && preg_match($regexEndKeys, $array['key'])) {
            return true;
        }

        return false;
    }

    private function getAlternateKey($key)
    {
        $search = ['-2', '-15'];
        $replace = ['-57', '-10'];
        return str_replace($search, $replace, $key);
    }
}
