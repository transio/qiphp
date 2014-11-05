<?php
namespace app\controllers;

class TestController extends \Qi\Controller\Controller
{
    public function __construct(\Qi\Application $application, \Qi\Http\Resource $resource)
    {
        parent::__construct($application, $resource);
    }
    
    /**
     * Before Filter override
     */
    protected function beforeFilter()
    {
        
    }
    
    public function index()
    {
        $this->name = "test";
        
        $this->form = new \Qi\Form\Form("test");
        //$this->form
        //    ->addElement(new TextInput("test1"))
        //    ->addElement(new TextInput("test2"));
        
        pr($this->form);
        $this->render();
    }
    
    public function getThings() 
    {
        
    }
}
