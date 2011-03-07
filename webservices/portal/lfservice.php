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

class LFService {

	private static $commands = array(
			'getdata', 
			'getdetails', 
			'getdepartments', 
			'getdbdata', 
			'gettags', 
			'getnews',
			'getrecdates'
		);

	/**
	 * Returns if the command-string is valid or not
	 **/
	public static function isvalidcmd($cmd) {
		return array_search($cmd, self::$commands) !== false;
	}

	public static function getdata($args) {
		// check what data is requested
		$all_mediatypes = !array_key_exists('mediatype', $args);// || empty($args['mediatype']);
		$mediatypes = array(
			'recordings' => ( $all_mediatypes || array_search('recordings', $args['mediatype']) !== false ),
			'video'      => ( $all_mediatypes || array_search('video'     , $args['mediatype']) !== false ),
			'lecturer'   => ( $all_mediatypes || array_search('lecturer'  , $args['mediatype']) !== false ),
			'podcast'    => ( $all_mediatypes || array_search('podcast'   , $args['mediatype']) !== false ),
			'slides'     => ( $all_mediatypes || array_search('slides'    , $args['mediatype']) !== false ),
			'series'     => ( $all_mediatypes || array_search('series'    , $args['mediatype']) !== false ) );

		/***************************************************************/
		/***************************************************************/
		/***************************************************************/
		$mediatypes['video']  = false;
		$mediatypes['slides'] = false;
		/***************************************************************/
		/***************************************************************/
		/***************************************************************/

		$date       = ( array_key_exists('date', $args)       && !empty($args['date']) )
			? mysql_escape_string($args['date'])	   : null;
		$filter     = ( array_key_exists('filter', $args)     && !empty($args['filter']) )
			? mysql_escape_string($args['filter'])	 : null;
		$dep_filter = ( array_key_exists('department', $args) && !empty($args['department']) )
			? mysql_escape_string($args['department']) : null;
		
		$result = array();

		$count = 0;

		Lernfunk::query("SET character_set_results = 'latin1', "
			."character_set_client = 'utf8', "
			."character_set_connection = 'utf8', "
			."character_set_database = 'utf8', "
			."character_set_server = 'utf8'; ");

		// ## Get recording results ###########################################
		if ($mediatypes['recordings']) {

			$sql = 'SELECT m.object_id, m.title, m.description, m.series_id, '
				  .'date_format( m.date, "'.DATETIME_FMT.'") as date, m.url, '
				  .'m.thumbnail_url, m.cou_id, m.duration, f.name as formatname, f.mimetype, s.name as seriesname '
				  .'FROM mediaobject m NATURAL JOIN format f left outer join series s on s.series_id = m.series_id '
				  .'where (m.access_id = 1) and (s.access_id = 1)'; // access_id 1 is public
			if ($filter) {
				$sql .= ' and ( (m.title like "%'.$filter.'%") or (m.description like "%'.$filter.'%") '
					   .'or (s.name like "%'.$filter.'%") )';
			}
			if ($date) {
				$sql .= ' and ( DATE(m.date) = "'.$date.'" )';
			}

			if ( $rs = Lernfunk::query($sql) ) {

				$result['recordings'] = array();
				
				foreach ($rs as $r) {

					$data = array();
					
					$sql2 = 'SELECT ls.lecturer_id, l.ac_title, l.firstname, l.name, '
						   .'TRIM(CONCAT_WS(" ", l.ac_title, l.firstname, l.name)) as fullname, '
						   .'l.dep_id, d.academy_id, d.dep_name, d.dep_description, d.dep_number, a.ac_name '
						   .'FROM lecturer_series ls natural join lecturer l '
						   .'left outer join department d on l.dep_id = d.dep_id '
						   .'left outer join academy a on d.academy_id = a.academy_id '
						   .'where ls.series_id = '.$r->series_id; 
					$insert_this_dataset = true;
					if ($dep_filter) { 
						$insert_this_dataset = false;
						$sql2 .= ' and ( dep_name like "%'.$dep_filter.'%")';
					}
					$sql2 .= ';';

					if ( $rs2 = Lernfunk::query($sql2) ) {

						$lecturer   = array();
						$department = array();
						$academy	= array();
				
						foreach ($rs2 as $r2) {
							$insert_this_dataset = true;
							if ($r2->fullname)
								$lecturer[$r2->lecturer_id] = $r2->fullname;
							if ($r2->dep_name)
								$department[$r2->dep_id]	= $r2->dep_name;
							if ($r2->ac_name)
								$academy[$r2->academy_id]   = $r2->ac_name;
						}

						if ( count( $lecturer ) )
							$data['l']   = $lecturer;
						if ( count( $department ) )
							$data['d'] = $department;
						if ( count( $academy ) )
							$data['a']	= $academy;
						
					} // end sql2
					
					if ($insert_this_dataset) {
						if ($r->title)          $data['t']     = $r->title;
						if ($r->description)    $data['de']    = $r->description;
						if ($r->date)           $data['da']    = $r->date;
						if ($r->thumbnail_url)  $data['i']     = $r->thumbnail_url;
						if ($r->duration)       $data['du']    = $r->duration;
						if ($r->formatname)     $data['f']     = $r->formatname;
						if ($r->mimetype)       $data['m']     = $r->mimetype;
						if ($r->seriesname)     $data['s']     = $r->seriesname;
						if ($r->series_id)      $data['si']    = $r->series_id;
						if ($r->cou_id)         $data['ci']    = $r->cou_id;
						$result['recordings'][$r->object_id] = $data;
						$count++;
					}
					
				}
			} else { // end sql
				if (mysql_error())
					return json_encode( array(
							'type' => 'error', 
							'errtype' => 'sql_error', 
							'errmsg' => mysql_error(), 
							'sql_statement' => $sql
						) );
			} // end !sql
		}

		// ## Get lecturer result #############################################
		if ($mediatypes['lecturer'] && !$date) {
			
//			$sql = 'SELECT l.lecturer_id, TRIM(CONCAT_WS(" ", l.ac_title, l.firstname, l.name)) fullname, '
			$sql = 'SELECT l.lecturer_id,  '
				  .'l.email, d.dep_id, d.dep_name, a.academy_id, a.ac_name, '
				  .'l.ac_title, l.firstname, l.name '
				  .'FROM lecturer l '
				  .'left outer join department d on l.dep_id = d.dep_id '
				  .'left outer join academy a on d.academy_id = a.academy_id';
			if ($filter) {
				$sql .= ' where ( CONCAT_WS(" ", l.ac_title, l.firstname, l.name) like "%'.$filter.'%" )';
			}
			if ($dep_filter) { 
				if ($filter)
					$sql .= ' and';
				else
					$sql .= ' where';
				$sql .= ' ( d.dep_name like "%'.$dep_filter.'%" )';
			}
			$sql .= ';';

			if ( $rs = Lernfunk::query($sql) ) {

				$result['lecturer'] = array();
				
				foreach ($rs as $r) {
					
					$data = array();
//					if ($r->fullname)    $data['t']      = $r->fullname;
					if ($r->email)       $data['e']      = $r->email;
					if ($r->dep_id)      $data['d']      = array($r->dep_id => $r->dep_name);
					if ($r->academy_id)  $data['a']      = array($r->academy_id => $r->ac_name);
					if ($r->ac_title)    $data['at']     = $r->ac_title;
					if ($r->name)        $data['n']      = $r->name;
					if ($r->firstname)   $data['f']      = $r->firstname;
					$result['lecturer'][$r->lecturer_id] = $data;
					$count++;
				}
			} else { // end sql
				if (mysql_error())
					return json_encode( array(
							'type' => 'error', 
							'errtype' => 'sql_error', 
							'errmsg' => mysql_error(), 
							'sql_statement' => $sql
						) );
			} // end !sql
			
		}
		
		// ## Get podcast result ##############################################
		if ($mediatypes['podcast'] && !$date) {
			
			$sql = 'SELECT f.feed_id, f.feed_url, f.series_id, s.description, '
				  .'s.description_sh, ft.feedtype_desc, s.name, s.thumbnail_url, '
				  .'s.term_id, t.term_sh, t.term_lg '
				  .'FROM feeds f '
				  .'left outer join feedtype ft on f.feedtype_id = ft.feedtype_id '
				  .'left outer join series    s on s.series_id   = f.series_id '
				  .'left outer join terms     t on t.term_id     = s.term_id';
			if ($filter) {
				$sql .= ' where s.name like "%'.$filter.'%"';
			}
			$sql .= ';';

			if ( $rs = Lernfunk::query($sql) ) {

				$result['podcast'] = array();
				
				foreach ($rs as $r) {

					$data = array();
					
					$sql2 = 'SELECT ls.lecturer_id, TRIM(CONCAT_WS(" ", l.ac_title, l.firstname, l.name)) as fullname, '
						   .'l.dep_id, d.academy_id, d.dep_name, d.dep_description, d.dep_number, a.ac_name '
						   .'FROM lecturer_series ls natural join lecturer l '
						   .'left outer join department d on l.dep_id = d.dep_id '
						   .'left outer join academy a on d.academy_id = a.academy_id '
						   .'where ls.series_id = '.$r->series_id; 
					$insert_this_dataset = true;
					if ($dep_filter) { 
						$insert_this_dataset = false;
						$sql2 .= ' and ( dep_name like "%'.$dep_filter.'%")';
					}
					$sql2 .= ';';

					if ( $rs2 = Lernfunk::query($sql2) ) {

						$lecturer   = array();
						$department = array();
						$academy	= array();
				
						foreach ($rs2 as $r2) {
							$insert_this_dataset = true;
							if ($r2->fullname)
								$lecturer[$r2->lecturer_id] = $r2->fullname;
							if ($r2->dep_name)
								$department[$r2->dep_id]    = $r2->dep_name;
							if ($r2->ac_name)
								$academy[$r2->academy_id]   = $r2->ac_name;
						}

						if ( count( $lecturer ) )
							$data['l'] = $lecturer;
						if ( count( $department ) )
							$data['d'] = $department;
						if ( count( $academy ) )
							$data['a'] = $academy;
						
					} // end sql2
					
					if ($insert_this_dataset) {
					
						if ($r->name)           $data['t']   = $r->name;
						if ($r->feed_url)       $data['u']   = $r->feed_url;
						if ($r->series_id)      $data['s']   = $r->series_id;
						if ($r->feedtype_desc)  $data['ft']  = $r->feedtype_desc;
						if ($r->thumbnail_url)  $data['i']   = $r->thumbnail_url;
						if ($r->description)    $data['de']   = $r->description;
						if ($r->description_sh) $data['de']   = $r->description_sh;
						if ($r->term_id)        $data['ti']   = $r->term_id;
						if ($r->term_sh)        $data['ts']   = $r->term_sh;
						if ($r->term_lg)        $data['tl']   = $r->term_lg;

						$result['podcast'][$r->feed_id]      = $data;
						$count++;
					
					}
				}
			} else { // end sql
				if (mysql_error())
					return json_encode( array(
							'type' => 'error', 
							'errtype' => 'sql_error', 
							'errmsg' => mysql_error(), 
							'sql_statement' => $sql
						) );
			} // end !sql
		}
		

		// ## Get series result ###############################################
		if ($mediatypes['series'] && !$date) {

			$sql = 'SELECT s.series_id, s.name, s.description_sh, '
				.'s.description, s.thumbnail_url, s.term_id, t.term_lg, '
				.'(SELECT count(*) FROM mediaobject m where m.series_id = s.series_id and m.access_id = 1) as count '
				.'FROM series s natural join terms t '
				.'where s.access_id = 1 ';
			if ($filter) {
				$sql .= ' and s.name like "%'.$filter.'%"';
			}
			$sql .= ';';

			if ( $rs = Lernfunk::query($sql) ) {

				$result['series'] = array();
				
				foreach ($rs as $r) {

					$data = array();
					
					$sql2 = 'SELECT ls.lecturer_id, l.ac_title, l.firstname, l.name, '
						   .'TRIM(CONCAT_WS(" ", l.ac_title, l.firstname, l.name)) as fullname, '
						   .'l.dep_id, d.academy_id, d.dep_name, d.dep_description, d.dep_number, a.ac_name '
						   .'FROM lecturer_series ls natural join lecturer l '
						   .'left outer join department d on l.dep_id = d.dep_id '
						   .'left outer join academy a on d.academy_id = a.academy_id '
						   .'where ls.series_id = '.$r->series_id; 
					$insert_this_dataset = true;
					if ($dep_filter) { 
						$insert_this_dataset = false;
						$sql2 .= ' and ( dep_name like "%'.$dep_filter.'%")';
					}
					$sql2 .= ';';

					if ( $rs2 = Lernfunk::query($sql2) ) {

						$lecturer   = array();
						$department = array();
						$academy    = array();
				
						foreach ($rs2 as $r2) {
							$insert_this_dataset = true;
							if ($r2->fullname)
								$lecturer[$r2->lecturer_id] = $r2->fullname;
							if ($r2->dep_name)
								$department[$r2->dep_id]    = $r2->dep_name;
							if ($r2->ac_name)
								$academy[$r2->academy_id]   = $r2->ac_name;
						}

						if ( count( $lecturer ) )
							$data['l'] = $lecturer;
						if ( count( $department ) )
							$data['d'] = $department;
						if ( count( $academy ) )
							$data['a'] = $academy;
						
					} // end sql2
					
					if ($insert_this_dataset) {
						if ($r->name)            $data['t']      = $r->name;
						if ($r->term_lg)         $data['te']     = $r->term_lg;
						if ($r->term_id)         $data['ti']     = $r->term_id;
						if ($r->description)     $data['de']     = $r->description;
						if ($r->description_sh)  $data['ds']     = $r->description_sh;
						if ($r->thumbnail_url)   $data['i']      = $r->thumbnail_url;
						if ($r->count)           $data['c']      = $r->count;

						$result['series'][$r->series_id]         = $data;
						$count++;
					}
					
				}
			} else { // end sql
				if (mysql_error())
					return json_encode( array(
							'type' => 'error', 
							'errtype' => 'sql_error', 
							'errmsg' => mysql_error(), 
							'sql_statement' => $sql
						) );
			} // end !sql
		}

		if (__DEBUG__)
			print_r($result);
		return json_encode( array(
				'type' => 'result', 
				'count' => $count, 
				'data' => $result
			) );
	}

/*****************************************************************************/
/*****************************************************************************/

