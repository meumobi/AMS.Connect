<?php

namespace App\Lib;

use Log;

class EmailReader
{
    // email login credentials
    private $_server = '{imap.gmail.com:993/imap/ssl}INBOX';
    private $_user   = 'daniel.indiano8@gmail.com';
    private $_pass   = 'limiteradical';
    
    
    private $_inbox;

    // connect to the server and get the inbox emails
    function __construct($server, $user, $pass)
    {
        $this->_server = $server;
        $this->_user = $user;
        $this->_pass = $pass;
        $this->connect();
    }
    
    // open the server connection
    // the imap_open function parameters will need to be changed for the particular server
    // these are laid out to connect to a Dreamhost IMAP server
    private function connect()
    {
        $this->_inbox = imap_open($this->_server, $this->_user, $this->_pass, OP_READONLY);
    }

    // close the server connection
    private function close()
    {
        $this->inbox = array();
        imap_close($this->conn);
    }

    function searchEmails($to = '', $date = '', $title = '')
    {
        $searchString = '';

        if ($to) {
            $searchString .= 'TO "' . $to . '" ';
        }

        if ($title) {
            $searchString .= 'SUBJECT "' . $title . '" ';
        }
        
        if ($date) {
            $searchString .= 'ON "' . $date . '" ';
        }
        
        $emails = imap_search($this->_inbox, $searchString, SE_UID);

        return $emails;
    }
}
