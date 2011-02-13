<?
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
	along with Lernfunk.  If not, see http://www.gnu.org/licenses/. 
*/


require_once(dirname(__FILE__).'/config.php');

function map_request( $req ) {
	
	$sql = 'select s.series_id, s.name '
		  .'from series s '
		  .'where s.portal_url = "'.mysql_escape_string( $req['portal_url'] ).'";';

	if ( $rs = Lernfunk::query($sql) ) {
		$rs = $rs[0];
		
		$sql2 = 'select cou_id from mediaobject '
			.'where series_id = "'.$rs->series_id.'" '
			.'and object_id = "'.intval($req['stream_id']).'";';

		$objln = '';
		if ( $rs2 = Lernfunk::query($sql2) ) {
			$rs2 = $rs2[0];
			$objln = '&couid='.$rs2->cou_id.'&id='.intval($req['stream_id']);
		}

		return '/portal-ui/user-interface/#cmd=search&filter='.$rs->name
			.'&details=1&mediatype=series&identifier='.$rs->series_id.$objln;

	} else { // end sql
		if (mysql_error())
			return json_encode( array(
					'type'          => 'error', 
					'errtype'       => 'sql_error', 
					'errmsg'        => mysql_error(), 
					'sql_statement' => $sql
				) );
	} // end !sql
	
}
