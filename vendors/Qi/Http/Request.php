<?php
namespace Qi\Http;

/**
 * The HttpRequest class abstracts Request URIs
 */
class Request {
    const RESOURCE = "qiresource";
    
    public function getResource() {
        $resource = isset($_GET[self::RESOURCE]) ? $_GET[self::RESOURCE] : "";
        return Resource::parse($this->getResource());
    }
    
    public function getParams() {
        $params = $_GET;
        if (isset($params[self::RESOURCE]))
            unset($params[self::RESOURCE]);
        return $params;
    }
    
    public static function getParameter($parameter) {
        return $_REQUEST[$parameter];
    }
    
    public function &getContent() {
        switch (Server::getRequestMethod()) {
            case Method::GET:
                return $_GET;
            default:
                return $_POST;
        }
    }
    
    public function matches($pattern) {
        $pattern = str_replace('/', '\/', $pattern);
        $pattern = str_replace('\\/', '\/', $pattern);
        $pattern = "/{$pattern}/";
        return preg_match($pattern, $this->getResource());
    }
}
