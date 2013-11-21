<?php
namespace Qi\Media\Chart;

class BarChart extends Chart
{
    private $dpi = 96;
    private $left = 60;
    private $right = 0;
    private $top = 40;
    private $bottom = 40;
    private $margin = 12;
    private $spacer = 5;
    
    public function __construct(ChartGraphic $chartGraphic, $dataType=DataType::INTEGER) {
        $this->dpi = $chartGraphic->dpi;
        parent::__construct($chartGraphic, $dataType);
        $this->left = round($this->left * $chartGraphic->dpi / 96);
        $this->right = round($this->right * $chartGraphic->dpi / 96);
        $this->top = round($this->top * $chartGraphic->dpi / 96);
        $this->bottom = round($this->bottom * $chartGraphic->dpi / 96);
        $this->margin = round($this->margin * $chartGraphic->dpi / 96);
        $this->spacer = round($this->spacer * $chartGraphic->dpi / 96);
    }
    
    public function __destruct() {
        parent::__destruct();
    }

    public function setColumnMargin($margin) {
        $this->margin = round($margin * $this->chartGraphic->dpi / 96);
    }

    // Generate the graph
    function generate($imageType=ImageType::PNG, $format=DataFormat::XML, DOMDocument &$dom=null) {
        $chartHeight = $this->chartGraphic->height - $this->top - $this->bottom;
        $chartWidth = $this->chartGraphic->width - $this->left - $this->right;
        $chartBottom = $this->chartGraphic->height - $this->bottom;
        $chartRight = $this->chartGraphic->width - $this->right;

        $black = imagecolorallocate($this->chartGraphic->image, 0, 0, 0);
        $white = imagecolorallocate($this->chartGraphic->image, 255, 255, 255);
        
        $this->chartGraphic->loadFonts("/home/creports/fonts/");

        // Draw the Y axis
        imagefilledrectangle($this->chartGraphic->image, $this->left, $chartBottom + 3, $this->left - 2, $this->top, $black);

        // Draw the X axis
        imagefilledrectangle($this->chartGraphic->image, $this->left, $chartBottom + 3, $chartRight, $chartBottom + 1, $black);

        // Calculate the max and min values of the chart sequences
        $max = array();
        $min = array();
        foreach($this->sequences as $sequence) {
            if ($sequence->visible) {
                $max[$i] = (int) round(max($sequence->values));
                $min[$i] = (int) round(min($sequence->values));
                //echo $max[$i] . " - " . $min[$i] . "<br>";
                $i++;
            }
        }
        $max = count($max) == 0 ? 0 : max($max);
        $min = count($min) == 0 ? 0 : min($min);

        // Find the best incremental order that will give between 5 and 10
        // increments based on the difference between max and min
        $min = round($min - ($max - $min) / 2);
        if ($min < 0) $min = 0;
        $delta = $max - $min;
        $power = strlen($delta);
        $order = pow(10, $power-1);
        if ($delta / $order < 5) {
            $order = round($order / 2);
        }
        $increments = round($delta / $order);

        // Set the new max, min, and number of iterations based upon the order
        // value calculated above
        $min = $min - ($min % $order);
        $max = $max + $order - ($max % $order);
        $delta = $max - $min;
        $iterations = (round($max - $min) / $order) + 1;

        // Put the y axis values on the chart
        $j = 0;
        for($i = $min; $i <= $max; $i+= $order) {
            $y = $this->chartGraphic->height - $this->top - ($j * $chartHeight / ($iterations - 1));
            if ($j) $this->chartGraphic->write(self::getValue($i, $this->dataType), "arial", 9, $this->left - $this->spacer, $y, $black, 0, TextAlign::RIGHT, TextAlign::TOP);
            $j++;
        }


        // Add the title to the image
        $titleWidth = imagefontwidth(3) * strlen($this->title);
        $this->chartGraphic->write(strtoupper($this->title), "arial_bold", 10, $this->chartGraphic->width/2, 0, $black, 0, TextAlign::CENTER, TextAlign::TOP);

        // Add the sequence legend to the image
        if (count($this->sequences) >= 1) {
            for ($i = 0; $i < count($this->sequences); $i++) {
                $sequence = $this->sequences[$i];
                if ($sequence->title != null && strlen($sequence->title) > 0) {
                    $this->chartGraphic->write($sequence->title, "arial", 9, $this->chartGraphic->width - $this->spacer * 3, $i * $this->spacer * 4, $black, 0, TextAlign::RIGHT, TextAlign::TOP);
                    imagefilledrectangle($this->chartGraphic->image, $this->chartGraphic->width - $this->spacer * 2, $i * $this->spacer * 4, $this->chartGraphic->width, $i * $this->spacer * 4 + $this->spacer * 2, $sequence->color);
                }
            }
        }

        // Divide the area for the values
        $columnWidth = $chartWidth / count($this->labels);

        // Write the Column Labels
        for($i = 0; $i < count($this->labels); $i++) {
            $this->chartGraphic->write($this->labels[$i], "arial_narrow_bold", 9, $this->left + $columnWidth * ($i + 0.5), $this->chartGraphic->height - 20, $black, 0, TextAlign::CENTER, TextAlign::BOTTOM);
        }

        // Draw the graphs
        $sequenceWidth = ($columnWidth - $this->margin) / count($this->sequences) - 1;
        for($j = 0; $j < count($this->sequences); $j++) {
            $sequence = $this->sequences[$j];
            if ($sequence->visible) {
                for($i = 0; $i < count($sequence->values); $i++) {
                    $x1 = $this->left + ($columnWidth * $i) + $this->margin/2 + ($sequenceWidth * $j);
                    $x2 = $this->left + ($columnWidth * $i) + $this->margin/2 + ($sequenceWidth * ($j + 0.86));
                    
                    $weight = ($sequence->values[$i] - $min) / $delta;

                    $y1 = $this->top + $chartHeight - ($weight * $chartHeight);
                    $y2 = $this->chartGraphic->height - $this->bottom;
                    
                    if ($sequence->values[$i] > 0) {
                        imagefilledrectangle($this->chartGraphic->image, $x1, $y1, $x2, $y2, $sequence->color);
                    }

                    // Write the value in the bar
                    $barHeight = $y2 - $y1;
                    $value = self::getValue($sequence->values[$i], $this->dataType);
                    $value = number_format($value, 0);
                    list($w, $h) = ChartGraphic::getTextSize($value, "arial_bold", 8);
                    if ($w * $this->dpi / 96 > $barHeight - 25 * $this->dpi / 96) {
                        $this->chartGraphic->write($value, "arial_bold", 8, $x1+($x2-$x1)/2, $y1-5, $black, 90, TextAlign::LEFT, TextAlign::MIDDLE);
                    } else {
                        $this->chartGraphic->write($value, "arial_bold", 8, $x1+($x2-$x1)/2, $y1+5, $white, 90, TextAlign::RIGHT, TextAlign::MIDDLE);
                    }
                }
            }
        }
        
        // Save the image and return the html tag
        $image = $this->chartGraphic->saveImage($imageType);
        if ($format == DataFormat::HTML) {
            return "<img src=\"/images/reports/{$image}\" width=\"{$this->chartGraphic->width}\" height=\"{$this->chartGraphic->height}\" border=\"0\" alt=\"{$this->title}\" />";
        } else {
            //return "<image src=\"/images/reports/{$image}\" width=\"{$this->chartGraphic->printWidth}in\" height=\"{$this->chartGraphic->printHeight}in\" />";
            $node = $dom->createElement("image");
            $node->setAttribute("src", "/images/reports/{$image}");
            $node->setAttribute("width", "{$this->chartGraphic->printWidth}in");
            $node->setAttribute("height", "{$this->chartGraphic->printHeight}in");
            return $node;
        }
    }
}
    
