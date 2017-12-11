<?php

config(
    ['AMS.provider'=> 
        [
            'name' => 'criteo',
            'url' => 'https://publishers.criteo.com/api/2.0/stats.json',
            'auth' => 'token', //token, http basic, oauth, etc
            'token'=>'',
            'date_format' => 'Y-m-d\TH:i:s',
            'inventaire' => 'AMS Market Place'
            //Any other config option must be setted here
        ]
    ]
);