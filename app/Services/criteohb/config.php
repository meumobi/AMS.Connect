<?php

config(
    ['AMS.provider'=> 
        [
            'name' => 'criteohb',
            'url' => 'https://publishers.criteo.com/api/2.0/stats.json',
            'auth' => 'token', //token, http basic, oauth, etc
            'token'=>'871f2f69-3d7a-4961-94f0-4cca68ebed10',
            'date_format' => 'Y-m-d\TH:i:s'
            //Any other config option must be setted here
        ]
    ]
);