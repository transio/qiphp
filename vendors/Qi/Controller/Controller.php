<?php
namespace Qi\Controller;

/**
 * The Controller class controls Qi Requests
 */
abstract class Controller
{    
    protected $_application;
    protected $_resource;
    private $_params;
    
    public function __construct(\Qi\Application $application, \Qi\Http\Resource $resource)
    {
        $this->_application = $application;
        $this->_resource = $resource;
        $this->_params = array();
    }
    
    /**
     * Sets a parameter for use in associated views
     * @param Variant $key
     * @param Variant $value
     */
    public function __set($key, $value)
    {
        $this->_params[$key] = $value;
    }
    
    /**
     * Retrives a parameter for use in associated views
     * @param Variant $key
     * @return Variant 
     */
    public function __get($key)
    {
        return isset($this->_params[$key]) ? $this->_params[$key] : null;
    }
    
    /**
     * Executed before the action is called
     */
    protected function beforeFilter() {
    }
    
    /**
     * Executed before the view is rendere
     */
    protected function beforeRender() {
    }
    
    /**
     * Executed after the view is rendere
     */
    protected function afterRender() {
    }
    
    /**
     * Executed after the action is called
     */
    protected function afterFilter() {
    }
    
    /**
     * Tells the Executor to execute the action defined by the associated Resource
     */
    public function execute() {
        if (!method_exists($this, $this->_resource->getAction()))
            throw new \Qi\Http\Exception\Http404Exception("Action not found: {$this->_resource->action}");
        $this->beforeFilter();
        call_user_func_array(array($this, $this->_resource->getAction()), $this->_resource->getArgs());
        $this->afterFilter();
    }
    
    /**
     * 
     * @param type $view
     * @param type $_params
     * @return type 
     */
    public function render($view=null, array $params=array())
    {
        $this->beforeRender();
        
        // If no view name provided, use the name of the calling controller/action as the view name
        if (empty($view)) {
            $callers = debug_backtrace();
            $pos = strrpos($callers[1]['class'], "\\")+1;
            $controller = strtolower(substr($callers[1]['class'], $pos, -10));
            $view = $controller . DS . $callers[1]['function'];
        }
        
        // Merge in externally set parameters with passed-in _params
        if (!empty($params))
            $this->+params = array_merge($this->_params, $params);
        
        // Generate the view file path
        $path = BASE_PATH . DS . "app" . DS . "views" . DS . $view . ".php";
        
        // If the view doesn't exist, throw an exception
        if (!file_exists($path)) throw new Exception("View not found ({$path}).");

        // Render the view
        include($path);
        
        $this->afterRender();
    }
}

