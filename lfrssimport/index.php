<?php
/* 
	Copyright (c) 2006 - 2010  Universitaet Osnabrueck, virtUOS 
	Authors: Lars Kiesow, Benjamin Wulff

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


require_once('config.php');
include_once($lflib_path.'lernfunk.php');
include_once($lflib_path.'lernfunk.classes.php');
include_once('rss-import.php');

$key = $_REQUEST['key'];
$action = $_REQUEST['action'];
$id = $_REQUEST['id'];

$debug = $_REQUEST['debug'] == 'yes';

$access = array();
$formats = array();
$languages = array( 'de-de' => '1', 'en-en' => '2');

//$rs = Lernfunk::query("SELECT * FROM mediaobject WHERE format_id = 14");
//foreach ($rs as $r) {
//	print "$r->object_id ";
//	$r->url = preg_replace("/oflaDemo\//", 'oflaDemo&url=', $r->url);
//	print "$r->url <br>";
//	if ( Lernfunk::query("UPDATE mediaobject SET preview_url='$r->preview_url' WHERE object_id=$r->object_id;") )
//		print "written<br>";
//	else
//		print "ERROR!<br>";
//}
//
//exit;

if ($key == 'DocNagdon2') {

	printlog("Action $action triggered with ID $id");

	// get list of formats
	$rs = Lernfunk::query("SELECT * FROM format WHERE mimetype != '';");
	foreach ($rs as $format) {
		$formats[$format->mimetype] = $format->format_id;
	}

	// remove objects and exit (if action is set to remove_xxx)
	if ($action == "remove_mediaobject") {
		$format = $formats[$_REQUEST['mimetype']];
		if (mediaobject_exists($id, $format)) {
			Lernfunk::query("DELETE FROM mediaobject WHERE mediaobject.lrs_object_id='$id' AND mediaobject.format_id=$format;");
			printlog(mysql_affected_rows()." objects removed");
			print "200 OK";
		} else {
			printlog("Object not found");
			print "401 Object not found.";
		}
		exit;
	} else if ($action == "remove_couobject") {
		if ( ($rs = Lernfunk::query("SELECT * FROM mediaobject WHERE mediaobject.cou_id='$id';")) && (count($rs) > 0) ) {
			Lernfunk::query("DELETE FROM mediaobject WHERE mediaobject.cou_id='$id';");
			printlog(mysql_affected_rows()." object(s) removed");
			print "200 OK";
		} else {
			printlog("COU Id not found");
			print "402 COU Id not found.";
		}
		exit;
	}

	// get list of access status
	$rs = Lernfunk::query("SELECT access_id,vp_access FROM access WHERE vp_access != '';");
	foreach ($rs as $r)
		$access[$r->vp_access] = $r->access_id;

	$eater = new MRSSEater($vp_url);

	if ($action == "update_series") {
		$eater->set_params("series=$id");
	}

	if ($action == "update_mediaobject") {
		$eater->set_params("object=$id");
	}

	$eater->run();

	if ($debug) print $eater->get_xml() . "\n\n";

	$series_ids = array();
	$previews = array();
	$mediaobjects = array();
	$tmp = array();

	// Generate Lernfunk mediaobjects
	foreach($eater->get_mediaobjects() as $object) {
		$mediaobject = array();

		$mediaobject['title']		 = utf8_decode( $object->title );
		$mediaobject['description']   = utf8_decode( $object->description );
		$mediaobject['series_id']	 = utf8_decode( find_series_id( $object->lrs_series_id ) );
		$mediaobject['lrs_object_id'] = utf8_decode( $object->lrs_object_id );
		$mediaobject['date']		  = utf8_decode( make_date( $object->pubdate ) );
		$mediaobject['format_id']	 = utf8_decode( $formats[$object->type.($object->expression != 'main' ? '/'.$object->expression : '')] );
		$mediaobject['url']		   = utf8_decode( $object->url );
		$mediaobject['author']		= utf8_decode( $object->author );
		$mediaobject['duration']	  = utf8_decode( $object->duration );
		$mediaobject['access_id']	 = utf8_decode( $access[$object->access] );
		if (array_key_exists($object->language, $languages))
			$mediaobject['language_id'] = $languages[$object->language];
		else
			$mediaobject['language_id'] = '';
		$mediaobject['cou_id'] = $object->lrs_object_id;
		

		preg_match('/\/([A-Za-z0-9\_\-]+)$/', $object->lrs_object_id, $m);
		$m = $m[1];
		$mediaobject['thumbnail_url'] = "http://vm057.rz.uos.de/images/thumbs/$m.jpg";
		$mediaobject['image_url'] = "http://vm057.rz.uos.de/images/regular/$m.jpg";

		if ($object->type == 'video/flv')
				$previews[$object->lrs_object_id] = $object->url;

		if ($DEBUG)
			var_dump( $mediaobject );

		$tmp[] = $mediaobject;
	}
	printlog(count($tmp).' objects found in feed');

	// set preview_url for format_id 14 if present
	$blacklist = array();
	foreach ($tmp as $mediaobject) {
		if ( ($mediaobject['format_id'] == '2') && ( array_key_exists($mediaobject['cou_id'], $previews)) ) {
			$mediaobject['preview_url'] = preg_replace("/oflaDemo\//", 'oflaDemo&url=', $previews[$mediaobject['cou_id']]);
			$blacklist[] = $mediaobject['cou_id'];
		}
		$mediaobjects[] = $mediaobject;
	}

	$writecount = 0;
	foreach ($mediaobjects as $mediaobject) {
		if ($mediaobject[format_id] == '14') {
			if (!in_array($mediaobject['cou_id'], $blacklist)) {
				if (! $DEBUG)
					write_mediaobject($mediaobject);
				$writecount++;
			} 
		} else {
			if (! $DEBUG)
				write_mediaobject($mediaobject);
			$writecount++;
		}
	}
	printlog("$writecount objects written to database");
	printlog("---------------------------------------------------");
	print "200 OK";
	if ($logfile != null) fclose($logfile);
}

exit;

/* Functions ___________________________________________________________________
 *
 */

