<?php
class EzPdfMap extends EzPdfElement {
	private $labels = "ABCDEFGHIJKLMNOPQRSTUVWXYZ";
	private $params;
	private $w;
	private $h;
	public function __construct($x=100, $y=1100, $w=1500, $h=600, array $params=null) {
		parent::__construct($x, $y, "");
		$this->w = $w;
		$this->h = $h;
		$defaults = array(
			"zoom" => 10,
			"center" => array(
				"name" => "Home",
				"address" => "3807 NE 168th Street",
				"city" => "North Miami Beach",
				"state" => "FL",
				"zip" => "33160"
			),
			"markers" => array(
				array(
					"name" => "Location 1",
					"address" => "1111 Lincoln Road",
					"city" => "Miami Beach",
					"state" => "FL",
					"zip" => "33139"
				),
				array(
					"name" => "Location 2",
					"address" => "168 SE 1st Street, Ste 1000",
					"city" => "Miami",
					"state" => "FL",
					"zip" => "33131"
				),
			)
		);
		
		$this->params = is_array($params) ? array_merge($defaults, $params) : $defaults;
	}
	
	public function __get($key) {
		return isset($this->params[$key]) ? $this->params[$key] : null;
	}
	
	public static function formatAddress($address) {
		$out = "";
		if (is_array($address)) {
			$fieldKeys = array("address", "city", "state", "zip");
			$data = array_intersect_key($address, array_flip($fieldKeys));
			$out = implode(",", $data);
		}
		return urlencode($out);
	}
	

	public function getMap() {
		// Get the zip
		$zip = isset($this->center["zip"]) ? $this->center["zip"] : "00000";
		$path = $GLOBALS["settings"]->path->files . "/maps/{$zip}.png";
		
		// If the file doesn't exist, get it!
		if (!file_exists($path)) {
			// Get map center and marker
			$centerAddress = self::formatAddress($this->center);
			$markers = "&markers=color:blue|label:A|{$centerAddress}";
			
			// Add other markers
			if (is_array($this->markers) && count($this->markers)) {
				$i = 1;
				foreach($this->markers as $marker) {
					$address = self::formatAddress($marker);
					$label = substr($this->labels, $i, 1);
					$markers .= "&markers=color:green|label:{$label}|{$address}";
					$i++;
				}
			}
			
			// Generate and save the map
			$w = round($this->w/2);
			$uri = "http://maps.google.com/maps/api/staticmap?sensor=true&maptype=roadmap&zoom={$this->zoom}&size={$w}x{$this->h}&center={$centerAddress}{$markers}";
			$uri = "http://maps.google.com/maps/api/staticmap?sensor=true&maptype=roadmap&size={$w}x{$this->h}{$markers}";
			
			copy($uri, $path);
		}
		
		// Return the path to the file
		return $path;
	}
	
	public function render(EzPdfWrapper &$pdfWrapper) {
		$map = $this->getMap();
		$x = $pdfWrapper->getX($this->x);
		$y = $pdfWrapper->getY($this->y);
		$w = $pdfWrapper->pixelsToPoints($this->w/2);
		$h = $pdfWrapper->pixelsToPoints($this->h);
		
		switch (strtolower(substr($map, -3))) {
			case "jpg":
				$pdfWrapper->pdf->addJpgFromFile($map, $x, $y-$h, $w, $h);
				break;
			case "png":
			default:
				$pdfWrapper->pdf->addPngFromFile($map, $x, $y-$h, $w, $h);
				break;
		}
		
		$this->renderAddressLabel($pdfWrapper, $this->center, "A", $x+$w, $y);
		
		// Add markers
		if (is_array($this->markers) && count($this->markers)) {
			$i = 1;
			foreach($this->markers as $marker) {
				$label = substr($this->labels, $i, 1);
				$this->renderAddressLabel($pdfWrapper, $marker, $label, $x+$w, $y, $i);
				$i++;
			}
		}
	}
	
	public function renderAddressLabel(EzPdfWrapper &$pdfWrapper, $marker, $label, $x, $y, $i=0) {
		$pdfWrapper->pdf->addText($x+10, $y - ($i*37) - 10, 10, "<b>{$label}</b>");
		//$pdfWrapper->pdf->addText($x+25, $y - ($i*37) - 10, 10, $value);
		$dy = 0;
		if (isset($marker["name"])) {
			$value = $marker["name"];
			if (isset($marker["distance"])) {
				$value .= " (" . $marker["distance"] . " mi.)";
			}
			$pdfWrapper->pdf->addText($x+25, $y - ($i*37) - 10 - $dy, 10, "<b>{$value}</b>");
			$dy += 12;
		}
		if (isset($marker["address"])) {
			$pdfWrapper->pdf->addText($x+25, $y - ($i*37) - 10 - $dy, 8, $marker["address"]);
			$dy += 12;
		}
		if (isset($marker["city"]) || isset($marker["state"]) || isset($marker["zip"])) {
			$value = (isset($marker["city"]) ? ($marker["city"] . ", ") : "")
				. (isset($marker["state"]) ? ($marker["state"] . " ") : "")
				. (isset($marker["zip"]) ? ($marker["zip"]) : "");
			$pdfWrapper->pdf->addText($x+25, $y - ($i*37) - 10 - $dy, 8, $value);
		}
		
		$dy = 12;
		if (isset($marker["phone"])) {
			$pdfWrapper->pdf->addText($x+140, $y - ($i*37) - 10 - $dy, 8, "T: " . $marker["phone"]);
			$dy += 10;
		}
		if (isset($marker["email"])) {
			$pdfWrapper->pdf->addText($x+140, $y - ($i*37) - 10 - $dy, 8, "E: " . $marker["email"]);
		}
	}
}
?>