	public static function getdetails($args) {

		// check arguments
		if ( !array_key_exists('mediatype', $args) || empty($args['mediatype']) )
			return json_encode( array('type' => 'error', 
									  'errtype' => 'missing argument', 
									  'errmsg' => 'Argument \'mediatype\' is missing.',
									  'missing_argument' => 'mediatype')
							  );
		$mediatype = $args['mediatype'];
		if ( !array_key_exists('identifier', $args) || empty($args['identifier']) )
			return json_encode( array('type' => 'error', 
									  'errtype' => 'missing argument', 
									  'errmsg' => 'Argument \'identifier\' is missing.',
									  'missing_argument' => 'identifier')
							  );
		$identifier = intval($args['identifier']);
		
		// ## MEDIATYPE = RECORDINGS ##########################################
		if ($mediatype == 'recordings') {
			
			$sql = 'SELECT m.object_id, m.title, m.description, m.series_id, '
				  .'date_format( m.date, "'.DATETIME_FMT.'") as date, m.url, '
				  .'m.memory_size, m.author, m.thumbnail_url, m.preview_url, m.image_url, '
				  .'m.duration, m.location, m.add_url, m.add_url_text, f.mimetype, '
				  .'f.name formatname, f.requirements, l.language_long, l.language_short, '
				  .'s.name seriesname, s.description seriesdesc, s.description_sh seriesdesc_sh, '
				  .'s.thumbnail_url seriesthumb, s.add_url series_add_url, s.add_url_text series_add_url_text, '
				  .'s.keywords, t.term_sh, t.term_lg, c.category '
				  .'FROM mediaobject m '
				  .'left outer join format f on m.format_id = f.format_id '
				  .'left outer join language l on m.language_id = l.lang_id '
				  .'left outer join series s on m.series_id = s.series_id '
				  .'left outer join terms t on s.term_id = t.term_id '
				  .'left outer join category c on s.cat_id = c.cat_id '
				  .'where m.object_id = '.$identifier
				  .' and m.access_id = 1 '
				  .'limit 0,1;';
				  
			if ( $rs = Lernfunk::query($sql) ) {

				$result['details'] = array();
				
				foreach ($rs as $r) {

					$data = array();
					
					$data['id']		   = $r->object_id;
					$data['title']		= $r->title;
					$data['desc']		 = $r->description;
					$data['date']		 = $r->date;
					$data['url']		  = $r->url;
					$data['memsize']	  = $r->memory_size;
					$data['author']	   = $r->author;
					$data['thumb']		= $r->thumbnail_url;
					$data['preview']	  = $r->preview_url;
					$data['img']		  = $r->image_url;
					$data['duration']	 = $r->duration;
					$data['location']	 = $r->location;
					$data['add_url']	  = $r->add_url;
					$data['add_url_text'] = $r->add_url_text;
					$data['mimetype']	 = $r->mimetype;
					$data['format']	   = $r->formatname;
					$data['requirements'] = $r->requirements;
					$data['lang']		 = $r->language_long;
					$data['lang_sh']	  = $r->language_short;
					$data['term']		 = $r->term_lg;
					$data['term_sh']	  = $r->term_sh;
					$data['cat']		  = $r->category;
					// make own subdir for series
					$data['series']['id']		   = $r->series_id;
					$data['series']['name']		 = $r->seriesname;
					$data['series']['desc']		 = $r->seriesdesc;
					$data['series']['desc_sh']	  = $r->seriesdesc_sh;
					$data['series']['thumb']		= $r->seriesthumb;
					$data['series']['add_url']	  = $r->series_add_url;
					$data['series']['add_urk_text'] = $r->series_add_url_text;
					$data['series']['keywords']	 = $r->keywords;
					
					$sql2 = 'SELECT ls.lecturer_id, l.ac_title, l.firstname, l.name, '
						   .'TRIM(CONCAT_WS(" ", l.ac_title, l.firstname, l.name)) as fullname, '
						   .'l.dep_id, d.academy_id, d.dep_name, d.dep_description, d.dep_number, a.ac_name '
						   .'FROM lecturer_series ls natural join lecturer l '
						   .'left outer join department d on l.dep_id = d.dep_id '
						   .'left outer join academy a on d.academy_id = a.academy_id '
						   .'where ls.series_id = '.$r->series_id.';'; 

					if ( $rs2 = Lernfunk::query($sql2) ) {

						$lecturer   = array();
						$department = array();
						$academy	= array();
				
						foreach ($rs2 as $r2) {
							$insert_this_dataset = true;
							$lecturer[$r2->lecturer_id] = $r2->fullname;
							$department[$r2->dep_id]	= $r2->dep_name;
							$academy[$r2->academy_id]   = $r2->ac_name;
						}

						$data['lecturer']   = $lecturer;
						$data['department'] = $department;
						$data['academy']	= $academy;
						
					} // end sql2
				}
				
				$result['details'] = $data;
				$result['type'] = 'result';
				
				if (__DEBUG__)
					print_r($result);
				return json_encode( $result );
				
			} else { // end sql
				if (mysql_error())
					return json_encode( array(
							'type' => 'error', 
							'errtype' => 'sql_error', 
							'errmsg' => mysql_error(), 
							'sql_statement' => $sql
						) );
			} // end !sql
		}

		// ## MEDIATYPE = LECTURER ############################################
		if ($mediatype == 'lecturer') {

			$sql = 'select l.lecturer_id, l.ac_title, l.firstname, l.name, l.email, '
				  .'l.lec_url, d.dep_id, d.dep_name, a.academy_id, a.ac_name '
				  .'from lecturer l '
				  .'left outer join department d on l.dep_id = d.dep_id '
				  .'left outer join academy a on d.academy_id = a.academy_id '
				  .'where l.lecturer_id = '.$identifier.' '
				  .'limit 0,1;';
				  
			if ( $rs = Lernfunk::query($sql) ) {

				$result['details'] = array();
				
				foreach ($rs as $r) {

					$data = array();
					
					$data['id']		 = $r->lecturer_id;
					$data['ac_title']   = $r->ac_title;
					$data['firstname']  = $r->firstname;
					$data['name']	   = $r->name;
					$data['email']	  = $r->email;
					$data['url']		= $r->lec_url;
					$data['academy']	= array( $r->academy_id => $r->ac_name  );
					$data['department'] = array( $r->dep_id	 => $r->dep_name );
					
					$sql2 = 'select ls.series_id, s.name, s.description, t.term_id, t.term_lg '
						.'from lecturer_series ls '
						.'natural join series s '
						.'left outer join terms t on s.term_id = t.term_id '
						.'where (lecturer_id = '.$r->lecturer_id.') and (s.access_id = 1) '
						.'order by s.term_id asc;';

					if ( $rs2 = Lernfunk::query($sql2) ) {

						$series = array();
				
						foreach ($rs2 as $r2) {
							$series[$r2->series_id] = array( 
									'name'    => $r2->name, 
									'desc'    => $r2->description,
									'term_id' => $r2->term_id,
									'term'    => $r2->term_lg
								);
						}

						$data['series']   = $series;
						
					} // end sql2
				}
				
				$result['details'] = $data;
				$result['type'] = 'result';
				
				if (__DEBUG__)
					print_r($result);
				return json_encode( $result );
				
			} else { // end sql
				if (mysql_error())
					return json_encode( array(
							'type' => 'error', 
							'errtype' => 'sql_error', 
							'errmsg' => mysql_error(), 
							'sql_statement' => $sql
						) );
			} // end !sql

		// ## MEDIATYPE = PODCAST #############################################
		} elseif ($mediatype == 'podcast') {

			$sql = 'select f.feed_id, f.feed_url, s.series_id, ft.feedtype_desc, '
				  .'s.name, s.description, s.description_sh, s.thumbnail_url, '
				  .'t.term_sh, t.term_lg, c.category '
				  .'from feeds f '
				  .'left outer join feedtype ft on f.feedtype_id = ft.feedtype_id '
				  .'left outer join series s on f.series_id = s.series_id '
				  .'left outer join terms t on s.term_id = t.term_id '
				  .'left outer join category c on s.cat_id = c.cat_id '
				  .'where f.feed_id = '.$identifier.' '
				  .'limit 0,1;';
				  
			if ( $rs = Lernfunk::query($sql) ) {

				$result['details'] = array();
				
				foreach ($rs as $r) {

					$data = array();
					
					$data['id']				= $r->feed_id;
					$data['url']			   = $r->feed_url;
					$data['feedtype']		  = $r->feedtype_desc;
					$data['term']			  = $r->term_lg;
					$data['term_sh']		   = $r->term_sh;
					$data['cat']			   = $r->category;
					$data['series']			= array();
					$data['series']['id']	  = $r->series_id;
					$data['series']['name']	= $r->name;
					$data['series']['desc']	= $r->description;
					$data['series']['desc_sh'] = $r->description_sh;
					$data['series']['thumb']   = $r->thumbnail_url;
					
				}
				
				$result['details'] = $data;
				$result['type'] = 'result';
				
				if (__DEBUG__)
					print_r($result);
				return json_encode( $result );
				
			} else { // end sql
				if (mysql_error())
					return json_encode( array(
							'type' => 'error', 
							'errtype' => 'sql_error', 
							'errmsg' => mysql_error(), 
							'sql_statement' => $sql
						) );
			} // end !sql

		// ## MEDIATYPE = SERIES ##############################################
		} elseif ($mediatype == 'series') {
			
			$sql = 'SELECT s.series_id, s.name, s.description, s.description_sh, '
				  .'s.thumbnail_url, s.add_url, s.add_url_text, s.portal_url, '
				  .'s.keywords, t.term_sh, t.term_lg, c.category, f.feed_url '
				  .'FROM series s '
				  .'left outer join terms t on s.term_id = t.term_id '
				  .'left outer join category c on s.cat_id = c.cat_id '
				  .'left outer join feeds f on s.series_id = f.series_id '
				  .'where s.series_id = '.$identifier.' and s.access_id = 1 '
				  .'limit 0,1;';
				  
			if ( $rs = Lernfunk::query($sql) ) {

				$result['details'] = array();
				
				foreach ($rs as $r) {

					$data = array();
					
					$data['id']           = $r->series_id;
					$data['name']         = $r->name;
					$data['desc']         = $r->description;
					$data['desc_sh']      = $r->description_sh;
					$data['thumb']        = $r->thumbnail_url;
					$data['add_url']      = $r->add_url;
					$data['add_urk_text'] = $r->add_url_text;
					$data['keywords']     = $r->keywords;
					$data['cat']          = $r->category;
					$data['term']         = $r->term_lg;
					$data['term_sh']      = $r->term_sh;
					$data['feed_url']     = $r->feed_url;
					$data['portal_url']   = $r->portal_url;
					
					// get lecturer, department and academy
					$sql2 = 'SELECT ls.lecturer_id, l.ac_title, l.firstname, l.name, '
						   .'TRIM(CONCAT_WS(" ", l.ac_title, l.firstname, l.name)) as fullname, '
						   .'l.dep_id, d.academy_id, d.dep_name, d.dep_description, d.dep_number, a.ac_name '
						   .'FROM lecturer_series ls natural join lecturer l '
						   .'left outer join department d on l.dep_id = d.dep_id '
						   .'left outer join academy a on d.academy_id = a.academy_id '
						   .'where ls.series_id = '.$r->series_id.';'; 

					if ( $rs2 = Lernfunk::query($sql2) ) {

						$lecturer   = array();
						$department = array();
						$academy	= array();
				
						foreach ($rs2 as $r2) {
							$insert_this_dataset = true;
							$lecturer[$r2->lecturer_id] = $r2->fullname;
							$department[$r2->dep_id]	= $r2->dep_name;
							$academy[$r2->academy_id]   = $r2->ac_name;
						}

						$data['lecturer']   = $lecturer;
						$data['department'] = $department;
						$data['academy']	= $academy;
						
					} // end sql2

					// get recordings
					$sql3 = 'select *, date_format( m.date, "'.DATETIME_FMT.'") as fmtdate '
						.'from mediaobject m '
						.'natural join format f '
						.'where (series_id = '.$identifier.') '
						.'and m.access_id = 1 '
						.'order by date asc;';

					if ( $rs3 = Lernfunk::query($sql3) ) {

						$recordings = array();
				
						foreach ($rs3 as $r3) {
							$recordings[] = array(
									'id'       => $r3->object_id,
									'title'    => $r3->title,
									'desc'     => $r3->description,
									'date'     => $r3->fmtdate,
									'url'      => $r3->url,
									'img'      => $r3->image_url,
									'thumb'    => $r3->thumbnail_url,
									'preview'  => $r3->preview_url,
									'duration' => $r3->duration,
									'cou_id'   => $r3->cou_id,
									'mimetype' => $r3->mimetype,
									'format'   => $r3->name
								);
						}

						$data['recordings'] = $recordings;
						
					} // end sql3

					// get feeds
					$sql4 = 'select f.feed_url, t.feedtype_desc '
						.'from feeds f '
						.'left outer join feedtype t '
						.'on f.feedtype_id = t.feedtype_id '
						.'where series_id = '.$identifier.';';

					if ( $rs4 = Lernfunk::query($sql4) ) {

						$feeds = array();
				
						foreach ($rs4 as $r4) {
							$feeds[] = array(
									'url'  => $r4->feed_url,
									'type' => $r4->feedtype_desc
								);
						}

						$data['feeds'] = $feeds;
						
					} // end sql3
				}
				
				$result['details'] = $data;
				$result['type'] = 'result';
				
				if (__DEBUG__)
					print_r($result);
				return json_encode( $result );
				
			} else { // end sql
				if (mysql_error())
					return json_encode( array(
							'type' => 'error', 
							'errtype' => 'sql_error', 
							'errmsg' => mysql_error(), 
							'sql_statement' => $sql
						) );
			} // end !sql

		// ## UNKNOWN MEDIATYPE ###############################################
		} else {
			return json_encode( array(
					'type' => 'error', 
					'errtype' => 'invalid_mediatype', 
					'errmsg' => '\''.$mediatype.'\' is not a valid mediatype.'
				) );
		}
		
		return '{}';
	}

/*****************************************************************************/
/*****************************************************************************/

