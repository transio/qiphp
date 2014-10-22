<?php

class EzPdfBBCode extends EzPdfElement {
    private $params;
    private $delimiter = null;
    protected $y;
    protected $toc = array();
    
    public function __construct($value, array $params = null) {
        parent::__construct(0, 0, $value);
        $defaults = array(
            "[H1]" => array("size" => 16, "color" => 0x000000, "margin-top" => 0, "margin-bottom" => 8, "font-weight" => "bold"),
            "[H2]" => array("size" => 14, "color" => 0x000000, "margin-top" => 0, "margin-bottom" => 8, "font-weight" => "bold"),
            "[H3]" => array("size" => 12, "color" => 0x000000, "margin-top" => 0, "margin-bottom" => 8, "font-weight" => "bold"),
            "[H4]" => array("size" => 10, "color" => 0x000000, "margin-top" => 0, "margin-bottom" => 8, "font-weight" => "bold"),
            "[P]" => array("size" => 10, "color" => 0x000000, "margin-top" => 0, "margin-bottom" => 8, "font-weight" => "normal", "justification" => "left"),
            "[LIST]" => array("margin-bottom" => 8, "delimiter" => "*"),
            "[LIST1]" => array("margin-bottom" => 8, "delimiter" => 0),
            "[*]" => array("type" => "list", "size" => 10, "color" => 0x000000, "margin-top" => 0, "margin-bottom" => 4, "margin-left" => 16, "font-weight" => "normal")
        );
        $this->params = count($params) ? array_merge($defaults, $params) : $defaults;
    }
    
    public function render(EzPdfWrapper &$pdfWrapper) {
        //parent::render($pdfWrapper);
        $this->resetMargins($pdfWrapper);
        if (get_class($this->value) == "SplFileObject") {
            $lines =& $this->value;
        } else {
            $lines = explode("\n", $this->value);
        }
        foreach ($lines as $line) {
            $line = str_replace("\n", " ", $line);
            $line = preg_split('/(\[[A-Z1-5\*\/]*\])/', $line, -1, PREG_SPLIT_NO_EMPTY | PREG_SPLIT_DELIM_CAPTURE);
            //if (substr($line, 0, 1) == "[" && 
            //$pdfWrapper->pdf->addText($x, $y, $pointSize, $value);
            if (count($line) >= 1) {
                $tag = strtoupper($line[0]);
                $text = isset($line[1]) ? $line[1] : "";
                switch ($tag) {
                    case "[H1]":
                        $level = 1;
                    case "[H2]":
                        $level = 2;
                        //$page = $pdfWrapper->pdf->ezWhatPageNumber($pdfWrapper->pdf->ezGetCurrentPageNumber());
                        //array_push($this->contents, array("text" => rawurlencode($text), "page" => $page, "level" => $level));
                        //$pdfWrapper->pdf->addDestination('toc'.(count($this->reportContents)-1),'FitH',$info['y']+$info['height']);
                    case "[H3]":
                    case "[H4]":
                    case "[H5]":
                    case "[P]":
                        $params = $this->params[$tag];
                        $this->addText($text, $params, $pdfWrapper);
                        break;
                    case "[*]":
                        $params = $this->params[$tag];
                        $this->addText($text, $params, $pdfWrapper);
                        break;
                    case "[LIST]":
                    case "[LIST1]":
                        // Start of list margins
                        $params = $this->params[$tag];
                        if (isset($params["margin-top"])) $pdfWrapper->pdf->ezSetDy(-$params["margin-top"]);
                        if (isset($params["delimiter"])) $this->delimiter = $params["delimiter"];
                        break;
                    case "[/LIST]":
                    case "[/LIST1]":
                        // End of list margins
                        $token = "[".strtoupper(substr($tag, 2));
                        $params = $this->params[$token];
                        if (isset($params["margin-bottom"])) $pdfWrapper->pdf->ezSetDy(-$params["margin-bottom"]);
                        $this->delimiter = null;
                        break;
                    case "[NEWPAGE]":
                        $pdfWrapper->newPage(null, true);
                        $this->resetMargins($pdfWrapper);
                        break;
                    default:
                        $params = $this->params["[P]"];
                        if (count($line) == 1) {
                            $this->addText($line[0], $params, $pdfWrapper);
                        } else {
                            $this->addText(trim($line[0])." ".trim($text), $params, $pdfWrapper);
                        }
                        break;
                }
            }
        }
    }
    
