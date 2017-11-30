<?php

config(
    ['AMS.provider'=> 
        [
            'name' => 'criteowf',
            'url' => 'https://publishers.criteo.com/api/2.0/stats.json',
            'auth' => 'token', //token, http basic, oauth, etc
            'token'=>'e36080a1-8f8b-44fb-9aef-1457f4223355',
            'date_format' => 'Y-m-d\TH:i:s'
            //Any other config option must be setted here
        ]
    ]
);