<?php
namespace Qi\Mail;

use \Qi\Mail\Enum;
use \Qi\Mail\Transport;

/**
 * The Mail class is a standalone PHP Mailer component that can also
 * be integrated into Qi applications.
 */
class Mail
{
    const CRLF = "\r\n";
    
    protected $messageId;
    protected $date;
    protected $sender;
    protected $replyTo;
    protected $recipients;
    protected $organization;
    protected $subject;
    protected $body;
    protected $boundary;
    protected $parts;
    protected $priority;
    protected $msPriority;
    protected $mimeVersion;
    protected $mimeType;
    protected $contentTransferEncoding;

    /**
      * Constructor
      */
    public function __construct(Address $sender=null, $subject=null, $body=null, array $parameters=null)
    {
        $this->messageId = "<" . date("Y-m-d H:i:s") . "." . rand(1111,9999) . " Qi.Mail@".$_SERVER['SERVER_NAME'].">";
        $this->date = date("D, j M Y H:i:s") . " -0500";
        $this->mimeVersion = "1.0";
        
        $this->setPriority(3);
        $this->setContentType(MimeType::HTML);
        $this->contentTransferEncoding = "7bit";
        
        if (count($parameters)) {
            foreach($parameters as $key => $value) {
                switch ($key) {
                    case "message-id":
                        $this->messageId = $value;
                        break;
                    case "mime-version":
                        $this->mimeVersion = $value;
                        break;
                    case "priority":
                        $this->setPriority($value);
                        break;
                    case "content-type":
                        $this->setContentType($value);
                        break;
                    case "charset":
                        $this->charset = $value;
                        break;
                    case "content-transfer-encoding":
                        $this->contentTransferEncoding = $value;
                        break;
                }
            }
        }
        
        
        $this->setSender($sender);
        $this->recipients = array();
        $this->subject = $subject;
        $this->body = $body;
        $this->parts = array();
    }
    
    /**
     * Set the content type of the email
     * @param $mimeType MimeType Possible values are MimeType::TEXT, MimeType::RTF, MimeType::HTML, and MimeType::MULTIPART_ALTERNATIVE
     * @param $charset String[optional] Default charset is UTF-8.  If you need something different, set it here
     * @param $boundary String[optional] For multi-part emails, if you want to set your own boundary, do it here
     */
    public function setContentType($mimeType, $charset="utf-8", $contentTransferEncoding="quoted-printable", $boundary=null)
    {
        // Set the content type to the specified mime type
        $this->mimeType = $mimeType;
        $this->charset = $charset;
        if ($mimeType == MimeType::MULTIPART_ALTERNATIVE) {
            if (is_null($boundary)) {
                $this->boundary = "-----boundary-" . md5(mktime()) . "-----";
            } else {
                $this->boundary = $boundary;
            }
        }
    }

    /**
     * Set the sender of the email
     * @param $from Address
     */
    public function setSender(Address $sender)
    {
        //$this->sender = get_class($from) == "Address" ? $from : new Address($from);
        $this->sender = $sender;
        $this->replyTo = $sender;
    }
    
    /**
     * Set the reply-to address
     * @param $replyTo Address
     */
    public function setReplyTo(Address $replyTo)
    {
        //$this->replyTo = get_class($replyTo) == "Address" ? $replyTo : new Address($replyTo);
        $this->replyTo = $replyTo;
    }
    
    /**
     * Add a recipient
     * @param $recipient Address
     * @param $type Enum[optional] Possible values are RecipientType::TO, RecipientType::CC, RecipientType::BCC
     */
    public function addRecipient(Address $emailAddress, $type=RecipientType::TO)
    {
        // Deprecated - if (get_class($emailAddress) != "Address") $emailAddress = new Address($recipient);
        array_push($this->recipients, array($type, $emailAddress));
    }
    
    /**
     * Set the email subject
     * @param $subject String
     */
    public function setSubject($subject)
    {
        $this->subject = $subject;
    }
    
    /**
     * Set the email body (single-part emails only)
     * @param $body String
     */
    public function setBody($body)
    {
        $this->body = $body;
    }
    
    /**
     * Set an email part (multi-part emails only)
     * @param $mimeType MimeType[optional] Possible values are MimeType::TEXT, MimeType::RTF, and MimeType::HTML
     * @param $body Object
     */
    public function setPart($mimeType=MimeType::TEXT, $body) {
        $this->parts[$mimeType] = $body;
    }
    
    /**
     * Set the sender's organization
     * @param $organization String
     */
    public function setOrganization($organization) {
            $this->organization = $organization;
    }
    
    /**
     * Set the numeric priority
     * @param $priority int 1=urgent, 2
     */
    public function setPriority($priority) {
        switch ($priority) {
            case 1:
                $this->msPriority = "Normal";
                break;
            case 2:
                $this->msPriority = "Normal";
                break;
            case 3:
                $this->msPriority = "Normal";
                break;
        }
        $this->priority = "{$priority} ({$this->msPriority})";
        
        return $this;
    }
    
