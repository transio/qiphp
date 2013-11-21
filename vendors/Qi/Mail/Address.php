<?php
namespace Qi\Mail;

/**
 * The Address class represents a sender or recipient of an email
 */
class Address
{
    private $email = null;
    private $name = null;

    /**
     * Constructor
     * @param $email String The email address of the individual
     * @param $name String[optional] The individual's name
     */
    public function __construct($email, $name=null)
    {
        $this->email = $email;
        $this->name = $name;
    }
    
    public function __toString()
    {
        return $this->getAddress();
    }
    
    /**
     * Get the email address formatte for SMTP
     * @return String The formatted email address
     * @param $showAlias Boolean[optional] Default true, if set to false, will not show alias info
     */
    public function getAddress($showAlias=true)
    {
        return ($showAlias && !is_null($this->name)) ? "$this->name <$this->email>" : $this->email;
    }
    
}
