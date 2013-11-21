<?php
namespace Qi\Http;

class Status extends \Qi\Archetype\Enum
{
    const HTTP_CONTINUE = "100 Continue";
    const SWITCHING_PROTOCOLS = "101 Switching Protocols";
    const OK = "200 OK";
    const CREATED = "201 Created";
    const ACCEPTED = "202 Accepted";
    const NON_AUTHORITATIVE_INFORMATION = "203 Non-Authoritative Information";
    const NO_CONTENT = "204 No Content";
    const RESET_CONTENT = "205 Reset Content";
    const PARTIAL_CONTENT = "206 Partial Content";
    const MULTIPLE_CHOICES = "300 Multiple Choices";
    const MOVED_PERMANENTLY = "301 Moved Permanently";
    const FOUND = "302 Found";
    const SEE_OTHER = "303 See Other";
    const NOT_MODIFIED = "304 Not Modified";
    const USE_PROXY = "305 Use Proxy";
    const TEMPORARY_REDIRECT = "307 Temporary Redirect";
    const BAD_REQUEST = "400 Bad Request";
    const UNAUTHORIZED = "401 Unauthorized";
    const PAYMENT_REQUIRED = "402 Payment Required";
    const FORBIDDEN = "403 Forbidden";
    const NOT_FOUND = "404 Not Found";
    const METHOD_NOT_ALLOWED = "405 Method Not Allowed";
    const NOT_ACCEPTABLE = "406 Not Acceptable";
    const PROXY_AUTHENTICATION_REQUIRED = "407 Proxy Authentication Required";
    const REQUEST_TIMEOUT = "408 Request Timeout";
    const CONFLICT = "409 Conflict";
    const GONE = "410 Gone";
    const LENGTH_REQUIRED = "411 Length Required";
    const PRECONDITION_FAILED = "412 Precondition Failed";
    const REQUEST_ENTITY_TOO_LARGE = "413 Request Entity Too Large";
    const REQUEST_URI_TOO_LONG = "414 Request-URI Too Long";
    const UNSUPPORTED_MEDIA_TYPE = "415 Unsupported Media Type";
    const REQUESTED_RANGE_NOT_SATISFIABLE = "416 Requested Range Not Satisfiable";
    const EXPECTATION_FAILED = "417 Expectation Failed";
    const INTERNAL_SERVER_ERROR = "500 Internal Server Error";
    const NOT_IMPLEMENTED = "501 Not Implemented";
    const BAD_GATEWAY = "502 Bad Gateway";
    const SERVICE_UNAVAILABLE = "503 Service Unavailable";
    const GATEWAY_TIMEOUT = "504 Gateway Timeout";
    const HTTP_VERSION_NOT_SUPPORTED = "505 HTTP Version Not Supported";
    
    public static $CODES = array(
        100 => self::HTTP_CONTINUE,
        101 => self::SWITCHING_PROTOCOLS,
        200 => self::OK,
        201 => self::CREATED,
        202 => self::ACCEPTED,
        203 => self::NON_AUTHORITATIVE_INFORMATION,
        204 => self::NO_CONTENT,
        205 => self::RESET_CONTENT,
        206 => self::PARTIAL_CONTENT,
        300 => self::MULTIPLE_CHOICES,
        301 => self::MOVED_PERMANENTLY,
        302 => self::FOUND,
        303 => self::SEE_OTHER,
        304 => self::NOT_MODIFIED,
        305 => self::USE_PROXY,
        307 => self::TEMPORARY_REDIRECT,
        400 => self::BAD_REQUEST,
        401 => self::UNAUTHORIZED,
        402 => self::PAYMENT_REQUIRED,
        403 => self::FORBIDDEN,
        404 => self::NOT_FOUND,
        405 => self::METHOD_NOT_ALLOWED,
        406 => self::NOT_ACCEPTABLE,
        407 => self::PROXY_AUTHENTICATION_REQUIRED,
        408 => self::REQUEST_TIMEOUT,
        409 => self::CONFLICT,
        410 => self::GONE,
        411 => self::LENGTH_REQUIRED,
        412 => self::PRECONDITION_FAILED,
        413 => self::REQUEST_ENTITY_TOO_LARGE,
        414 => self::REQUEST_URI_TOO_LONG,
        415 => self::UNSUPPORTED_MEDIA_TYPE,
        416 => self::REQUESTED_RANGE_NOT_SATISFIABLE,
        417 => self::EXPECTATION_FAILED,
        500 => self::INTERNAL_SERVER_ERROR,
        501 => self::NOT_IMPLEMENTED,
        502 => self::BAD_GATEWAY,
        503 => self::SERVICE_UNAVAILABLE,
        504 => self::GATEWAY_TIMEOUT,
        505 => self::HTTP_VERSION_NOT_SUPPORTED,    
    );
    
    public static function get($code)
    {
        return isset(self::$CODES[$code]) ? self::$CODES[$code] : null;
    }
}
