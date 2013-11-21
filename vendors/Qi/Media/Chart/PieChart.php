<?php


class PieChart
{
    var $imageWidth = 400;
    var $imageHeight = 300;
    var $bgR = 255;
    var $bgG = 255;
    var $bgB = 255;
    var $title = "Pie Chart";


    function create($varDesc, $varValues)
    {
        Header("Content-type: image/png");
        $image = ImageCreate($this->imageWidth, $this->imageHeight);


        $bgcolor = ImageColorAllocate($image, 
            $this->bgR, $this->bgG, $this->bgB);

        $white = ImageColorAllocate($image, 255, 255, 255);
        $black = ImageColorAllocate($image, 0, 0, 0);
        ImageFill($image, 0, 0, $bgcolor);

        $num = 0;
        foreach($varDesc as $v)
        {
            $r = rand (0, 255);
            $g = rand (0, 255);
            $b = rand (0, 255);

            $sliceColors[$num] = ImageColorAllocate($image, $r, $g, $b);
            $num++;
        }

        // now $num has the number of elements

        // draw the box
        ImageLine($image, 0, 0, $this->imageWidth - 1, 0, $black);
        ImageLine($image, $this->imageWidth - 1, 0, $this->imageWidth - 1, $this->imageHeight - 1, $black);
        ImageLine($image, $this->imageWidth - 1, $this->imageHeight - 1, 0, $this->imageHeight - 1, $black);
        ImageLine($image, 0, $this->imageHeight - 1, 0, 0, $black);


        $total = 0;
        for ($x = 0; $x < $num; $x++)
        {
            $total += $varValues[$x];
        }

        // convert each slice into corresponding percentage of 360-degree circle
        for ($x = 0; $x < $num; $x++)
        {
            $angles[$x] = ($varValues[$x] / $total) * 360;
        }


        for($x = 0; $x < $num; $x++)
        {
            // calculate and draw arc corresponding to each slice
            ImageArc($image, 
                $this->imageWidth/4, 
                $this->imageHeight/2, 
                $this->imageWidth/3, 
                $this->imageHeight/3, 
                $angle,
                ($angle + $angles[$x]), $sliceColors[$x]);

            $angle = $angle + $angles[$x];

            $x1 = round($this->imageWidth/4 + ($this->imageWidth/3 * cos($angle*pi()/180)) / 2);
            $y1 = round($this->imageHeight/2 + ($this->imageHeight/3 * sin($angle*pi()/180)) / 2);

            // demarcate slice with another line
            ImageLine($image, 
                $this->imageWidth/4,
                $this->imageHeight/2, 
                $x1, $y1, $sliceColors[$x]);

            
        }

        // fill in the arcs
        $angle = 0;
        for($x = 0; $x < $num; $x++)
        {
            $x1 = round($this->imageWidth/4 + 
                ($this->imageWidth/3 * cos(($angle + $angles[$x] / 2)*pi()/180)) / 4);
            $y1 = round($this->imageHeight/2 + 
                ($this->imageHeight/3 * sin(($angle + $angles[$x] / 2)*pi()/180)) / 4);

            ImageFill($image, $x1, $y1, $sliceColors[$x]);

            $angle = $angle + $angles[$x];
        }


        // put the desc strings
        ImageString($image, 5, $this->imageWidth/2, 60, "Legend", $black);
        for($x = 0; $x < $num; $x++)
        {
            $fl = sprintf("%.2f", $varValues[$x] * 100 / $total);
            $str = $varDesc[$x]." (".$fl."%)";
            ImageString($image, 3    , $this->imageWidth/2, ($x + 5) * 20, $str, $sliceColors[$x]);
        }

        // put the title
        ImageString($image, 5, 20, 20, $this->title, $black);
        

        ImagePng($image);
        ImageDestroy($image);

    }
}