    public function renderToc(EzPdfWrapper &$pdfWrapper, $appendedText="") {
        $this->resetMargins($pdfWrapper);
        if (get_class($this->value) == "SplFileObject") {
            $lines =& $this->value;
        } else {
            $lines = explode("\n", $this->value . $appendedText);
        }
        
        $pdfWrapper->pdf->setColor(0,0,0);
        $y = $this->addText("Table of Contents", $this->params["[H1]"], $pdfWrapper);
        $page = 1;
        foreach ($lines as $line) {
            $line = str_replace("\n", " ", $line);
            $line = preg_split('/(\[[A-Z1-5\*\/]*\])/', $line, -1, PREG_SPLIT_NO_EMPTY | PREG_SPLIT_DELIM_CAPTURE);
            if (count($line) >= 1) {
                $tag = strtoupper($line[0]);
                $text = isset($line[1]) ? $line[1] : "";
                $params = $this->params["[P]"];
                $params["margin-bottom"] = 4;
                $params["page"] = $page;
                $params["type"] = "toc";
                switch ($tag) {
                    case "[H1]":
                    case "[H2]":
                    //case "[H3]":
                        if ($tag == "[H1]") {
                            $params["margin-left"] = 0;
                            $text = "<b>{$text}</b>";
                        } else {
                            $params["margin-left"] = 10;
                        }
                        
                        //$page = $pdfWrapper->pdf->ezWhatPageNumber($pdfWrapper->pdf->ezGetCurrentPageNumber());
                        $this->addText($text, $params, $pdfWrapper);
                        break;
                    case "[NEWPAGE]":
                        $page++;
                        break;
                }
            }
        }
    }
    
    
    public function resetMargins(&$pdfWrapper) {
        $pdfWrapper->pdf->ezSetMargins($pdfWrapper->template->params["margin-top"], $pdfWrapper->template->params["margin-bottom"], $pdfWrapper->template->params["margin-left"], $pdfWrapper->template->params["margin-right"]);
    }
    
    public function addText($value, &$params, &$pdfWrapper) {
    
        if (isset($params["type"])) {
            switch ($params["type"]) {
                case "list":
                    $delim = "";
                    if ($this->delimiter === "*") {
                        $delim = "<b>•</b>";
                    } else {
                        $this->delimiter++;
                        $delim = "{$this->delimiter}.";
                    }
                    $pdfWrapper->pdf->addText($pdfWrapper->template->params["margin-left"]+4, $this->y-11, $params["size"], $delim);
                    break;
                case "toc":
                    if (isset($params["page"])) {
                        $w = $pdfWrapper->getWidth();
                        $x1 = $pdfWrapper->pdf->getTextWidth($params["size"], $value) + $params["margin-left"] + $pdfWrapper->getTemplate()->get("margin-left");
                        $x2 = $pdfWrapper->pdf->getTextWidth($params["size"], $params["page"]) + $pdfWrapper->getTemplate()->get("margin-right");
                        $pdfWrapper->pdf->saveState();
                        $pdfWrapper->pdf->setLineStyle(1, "round", "", array(0,10));
                        $pdfWrapper->pdf->line($x1+5, $this->y-11, $w-$x2-5, $this->y-11);
                        $pdfWrapper->pdf->restoreState();
                        $pdfWrapper->pdf->addText($w-$x2, $this->y-11, $params["size"], $params["page"]);
                    }
                    break;
            }
        }
        
        if (isset($params["margin-top"])) $pdfWrapper->pdf->ezSetDy(-$params["margin-top"]);
        if (isset($params["font-weight"]) && $params["font-weight"] == "bold") $value = "<b>" . $value . "</b>";
        $options = array(
            "justification" => isset($params["justification"]) ? $params["justification"] : "left",
            "leading" => $params["size"]+1,
            "left" => isset($params["margin-left"]) ? $params["margin-left"] : 0,
            "right" => isset($params["margin-right"]) ? $params["margin-right"] : 0
        );
        $this->y = $pdfWrapper->pdf->ezText($value, $params["size"], $options);
        if (isset($params["margin-bottom"])) {
            $pdfWrapper->pdf->ezSetDy(-$params["margin-bottom"]);
            $this->y -= $params["margin-bottom"];
        }
        return $this->y;
    }
}
?>