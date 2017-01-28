<?php

config(
    ['AMS.provider'=> 
        [
            'name' => 'adsense',
            'url'  => 'https://www.googleapis.com/adsense/v1.4/reports',
            'date_format' => 'Y-m-d',
            'auth' => 'oauth', //basic, oauth, etc
            'scope'=> 'https://www.googleapis.com/auth/adsense.readonly',
            //Any other config option
            'serviceAccountFile' => realpath(dirname(__FILE__)).'/credentials.json'
        ]
    ]
);