<?php

config(
    ['AMS.provider'=> 
        [
            'name' => 'adserving',
            'email_server' => '{imap.gmail.com:993/imap/ssl}INBOX',
            'email_username' => 'amsconnect@dailyfresh-media.com',
            'email_password' => 'Reports@AMS0',
            'email_to' => 'amsconnect+adserving@dailyfresh-media.com',
            'auth' => 'email', //token, http basic, oauth, etc
            'date_format' => 'd/m/Y',
            'file_name' => 'adserving.csv',
            //Any other config option must be setted here
        ]
    ]
);