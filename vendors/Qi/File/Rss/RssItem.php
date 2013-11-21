<?php
namespace Qi\File\Rss;

/**
 * The RSSItem class is used to model an RSS Item
 * 
 * Example:
 * 
 * $item = $rssFeed->current();
 * print($item->title);
 * print($item->description);
 * print($item->link);
  */
class RssItem {
	public $title;
	public $description;
	public $link;
	public function __construct($title, $description, $link) {
		$this->title = $title;
		$this->description = $description;
		$this->link = $link;
	}
}
