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
	along with Lernfunk.  If not, see <http://www.gnu.org/licenses/>.
 */



include_once(dirname(__FILE__).'/../../libraries/base/lernfunk.php');
require_once('rss_export.php');

error_reporting(E_ERROR);
ini_set("display_errors", TRUE);

$formats = array( 'audio' => '3',
		'enhanced' => '11',
		'video' => '12',
		'screen' => '15',
		'matterhorn' => '23',
		'playlist' => '2222');

$path = preg_split('/\//', $_REQUEST['path']);
if ($_REQUEST['id']) {
	$id = $_REQUEST['id'];
} else {
	$id = $_REQUEST['series'];
}

if ( array_key_exists($path[0], $formats)) {

	$format = $formats[$path[0]];

	if ($_REQUEST['series']) {
		// get mo's and series for playlist id
		$sql = 'SELECT * FROM mediaobject'.
			' WHERE mediaobject.series_id='.$id.
			' AND mediaobject.format_id='.$format.
			' ORDER BY mediaobject.date ASC;';
		$mediaobjects = Lernfunk::query($sql);
		$sql = 'SELECT * FROM series WHERE series.series_id='.$id.';';
		if (!$series = Lernfunk::query($sql)) {
			die("ERROR: Cannot load series $id.<br>\n");
		}
		$series = $series[0];

	} elseif($_REQUEST['id']) {
		// get mo's and series for series id
		$sql = "SELECT * FROM mediaobject ". 
			"LEFT JOIN playlist_entry ON (mediaobject.object_id = playlist_entry.object_id) ".
			"WHERE playlist_entry.playlist_id = '$id';";
		//"AND mediaobject.format_id='$format';";
		$mediaobjects = Lernfunk::query($sql);
		$sql = "SELECT * FROM series ".
			"LEFT JOIN playlist ON (series.series_id = playlist.reciever_id) ".
			"WHERE playlist.playlist_id='$id';";
		if (!$series = Lernfunk::query($sql)) {
			die("ERROR: Cannot load series $id.<br>\n");
		}
		$series = $series[0];
	}

	$sql = 'select itunes_status from feeds where series_id='.$series->series_id;
	$itunes_status = 1;
	if (!$itunes_status = Lernfunk::query($sql)) {

		/* uncomment this if you want to have an email for each error
		$from_name  = 'lernfunk-rss-feed-generator';
		$from_email = 'no-reply@lernfunk.de';
		$recipient  = 'admin@larskiesow.de';
		$mail_body  = 'Error while creating feed.'."\n"
			.'Cannot get "itunes_status" for series with series_id '.$id."\n"
			.'Maybe there is no entry in table feeds for this podcast';
		$subject    = "Subject for reviever"; 
		$header     = 'From: '.$from_name.' <'.$from_email.">\r\n";

		ini_set( 'sendmail_from', $from_email );
		mail( $recipient, $subject, $mail_body, $header );
		*/

		$log  = file_get_contents( 'error.log' );
		if (!$log)
			$log = '';
		$log .= $series->series_id."\n";
		file_put_contents( 'error.log', $log );

	}
	$series->itunes_status = $itunes_status[0]->itunes_status;

	// print_r( $series );

	$exporter = new RSSExporter();
	$exporter->set_series($series);
	$exporter->set_mediaobjects($mediaobjects);
	print $exporter->generateRSS();

} else
die ("Error: Unknown format " + $path[0]);
?>
