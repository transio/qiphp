<?php
namespace Qi\Controller;

class Factory
{
    public static function create(\Qi\Application $application, \Qi\Http\Resource $resource)
    {
        $controller = new \Qi\Token\Token($resource->getController(), \Qi\Token\TokenCase::UNDERSCORE);
        $controller = $controller->toTitle();
        
        // If no controller is specified, use the default controller
        $controller = $controller->__toString() ? "{$controller}Controller" : $application->getConfig()->read("default.controller");
        
        $path = BASE_PATH . "/app/controllers/";
        $namespace = "\\app\\controllers\\";
        
        // If the controller doesn't exist, throw an exception
        if (!file_exists("{$path}{$controller}.php")) throw new Exception("Controller not found: {$controller}");

        // Load the controller
        $controller = $namespace . $controller;

        return new $controller($application, $resource);
    }
}