<?php

namespace App\Services;

use Log;

class AdServingTable
{

    private $_tableData;
    const FILE_PATH = "app/public/adserving.csv";  

    private function __construct()
    {
        $csv = array_map('str_getcsv', file(storage_path(self::FILE_PATH)));
        $header = array_map('strtolower', array_shift($csv));
        $this->_tableData = array_reduce(
            $csv,
            function ($data, $row) use ($header) {
                $row = array_combine($header, $row);
                $tableKey = $row['key'] . array_shift($row);
                $data[$tableKey] = $row;
                return $data;
            },
            []
        );
        Log::info('AdServingTable initialized');
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
