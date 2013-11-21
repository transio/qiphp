<?php
namespace Qi\Media\Chart;

class ChartSequence
{
    private $title;
    private $color;
    private $values = array();
    public $visible = true;

    public function __construct($title, $values, $color) {
        if (!is_array($values) || count($values) == 0) {
            //throw new Exception("No values specified!");
            $this->visible = false;
        }
        $this->title = $title;
        $this->values = $values;
        $this->color = $color;
    }
    public function __get($property) {
        switch ($property) {
            case "title":
                return $this->title;
                break;
            case "values":
                return $this->values;
                break;
            case "color":
                return $this->color;
                break;
        }
    }
}
    
