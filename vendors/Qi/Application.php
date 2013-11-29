<?php
namespace Qi;

class Application
{
    private $_config;
    
    public function __construct(Config $config)
    {
        $this->_config = $config;
    }
    
    public function run()
    {
        try {
            // Get the resource
            $resource = \Qi\Http\Resource::parse();
        
            // Build the controller
            $controller = \Qi\Controller\Factory::create($this, $resource);
            
            // Execute it
            $controller->execute();
            
        } catch (Exception $e) {
            // Unhandled exception
            print($e->getMessage());
            print_r($e->getTrace());
        }
    }

    public function getConfig()
    {
        return $this->_config;
    }
}


