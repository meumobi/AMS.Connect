<?php

config(
    ['AMS.provider'=> 
        [
            'email_server' => '{imap.gmail.com:993/imap/ssl}INBOX',
            'email_username' => 'daniel.indiano8@gmail.com',
            'email_password' => 'limiteradical',
            'email_to' => 'daniel.indiano8+premium@gmail.com',
            'auth' => 'email', //token, http basic, oauth, etc
            'date_format' => 'Y-m-d\TH:i:s'
            //Any other config option must be setted here
        ]
    ]
);