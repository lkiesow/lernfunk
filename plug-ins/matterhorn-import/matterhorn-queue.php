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


require_once(dirname(__FILE__).'/config.php');

class LFMatterhornInportQueue {

	private static $commands = array(
			'adddata'
		);


/*****************************************************************************/
/*****************************************************************************/


	/**
	 * Returns if the command-string is valid or not
	 **/
	public static function isvalidcmd($cmd) {
		return array_search($cmd, self::$commands) !== false;
	}


/*****************************************************************************/
/*****************************************************************************/


	private static function ensureArray( $arr ) {
		
		$isArray = true;

		foreach ( $arr as $key => $val ) {
//			echo $key.' --------> '.$val.' -- '.intval($key).' -- '.(strval($key) == strval(intval($key)))."\n\n";
			$isArray = $isArray && (strval($key) == strval(intval($key)));
		}

		if ($isArray)
			return $arr;
		return array( $arr );

	}


/*****************************************************************************/
/*****************************************************************************/


	private static function query($query) {

		global $mysql;
		$sql = mysql_connect($mysql['server'],
			$mysql['user'], $mysql['passwd']);
		if (!$sql) {
			 die( json_encode( array(
			 		'type'          => 'error', 
					'errtype'       => 'sql_error', 
					'errmsg'        => 'Could not connect to server.', 
					'sql_statement' => $query
				) ) );
		}
		if (! mysql_select_db($mysql['db'], $sql) )
			 die( json_encode( array(
			 		'type'          => 'error', 
					'errtype'       => 'sql_error', 
					'errmsg'        => 'Could not access database', 
					'sql_statement' => $query
				) ) );

		mysql_set_charset('utf8', $sql);

		$result = mysql_query($query, $sql);
		if ( !$result )
			 die( json_encode( array(
			 		'type'          => 'error', 
					'errtype'       => 'sql_error', 
					'errmsg'        => mysql_error(), 
					'sql_statement' => $query
				) ) );
			
		return $result;
		
	}


/*****************************************************************************/
/*****************************************************************************/


	private static function getFormat() {

		$query    = 'select format_id, mimetype, name from format;';
		if ($rs = self::query($query)) {
			$formats = array();
			while ($r = mysql_fetch_object($rs)) {
				$formats[ $r->mimetype ] = $r->format_id;
			}
			return $formats;
		}
		return array();

	}


/*****************************************************************************/
/*****************************************************************************/


	public static function adddata( $request ) {
		
		$formats = self::getFormat();

		$mediapackage = array_key_exists( 'mediapackage', $request ) ? $request['mediapackage'] : null;

		if (!is_array( $mediapackage )) // nothing to do
			return json_encode( array(
					'type'    => 'message', 
					'msgtype' => 'success', 
					'msg'     => 'There was nothing to do at all.'
				) );

		$start = $mediapackage['start'];
		$title = $mediapackage['title'];
		$id    = $mediapackage['id'];

		// get images
		$image = '';
		$thumb = '';
		foreach( self::ensureArray( $mediapackage['attachments']['attachment'] ) as $att ) {
			if ($att['type'] == 'presenter/player preview') 
				$image = $att['url'];
			if ($att['type'] == 'presenter/search preview') 
				$thumb = $att['url'];
		}

		// get tracks
		$query = '';
		foreach( self::ensureArray( $mediapackage['media']['track'] ) as $track ) {
		
			$type     = $track['type'];
			$mimetype = $track['mimetype'];
			$url      = $track['url'];
			$duration = $track['duration'];
			if ($query)
				$query .= ",\n";
			$query .= "( "
				."'".$title."', "
				.(array_key_exists( $mimetype, $formats ) ? $formats[$mimetype] : 'NULL').", "
				."'".mysql_escape_string($url)."', "
				."'".mysql_escape_string($id)."', "
				."'".mysql_escape_string($thumb)."', "
				."'".mysql_escape_string($image)."', "
				."'".$duration."', '3')";
		
		}
		if ($query) {
			$query = "INSERT INTO `mediaobject` "
				."( `title`, `format_id` , `url`, `cou_id`, `thumbnail_url`, "
				."`image_url`, `duration`, `access_id` ) VALUES \n"
				.$query.';';
		}

		if (__DEBUG__)
			print_r($query);

		if (self::query($query)) {
			return json_encode( array(
					'type'    => 'message', 
					'msgtype' => 'success', 
					'msg'     => 'Data successfully set.'
				) );
		} else {
			return json_encode( array(
			 		'type'          => 'error', 
					'errtype'       => 'sql_error', 
					'errmsg'        => mysql_error(), 
					'sql_statement' => $query
				) );
		}

		
	}


}

?>
