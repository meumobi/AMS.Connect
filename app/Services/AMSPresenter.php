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
        $correlationTable = new CorrelationTable();
        $row = $correlationTable->getRow($key);
        if (empty($row)) {
            Log::warning('Correlation Key Not Found', ['key' => $key]);
        }
        return $row;
    }

    protected function getCpm($impressions, $revenue) 
    {
        $row = array('cpm' => 'NA');

        if ($impressions != 0) {
            $row['cpm'] = ($revenue / $impressions) * 1000;
        }

        return $row;
    }
    
    protected function getFillRate($received, $matched)
    {
        $row = array('fillRate' => '0%');

        if ($received == 'NA') {
            $row['fillRate'] = 'NA';
        } else if ($matched != 0) {
            $row['fillRate'] = (($matched / $received) * 100) . '%';
        } 

        return $row;
    }
}
