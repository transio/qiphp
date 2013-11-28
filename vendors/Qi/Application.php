<?php
namespace Qi;

class Application
{
    private $_config;
    private $_resource;
    private $_controller;
    
    public function __construct(Config $config)
    {
        $this->_config = $config;
`    }
    
    public function run()
    {
        // Get the resource
        $this->_resource = \Qi\Http\Resource::parse();
        
        try {
            // Build and execute the controller
            $this->_controller = new \Qi\Controller\Factory::create($this, $resource);
            $this->_controller->execute();
        } catch (Exception $e) {
            // Unhandled exception
        }
    }
}
