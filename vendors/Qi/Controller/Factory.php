<?php
namespace Qi\Controller;

class Factory
{
    public static function create(\Qi\Application $application, \Qi\Http\Resource $resource)
    {
        $path = $application->getConfig()->read("path.controllers");
        $controller = new \Qi\Token\Token($resource->getController(), \Qi\Token\TokenCase::UNDERSCORE);
        $controller = $controller->toTitle();
        $controller = "\\app\\controllers\\{$controller}Controller";
        return new $controller($application, $resource);
    }
}