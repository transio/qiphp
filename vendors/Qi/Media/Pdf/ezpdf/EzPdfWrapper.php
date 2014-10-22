<?php
// Suppress all messages so as not to damage the PDF
ini_set('display_errors', 0);
error_reporting(E_ERROR);

require_once("ezpdf/class.ezpdf.php");
class EzPdfWrapper {
    public $params;
    public $template;
    public $pdf;
    private $page;
    
    public function __construct(array $params=null) {
        $defaults = array(
            "w" => 8.5,
            "h" => 11,
            "dpi" => 300,
            "points" => 72,
            "xOffset" => 0,
            "yOffset" => 0
        );
        
        // Set parameters
        $this->params = count($params) ? array_merge($defaults, $params) : $defaults;
        
        // Set template if provided
        $this->template = isset($params["template"]) ? $params["template"] : null;
        
        // Set up the EZ PDF Object
        $this->pdf = new Cezpdf(array(0, 0, $this->w * $this->points, $this->h * $this->points));
        
        $this->page = 1;
        
        // Render the BG of the first page
        $this->renderBg();
        
        // Reset fonts
        $this->resetFont();
    }
    
    public function __get($param) {
        return $this->get($param);
    }
    
    public function get($param) {
        return isset($this->params[$param]) ? $this->params[$param] : null;
    }
    
    public function getWidth() {
        return $this->w * $this->points;
    }
    
    public function getHeight() {
        return $this->h * $this->points;
    }
    
    public function getPage() {
        return $this->page;
    }
    
    public function render($fileName=null) {
        $output = $this->pdf->ezOutput();

        if (!is_null($fileName)) {
            //header("Content-Disposition: attachment; filename=\"{$fileName}\"");
        } else {
            //header("Content-Disposition: attachment");
        }
        header("Content-Type: application/pdf");
        header("Pragma: private");
        header("Cache-Control: private");
        header("Content-Length: " . strlen($output));
        //header("Cache-Control: max-age=290304000, public");
        //header("Expires: " . date("D, d M Y H:i:s", time() + 290304000) . " GMT");

        print($output);
        exit();
    }
    
    public function setTemplate(EzPdfTemplate $template) {
        $this->template = $template;
    }
    
    public function &getTemplate() {
        return $this->template;
    }
    
    public function newPage(EzPdfTemplate $newTemplate=null, $ez=false) {
        // Set template for this page if specified
        if (!is_null($newTemplate)) {
            $this->template = $newTemplate;
        }
        
        // New Page
        if ($ez) {
            $this->pdf->ezNewPage();
        } else {
            $this->pdf->newPage();
        }
        $this->page++;
        
        // Render BG
        $this->renderBg();
        
        // Reset Fonts
        $this->resetFont();
    }
    
    public function renderBg() {
        // Background Image
        if (!is_null($this->template)) {
            $bg = $this->template->getBg();
            if (!is_null($bg) && $bg) {
                switch (strtolower(substr($bg, -3))) {
                    case "jpg":
                        $this->pdf->addJpgFromFile($bg, 0, 0, $this->w * $this->points, $this->h * $this->points);
                        break;
                    case "gif":
                        $this->pdf->addGifFromFile($bg, 0, 0, $this->w * $this->points, $this->h * $this->points);
                        break;
                    case "png":
                        $this->pdf->addPngFromFile($bg, 0, 0, $this->w * $this->points, $this->h * $this->points);
                        break;
                }
            }
        }
    }
    
    public function resetFont() {
        if (!is_null($this->template)) {
            $this->pdf->selectFont($this->template->getFont());
            $color = $this->template->getColor();
            $this->pdf->setColor($color->r, $color->g, $color->b);
        }
    }
    
    public function getX($x) {
        return $this->pixelsToPoints($x) + $this->xOffset;
    }
    
    public function getY($y) {
        return ($this->h * $this->points) - $this->pixelsToPoints($y) + $this->yOffset;
    }
    
    public function pixelsToPoints($val) {
        return round($val * $this->points / $this->dpi);
    }
}
?>
