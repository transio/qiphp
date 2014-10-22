<?php

class ZendPdfBB {
    private $params;
    private $bullet = null;
    private $pdf;
    
    public function __construct(ZendPdf $pdf, array $params = null) {
        $this->pdf =& $pdf;
        
        $defaults = array(
            "[H1]" => array("font-size" => 16, "line-height" => 20, "color" => 0x000000, "margin-bottom" => 8, "font-face" => Zend_Pdf_Font::FONT_HELVETICA_BOLD),
            "[H2]" => array("font-size" => 14, "line-height" => 18, "color" => 0x000000, "margin-bottom" => 8, "font-face" => Zend_Pdf_Font::FONT_HELVETICA_BOLD),
            "[H3]" => array("font-size" => 12, "line-height" => 16, "color" => 0x000000, "margin-bottom" => 8, "font-face" => Zend_Pdf_Font::FONT_HELVETICA_BOLD),
            "[H4]" => array("font-size" => 10, "line-height" => 14, "color" => 0x000000, "margin-bottom" => 8, "font-face" => Zend_Pdf_Font::FONT_HELVETICA_BOLD),
            "[P]" => array("font-size" => 10, "line-height" => 12, "color" => 0x000000, "margin-bottom" => 8, "font-face" => Zend_Pdf_Font::FONT_HELVETICA),
            "[LIST]" => array("margin-bottom" => 8, "bullet" => "*"),
            "[LIST1]" => array("margin-bottom" => 8, "bullet" => 1),
            "[*]" => array("type" => "list", "font-size" => 10, "line-height" => 12, "color" => 0x000000, "margin-bottom" => 2, "margin-left" => 32, "font-face" => Zend_Pdf_Font::FONT_HELVETICA)
        );
        $this->params = count($params) ? array_merge($defaults, $params) : $defaults;
    }
    
    public function drawBBCode($value) {
        if (get_class($this->value) == "SplFileObject") {
            $lines =& $this->value;
        } else {
            $lines = explode("\n", $this->value);
        }
        foreach ($lines as $line) {
            $line = str_replace("\n", " ", $line);
            $line = preg_split('/(\[[A-Z1-5\*\/]*\])/', $line, -1, PREG_SPLIT_NO_EMPTY | PREG_SPLIT_DELIM_CAPTURE);
            if (count($line) >= 1) {
                $tag = strtoupper($line[0]);
                $text = isset($line[1]) ? $line[1] : "";
                switch ($tag) {
                    case "[H1]":
                    case "[H2]":
                    case "[H3]":
                    case "[H4]":
                    case "[H5]":
                    case "[P]":
                    case "[*]":
                        $params = $this->params[$tag];
                        $params["bullet"] = $this->bullet;
                        $this->pdf->drawWrappedText($text, $params);
                        if (is_numeric($this->bullet) $this->bullet++;
                        break;
                    case "[LIST]":
                    case "[LIST1]":
                        // Start of list margins
                        $params = $this->params[$tag];
                        if (isset($params["margin-top"])) $this->pdf->setDy(-$params["margin-top"]);
                        if (isset($params["bullet"])) $this->bullet = $params["bullet"];
                        break;
                    case "[/LIST]":
                    case "[/LIST1]":
                        // End of list margins
                        $token = "[".strtoupper(substr($tag, 2));
                        $params = $this->params[$token];
                        if (isset($params["margin-bottom"])) $this->pdf->setDy(-$params["margin-bottom"]);
                        $this->bullet = null;
                        break;
                    case "[NEWPAGE]":
                        $pdf->newPage(null, true);
                        $this->resetMargins($pdf);
                        break;
                    default:
                        $params = $this->params["[P]"];
                        if (count($line) == 1) {
                            $this->addText($line[0], $params, $pdf);
                        } else {
                            $this->addText(trim($line[0])." ".trim($text), $params, $pdf);
                        }
                        break;
                }
            }
        }
    }
    
    
    public function addText($value, &$params, &$pdf) {
    
        if (isset($params["type"])) {
            switch ($params["type"]) {
                case "list":
                    $delim = "";
                    if ($this->bullet === "*") {
                        $delim = "<b>•</b>";
                    } else {
                        $this->bullet++;
                        $delim = "{$this->bullet}.";
                    }
                    $pdf->pdf->addText($pdf->template->params["margin-left"]+4, $this->y-11, $params["size"], $delim);
                    break;
                case "toc":
                    if (isset($params["page"])) {
                        $w = $pdf->getWidth();
                        $x1 = $pdf->pdf->getTextWidth($params["size"], $value) + $params["margin-left"] + $pdf->getTemplate()->get("margin-left");
                        $x2 = $pdf->pdf->getTextWidth($params["size"], $params["page"]) + $pdf->getTemplate()->get("margin-right");
                        $pdf->pdf->saveState();
                        $pdf->pdf->setLineStyle(1, "round", "", array(0,10));
                        $pdf->pdf->line($x1+5, $this->y-11, $w-$x2-5, $this->y-11);
                        $pdf->pdf->restoreState();
                        $pdf->pdf->addText($w-$x2, $this->y-11, $params["size"], $params["page"]);
                    }
                    break;
            }
        }
        
        if (isset($params["margin-top"])) $pdf->pdf->ezSetDy(-$params["margin-top"]);
        if (isset($params["font-weight"]) && $params["font-weight"] == "bold") $value = "<b>" . $value . "</b>";
        $options = array(
            "justification" => isset($params["justification"]) ? $params["justification"] : "left",
            "leading" => $params["size"]+1,
            "left" => isset($params["margin-left"]) ? $params["margin-left"] : 0,
            "right" => isset($params["margin-right"]) ? $params["margin-right"] : 0
        );
        $this->y = $pdf->pdf->ezText($value, $params["size"], $options);
        if (isset($params["margin-bottom"])) {
            $pdf->pdf->ezSetDy(-$params["margin-bottom"]);
            $this->y -= $params["margin-bottom"];
        }
        return $this->y;
    }
}
?>