<?php

config(
    ['AMS.provider'=> 
        [
            'email_server' => '{imap.gmail.com:993/imap/ssl}INBOX',
            'email_username' => 'amsconnect@dailyfresh-media.com',
            'email_password' => 'Reports@AMS0',
            'email_to' => 'amsconnect+adserving@dailyfresh-media.com',
            'auth' => 'email', //token, http basic, oauth, etc
            'date_format' => 'd/m/Y',
            'file_path' => 'app/public/adserving.csv',
            'pid_lock_file' => 'date-adserving.lock'
            //Any other config option must be setted here
        ]
    ]
);