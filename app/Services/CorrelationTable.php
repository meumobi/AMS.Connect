<?php

namespace App\Services;

use Log;

class CorrelationTable
{

    private $_tableData;

    private function __construct()
    {
        $csv = array_map('str_getcsv', file('ams-correlation-table.csv'));
        $header = array_map('strtolower', array_shift($csv));
        $this->_tableData = array_reduce(
            $csv,
            function ($data, $row) use ($header) {
                $row = array_combine($header, $row);
                $data[$row['key']] = $row;
                return $data;
            },
            []
        );
        Log::info('CorrelationTable initialized');
    }

    public function getRow($key)
    {
        if (isset($this->_tableData[$key])) {
            return $this->_tableData[$key];
        }
        
        return [];
    }

    public static function getInstance()
    {
        static $instance = null;
        if (null === $instance) {
            $instance = new static();
        }

        return $instance;
    }

    private function __clone()
    {
    }

    private function __wakeup()
    {
    }
}