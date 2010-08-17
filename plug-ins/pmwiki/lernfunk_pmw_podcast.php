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
	define (DB_SERVER_ZW,'db_server');
	define (DB_NAME_ZW,'db_name');
	define (DB_USER_ZW,'db_user');
	define (DB_PASS_ZW,'db_paswd');


   Markup('formatplugin', 'fulltext', "/^\(:formatplugin[ 	]*(.*?):\)\s*$/e", "LernfunkDb(PSS('$1'))");
	

	if (!is_array($SQdata)) $SQdata = array();
	SDVA($SQdata,$_REQUEST);

function LernfunkDb($params) {
  global $SQofflimits, $SQdata;

	// Connect to Database
  $dblink = mysql_connect(DB_SERVER_ZW, DB_USER_ZW, DB_PASS_ZW) or die("Could not connect : " . mysql_error());
  mysql_select_db(DB_NAME_ZW,$dblink) or die("Could not select database: ".mysql_error());
  
	$params = ParseArgs($params);
  	SDVA($params,$SQdata);
  	
  	if ($params['type']=='podcast'){
    	$sql_query1 = "SELECT series.name, GROUP_CONCAT(DISTINCT lecturer.ac_title,' ',lecturer.firstname, ' ', lecturer.name SEPARATOR ', ') AS 'lecturer', series.thumbnail_url AS 'Bild', series.portal_url AS 'URL', terms.term_sh FROM series, lecturer_series, format, terms, lecturer, mediaobject WHERE mediaobject.format_id=3 AND mediaobject.series_id=series.series_id AND series.series_id = lecturer_series.series_id AND lecturer_series.lecturer_id = lecturer.lecturer_id AND series.term_id = terms.term_id GROUP BY series.name ORDER BY series.series_id DESC ";
	
	 if ($query = mysql_query($sql_query1)) {
	     while ($queryd = mysql_fetch_assoc($query)){  
	     $ausg .= '<table width=80% border=0 bgcolor=#e4e4e4 cellpadding=6>'. "\n";   

		$ausg .= '<tr> <td style="width:113px;">' ."\n";
		$ausg .= '(:html:) <img src = "' . $queryd['Bild'] .'" width="113" align ="left">(:htmlend:)' . "\n";
		$ausg .= '</td>';
	    $ausg .= '<td valign="top">'."\n"."<h2>" . $queryd['name'].', '. $queryd['term_sh'] .  '</h2>' ."\n";
	    $ausg .= $queryd['lecturer'] . "</br>" . "\n";
        if ($feeds = mysql_query($podcast_query)){
			while ($feed = mysql_fetch_assoc($feeds)) {
				$ausg .= '(:html:) <a href="'.$feed['feed_url'].'" target="new">' . $feed['feedtype_desc'] . ' abonnieren <img src = "'. $servway .'/uploads/Main/rss_minimini.png"> </a>(:htmlend:)'."\n";
      		}
    	}
		$ausg .= "</td> </tr>". "\n";
		$ausg .= '<tr> <td colspan=2>Mehr Informationen zum Podcast '."<i>" . $queryd['name'] ."</i>". ' finden Sie auf %newwin%[[http://www.lernfunk.de/Main/'.$queryd['URL'].'| http://www.lernfunk.de]] </td> </tr>'."\n";
        $ausg .= '</table>'. "\n";
			 $ausg .= "<br>"."\n";
	    }//end of while
//			  $ausg .= '(:tableend:)'. "\n";
			  
	 }//end of if query
	 else { //query didn't work
        	$ausg .= "%red%$query\\\\\n".mysql_error();
     }//end of else 
  }//end of if params
  return $ausg;
}//end of function											


													 
?>
