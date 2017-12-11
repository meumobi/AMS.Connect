<?php

config(
    ['AMS.provider'=> 
        [
            'name' => 'unplugged',
            'email_server' => '{imap.gmail.com:993/imap/ssl}INBOX',
            'email_username' => 'amsconnect@dailyfresh-media.com',
            'email_password' => 'Reports@AMS0',
            'email_to' => 'amsconnect+unplugged@dailyfresh-media.com',
            'auth' => 'email', //token, http basic, oauth, etc
            'date_format' => 'd/m/Y'
            //Any other config option must be setted here
        ]
    ]
);