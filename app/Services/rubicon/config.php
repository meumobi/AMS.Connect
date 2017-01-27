<?php

config(
    ['AMS.provider'=> 
        [
            'name' => 'rubicon',
            'url' => 'https://api.rubiconproject.com/analytics/v1/report',
            'auth' => 'http basic', //token, http basic, oauth, etc
            'username' => '11378197bbd3bec4b23215a32186d079bcd24f3a',
            'password' => '3353bbb03d5d34e65e0d94b9b6dc0c7f',
            'date_format' => 'Y-m-d'
            //Any other config option must be setted here
        ]
    ]
);