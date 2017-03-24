<?php

namespace App\Lib;

use Log;

class EmailReader
{

    private $_inbox;

    // connect to the server and get the inbox emails
    function __construct()
    {

    }
    
    // open the server connection
    // the imap_open function parameters will need to be changed for the particular server
    // these are laid out to connect to a Dreamhost IMAP server
    public function connect($server, $user, $pass)
    {
        $this->_inbox = imap_open($server, $user, $pass, OP_READONLY);
    }

    // close the server connection
    public function close()
    {
        imap_close($this->_inbox);
    }

    function searchEmails($recipient = '', $date = '', $title = '')
    {
        $searchString = '';

        if ($recipient) {
            $searchString .= 'TO "' . $recipient . '" ';
        }

        if ($title) {
            $searchString .= 'SUBJECT "' . $title . '" ';
        }
        
        if ($date) {
            $searchString .= 'ON "' . $date . '" ';
        }

        $emails = imap_search($this->_inbox, $searchString);
        $total = count($emails);
        Log::debug('Number of emails found with the searchString', compact('searchString', 'total'));
        
        return $emails;
    }

    public function getEmailAttachments($index)
    {
        $structure = imap_fetchstructure($this->_inbox, $index);
        
        $attachments = [];
        // check for attachments
        if (isset($structure->parts) && count($structure->parts)) {
            // loop through all attachments
            for ($i = 0; $i < count($structure->parts); $i++) {
                // set up an empty attachment
                $isAttachment = false;
                $attachment = array(
                    'filename'      => '',
                    'name'          => '',
                    'attachment'    => ''
                );

                // if this attachment has idfparameters, then proceed
                if ($structure->parts[$i]->ifdparameters) {
                    foreach ($structure->parts[$i]->dparameters as $object) {
                        // if this attachment is a file, mark the attachment and filename
                        if (strtolower($object->attribute) == 'filename') {
                            $isAttachment = true;
                            $attachment['filename']      = $object->value;
                        }
                    }
                }

                // if this attachment has ifparameters, then proceed as above
                if ($structure->parts[$i]->ifparameters) {
                    foreach ($structure->parts[$i]->parameters as $object) {
                        if (strtolower($object->attribute) == 'name') {
                            $isAttachment = true;
                            $attachment['name']          = $object->value;
                        }
                    }
                }

                // if we found a valid attachment for this 'part' of the email, process the attachment
                if ($isAttachment) {
                    // get the content of the attachment
                    $attachment['attachment'] = imap_fetchbody($this->_inbox, $index, $i+1);

                    // check if this is base64 encoding
                    if ($structure->parts[$i]->encoding == 3) { // 3 = BASE64
                        $attachment['attachment'] = base64_decode($attachment['attachment']);
                    // otherwise, check if this is "quoted-printable" format
                    } elseif ($structure->parts[$i]->encoding == 4) { // 4 = QUOTED-PRINTABLE
                        $attachment['attachment'] = quoted_printable_decode($attachment['attachment']);
                    }
                    $attachments[] = $attachment;
                }
            }
        }
        
        return $attachments;
    }
}
