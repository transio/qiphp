<?php
namespace Qi\Log;

/**
 * The Log class is a utility for Qi exception handling and logging.
 */
class MessageManager
{
    const SESSION_KEY = "qi_message_manager";
    /**
     * Private Constructor
     */
    private function __construct() {}
    
    public static function addMessage($txt, $type=MessageType::INFO)
    {
        // Get the message queue
        $messages = Session::getParameter(self::SESSION_KEY);
        if (!is_array($messages)) {
            $messages = array();
        }
        
        // Create the message
        $message = new stdClass();
        $message->type = $type;
        $message->message = $txt;
        
        // Add message to queue
        array_push($messages, $message);
        Session::setParameter(self::SESSION_KEY, $messages);
    }
    
    public static function hasMessages()
    {
        $messages = Session::getParameter(self::SESSION_KEY);
        return (is_array($messages) && !empty($messages));
    }
    
    public static function getMessages()
    {
        $messages = Session::getParameter(self::SESSION_KEY);
        if (is_array($messages) && !empty($messages)) {
            return $messages;
        } else {
            return array();
        }
    }
    
    public static function purgeMessages()
    {
        Session::setParameter(self::SESSION_KEY, null);
    }
    
}
