<?php
namespace Qi\Security;

 /**
 * The Cryptor class is used to easily encrypt data
 */
class Cryptor
{
    const MD5 = "MD5";
    const MI5 = "MI5";
    const MCRYPT = "MCRYPT";
    
    private $encryptionMethod;
    private $salt;
    private $cipher;
    private $mode;
    private $ivSource;
    
    /**
     * Constructor - creates a Cryptor
     * @param $encryptionMethod Object
     * @param $salt Object[optional]
     * @param $cipher Object[optional]
     * @param $mode Object[optional]
     * @param $ivSource Object[optional]
     */
    public function __construct($encryptionMethod, $salt="", $cipher=MCRYPT_RIJNDAEL_256, $mode=MCRYPT_MODE_ECB, $ivSource=MCRYPT_RAND)
    {
        $this->encryptionMethod = $encryptionMethod;
        $this->salt = strlen($salt) > 16 ? substr($salt, 0, 16) : $salt;
        if ($encryptionMethod == self::MCRYPT) {
            $this->cipher = $cipher;
            $this->mode = $mode;
            $this->ivSource = $ivSource;
        }
    }
    
    /**
     * Encrypts a string using the Encryption Method specified
     * @return String The encrypted data
     * @param $data String The data you would like to encrypt
     */
    public function encrypt($data, $dataSource=null)
    {
        // If the data is coming from Database/Session/Cookie
        // It's already encrypted so just return the encrypted data
        if ($dataSource == DataSource::DATA 
                || $dataSource == DataSource::SESSION 
                || $dataSource == DataSource::COOKIE) {
            return $data;
        }
        
        // Encrypt with proper EncryptionMethod
        switch ($this->encryptionMethod) {
            case self::MD5:
                $data = md5($data . $this->salt);
                break;
            case self::MI5:
                $length = strlen($this->salt);
                $salt2 = substr(md5($this->salt), -$length);
                $data = md5($data . $this->salt);
                $data = md5($salt2 . substr($data, $length));
                break;
            case self::MCRYPT:
                $ivSize = mcrypt_get_iv_size($this->cipher, $this->mode);
                $iv = mcrypt_create_iv($ivSize, $this->ivSource);
                $data = mcrypt_encrypt($this->cipher, $this->salt, $data, $this->mode, $iv);
                $data = trim(base64_encode($data));
                break;
            default:
                throw new Exception("Unsupported encryption method.");
        }
        return $data;
        
    }
    
    /**
     * Decrypts a string using the Encryption Method specified
     * @return String The decrypted data
     * @param $data String The data you would like to decrypt
     */
    function decrypt($data)
    {
        // Decrypt with proper EncryptionMethod
        switch ($this->encryptionMethod) {
            case self::MD5:
            case self::MI5:
                // Can't decrypt MD5 or MI5 - just return the encrypted data
                break;
            case self::MCRYPT:
                $ivSize = mcrypt_get_iv_size($this->cipher, $this->mode);
                $iv = mcrypt_create_iv($ivSize, $this->ivSource);
                $data = base64_decode($data);
                $data = mcrypt_decrypt($this->cipher, $this->salt, $data, $this->mode, $iv);
                $data = trim($data);
                break;
            default:
                throw new Exception("Unsupported encryption method.");
        }
        return $data;        
    } 
}

    
