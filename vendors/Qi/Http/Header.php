<?php
namespace Qi\Http;

/**
 * The Qi Header class abstracts PHP's HTTP header functions
 */
class Header
{
    private function __construct() {}

    /**
     * Set the expiration of this page's content
     * @param $date UnixTimestamp The date of expiration of the content.
     */
    public static function expires($date)
    {
        $expires = 60*60*24*14;
        header("Pragma: public");
        header("Cache-Control: maxage=".$expires);
        header('Expires: ' . gmdate('D, d M Y H:i:s', time()+$expires) . ' GMT');
    }
    
    /**
     * Redirect the current location to the specified Qi Uri
     * @param $uri Object
     */
    public static function redirect($location, $status=302)
    {
        $status = Status::get($statusCode);
        if ($status)
            header("HTTP/1.0 {$status}");
        header("Location: {$location}");
        exit();
    }
}