    public function setTransport(Transport $transport) {
        $this->_transport = $transport;
    }
    
    /**
     * Send the email using PHP mail()
     * @return mail function result
     */
    public function send()
    {
        return $this->_transport->send();
    }
        
    /**
     * Private - Get the header of the email
     * @return String The full formatted header
     */
    public function getHeader()
    {
        $header = self::_generateHeaderLine("From", $this->sender->getAddress());
        $header .= self::_generateHeaderLine("Reply-To", $this->replyTo->getAddress());
        $header .= self::_generateHeaderLine("Return-Path", $this->replyTo->getAddress());
        $header .= self::_generateHeaderLine("X-Mailer", "PHP/".phpversion());
        $header .= self::_generateHeaderLine("Mime-Version", $this->mimeVersion);
        $header .= self::_generateHeaderLine("Content-Type", self::_generateContentType($this->mimeType, $this->charset, $this->boundary));
        $header .= self::_generateHeaderLine("Content-Transfer-Encoding", $this->contentTransferEncoding);
        $header .= self::_generateHeaderLine("Message-ID", $this->messageId);
        $header .= self::_generateHeaderLine("Subject", $this->subject);
        //$header .= self::_generateHeaderLine("Auto-Submitted", "auto-generated");
        //$header .= self::_generateHeaderLine("Organization", $this->organization);
        //$header .= self::_generateHeaderLine("X-Priority", $this->priority);
        //$header .= self::_generateHeaderLine("X-MSMail-Priority", $this->msPriority);
        $header .= self::CRLF . self::CRLF;
        return $header;
    }
    
    /**
     * Private - Generate a single line of the header
     * @return String The formatted header line
     * @param $name Object
     * @param $value Object
     */
    private static function _generateHeaderLine($name, $value)
    {
        if (strlen($value) > 0) {
            return "{$name}: {$value}".self::CRLF;
        } else {
            return "";
        }
    }
    
    private function _getParameters()
    {
        return "-f".$this->sender->getAddress(false);
    }

    /**
     * Protected - Get email recipients of a particular type
     * @param $type Enum[optional] Possible values are RecipientType::TO, RecipientType::CC, RecipientType::BCC
     * @param $showAlias Boolean[optional] True = show alias, False = don't show alias
     */
    protected function _getRecipients($type=RecipientType::TO, $showAlias=true)
    {
        $emails = array();
        foreach ($this->recipients as $recipient) {
            if (is_array($recipient) && !empty($recipient) && count($recipient) == 2 && $recipient[0] == $type) {
                $email =& $recipient[1];
                if ($email instanceof Address) {
                    array_push($emails, $email->getAddress($showAlias));
                }
            }
        }
        return implode(", ", $emails);
    }
    
    /**
     * Protected - Get the body of the email
     * @return String The formatted body+
     * 
     */
    protected function _getBody()
    {
        if ($this->mimeType == MimeType::MULTIPART_ALTERNATIVE) {
            $body = "This is a multi-part mime message.\r\n\r\n";
            $body .= $this->_getPart(MimeType::TEXT);
            $body .= $this->_getPart(MimeType::RTF);
            $body .= $this->_getPart(MimeType::HTML);
            $body .= "{$this->boundary}\r\n";
            return $body;
        } else {
            return str_replace("\r", "", $this->body);
        }
    }
    
    protected function _getPart($mimeType, $contentTransferEncoding="quoted-printable")
    {
        if (array_key_exists($mimeType, $this->parts)) {
            $part .= "{$this->boundary}\r\n";
            $part .= self::_generateHeaderLine("Content-Type", self::_generateContentType($mimeType, $this->charset));
            $part .= self::_generateHeaderLine("Content-Transfer-Encoding", $contentTransferEncoding);
            $part .= "\r\n\r\n";
            $part .= str_replace("\r", "", $this->parts[$mimeType]);
            $part .= "\r\n\r\n";
        }
    }
    
    protected static function _generateContentType($mimeType, $charset=null, $boundary=null)
    {
        $contentType = $mimeType;
        
        // Add charset if specified
        if (!is_null($charset)) {
            $contentType .= "; charset={$charset}";
        }
        
        // Add boundary if applicable
        if ($mimeType == MimeType::MULTIPART_ALTERNATIVE && !is_null($boundary)) {
            $contentType .= "; boundary=\"{$boundary}\"";
        }
        
        return $contentType;
    }

    /**
     * Protected - Get the RFC Date
     * @return String the date as a string
     */
    protected function _getRFCDate()
    {
        return date("D, j M Y H:i:s") . " " . self::_getRFCTimezone();
    }
    
    /**
     * Protected - Get the RFC Timezone
     * @return String the timezone
     */
    protected function _getRFCTimezone()
    {
        $timezone = date("Z");
        $timezoneSign = ($timezone < 0) ? "-" : "+";
        $timezone = abs($timezone);
        $timezone = ($timezone/3600)*100 + ($timezone%3600)/60;
        return sprintf("%s%04d", $timezoneSign, $timezone);
    }
}
