<?php
namespace Qi\Media\Chart;

final class Alignment {
    const LEFT = "align-left";
    const RIGHT = "align-right";
    const CENTER = "align-right";
    const NONE = false;

    // Private constructor enforces no-instantiation
    private function __construct(){}
}

final class DividerType {
    const LEFT = "divider-left";
    const RIGHT = "divider-right";
    const NONE = false;

    // Private constructor enforces no-instantiation
    private function __construct(){}
}

final class LockType {
    const NONE = 0;
    const DATA = 1;
    const ALL = 2;

    // Private constructor enforces no-instantiation
    private function __construct(){}
}

final class Tabulation {
    const COUNT = "COUNT";
    const MAX = "MAX";
    const MIN = "MIN";
    const SUM = "SUM";
    const AVERAGE = "AVERAGE";
    const WEIGHTED_AVERAGE = "WEIGHTED_AVERAGE";
    const FORMULA = "FORMULA";
    const NONE = false;

    // Private constructor enforces no-instantiation
    private function __construct(){}
}

class TabularChart {
    protected $data;
    protected $title;
    protected $tabulate;
    protected $showRowHeaders;
    protected $columns;
    protected $columnCount = 0;

    public function __construct($title="", $data=null, $tabulate=false, $showRowHeaders=false) {
        $this->data = $data;
        $this->title = $title;
        $this->tabulate = $tabulate;
        $this->showRowHeaders = $showRowHeaders;
        $this->columns = array();
    }

    public function setTitle($title) {
        $this->title = $title;
    }

    public function setDataProvider($data) {
        $this->data = $data;
    }

    public function addColumn(TabularChartColumn $column) {
        $this->columns[$column->name] = $column;
        $this->columnCount ++;
    }

    public function generate() {
    }
}

class XMLTabularChart extends TabularChart {
    public function __construct($title="", $data=null, $tabulate=false, $showRowHeaders=false) {
        parent::__construct($title, $data, $tabulate, $showRowHeaders);
    }
    public function generate(DOMDocument &$dom) {
        $blockNode = null;
        $tableNode = null;
        $colNode = null;
        $rowNode = null;
        $cellNode = null;

        $blockNode = $dom->createElement("table-block");
        $blockNode->setAttribute("id", $this->title);

        // Check if there's data
        $noData = true;
        // If it iterates, there's data
        if ((is_object($this->data) && $this->data->getRecordCount() > 0) || (is_array($this->data) && count($this->data > 0))) {
            $noData = false;
        }
        // Render the table
        if ($noData) {
            $node = $dom->createElement("p", "No data.");
            $blockNode->appendChild($node);
        } else {
            $tableNode = $dom->createElement("table");
            $blockNode->appendChild($tableNode);
            if ($this->showRowHeaders) {
                $colNode = $dom->createElement("col");
                $tableNode->appendChild($colNode);
            }
            foreach ($this->columns as &$column) {
                $colNode = $dom->createElement("col", $column->header);
                if ($column->divider) $colNode->setAttribute("divider", $divider);
                if ($column->width) $colNode->setAttribute("width", $column->width);
                if ($column->type == DataType::GENERAL || $column->type == DataType::DATE) $colNode->setAttribute("align", "left");
                $tableNode->appendChild($colNode);
            }
            foreach ($this->data as $rowHeader => $fields) {
                $class = ($class == "odd" ? "even" : "odd");
                $rowNode = $dom->createElement("row");
                $tableNode->appendChild($rowNode);
                if ($this->showRowHeaders) {
                    $cellNode = $dom->createElement("cell", $rowHeader);
                    $rowNode->appendChild($cellNode);
                }
                foreach ($this->columns as &$column) {
                    $value = $fields[$column->name];
                    $cellNode = $dom->createElement("cell", $column->getValue($value));
                    $rowNode->appendChild($cellNode);
                    if ($this->tabulate) {
                        $weight = ($column->weightColumn) ? $fields[$column->weightColumn] : 1;
                        $column->tabulate($value, $weight);
                    }
                }
            }

            // Render footer tabulation
            if ($this->tabulate) {
                $rowNode = $dom->createElement("row");
                $tableNode->appendChild($rowNode);
                foreach ($this->columns as &$column) {
                    if ($column->weightColumn) {
                        $weightColumn = $this->columns[$column->weightColumn];
                        $weight = $weightColumn->getTabulation();
                    } else {
                        $weight = 1;
                    }
                    $value = $column->getValue($column->getTabulation($weight));
                    $cellNode = $dom->createElement("cell", $value);
                    $cellNode->setAttribute("tabulation", "true");
                    $rowNode->appendChild($cellNode);
                }
            }
        }
        return $blockNode;
    }
}

