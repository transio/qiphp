<?php
namespace Qi\File\Rss;

/**
 * The RSSIterator class is used to load and iterate an RSS Feed
 * 
 * Example:
 * 
 * $rss = new RSSIterator("http://path.to/rss.xml");
 * foreach ($rss as $item){
 *     print($item->title);
 *     print($item->description);
 *     print($item->link);
 * }
 */
class RssIterator implements \Iterator
{
    private $items;
    private $currentItem = 0;
    private $limit;

    public function __construct($path, $limit=null) {
        $dom = new DOMDocument();

        if ($dom->load($path)) {
            $this->items = $dom->getElementsByTagName("item");
        } else {
            throw new Exception("Unable to read RSS file.");
        }

        $this->limit = $limit;
    }

    public function rewind()
    {
        $this->currentItem = 0;
    }

    public function valid()
    {
        return $this->currentItem < $this->items->length &&
            (is_null($this->limit) || $this->limit == 0 || $this->currentItem < $this->limit);
    }
    
    public function current()
    {
        $itemNode = &$this->items->item($this->currentItem);
        $titleNodes = $itemNode->getElementsByTagName("title");
        $title = $titleNodes->item(0)->nodeValue;
        $descriptionNodes = $itemNode->getElementsByTagName("description");
        $description = $descriptionNodes->item(0)->nodeValue;
        $linkNodes = $itemNode->getElementsByTagName("link");
        $link = $linkNodes->item(0)->nodeValue; 
        return new RSSItem($title, $description, $link);
    }

    public function key()
    {
        return $this->currentItem;
    }

    public function next()
    {
        $this->currentItem++;
    }

    public function seek($itemNumber)
    {
        $this->currentItem = $itemNumber;
    }

}
