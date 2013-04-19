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
require_once(dirname(__FILE__).'/http_request.php');
include_once(dirname(__FILE__).'/write_rss_item.php');

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
			header( 'HTTP/1.1 500 Internal Server Error' );
			die( json_encode( array(
							'type'          => 'error', 
							'errtype'       => 'sql_error', 
							'errmsg'        => 'Could not connect to server.', 
							'sql_statement' => $query
							) ) );
		}
		if (! mysql_select_db($mysql['db'], $sql) ) {
			header( 'HTTP/1.1 500 Internal Server Error' );
			die( json_encode( array(
							'type'          => 'error', 
							'errtype'       => 'sql_error', 
							'errmsg'        => 'Could not access database', 
							'sql_statement' => $query
							) ) );
		}

		mysql_set_charset('utf8', $sql);

		$result = mysql_query($query, $sql);
		if ( !$result ) {
			header( 'HTTP/1.1 500 Internal Server Error' );
			die( json_encode( array(
							'type'          => 'error', 
							'errtype'       => 'sql_error', 
							'errmsg'        => mysql_error(), 
							'sql_statement' => $query
							) ) );
		}

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

//		$formats = self::getFormat();
		$created_series = false;
		$no_server_set  = false;

		$mediapackage = array_key_exists( 'mediapackage', $request ) 
			?  $request['mediapackage']  :  null;

		if (!is_array( $mediapackage )) // nothing to do
			return json_encode( array(
						'type'    => 'message', 
						'msgtype' => 'success', 
						'msg'     => 'There was nothing to do at all.'
						) );

		$start = $mediapackage['start'];
		$title = $mediapackage['title'];
		$id    = $mediapackage['id'];
		$lrsid = array_key_exists( 'series', $mediapackage )  
			?  $mediapackage['series']  :  null;
		$series_id = 0;

		if ( $lrsid ) {
			$query = 'SELECT series_id FROM series '
				.'where lrs_series_id = "'.mysql_escape_string( $lrsid ).'";';
			$rs = self::query($query);
			while ($r = mysql_fetch_object($rs)) {
				$series_id = $r->series_id;
			}
		}
		/* Insert new series if not present */

		if ( $lrsid && !$series_id ) {
			$new_portal_url = preg_replace( '/[^a-zA-Z0-9_ -]/s', '', $mediapackage['seriestitle'] ).date('Y');
			$query = 'insert into series ( name, description, course_id, access_id, '
				.'portal_url, thumbnail_url, lms_course_id, lrs_series_id, keywords ) '
				.'values ( "'.mysql_escape_string( $mediapackage['seriestitle'] ).'", "", "", 2, '
				.'"'.$new_portal_url.'", "", "", "'.mysql_escape_string( $lrsid ).'", "" ); ';
			$rs = self::query($query);
			$query = 'SELECT series_id FROM series where lrs_series_id = "'.mysql_escape_string( $lrsid ).'";';
			$rs = self::query($query);
			while ($r = mysql_fetch_object($rs)) {
				$series_id = $r->series_id;
				$created_series = true;
			}
		}

		// get images
		$image = '';
		$thumb = '';
		$atts  = self::ensureArray( $mediapackage['attachments']['attachment'] );
		foreach( $atts as $att ) {
			if ($att['type'] == 'presenter/player+preview') 
				$image = $att['url'];
			if ($att['type'] == 'presenter/search+preview') 
				$thumb = $att['url'];
		}

		/* Try to get additional metadata */
		$metadata = array( 
				'title' => '', 
				'creator' => '', 
				'description' => ''
			);
		foreach( self::ensureArray( $mediapackage['metadata']['catalog'] ) as $meta ) {
			$tags = array();
			try {
				$tags = is_array( $meta['tags']['tag'] ) ? $meta['tags']['tag'] : array( $meta['tags']['tag'] );
			} catch ( Exception $e ) {
			}
			/* Check if there is a publish tag. If not: cancel import of these
			 * metadata. */
			if ( !in_array( 'publish', $tags ) ) {
				continue;
			}
			if ( ( $meta['type'] == 'dublincore/episode' )
				&& ( $meta['mimetype'] == 'text/xml' ) ) {
				$xmlreq = get_url( $meta['url'] );
				$xml = XMLReader::xml( $xmlreq['content'] );
				while ( $xml->read() ) {
					if ( $xml->localName == 'title' && $xml->nodeType == XMLReader::ELEMENT ) {
						$xml->read(); // go to text
						$metadata['title'] = mysql_escape_string( $xml->value );
					} elseif ( $xml->localName == 'creator' && $xml->nodeType == XMLReader::ELEMENT ) {
						$xml->read(); // go to text
						$metadata['creator'] = mysql_escape_string( $xml->value );
					} elseif ( $xml->localName == 'description' && $xml->nodeType == XMLReader::ELEMENT ) {
						$xml->read(); // go to text
						$metadata['description'] = mysql_escape_string( $xml->value );
					}
				}
			}

		}

		// get tracks
		$query = '';
		$format_urls = array();
		foreach( self::ensureArray( $mediapackage['media']['track'] ) as $track ) {

			/**
			 * type                   mimetype     tags          format                    format-id
			 *
			 * presenter/delivery     video/mp4    mobil         Video Podcast             12
			 * presentation/delivery  video/mp4    mobil         Screenrecording Podcast   15
			 * presenter/delivery     video/mp4    high-quality  HQ-Video                  30
			 * presentation/delivery  video/mp4    high-quality  HQ-Screenrecording        31
			 * presenter/delivery     video/mp4    hd-quality    HD-Video                  32
			 * presentation/delivery  video/mp4    hd-quality    HD-Screenrecording        33
			 * presenter/delivery     video/x-flv  high-quality  HQ-Flash-Video            34
			 * presentation/delivery  video/x-flv  high-quality  HQ-Flash-Screenrecording  35
			 * presenter/delivery     video/x-flv  hd-quality    HD-Flash-Video            36
			 * presentation/delivery  video/x-flv  hd-quality    HD-Flash-Screenrecording  37
			 *                        audio/mp3                  Audio Podcast             3
			 *
			 **/
			
			$tags = array();
			try {
				$tags = $track['tags']['tag'];
			} catch ( Exception $e ) {
			}
			$type        = $track['type'];
			$mimetype    = $track['mimetype'];
			$url         = $track['url'];
			$duration    = intval( intval( $track['duration'] ) / 1000 );
			$cou_id      = 'videoVirtuosUniOsnabrueckDe'.mysql_escape_string($id);

			/* Continue if we got an RTMP-stream. We do not want them. */
			if ( substr( strtolower($url), 0, 7 ) == 'rtmp://' ) {
				continue;
			}

			/* Select format */
			$format_id = 0;
			/* Check if there is a publish tag. If not: cancel import of this 
			 * track. */
			if ( !in_array( 'publish', $tags ) ) {
				continue;
			}
			if ( $mimetype == 'audio/mp3' ) {
				$format_id = 3;
			} elseif ( $type == 'presenter/delivery' ) {
				if ( $mimetype == 'video/mp4' ) {
					if ( in_array( 'mobil', $tags ) ) {
						$format_id = 12;
					} elseif ( in_array( 'high-quality', $tags ) ) {
						$format_id = 30;
					} elseif ( in_array( 'hd-quality', $tags ) ) {
						$format_id = 32;
					}
/*
				} elseif ( $mimetype == 'video/x-flv' ) {
					if ( in_array( 'high-quality', $tags ) ) {
						$format_id = 34;
					} elseif ( in_array( 'hd-quality', $tags ) ) {
						$format_id = 36;
					}
*/
				}
			} elseif ( $type == 'presentation/delivery' ) {
				if ( $mimetype == 'video/mp4' ) {
					if ( in_array( 'mobil', $tags ) ) {
						$format_id = 15;
					} elseif ( in_array( 'high-quality', $tags ) ) {
						$format_id = 31;
					} elseif ( in_array( 'hd-quality', $tags ) ) {
						$format_id = 33;
					}
/*
				} elseif ( $mimetype == 'video/x-flv' ) {
					if ( in_array( 'high-quality', $tags ) ) {
						$format_id = 35;
					} elseif ( in_array( 'hd-quality', $tags ) ) {
						$format_id = 37;
					}
*/
				}
			}
			if ( !$format_id ) {
				continue;
			}

			if ($query) {
				$query .= ",\n";
			}
			$query .= "( "
				."'".$title."', "
				.$format_id.", "
				."'".mysql_escape_string($url)."', "
				."'".$cou_id."', "
				."'".mysql_escape_string($id)."', "
				."'".mysql_escape_string($thumb)."', "
				."'".mysql_escape_string($image)."', "
				."'".$duration."', '".ACCESS_ID."', NULL, '".$series_id."', "
				."'".$metadata['creator']."', "
				."'".$metadata['description']."', "
				."'".$start."')\n";
				$format_urls[ $url ] = $format_id;
		}

		// finally add matterhorn recording

		$server = ( array_key_exists( 'server', $_REQUEST ) && !empty( $_REQUEST['server'] ) ) 
			? $_REQUEST['server'] : '';

		if ( !$server ) {
			$no_server_set = true;
			json_encode( array(
						'type'          => 'error', 
						'errtype'       => 'parameter_error', 
						'errmsg'        => 'No server parameter given.'
					) );
		}

		if ($query)
			$query .= ",\n";
		$query .= "( "
			."'".$title."', "
			."29, "
			."'".$server."engage/ui/watch.html?id=".mysql_escape_string($id)."', "
			."'".$cou_id."', "
			."'".mysql_escape_string($id)."', "
			."'".mysql_escape_string($thumb)."', "
			."'".mysql_escape_string($image)."', "
			."'".$duration."', '".ACCESS_ID."', "
			."'".$server."engage/ui/embed.html?id=".mysql_escape_string($id)."', "
			."'".$series_id."', "
			."'".$metadata['creator']."', "
			."'".$metadata['description']."', "
			."'".$start."')\n";
		$format_urls[ $server."engage/ui/watch.html?id=".mysql_escape_string($id) ] = 'Opencast Matterhorn 1.1';

		if ($query) {
			$query = "INSERT INTO `mediaobject` "
				."( `title`, `format_id` , `url`, `cou_id`, `lrs_object_id`, `thumbnail_url`, "
				."`image_url`, `duration`, `access_id`, `preview_url`, `series_id`, "
				."`author`, `description`, `date` ) VALUES \n"
				.$query.';';
		}

		$desc = 'Series: '.$mediapackage['seriestitle'].' (ID: '.$series_id.')<br />';
		$desc = 'Request from: '.$_SERVER['REMOTE_ADDR'].'<br />'
			.$metadata['description'];
		if ( $created_series ) {
			$title .= ' !1 hint!';
			$desc  .= '<br />'
				.'Hint: Created one new series with series_id = '.$series_id;
		}
		if ( $no_server_set ) {
			$title .= ' !1 warning!';
			$desc  .= '<br />'
				.'Warning: No server parameter was set. Import may be corrupt.';
		}

		write_rss_item( $title, $format_urls, $metadata['creator'], $desc, $id );

		if (__DEBUG__)
			print_r($query);

		file_put_contents( dirname(__FILE__).'/import_mediapackages/'
			.time().'.query', $query );

		if (self::query($query)) {
			return json_encode( array(
						'type'    => 'message', 
						'msgtype' => 'success', 
						'msg'     => 'Data successfully set.'
						) );
		} else {
			header( 'HTTP/1.1 500 Internal Server Error' );
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
