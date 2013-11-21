<?php
namespace Qi\Mail\Transport;

use \Qi\Mail\Enum;

class PhpMail implements \Qi\Mail\Transport
{
    public function __construct()
    {
    }
    
    public function send()
    {
        mail($this->_getRecipients(RecipientType::TO), $this->subject, $this->_getBody(), $this->getHeader(), $this->_getParameters());
    }
}
