<?php
namespace Qi\Media\Chart;

class Chart
{
    protected $title;
    protected $titleColor;
    protected $labels = array();
    protected $sequences = array();
    protected $dataType;
    protected $chartGraphic;

    public function __construct(ChartGraphic $chartGraphic, $dataType=DataType::PERCENTAGE) {
        $this->chartGraphic = $chartGraphic;
        $this->dataType = $dataType;
    }

    public function __destruct() {
        unset($this->chartGraphic);
        unset($this->values);
        unset($this->labels);
        unset($this->sequence);
    }

    /**
      * Sets the chart title
      */

    function setTitle($title, $color=false) {
        //if (isset($this->titleColor)) imagecolordeallocate($this->image, $this->titleColor);
        $this->title = $title;
        $this->titleColor = $color;
    }

    /**
      * Sets the labels for the data "columns"
      */
    function setLabels(array $labels) {
        $this->labels = $labels;
    }

    /**
      * Adds a data sequence to the chart
      */
    function addSequence(ChartSequence $sequence) {
        if (count($this->labels) == 0) {
            throw new Exception("Must set labels before adding sequence.");
        }
        if (count($sequence->values) != count($this->labels)) {
            $sequence->visible = false;
            //throw new Exception("Sequence values do not match label values.");
        }
        $this->sequences[] = $sequence;
    }

    /**
      * DEPRECATED - Sets the axis color
      *
      */
    public function setAxisColor($color) {
        //if (isset($this->axisColor)) imagecolordeallocate($this->image, $this->axisColor);
        $this->axisColor = $color;
    }
    
    /**
      * Translates a value for presentation
      *
      * @return translated value
      */
    protected static function getValue($value, $dataType) {
        switch ($dataType) {
        case DataType::INTEGER:
            $value = (int) round($value);
        case DataType::NUMERIC:
            return $value > 0 ? $value : 0;
            break;
        case DataType::PERCENTAGE:
            return ($value > 0 ? $value : 0) . "%";
            break;
        default:
            return $value;
        }
    }
}
    