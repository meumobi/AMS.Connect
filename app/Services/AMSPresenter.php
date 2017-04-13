<?php

namespace App\Services;

use Log;
use DateTime;

class AMSPresenter
{
    protected $_dateFormat;
    
    public function __construct()
    {
        Log::info('Presenter initialized: '.get_class($this));
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
        Log::info('CSV echoed successfully', ['file'=>$file]);
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
        Log::info('CSV attached successfully', ['file'=>$file]);
    }

    protected function getCorrelatedFields($key)
    {
        $correlationTable = CorrelationTable::getInstance();
        $row = $correlationTable->getRow($key);
        if (empty($row)) {
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
            Log::warning('AdServing Key Not Found', ['key' => (string)$key, 'date' => $date]);
        }
        if (isset($row['impressions envoyees'])) {
            $row['impressions envoyees'] = preg_replace("/[^0-9]/", "", $row['impressions envoyees']);
        } else {
            $row['impressions envoyees'] = 'NA';
        }

        return $row;
    }

    protected function getCpm($impressions, $revenue)
    {
        $row = array('cpm' => 'NA');

        if ($impressions != 0) {
            $data = ($revenue / $impressions) * 1000;
            $row['cpm'] = number_format($data, 2, '.', '');
        }

        return $row;
    }

    protected function getDiscrepencies($sent, $received)
    {
        $row = array('discrepencies' => 'NA');

        if ($received != 0 && $received != 'NA' && $sent != 0 && $sent != 'NA') {
            $data = (1 - ((int)$received / (int)$sent)) * 100;
            $row['discrepencies'] = number_format($data, 2, '.', '') . '%';
        }

        return $row;
    }
    
    protected function getFillRate($received, $matched)
    {
        $row = array('fillRate' => '0%');

        if ($received == 'NA' || $received == 0) {
            $row['fillRate'] = 'NA';
        } elseif ($matched != 0) {
            $data = (($matched / $received) * 100);
            $row['fillRate'] = number_format($data, 2, '.', '') . '%';
        }

        return $row;
    }

    protected function addFields($array)
    {
        $array += $this->getFillRate($array['impressions reçues'], $array['impressions prises']);
        $array += $this->getCpm($array['impressions prises'], $array['revenu']);
        $array += $this->getCorrelatedFields($array['key']);
        $array += $this->getAdServingFields($array['key'], $array['date']);
        $array += $this->getDiscrepencies($array['impressions envoyees'], $array['impressions reçues']);
        $array += array('impressions facturables' => 'ND');
        $array += array('campagne' => 'ND');

        return $array;
    }
}
