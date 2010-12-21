<?
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

	/**
	 * Returns if the command-string is valid or not
	 **/
	public static function isvalidcmd($cmd) {
		return array_search($cmd, self::$commands) !== false;
	}


/*****************************************************************************/
/*****************************************************************************/


	private static function query($query) {

		global $mysql;
		$sql = mysql_connect($mysql['server'], $mysql['user'], $mysql['passwd']);
		if (!$sql) {
			 die( json_encode( array('type' => 'error', 'errtype' => 'sql_error', 'errmsg' => 'Could not connect to server.', 'sql_statement' => $query) ) );
		}
		if (! mysql_select_db($mysql['db'], $sql) )
			 die( json_encode( array('type' => 'error', 'errtype' => 'sql_error', 'errmsg' => 'Could not access database', 'sql_statement' => $query) ) );

		mysql_set_charset('utf8', $sql);

		$result = mysql_query($query, $sql);
		if ( !$result )
			 die( json_encode( array('type' => 'error', 'errtype' => 'sql_error', 'errmsg' => mysql_error(), 'sql_statement' => $query) ) );
			
		return $result;
		
	}


/*****************************************************************************/
/*****************************************************************************/


	public static function adddata($args) {
		
		if (!is_array($args)) // nothing to do
			return json_encode( array('type' => 'message', 'msgtype' => 'success', 'msg' => 'There was nothing to do at all.') );

		$post_id  = intval($args['post_id']);
		$filename = mysql_escape_string($args['filename']);
		$comment  = mysql_escape_string($args['comment']);
		$query = 'INSERT INTO files (post_id, filename, comment) VALUES ('.$post_id.', "'.$filename.'", "'.$comment.'")'
			.' ON DUPLICATE KEY UPDATE comment = "'.$comment.'"';

		if (__DEBUG__)
			print_r($query);
		if (self::query($query)) {
			if (!$post_id)
				$post_id = mysql_insert_id();
			return json_encode( array('type' => 'message', 'msgtype' => 'success', 'msg' => 'Data successfully set.', 'id' => $post_id) );
		}

		
	}


}

?>
