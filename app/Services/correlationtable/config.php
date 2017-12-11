<?php

config(
    ['AMS.provider'=> 
        [
            'name' => 'correlationtable',
            'tdc_url' => 'https://docs.google.com/spreadsheets/d/e/2PACX-1vS6JMrFR7Aer9fO-rQOtHDLX_nnDN7RvzKQnm62PQV538VlHrPWjl-5gwVvcB7SsIL9DjLNmCVt6hRP/pub?output=csv',
            'file_name' => 'ams-correlation-table.csv'
            //Any other config option must be setted here
        ]
    ]
);