class HTMLTabularChart extends TabularChart {
    public function __construct($title="", $data=null, $tabulate=false, $showRowHeaders=false) {
        parent::__construct($title, $data, $tabulate, $showRowHeaders);
    }
    
    public function generate() {
        $output = "";
        if ($this->title) {
            $output .= "<h3>$this->title</h3>";
        }
        $output .= "<table class=\"tabular-data\">";
        foreach ($this->columns as &$column) {
            $output .= "<col " . ($column->width ? " width=\"{$column->width}\"" : "") . "\" />";
        }
        $output .= "<thead>";
        $output .= "<tr>";
        if ($this->showRowHeaders) {
            $output .= "<th></th>";
        }
        foreach ($this->columns as &$column) {
            $output .= "<th valign=\"bottom\" class=\"{$column->getClass()}\">{$column->header}</th>";
            //$output .= "<th valign=\"bottom\">{$column->header}</th>";
        }
        $output .= "</tr>";
        $output .= "</thead>";
        $output .= "<tbody>";
        foreach ($this->data as $rowHeader => $fields) {
            $class = ($class == "odd" ? "even" : "odd");
            $output .= "<tr class=\"$class\">";
            if ($this->showRowHeaders) {
                $output .= "<td align=\"right\"><b>{$rowHeader}</b></td>";
            }
            foreach ($this->columns as &$column) {
                $value = $fields[$column->name];
                $output .= "<td class=\"{$column->getClass()}\">{$column->getValue($value)}</td>";
                if ($this->tabulate) {
                    $weight = ($column->weightColumn) ? $fields[$column->weightColumn] : 1;
                    $column->tabulate($value, $weight);
                }
            }
            $output .= "</tr>";
        }
        $output .= "</tbody>";
        if ($this->tabulate) {
            $output .= "<tfoot>";
            $output .= "<tr>";
            foreach ($this->columns as &$column) {
                $output .= "<td class=\"{$column->getClass()}\">";
                if ($column->weightColumn) {
                    $weightColumn = $this->columns[$column->weightColumn];
                    $weight = $weightColumn->getTabulation();
                } else {
                    $weight = 1;
                }
                $output .= $column->getValue($column->getTabulation($weight));
                $output .= "</td>";
            }
            $output .= "</tr>";
            $output .= "</tfoot>";
        }
        $output .= "</table>";
        return $output;
    }
}

class TabularChartColumn {
    public $header;
    public $name;
    public $type;
    public $width;
    public $divider;
    public $tabulation;
    public $weightColumn;
    public $lockType;

