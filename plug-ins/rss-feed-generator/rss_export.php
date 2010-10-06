<?php
/*
Copyright (c) 2007 - 2010  Universitaet Osnabrueck, virtUOS
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
along with virtPresenter.  If not, see <http://www.gnu.org/licenses/>.
*/
include_once('../lernfunk/lernfunk.php');

class RSSExporter {
    
    private static $template_path = 'templates';
    private static $template_item_file = 'item.xml';
    private static $template_rss_file = 'itunes_rss.xml';
    
    private $item_template;
    private $rss_template;

    private $mimetypes = array();
	//$mimetypes = list_formats();
	
    private $series;
    private $mediaobjects = array();
    
    function __construct() {
        $this->loadTemplates();
        $this->mimetypes = $this->loadMimeTypes();
    }
    
    public function generateItem($mediaobject, $mimetypes) {
        if (!is_array($mediaobject)) {
	    	$mediaobject = $mediaobject->toArray();
	    	$mediaobject[date] = gmstrftime('%a, %d %b %Y %T %Z' , strtotime($mediaobject[date]));
	    if ($mediaobject[format_id] == 15){
	    	$mediaobject[format_id] = 12;
	    }
	    //var_dump($mediaobject[date]);
	    }
		$subs = $mediaobject;
        $subs['site_link'] = 'http://www.lernfunk.de';   
        //$mimetypes = loadMimeTypes();
      //  var_dump($mimetypes);
        
        $subs['mimetype'] = $this->mimetypes[$subs['format_id']];
        return $this->process($this->item_template, $subs);
    }
    
    public function utf8_array_encode($input){
    	return $input;
		$return = array();
	    foreach ($input as $key => $val){
        	if( is_array($val) ){
	            $return[$key] = utf8_array_encode($val);
        	}
        	else{
            	$return[$key] = utf8_encode($val);
        	}
    	}
    return $return;          
	} 
    
    public function generateRSS() {
        $builddate = date('D, d M Y H:i:s Z', time());

        $subs = $this->series->toArray();
        $subs['lernfunk_link'] = 'http://www.lernfunk.de';
        $subs['build_date'] = $builddate;
        $subs['pub_date'] = $builddate;

        // find author/owner
        $sql = "SELECT * FROM lecturer LEFT JOIN lecturer_series ON (lecturer.lecturer_id=lecturer_series.lecturer_id) WHERE lecturer_series.series_id = " . $subs['series_id'];
        if ($rs = Lernfunk::query($sql)) {
			$rs = $rs[0];
            $author = array();
            if ($rs->ac_title != '')  $author[] = $rs->ac_title;
            if ($rs->firstname != '') $author[] = $rs->firstname;
            if ($rs->name != '')      $author[] = $rs->name;
            $author = implode(' ', $author);
            $author_email = $rs->email;
        }
        $subs['author'] = $author;
        $subs['owner_name'] = $author;
        $subs['owner_email'] = $author_email;
//var_dump($rc);
        // generate items
	if ( (!$this->mediaobjects) || (count($this->mediaobjects) < 1) ) {
    	    $subs['items'] = "";
    	    //var_dump($subs);
	} else {
	    foreach ($this->mediaobjects as $object)
	       	$subs['items'] .= $this->generateItem($object);
        	//var_dump($subs);
	}
			$subs['itunes_block'] = ($this->series->itunes_status == 0) ? 'yes': 'no';

        return $this->process($this->rss_template, $subs);
    }
    
    private function loadTemplates() {
        $this->item_template = 						$this->load_file(self::$template_path.'/'.self::$template_item_file);
        $this->rss_template = $this->load_file(self::$template_path.'/'.self::$template_rss_file);
    }

    private function loadMimeTypes() {
        if ($rs = Lernfunk::query("SELECT format_id,mimetype FROM format WHERE 1;")) {
        
            foreach ($rs as $r){
                $mimetypes[$r->format_id] = $r->mimetype;}
                //var_dump($mimetypes);
             return $mimetypes;
        } else
            die ("Error: Cannot load Mimetype.");
    }

    private function load_file($filename) {
        if (file_exists($filename)) {
            $handle = fopen($filename, 'r');
            $out = fread($handle, filesize($filename));
            fclose($handle);
            return $out;
        } else
            die("Error: Cannot load $filename");
    }

	

    private function process($template, $substitutions) {
        foreach (array_keys($substitutions) as $s)
            $m[] = '/\(:'.$s.':\)/';
        return preg_replace($m, array_values($substitutions), $template);
    }

    // Get / Set
    public function set_series($series) {
    	$this->series = $series;
    }
    
    public function get_series() {
        return $this->series;
    }
    
    public function set_mediaobjects($mediaobjects) {
        $this->mediaobjects = $mediaobjects;
    }
    
    public function get_mediaobjects() {
        return $this->mediaobjects;
    }
}

?>
