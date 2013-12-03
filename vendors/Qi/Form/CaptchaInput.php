<?php
namespace Qi\Form;

/**
 * The CaptchaInput class represents a captcha human verification form element
 */
class CaptchaInput extends Input
{
    const SESSION_KEY = "qf_captcha__";
    
    private $stringLength = 10;
    private $width;
    private $height;
    private $squeeze = 1.9;
    private $font = array("times_bold", "teen_bold", "28_days_later");
    private $fontSize = 48;
    private $fontScaleX = 1;
    private $textColor = 0x33AADD;
    private $bgColor = 0xFFFFFF;
    private $shadowColor = 0x808080;
    private $bgImage = null;
    private $sessionId = null;
    public $code = null;
    
    /**
     * Constructor
     * @param $name String
     * @param $title String
     * @param $properties Array[optional]
     */
    public function __construct($name, array $properties=null)
    {
        if (is_null($properties)) $properties = array();
        
        // The input portion of the CAPTCHA is a text input
        parent::__construct(\Qi\Form\Enum\InputType::TEXT, $name, $properties);
        
        // For CSS purposes, style this as a "captcha" input
        $this->addClass("qf-captcha");
        
        
        
        // Set Captcha-specific properties
        foreach ($properties as $key => $value) {
            $this->setProperty($key, $value);
        }
        
        // Select a random font
        if (is_array($this->font)) {
            $this->font = $this->font[rand(0, count($this->font)-1)];
        }
        
        // X-Scaling based on font
        switch ($this->font) {
            case "teen_bold":
                $this->fontScaleX = $this->fontSize*0.9;
                break;
            case "28_days_later":
                $this->fontScaleX = $this->fontSize*0.95;
                break;
            case "times_bold":
            case "arial":
                $this->fontScaleX = $this->fontSize*0.8;
                break;
            default:
                $this->fontScaleX = $this->fontSize;
        }
        
        // Width and height based on font size, scale, squeeze, and string length
        $this->width = round(($this->fontSize * $this->stringLength * .66) / $this->squeeze);
        $this->height = round($this->fontSize * 1.6);
    }
    
    public function __sleep()
    {
        return array("stringLength","width","height","squeeze","font","fontSize","fontScaleX","textColor","bgColor","shadowColor","bgImage","sessionId","code");
    }
    
    public function setProperty($key, $value)
    {
        switch ($key) {
            case "squeeze":
                $this->squeeze = $value;
                break;
            case "length":
            case "string-length":
                $this->stringLength = $value;
                break;
            case "font":
            case "font-family":
                $this->font = $value;
                break;
            case "font-size":
                $this->fontSize = $value;
                break;
            case "color":
            case "font-color":
            case "text-color":
                $this->textColor = $value;
                break;
            case "shadow-color":
                $this->shadowColor = $value;
                break;
            case "bgcolor":
            case "bg-color":
            case "background-color":
                $this->bgColor = $value;
                break;
            case "bgimage":
            case "bg-image":
            case "background-image":
                $this->bgImage = $value;
                break;
        }
    }
    
    
    public function getSessionId()
    {
        if (!strlen($this->sessionId)) {
            $this->sessionId = self::SESSION_KEY . $this->getId();
        }
        return $this->sessionId;
    }
    
    /**
     * Override the default getNode method to add Captcha-specific functionality
     * @return HTML string
     */
    public function &getNode()
    {
        // Get the ID and save to session
        $this->getSessionId();
        $this->saveToSession();
        
        // Return the node
        $node = parent::getNode($dom);

        // Span
        $id = $this->getId();
        $html = <<<HTML
        <div class="qf-captcha" id="{$id}_captcha">
            <img id="{$id}__img" src="/_/qf_captcha?id={$id}" class="qf-captcha-image" alt="CAPTCHA" >
            <a href="javascript:refreshCaptcha('{$id}');"><img src="/scripts/qi/reload.gif" alt="Reload" border="0"  /></a>
        </div>
        {$node}
HTML;
        return $html;
    }
    
