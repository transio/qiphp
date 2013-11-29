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
            
        } catch (\Exception $e) {
            if (DEBUG) {
                print("<h1>" . $e->getMessage() . "</h1>");
                pr($e->getTrace());
            } else {
                // Graceful handling in shutdown function
                throw $e;
            }
        }
    }

    public function &getConfig()
    {
        return $this->_config;
    }
}


