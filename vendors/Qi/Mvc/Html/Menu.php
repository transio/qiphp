<?php

/**
 * The HtmlMenu abstracts a Qi menu in an HtmlTemplate, which is used to generate
 * lists of links in an organized fashion.
 */
class Html
{
    const MENU = "ul";
    const CONTAINER = "div";
    const ITEM = "li";
    
    private $title;
    private $uri;
    private $type;
    private $rewriteUri;
    private $className;
    private $items; // Children
    private $compareParams = false;
    public $parentId = "";
    public $level = 0;
    public $selected = false;
    public $order;
    
    /**
     * Construct
     * @param $title String[optional]
     * @param $uri Uri[optional]
     * @param $type String[optional]
     * @param $items array[optional]
     */
    public function __construct($title, Uri $uri=null, $type=self::MENU, array $items=null, $compareParams=false, $rewriteUri=null, $className=null) {
        $this->title = $title;
        $this->uri = $uri;
        $this->type = $type;
        $this->items = $items;
        $this->compareParams = $compareParams;
        $this->rewriteUri = $rewriteUri;
        $this->className = $className;
    }
    
    /**
     * Render the menu as an html string
     * @return String
     */
    public function __toString() {
        $output = "";
        $children = "";
        
        $this->number = isset($this->number) ? (int) $this->number : 0;
        $id = (strlen($this->parentId) ? $this->parentId : "qi-menu") . "-{$this->level}-{$this->number}";
        // Render Children
        if (!empty($this->items)) {
            $itemClass = "";
            $children .= "<ul  class=\"qi-menu qi-menu-level-{$this->level}{$itemClass}\">";
            for ($i = 0; $i < count($this->items); $i++) {
                $item = $this->items[$i];
                if (get_class($item) == "HtmlMenu") {
                    $item->parentId = $id;
                    $item->level = $this->level + 1;
                    $item->number = $i;
                    $item->order = $i == 0 ? "first": ($i == count($this->items) - 1 ? "last" : false);
                    $children .= $item;
                    if ($item->selected) $this->selected = true;
                }
            }
            $children .= "</ul>";
        }
        
        $uri = $this->getUri();
        
        // Render Item
        if (strlen($this->title)) {
            
            // Build the item class
            $class="qi-menu-item qi-menu-item-level-" . ($this->level);
            if (!is_null($uri) && $uri->equals($GLOBALS["uri"], true)) {
                $this->selected = true;
            }
            if ($this->selected) {
                $class .= " qi-menu-selected";
                $class .= " qi-menu-selected-level-" . ($this->level);
            }
            if ($this->order) {
                $class .= " qi-menu-{$this->order}";
            }
            if ($children) {
                $class .= " qi-menu-parent";
            }
            if ($this->className) {
                $class .= " {$this->className}";
            }
            
            $itemId = "{$this->parentId}-item-{$this->level}-{$this->number}";
            // Build the item
            if ($this->level == 0) {
                $output .= "<h3 class=\"{$class}\">";
            } else {
                $output .= "<li class=\"{$class}\">";
            }
            
            // Build the item title / link
            if (!is_null($uri)) $output .= "<a href=\"{$uri}\">";
            $output .= "<span>{$this->title}</span>";
            if (!is_null($uri)) $output .= "</a>";
            
            // Close the item and add its children
            if ($this->level == 0) {
                $output .= "</h3>";
                $output .= $children;
            } else {
                $output .= $children;
                $output .= "</li>";
            }
        } else {
            if ($this->type == self::CONTAINER) {
                $output .= "<li class=\"qi-menu-container\">";
                $output .= $children;
                $output .= "</li>";
            } else {
                $output = $children;
            }
        }
        return $output;
    }
    
    public function getSitemap($isChild=false) {
        // Init
        $output = "";
        
        // Render Item
        $uri = $this->getUri();
        if ($this->type != self::CONTAINER && !is_null($uri) && strlen($uri) > 1) {
            $uri = $GLOBALS["settings"]->uri->absolute . substr($uri, 1);
            $uri = str_replace("&", "&amp;", $uri);
            $uri = str_replace("\"", "&quot;", $uri);
            $uri = str_replace("<", "&lt;", $uri);
            $output = "<url><loc>{$uri}</loc></url>\n\t";
        }
        
        // Render Children
        if (!empty($this->items)) {
            foreach ($this->items as $item) {
                if (get_class($item) == "HtmlMenu") $output .= $item->getSitemap(true);
            }
        }
        
        // Render Wrapper for top level item
        if (!$isChild) {
            $output = <<<XML
<?xml version="1.0" encoding="UTF-8"    
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"
        xmlns:image="http://www.sitemaps.org/schemas/sitemap-image/1.1"
        xmlns:video="http://www.sitemaps.org/schemas/sitemap-video/1.1">
    {$output}
</urlset>
XML;
        }
        
        // Return it all
        return $output;
    }
    
    public function getUri() {
        return get_class($this->rewriteUri) == "Uri" ? $this->rewriteUri : $this->uri;
    }
    
    public function getBreadcrumbs(array &$crumbs=null) {
        $uri = $this->getUri();
        $isTop = false;
        // Render Children
        if (is_null($crumbs)) {
            $crumbs = array();
            $isTop = true;
        }
        if (!is_null($uri) && $uri->equals($GLOBALS["uri"], $this->compareParams)) {
            $this->selected = true;
        } else {
            if (!empty($this->items)) {
                foreach ($this->items as $item) {
                    if (get_class($item) == "HtmlMenu") {
                        $itemOutput = $item->getBreadcrumbs($crumbs);
                        if ($item->selected) {
                            $this->selected = true;
                        }
                    }
                }
            }
        }
        if ($this->selected) {
            if ($isTop || ($this->title && $this->type != self::CONTAINER && $this->uri)) {
                $crumb = "<span class=\"qi-menu-breadcrumb\">";
                $crumb .= ($this->uri) ? $crumb .= "<a href=\"{$this->uri}\">" : ($isTop ? "<a href=\"/\">" : "");
                $crumb .= ($this->title) ? $this->title : ($isTop ? "Home" : "");
                $crumb .= ($this->uri || $isTop) ? "</a>" : "";
                $crumb .= "</span>";
                array_push($crumbs, $crumb);
            }
        }
        if ($isTop) {
            $output = "<div class=\"qi-menu-breadcrumbs\">";
            $output .= implode(" - ", array_reverse($crumbs));
            $output .= "</div>";
            return $output;
        }
    }
    
    /**
     * Add an HtmlMenu item to the menu's sub-items
     * @return HtmlMenu
     * @param $title String[optional]
     * @param $uri Uri[optional]
     * @param $type String[optional]
     * @param $items array[optional]
     */
    public function &addItem($title=null, Uri $uri=null, $type=self::MENU, array $items=null, $compareParams=false, $rewriteUri=null, $className=null) {
        $item =& new HtmlMenu($title, $uri, $type, $items, $compareParams, $rewriteUri, $className);
        $this->addMenu($item);
        return $item;
    }
    
    public function addMenu(HtmlMenu $menu) {
        if (!is_array($this->items)) $this->items = array();
        array_push($this->items, $menu);
    }
}