    /**
     * Generates an image of
     * @return 
     */
    public function generateImage()
    {
        global $settings;
        
        // Generate the code for the captcha on render
        // This prevents regeneration on retrieval
        $this->generateCode();
        
        // Create the image resource
        $image = imagecreate($this->width * $this->squeeze, $this->height);
        
        // Allocate image colors
        list($r, $g, $b) = self::parseColorsFromHex($this->bgColor);
        $bgColor = imagecolorallocate($image, $r, $g, $b);
        
        list($r, $g, $b) = self::parseColorsFromHex($this->textColor);
        $textColor = imagecolorallocate($image, $r, $g, $b);
        
        list($r, $g, $b) = self::parseColorsFromHex($this->shadowColor);
        $shadowColor = imagecolorallocate($image, $r, $g, $b);
        
        // Offsets for x and y in the image
        $xOffset = 1;
        $yOffset = 20;

        // Check if a font is set
        if(strlen($this->font)) {
            $path = dirname(__FILE__) . DIRECTORY_SEPARATOR . ".." . DIRECTORY_SEPARATOR . "fonts" . DIRECTORY_SEPARATOR;
            $font = $this->font . ".ttf";
            if(file_exists($path . $font)) {
                // If the font file exists on the server write the environment variable
                putenv('GDFONTPATH=' . $path);
            } else {
                // Font not found
                throw new Exception("CaptchaInput Exception: Font '{$this->font}' not found.");
            }
        } else {
            throw new Exception("CaptchaInput Exception: No font set.");
        }
        
        // Walk through each character in the code, to print it 
        $xOffset = $this->fontScaleX*0.25;
        $yOffset = round($this->height*0.7);
        $angle = rand(-15,15);
        for($i = 0; $i < strlen($this->code); $i++) {
            $yOffset += rand(-2, 2);
            $yOffset = ($yOffset > $this->height) ? $this->height : $yOffset;
            $yOffset = ($yOffset < $this->height/2) ? round($this->height/2) : $yOffset;
            $angle += rand(-5,5);
            $this->writeString($image, $xOffset, $yOffset, $angle, $this->code{$i},$textColor, $shadowColor);
            $xOffset += rand(round($this->fontScaleX*0.63),round($this->fontScaleX*0.65)) // Random space for all letters
                + ($i == 0 ? $this->fontScaleX*0.1 : 0) // First letter is caps, so add some space
                + ($this->code{$i} == "m" || $this->code{$i} == "w" ? $this->fontScaleX*0.18 : 0) // Add space for m's and w's
                - ($this->code{$i} == "l" || $this->code{$i} == "i" ? $this->fontScaleX*0.2 : 0); // Subtract space for l's and i's
        }
        
        // Render the image
        header("Content-type: image/png");            
        
        $finalImage = imagecreatetruecolor($this->width, $this->height);
        imagecopyresampled($finalImage, $image, 0, 0, 0, 0, $this->width, $this->height, $this->width * $this->squeeze, $this->height);
          imagepng($finalImage);
        
        // Destroy the image to free resources
        imagedestroy($image);
        imagedestroy($finalImage);
    }
    
    private function writeString($image, $xOffset, $yOffset, $angle, $string, $textColor, $shadowColor)
    {
        // Draw a shadow
        //imagettftext($this->image, $this->fontSize, $angle, $xOffset + 1, $yOffset + 1, $this->shadowColor, $this->font, $string);
        
        // Draw the text    
        imagettftext($image, $this->fontSize, $angle, $xOffset, $yOffset, $textColor, $this->font, $string);
    }
    
    public static function parseColorsFromHex($hex)
    {
        //print($hex."\n");
        $b = $hex % 256;
        $hex = floor($hex/256);
        $g = $hex % 256;
        $r = floor($hex/256);
        return array($r, $g, $b);
    }
    
    /**
     * Generate a random code
     * @return String the code
     */
    public function generateCode()
    {
        $v = array("a", "e", "i", "o", "u", "ou", "ie", "ee", "ei", "ao");
        $c = array("b", "bl", "br", "c", "ch", "cl", "d", "dh", "f", "fh", "g", "gh", "h", "j", "k", "kr", "kl", "l", "m", "mn", "mp", "n", "np", "p", "pp", "qu", "r", "s", "sh", "sch", "st", "t", "th", "thr", "tt", "v", "w", "xy", "y", "z");
        $this->code = "";
        for ($i = 0; $i < $this->stringLength; $i++) {
            //$this->code .= chr(rand(97,122));
            $this->code .= $c[rand(0, count($c)-1)] . $v[rand(0, count($v)-1)];
        }
        $this->code = strtoupper(substr($this->code, 0, 1)) . substr($this->code, 1, $this->stringLength-1);
        $this->saveToSession();
    }
    
    /**
     * Validate the provided code against the stored code
     * @return Boolean true = pass, false = fail
     * @param $code String the code entered by the user
     */
    public function validateCode($userCode=null)
    {
        if (is_null($userCode)) $userCode = $_REQUEST[$this->getName()];
        $temp = $this->retrieveFromSession();
        $valid = isset($temp->code) && strtolower($userCode) == strtolower($temp->code);;
        $this->clearSession();
        return $valid;
    }
    
    public function validate()
    {
        $code = $_REQUEST[$this->getName()];
        return $this->validateCode($code);
    }
    
    /**
     * Save code to session
     * @param $code String the code to save
     */
    public function saveToSession()
    {
        Session::setParameter($this->getSessionId(), $this);
    }
    
    /**
     * Get code from the session
     * @return String the code
     */
    public function retrieveFromSession()
    {
        return Session::getParameter($this->getSessionId());
    }
    
    public function clearSession()
    {
        Session::removeParameter($this->getSessionId());
    }
    
    public function setData($data)
    {
        // Do not set data
        parent::setData("");
    }
    
    public function setValue($value)
    {
        // Do not set value
        parent::setValue("");
    }
}