<?php
namespace Qi\Mvc\Controller;

class Factory
{
    public static function create(\Qi\Application $application, \Qi\Http\Resource $resource)
    {
        $application->getConfig()->read("path.controllers");
        
        $controllerName = "\\App\\Controller\\" . $resource->getControllerName();
        $controller = new $controllerName($application, $resource);
    }
}