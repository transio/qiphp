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
     * @return \DOMElement
     * @param $dom Object
     */
    public function &getNode(\DOMDocument &$dom=null)
    {
        $aNode = $dom->createElement("a");
        $aNode->setAttribute("href", "#{$this->nodeId}");
        $aNode->nodeValue = $this->title;
        $node = $dom->createElement("li");
        if ($this->wizard) {
            $node->setAttribute("id", "qf_wizard" . self::PREFIX_SEPARATPR . $this->nodeId);
            $node->setAttribute("class", "qf-wizard-tab");
        }
        
        $node->appendChild($aNode);
        return $node;
    }
}
