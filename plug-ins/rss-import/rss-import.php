<?php
/* 
	Copyright (c) 2006 - 2010  Universitaet Osnabrueck, virtUOS 
	Authors: Nils Birnbaum, Lars Kiesow, Benjamin Wulff

	This file is part of Lernfunk. 

	Lernfunk is free software: you can redistribute it and/or modify 
	it under the terms of the GNU General Public License as published by 
	the Free Software Foundation, either version 3 of the License, or 
	(at your option) any later version. 

	Lernfunk is distributed in the hope that it will be useful, 
	but WITHOUT ANY WARRANTY; without even the implied warranty of 
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the 
	GNU General Public License for more details. 

	You should have received a copy of the GNU General Public License 
	along with Lernfunk.  If not, see <http://www.gnu.org/licenses/>. 
*/

class MRSSEater {

	private $url;
	private $params;
	private $xml;
	private $mediaobjects;

	function __construct($url) {
		$this->url = $url;
	}

	// Downloads XML from feed url
	public function download() {
		$url = $this->url;
		if ($this->params)
			$url = $url.'?'.$this->params;
		if (!($feed = fopen( $url, 'r' )))
			die('<br>ERROR: Could not open URL: '.$this->url);
		$this->xml = stream_get_contents( $feed );
		fclose( $feed );
		return true;
	}

	public function parse() {
		$this->mediaobjects = array();

		// Parse XML from feed
		$handler = new XMLHandler();
	  	$parser = xml_parser_create();
	xml_set_object( $parser, $handler );
		xml_set_element_handler( $parser, 'tag_start', 'tag_end' );
	xml_set_character_data_handler( $parser, 'tag_data' );
	xml_parse( $parser, $this->xml );

		// Build Mediaobjects if found in feed
		$rss = $handler->get_tree();
		if ($rss->CHANNEL->ITEM != NULL) {
			if (is_array($rss->CHANNEL->ITEM))
				$items = $rss->CHANNEL->ITEM;
			else
				$items = array($rss->CHANNEL->ITEM);

			foreach ($items as $item) {

				if (is_array($item->MEDIA_GROUP->MEDIA_CONTENT))
					$cobjects = $item->MEDIA_GROUP->MEDIA_CONTENT;
				else
					$cobjects = array($item->MEDIA_GROUP->MEDIA_CONTENT);

				foreach ($cobjects as $obj) {
					$mob = new XMLObject('mediaobject');
					$mob->title = $item->TITLE->get_cdata();
					$mob->description = $item->DESCRIPTION->get_cdata();
					$mob->lrs_series_id = $item->MEDIA_GROUP->MEDIA_CATEGORY->get_cdata();
					$mob->lrs_object_id = $item->GUID->get_cdata();
					$mob->pubdate = $item->PUBDATE->get_cdata();
					$mob->type = $obj->TYPE;
					$mob->expression = $obj->EXPRESSION;
					$mob->url = $obj->URL;
					$mob->author = $item->AUTHOR->get_cdata();
					$mob->duration = $obj->DURATION;
					$mob->access = $item->MEDIA_GROUP->MEDIA_RATING->get_cdata();

					if ($rss->CHANNEL->LANGUAGE->get_cdata()) {
						$tmp = preg_split('/\-/', $rss->CHANNEL->LANGUAGE->get_cdata());
						$mob->language = $tmp[0];
					} else
						$mob->language = '';

					$this->mediaobjects[] = $mob;
				}
			}
		} else
			print "<p>No items found.</p>";
	}

	public function run() {
		$this->download();
		$this->parse();
	}

	public function get_url() {
		return $this->url;
	}

	public function set_url($url) {
		$this->url = $url;
	}

	public function get_xml() {
		return $this->xml;
	}

	public function get_params() {
		return $this->params;
	}

	public function set_params($params) {
		$this->params = $params;
	}

	public function get_mediaobjects() {
		return $this->mediaobjects;
	}

	public function count_mediaobjects() {
		return count($this->mediaobjects);
	}
}

class XMLHandler {

	private $tree;
	private $fathers;
	private $current_element;

	function __construct() {
		$this->fathers = array();
	}

	public function get_tree() {
		return $this->tree;
	}

	public function reset() {
		$this->tree = NULL;
		$this->current_father = NULL;
		$this->fathers = array();
	}

	function tag_start( $parser, $name, $attrs ) {
		$name = preg_replace('/:/', '_', $name);
		$this->current_element = new XMLObject($name);	  // create the element
		if ($this->tree == NULL)
			$this->tree = $this->current_element;

		foreach ($attrs as $field => $value)				// add attributes
			eval('$this->current_element->'.$field.' = \''.$value.'\';');

		if (count($this->fathers) > 0) {
			$father = $this->fathers[count($this->fathers)-1];
			eval('$val = $father->'.$name.';');
			
			if ($val == NULL)
				eval('$father->'.$name.' = $this->current_element;');

			elseif ($val instanceof XMLObject) 
				eval('$father->'.$name.' = array($val, $this->current_element);');

			elseif (is_array($val)) 
				eval('$father->'.$name.'[] = $this->current_element;');
		}

		$this->fathers[] = $this->current_element;	// redundant, can get current_element also from fathers stack
	}

	function tag_end( $parser, $name ) {
		array_pop($this->fathers);		  // remove current element from stack
	}

	function tag_data( $parser, $data ) {
	$data = preg_replace( '/\n/', '', $data );	  // correcting some strangnes the parser causes
		$data = preg_replace( '/\s+/', ' ', $data );
		$data = preg_replace( '/\s$/', '', $data);	 // <-- could cause problems
		$this->current_element->append_cdata($data);
	}

}

class XMLObject {

	private $tagname;
	private $cdata;

	function __construct($tagname) {
		$this->tagname = $tagname;
	}

	public function get_tagname() {
		return $this->tagname;
	}

	public function get_cdata() {
		return $this->cdata;
	}

	public function set_cdata($cdata) {
		$this->cdata = $cdata;
	}

	public function append_cdata($cdata) {
		$this->cdata = $this->cdata.$cdata;
	}
}
