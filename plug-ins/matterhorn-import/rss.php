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

//header( 'Content-Type: text/plain' );
	
	$filter = null;
	if ( array_key_exists( 'filter', $_REQUEST ) && !empty( $_REQUEST['filter'] ) ) {
		$filter = $_REQUEST['filter'];
	}

	$directory = "rss_data/";
	// create a handler to the directory
	$dirhandler = opendir( $directory );

	// read all the files from directory
	while ( $file = readdir( $dirhandler ) ) {

		if ( $file[0] != '.' ) {
			$files[] = $file;
		}   

	}

	// close the handler
	closedir( $dirhandler );

	echo '<?xml version="1.0" encoding="UTF-8" ?>';
	echo '<rss version="2.0">';
	echo '<channel>';
	echo '<title>Lernfunk-Matterhorn-Import</title>';
	echo '<description>This feed lists the Recordings automatically imported '
		.'from Opencast Matterhorn to Lernfunk.</description>';
	echo '<link>http://lernfunk.de/</link>';
	echo '<lastBuildDate>'.date( DATE_RSS ).'</lastBuildDate>';
	echo '<pubDate>'.date( DATE_RSS ).'</pubDate>';

	foreach ( $files as $itemfile ) {
		$item_str = file_get_contents( $directory.$itemfile );
		if ( $filter ) {
			if ( strpos( $item_str, $filter ) ) {
				echo $item_str;
			}
		} else {
			echo $item_str;
		}
	}

	echo '</channel>';
	echo '</rss>';

?>
