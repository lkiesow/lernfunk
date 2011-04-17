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


include_once( dirname(__FILE__).'/config.php' );
include_once( dirname(__FILE__).'/feedcreator.class.php' );

class LFFeedGenerator {


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
		if ( !$result ) {
			die( 'SQL_ERROR: '.mysql_error()."\n".'QUERY    : '.$query );
		}
		return $result;
		
	}


/*****************************************************************************/
/*****************************************************************************/


	public static function createFeed( $id ) {

		$rss = new UniversalFeedCreator();
		$rss->useCached();
		$rss->encoding = 'utf-8';
		$rss->_feed->encoding = 'utf-8';
		
		$series = null;
		$rs = self::query( 'select * from series '
			.'where series_id = '.intval($id).' '
			.'and access_id = 1;' );
		while ($r = mysql_fetch_object($rs)) {
			$rss->title = $r->name;
			$rss->description = $r->description;
			$rss->link = PORTAL_URL.'Main/'.$r->portal_url;
			$rss->syndicationURL = $_SERVER[ 'REQUEST_URI' ];

			$image = new FeedImage();
			$image->title = $r->name;
			$image->url = $r->thumbnail_url;
			$image->link = PORTAL_URL.'Main/'.$r->portal_url;
			$image->description = 'Vorlesung der Universität Osnabrück';
			$rss->image = $image;

			$series = $r;
		}

		if ( !$series ) {
			die( 'ERROR    : Invalid »series_id« or protected series' );
		}

		$rec = array();
		$rs = self::query( 'select *, '
			.'DATE_FORMAT( date,"%a, %d %b %Y %T") AS rfcdate '
			.'from mediaobject '
			.'where series_id = '.intval($id).' '
			.'and access_id = 1 '
	  		.'order by date asc;' );
		while ($r = mysql_fetch_object($rs)) {
			$rec[ $r->cou_id ? $r->cou_id : $r->object_id ] = $r;
		}
		foreach ( $rec as $r ) {
		
			$item = new FeedItem();
			$item->title = $r->title;
			$item->link = $rss->link.'?stream_id='.$r->object_id;
			$item->description = $r->description;
			$item->date = $r->rfcdate;
			$item->source = $rss->link;

			$rss->addItem($item);
		}

		//header( 'Content-Type: text/plain' );
		//print_r( $rss );
		$rss->outputFeed( 'RSS2.0' );

	}

}

?>
