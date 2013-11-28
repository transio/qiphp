<?php
namespace Qi\Controller;

/**
 * The RestfulExecutor class controls RESTful Qi Requests
 */
abstract class RestfulController extends Controller {
    public function execute() {
        $action = "{$this->_resource->action}_{$this->_resource->method}"
        if (!method_exists($this, $action))
            throw new \Qi\Http\Exception\Http404Exception("Action not found: {$action}");
        
        $this->beforeFilter();
        call_user_func_array(array($this, $action), $this->_resource->args);
        $this->afterFilter();
    }
}