    public $count = 0;
    public $max = 0;
    public $min = false;
    public $sum = 0;

//    public function __construct($header, $name, $type=DataType::GENERAL, $width=null, $divider=Alignment::NONE, $divider=DividerType::NONE, $tabulation=Tabulation::NONE, $weightColumn=false) {
    public function __construct($header, $name, $type=DataType::GENERAL, $width=null, $divider=DividerType::NONE, $tabulation=Tabulation::NONE, $weightColumn=false, $lockType=LockType::NONE) {
        $this->header = $header;
        $this->name = $name;
        $this->type = $type;
        $this->width = $width;
        $this->divider = $divider;
        $this->tabulation = $tabulation;
        $this->weightColumn = $weightColumn;
        $this->lockType = $lockType;
    }
    public function getValue($value) {
        if ($this->lockType > 0) {
            return "<img src=\"/images/lock.gif\" alt=\"Locked\" /> Locked";
        } else {
            switch ($this->type) {
                case DataType::LOCKED:
                    return "<img src=\"/images/lock.gif\" alt=\"Locked\" /> Locked";
                    break;
                case DataType::INTEGER:
                case DataType::CURRENCY:
                    $value = $value ? number_format($value, 0) : "-";
                    break;
                case DataType::NUMERIC:
                    $value = $value ? number_format($value, 2) : "-";
                    break;
                case DataType::PERCENTAGE:
                    $value = ($value ? $value : 0) . "%";
                    break;
                case DataType::PERCENT_CHANGE:
                    $change = $value == 0 ? "none" : ($value > 0 ? "up" : "down");
                    $value = abs($value);
                    $value = ($value > 0 && $value < 1) ? "&lt; 1%" : round($value)."%";
                    //$value = "<div style=\"float:right\"><div style=\"float:right\"><img src=\"/images/reports/{$change}.gif\" width=\"20\" height=\"20\" alt=\"\" /></div><div style=\"float:right;line-height:20px\">" . ($value ? $value : 0) . "</div></div>";
                    $value = "<change-arrow change=\"{$change}\">" . ($value ? $value : 0) . "</change-arrow>";
                    break;
                case DataType::DATE:
                    //$dateValue = date_parse($value);
                    //$value = is_array($dateValue) ? $dateValue["year"]."-".str_pad($dateValue["month"], 2, "0", STR_PAD_LEFT) : $value;
                    //break;
                    $value = $value ? $value : "-";
                case DataType::GENERAL:
                    $value = str_replace(array("&", "<", "\""), array("&amp;", "&lt;", "&quot;"), $value);
                default:
                    break;
            }
            return $value;
        }
    }
    public function tabulate($value, $weight=1) {
        switch ($this->tabulation) {
            case Tabulation::MAX:
                if (is_numeric($value) && $value > $this->max) $this->max = $value;
                break;
            case Tabulation::MIN:
                if (is_numeric($value) && $value > 0 && ($value < $this->min || $this->min === false)) {
                    $this->min = $value;
                }
                break;
            case Tabulation::WEIGHTED_AVERAGE:
                if (is_numeric($value) && $value > 0) {
                    $this->count++;
                    $this->sum += $value * $weight;
                }
                //echo "<pre>$value * $weight = " . ($value * $weight) . " | " . $this->sum . "</pre>";
                break;
            case Tabulation::AVERAGE:
                if (is_numeric($value) && $value > 0) {
                    $this->count++;
                    $this->sum += $value;
                }
                break;
            case Tabulation::SUM:
                if (is_numeric($value)) $this->sum += $value;
                break;
            case Tabulation::COUNT:
                $this->count++;
                break;
            default:
                break;
        }
    }
    public function getTabulation($weight=1) {
        if ($this->lockType == LockType::ALL || $this->type == DataType::LOCKED) {
            return "<img src=\"/images/lock.gif\" alt=\"Locked\" /> Locked";
        } else {
            switch ($this->tabulation) {
                case Tabulation::COUNT:
                    return $this->count;
                    break;
                case Tabulation::MAX:
                    return $this->max;
                    break;
                case Tabulation::MIN:
                    return $this->min;
                    break;
                case Tabulation::AVERAGE:
                    return $this->count == 0 ? "n/a" : round($this->sum / $this->count);
                    break;
                case Tabulation::WEIGHTED_AVERAGE:
                    //echo "<pre>" . $this->sum . " / " . $weight . "</pre>";
                    return $weight == 0 ? "n/a" : round($this->sum / $weight);
                    break;
                case Tabulation::SUM:
                    // The below is a 99% or 101% bug fix.
                    // The bug was introduced from rounding of percentages
                    // Coming from the database, which sometimes leads
                    // to a sum incremented up or down by 1.
                    //
                    // A better fix would be to retrieve raw values from the DB
                    // without rounding and alter the PERCENTAGE data type
                    // presentation in this component to round the values
                    if ($this->type == DataType::PERCENTAGE) {
                        return $this->sum == 99 || $this->sum == 101 ? 100 : $this->sum;
                    } else {
                        return $this->sum;
                    }
                    break;
                default:
                    return $this->tabulation;
                    break;
            }
        }
    }

    public function getClass() {
        return strtolower($this->type) . ($this->divider ? (" " . $this->divider) : "");
    }
}
    
