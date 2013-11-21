<?php
namespace Qi\Mail\Transport;

use \Qi\Mail\Enum;

/**
 * The Smtp class is an SMTP extension of QiMail
 * that uses PEAR SMTP to send an email
 */
class Smtp implements \Qi\Mail\Transport
{
    const SSL = 465;
    
    private $smtpHost;
    private $smtpPort;
    private $smtpScheme;
    private $smtpAuth = false;
    private $smtpUsername;
    private $smtpPassword;
    
    /**
      * Constructor
      */
    public function __construct()
    {    
        if (count($parameters)) {
            foreach($parameters as $key => $value) {
                switch ($key) {
                    case "host":
                        $this->smtpHost = $value;
                        break;
                    case "port":
                        $this->smtpPort = $value;
                        break;
                    case "scheme":
                        $this->smtpScheme = $value;
                        break;
                    case "username":
                        $this->smtpUsername = $value;
                        $this->smtpAuth = true;
                        break;
                    case "password":
                        $this->smtpPassword = $value;
                        $this->smtpAuth = true;
                        break;
                }
            }
        }
    }

    /**
     * Send the email using PHP mail()
     * @return mail function result
     */
    public function send()
    {
        require_once "Mail.php";
        require_once "Mail/mime.php";
        
        $headers = array("From" => $this->sender->getAddress(true),
            RecipientType::TO => $this->getRecipients(RecipientType::TO),
            "Subject" => $this->subject);
        
        $cc = $this->getRecipients(RecipientType::CC);
        if (isset($cc)) $headers[RecipientType::CC] = $cc;
        
        $bcc = $this->getRecipients(RecipientType::BCC);
        if (isset($bcc)) $headers[RecipientType::BCC] = $bcc;
        
        $parameters = array("host" => ($this->smtpPort == EncryptionType::SSL ? "ssl://" : "") . $this->smtpHost);
        if ($this->smtpPort) $parameters["port"] = $this->smtpPort;
        if ($this->smtpAuth) $parameters["auth"] = $this->smtpAuth;
        if ($this->smtpUsername) $parameters["username"] = $this->smtpUsername;
        if ($this->smtpPassword) $parameters["password"] = $this->smtpPassword;
        
        // Build Multipart mail
        $mime = new Mail_mime("\r\n");
        $body = $this->getBody();
        $mime->setTXTBody(strip_tags($body));
        $mime->setHTMLBody($body);
        $body = $mime->get();
        $headers = $mime->headers($headers);
        
        // Send via SMTP
        //$smtp =& Mail::factory("smtp", $parameters);

        $mail = new Mail();
        $smtp = $mail->factory("smtp", $parameters);

        $response = $smtp->send($this->getRecipients(RecipientType::TO, false), $headers, $body);
    
        if (PEAR::isError($response)) {
            throw new Exception($response->getMessage());
        } else {
            return true;
        }
    }
}
    
