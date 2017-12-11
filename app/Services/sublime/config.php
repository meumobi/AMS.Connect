<?php

config(
    ['AMS.provider'=> 
        [
            'name' => 'sublime',
            'url' => 'http://sasapi.ayads.co/stats/',
            'auth' => 'http basic', //token, http basic, oauth, etc
            'apiKey' => 'OIYW4XmzvrUEIoIwxJbwAY4rKSvEOufpgyK7A07Y',
            'apiSecret' => 'GGaCkgE9En1sgQk6jYEjbczyZ2aDfPA3XIazxtouTBMF9JFhoK',
            'date_format' => 'Y-m-d',
            'inventaire' => 'AMS Market Place'
            //Any other config option must be setted here
        ]
    ]
);