	public static function getdepartments($args) {
		
		$sql = 'select d.dep_id, d.dep_name, d.dep_description, a.ac_name '
			  .'from department d '
			  .'left outer join academy a on d.academy_id = a.academy_id '
			  .'order by ac_name, dep_name asc;';
		
		if ( $rs = Lernfunk::query($sql) ) {

			$result = array();
			
			foreach ($rs as $r) {
				if ( !array_key_exists($r->ac_name, $result) )
					$result[$r->ac_name] = array();
				$result[$r->ac_name][$r->dep_id] = array('name' => $r->dep_name, 'desc' => $r->dep_description);
			}

			if (__DEBUG__)
				print_r($result);
			return json_encode( array(
					'type' => 'result', 
					'departments' => $result
				) );

		} else { // end sql
			if (mysql_error())
				return json_encode( array(
						'type' => 'error', 
						'errtype' => 'sql_error', 
						'errmsg' => mysql_error(), 
						'sql_statement' => $sql
					) );
		} // end !sql
	}

/*****************************************************************************/
/*****************************************************************************/

	public static function gettags($args) {
		
		$sql = 'select t.tagname, t.count '
			  .'from tag t '
			  .'order by count desc';

		if ( array_key_exists('maxcount', $args) && !empty($args['maxcount']) )
			$sql .= ' limit 0, '.intval($args['maxcount']);
		$sql .= ';';
		if ( $rs = Lernfunk::query($sql) ) {

			$result = array();
			
			foreach ($rs as $r) {
				if ( !array_key_exists($r->tagname, $result) )
					$result[$r->tagname] = array();
				$result[$r->tagname] = $r->count;
			}

			if (__DEBUG__)
				print_r($result);
			return json_encode( array('type' => 'result', 'tags' => $result) );

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

/*****************************************************************************/
/*****************************************************************************/

	public static function getrecdates($args) {
		
		$year      = intval( $args['year']  );
		$month     = intval( $args['month'] );
		$nextmonth = intval( $args['month'] ) + 1;
		if (strlen( $month ) < 2)
			$month = '0'.$month;
		if (strlen( $nextmonth ) < 2)
			$nextmonth = '0'.$nextmonth;

		$sql = 'select date_format( m.date, "%Y" ) as year, '
			.'date_format( m.date, "%c" ) as month, '
			.'date_format( m.date, "%e" ) as day '
			.'from mediaobject m '
			.'left outer join series s on s.series_id = m.series_id '
			.'where m.access_id = 1 and s.access_id = 1 '
			.'group by date_format( date, "%Y-%m-%d" )';

		if ( $rs = Lernfunk::query($sql) ) {

			$result = array();
			
			foreach ($rs as $r) {
				if (!is_array( $result[$r->year] ))
					$result[$r->year] = array();
				if (!is_array( $result[$r->year][$r->month] ))
					$result[$r->year][$r->month] = array();
				$result[$r->year][$r->month][] = $r->day;
			}

			if (__DEBUG__)
				print_r($result);
			return json_encode( array('type' => 'result', 'recdates' => $result) );

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

/*****************************************************************************/
/*****************************************************************************/

	public static function getnews( $args ) {
		
		// check if only some mediatypes are requested
		$mimetypefilter = '';
		if (array_key_exists( 'mimetypefilter', $args ) 
				&& is_array( $args['mimetypefilter'] ) ) {
			foreach ( $args['mimetypefilter'] as $key => $value ) {
				if ( $mimetypefilter == '' ) {
					$mimetypefilter = ' and ( f.mimetype like "'.mysql_escape_string($value).'" ';
				} else {
					$mimetypefilter .= ' or f.mimetype like "'.mysql_escape_string($value).'" ';
				}
			}
			// close bracket
			if ( $mimetypefilter != '' ) {
				$mimetypefilter .= ' ) ';
			}
		}

		// number of datasets to return
		$count = intval( $args['count'] );

		$result = array();

		$sql = 'SELECT m.object_id, m.title, m.description, '
			.'m.series_id, m.date as datenf, date_format( m.date, "'.DATETIME_FMT.'") as date, m.url, m.thumbnail_url, '
			.'m.preview_url, m.image_url, f.mimetype, f.name as formatname, '
			.'s.name as seriesname, s.description as seriesdesc, s.thumbnail_url as seriesthumb '
			.'FROM mediaobject m '
			.'left outer join format f '
			.'on f.format_id = m.format_id '
			.'left outer join series s '
			.'on s.series_id = m.series_id '
			.'where m.access_id = 1 '
			.$mimetypefilter
			.'and s.access_id = 1 '
			.'group by series_id '
			.'order by datenf desc '
			.'limit 0,'.$count.';';
		if ( $rs = Lernfunk::query($sql) ) {

			$result = array();
			
			foreach ($rs as $r) {
				$data = array();
				$data['series_id'] = $r->series_id;
				$data['object_id'] = $r->object_id;
				$data['title'] = $r->title;
				$data['description'] = $r->description;
				$data['date'] = $r->date;
				$data['url'] = $r->url;
				$data['thumbnail_url'] = $r->thumbnail_url;
				$data['preview_url'] = $r->preview_url;
				$data['image_url'] = $r->image_url;
				$data['mimetype'] = $r->mimetype;
				$data['formatname'] = $r->formatname;
				$data['seriesname'] = $r->seriesname;
				$data['seriesdesc'] = $r->seriesdesc;
				$data['seriesthumb'] = $r->seriesthumb;
				$result[] = $data;
			}

			$sql2 = 'select (SELECT count(*) FROM mediaobject m '
				.'NATURAL JOIN format f '
				.'left outer join series s on s.series_id = m.series_id '
				.'where m.access_id = 1 and s.access_id = 1) as recording_count, '
				.'(SELECT count(*) FROM lecturer) as lecturer_count, '
				.'(SELECT count(*) FROM feeds) as feed_count, '
				.'(SELECT count(*) FROM series where access_id = 1) as series_count';

			if ( $r = Lernfunk::query($sql2) ) {
				$r = $r[0];
				$result['count'] = array(
						'recording' => $r->recording_count,
						'lecturer'  => $r->lecturer_count,
						'feed'      => $r->feed_count,
						'series'    => $r->series_count
					);
			} else { // end sql
				if (mysql_error())
					return json_encode( array(
							'type' => 'error', 
							'errtype' => 'sql_error', 
							'errmsg' => mysql_error(), 
							'sql_statement' => $sql ) 
						);
			} // end sql2

			if (__DEBUG__)
				print_r($result);
			return json_encode( array('type' => 'result', 'news' => $result) );

		} else { // end sql
			if (mysql_error())
				return json_encode( array(
						'type' => 'error', 
						'errtype' => 'sql_error', 
						'errmsg' => mysql_error(), 
						'sql_statement' => $sql ) 
					);
		} // end !sql
		
	}

/*****************************************************************************/
/*****************************************************************************/

	public static function getdbdata($args) {
		return '{}';
	}

}

?>
