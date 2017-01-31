<?php

namespace App\Services;

class CorrelationTable
{

    private $_tableData;

    public function __construct()
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
    }

    public function getRow($key)
    {
        if (isset($this->_tableData[$key])) {
            return $this->_tableData[$key];
        }
        
        return [];
    }
}
