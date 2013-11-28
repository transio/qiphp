<?php
namespace Qi\Controller;

/**
 * The Controller class controls Qi Requests
 */
abstract class Controller
{    
    private $_application;
    private $_resource;
    private $_params;
    
    public function __construct(Application $application, Resource $resource) {
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
        if (!method_exists($this, $this->_resource->action))
            throw new \Qi\Http\Exception\Http404Exception("Action not found: {$this->_resource->action}");
        $this->beforeFilter();
        call_user_func_array(array($this, $this->_resource->action), $this->_resource->args);
        $this->afterFilter();
    }
    
    /**
     * 
     * @global type $settings
     * @param type $view
     * @param type $_params
     * @return type 
     */
    public function render($view, $_params = null)
    {
        $this->beforeRender();
        
        // Merge in externally set parameters with passed-in _params
        $_params = is_array($_params) ? array_merge($this->_params, $_params) : $this->_params;
        
        // Switch _params to an object for easy reference by the view
        $_params = (object) $_params;
        
        // Get the view path
        $path = $settings->path->app . DIRECTORY_SEPARATOR . $view . ".tpl";
        
        // Render the view
        if (file_exists($path)) {
            include($path);
        } else {
            throw new Exception("View not found ({$path}).");
        }
    
        // Try to load the controller class
        if (!empty($settings->controllers) &&
                array_key_exists($request->pathInfo, $settings->controllers)) {
            $controllerClass = $settings->controllers[$request->pathInfo];
            $controller = new $controllerClass($request);
        } else {
            if (ArchetypeController::exists($request->pathInfo))
            try {
                $controller = new ArchetypeController($request->pathInfo);
            } catch (Exception $e) {
                return null;
            }
        }
        
        $this->afterRender();
    }
}
