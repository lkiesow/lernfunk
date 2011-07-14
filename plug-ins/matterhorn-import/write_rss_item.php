<?php
/* 
	Copyright (c) 2006 - 2010  Universitaet Osnabrueck, virtUOS 
	Authors: Lars Kiesow

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


function write_rss_item( $title, $urls, $author, $description, $guid) {

	$desc = '<![CDATA['.$title."<br />\n"
		.'<small>'.$author."</small>\n"
		.'<p>'.$description."</p>\n\n"
		."Imported mediaobjects:<br /><ul>";

	foreach ( $urls as $u => $f ) {
		$desc .= "\n"
			."<li>(format: ".$f.") \n"
			."url: ".$u.'</li>';
	}
	$desc .= '</ul>]]>';
	$url = ''; /* generate imported mediapackage url */

	$data = "<item>\n"
		."\t<title>".$title."</title>\n"
		."\t<description>".$desc."</description>\n"
		."\t<link>".$url."</link>\n"
		."\t<guid>".$guid."</guid>\n"
		."\t<pubDate>".date( DATE_RSS )."</pubDate>\n"
		."</item>";

	file_put_contents( dirname(__FILE__).'/rss_data/'.time().$guid.'.item', $data );

}

?>
