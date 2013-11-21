<?php
namespace Qi\Form;

/**
 * Input Node
 */
class Input extends Element
{
    /**
     * Constructor is protected, meaning Input cannot be instantiated
     * @param $inputType InputType
     * @param $name Object
     * @param $value Object
     * @param $title Object[optional]
     * @param $required Object[optional]
     * @param $disabled Object[optional]
     */
    public function __construct($type, $name, array $properties=null)
    {
        $properties["type"] = $type;
        parent::__construct("input", $name, $properties);
        $this->addClass("qf-{$type}-input");
    }
    
    /**
     * Override Element->getNode
     * @return DOMNode The DOMNode for this element
     * @param $dom DOMDocument
     */
    public function &getNode(DOMDocument &$dom=null)
    {
        $node = parent::getNode($dom);

        switch ($this->type) {
            case InputType::HIDDEN:
                // Hidden inputs render input only
                        $wrapNode = $dom->createElement("div");
                        $wrapNode->appendChild($node);
                return $wrapNode;
                break;
            default:
                // Standard inputs render input with wrapper
                $wrapperNode = $dom->createElement("div");
                $wrapperNode->setAttribute("class", "qf-input-wrapper qf-{$this->type}-input-wrapper");
                $wrapperNode->setAttribute("id", $this->getId() . self::PREFIX_SEPARATOR . "wrapper");

                $spanNode = $dom->createElement("span");
                $spanNode->setAttribute("class", "qf-input-span qf-{$this->type}-input-span");
                $spanNode->setAttribute("id", $this->getId() . self::PREFIX_SEPARATOR . "span");
                $spanNode->appendChild($node);

                if (!is_null($this->label)) $labelNode = $this->generateLabel($dom);
                switch ($this->type) {
                    case InputType::CHECKBOX:
                    case InputType::RADIO:
                        // Label after input for check / radio
                        $wrapperNode->appendChild($spanNode);
                        if ($labelNode) $wrapperNode->appendChild($labelNode);
                        break;
                    default:
                        if ($this->prompt) {
                            // Create the prompt
                            $promptNode = $dom->createElement("label");
                            $promptNode->setAttribute("id", $this->getId() . self::PREFIX_SEPARATOR . "prompt");
                            $promptNode->setAttribute("for", $this->getId());
                            $promptNode->setAttribute("class", "qf-watermark");
                            $promptNode->setAttribute("style", "display:none");
                            $promptNode->nodeValue = $this->prompt;
                            $spanNode->appendChild($promptNode);
                        }
                        // Label before input for all else
                        if (isset($labelNode)) $wrapperNode->appendChild($labelNode);
                        $wrapperNode->appendChild($spanNode);
                        break;
                }
                return $wrapperNode;
                break;
        }
    }
}