function write_mediaobject($object) {
	global $debug;
	if ($debug) {
		var_dump($object);
		return;
	}

	// Testläufe workaround
	if ( preg_match("/(T|t)est/", $object['title']) && ($object['access_id'] == '8')) {
		var_dump($object);
		return;
	}

	if (!preg_match("/^\:TEST\:/", $object['title'])) {
		$logmsg = 'object [ cou_id='.$object['cou_id'].' series_id='.$object['series_id'].' format='.$object['format_id'].' title='.$object['title'].' ';
		if ($object['preview_url'])
			$logmsg .= 'preview_url='.$object['preview_url'].' ';
		$logmsg .= ']';
		
		if (mediaobject_exists($object['cou_id'], $object['format_id'])) {
			$logmsg = "[ updating ".$logmsg;
			$sql = "UPDATE mediaobject SET ".make_update_data_string($object)." WHERE cou_id='".$object['cou_id']."' AND format_id='".$object['format_id']."';";
			Lernfunk::query($sql);
		} else {
			$logmsg = "[ inserting ".$logmsg;
			$sql = "INSERT INTO mediaobject (".make_insert_field_string($object).") VALUES (".make_insert_data_string($object).");";
			Lernfunk::query($sql);
		}

		printlog( $logmsg );
	} else 
		var_dump($object);
}

function mediaobject_exists($cou_id, $format_id) {

	if ($rs = Lernfunk::query("SELECT * FROM mediaobject WHERE cou_id='$cou_id' AND format_id='$format_id';")) {
		if (count($rs) > 0)
			return true;
		else
			return false;
	} else
		return false;

}

function make_date($date) {
	$date = strtotime($date);
	return date('Y-m-d H:i:s', $date);
}

function find_series_id($lrs_series_id) {
	if (array_key_exists($lrs_series_id, $series_ids))
		return $series_ids[$lrs_object_id];
	else {
		$id = Lernfunk::find_series_by_lrs_id( $lrs_series_id );
		if ($id)
			$series_ids[$lrs_series_id] = $id;
		return $id;
	}
}

function make_update_data_string($data) {
	foreach ($data as $key => $value)
		$out[] = "`$key`='$value'";
	return implode(',', $out);
}

function make_insert_field_string($data) {
	foreach ($data as $key => $value)
		$out[] = "`$key`";
	return implode(',', $out);
}

function make_insert_data_string($data) {
	foreach ($data as $key => $value)
		$out[] = "'$value'";
	return implode(',', $out);
}

function printlog($msg) {
	global $logfile, $debug;

	if (!$debug) {
		// open logfile if not open
		if ($logfile == null)
			$logfile = fopen('/var/log/lernfunk/lernfunk.import.log', 'a');
			if (!$logfile) die("Can't open logfile");
		fwrite( $logfile, date('[D, d M Y H:i:s]  ', time()).$msg."\n");
	}
}
