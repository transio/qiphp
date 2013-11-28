<?php
namespace Qi\Controller;

class Factory
{
    public static function create(\Qi\Application $application, \Qi\Http\Resource $resource)
    {
        $path = $application->getConfig()->read("path.controllers");
        $controller = (new \Qi\Token\Token($resource->getController(), TokenCase::UNDERSCORE))->toTitle();
        $controllerClass = "\\app\\controllers\\{$controller}Controller";
        $controller = new $controllerClass($application, $resource);
    }
}