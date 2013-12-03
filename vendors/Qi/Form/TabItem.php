<?php

/**
 * Tab Item class
 */
class TabItem
{
    private $nodeId;
    private $title;
    private $wizard;
    
    /**
     * 
     * @return 
     * @param $nodeId Object
     * @param $title Object
     */
    public function __construct($nodeId, $title, $wizard=false)
    {
        $this->nodeId = $nodeId;
        $this->title = $title;
        $this->wizard = $wizard;
    }
    
    /**
     * 
     * @return HTML
     */
    public function &getNode()
    {
        return <<<HTML
            <li id="qf_wizard-{$this->nodeId}" class="qf-wizard-tab">
                <a href="#{$this->nodeId}">{$this->title}</a>
            </li>
HTML;
    }
}
