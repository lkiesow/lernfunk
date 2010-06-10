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
	along with Lernfunk.  If not, see <http://www.gnu.org/licenses/>. 
*/


require_once(dirname(__FILE__).'/config.php');

class LFService {

	private static $commands = array('getdata', 'getdetails', 'getdepartments', 'getdbdata', 'gettags', 'getvideoformats');

	/**
	 * Returns if the command-string is valid or not
	 **/
	public static function isvalidcmd($cmd) {
		return array_search($cmd, self::$commands) !== false;
	}

	public static function getdata($args) {
		// check what data is requested
		$all_mediatypes = !array_key_exists('mediatype', $args);// || empty($args['mediatype']);
		$mediatypes = array('video'	=> ( $all_mediatypes || array_search('video'   , $args['mediatype']) !== false ),
							'lecturer' => ( $all_mediatypes || array_search('lecturer', $args['mediatype']) !== false ),
							'podcast'  => ( $all_mediatypes || array_search('podcast' , $args['mediatype']) !== false ),
							'slides'   => ( $all_mediatypes || array_search('slides'  , $args['mediatype']) !== false ),
							'series'   => ( $all_mediatypes || array_search('series'  , $args['mediatype']) !== false ) );

		$date	   = ( array_key_exists('date', $args)	   && !empty($args['date']) )	   ? mysql_escape_string($args['date'])	   : null;
		$filter	 = ( array_key_exists('filter', $args)	 && !empty($args['filter']) )	 ? mysql_escape_string($args['filter'])	 : null;
		$dep_filter = ( array_key_exists('department', $args) && !empty($args['department']) ) ? mysql_escape_string($args['department']) : null;
		
		$result = array();

		$count = 0;

		Lernfunk::query("SET character_set_results = 'latin1', "
			."character_set_client = 'utf8', "
			."character_set_connection = 'utf8', "
			."character_set_database = 'utf8', "
			."character_set_server = 'utf8'; ");
		// ## Get video result ################################################
		if ($mediatypes['video']) {

			$sql = 'SELECT m.object_id, m.title, m.description, m.series_id, m.date, m.url, '
				  .'m.thumbnail_url, m.duration, f.name as formatname, f.mimetype, s.name as seriesname '
				  .'FROM mediaobject m NATURAL JOIN format f left outer join series s on s.series_id = m.series_id '
				  .'where (f.mimetype like "%video%") and (m.access_id = 1)'; // access_id 1 is public
			if ($filter) {
				$sql .= ' and ( (m.title like "%'.$filter.'%") or (m.description like "%'.$filter.'%") '
					   .'or (s.name like "%'.$filter.'%") )';
			}
			if ($date) {
				$sql .= ' and ( DATE(m.date) = "'.$date.'" )';
			}

			if ( $rs = Lernfunk::query($sql) ) {

				$result['video'] = array();
				
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
							$lecturer[$r2->lecturer_id] = $r2->fullname;
							$department[$r2->dep_id]	= $r2->dep_name;
							$academy[$r2->academy_id]   = $r2->ac_name;
						}

						$data['lecturer']   = $lecturer;
						$data['department'] = $department;
						$data['academy']	= $academy;
						
					} // end sql2
					
					if ($insert_this_dataset) {
						$data['title']     = $r->title;
						$data['desc']      = $r->description;
						$data['date']      = $r->date;
						$data['img']       = $r->thumbnail_url;
						$data['duration']  = $r->duration;
						$data['format']    = $r->formatname;
						$data['mimetype']  = $r->mimetype;
						$data['series']    = $r->seriesname;
						$data['series_id'] = $r->series_id;
						$result['video'][$r->object_id] = $data;
						$count++;
					}
					
				}
			} else { // end sql
				if (mysql_error())
					return json_encode( array('type' => 'error', 'errtype' => 'sql_error', 'errmsg' => mysql_error(), 'sql_statement' => $sql) );
			} // end !sql
		}

		// ## Get lecturer result #############################################
		if ($mediatypes['lecturer'] && !$date) {
			
			$sql = 'SELECT l.lecturer_id, TRIM(CONCAT_WS(" ", l.ac_title, l.firstname, l.name)) fullname, '
				  .'l.email, d.dep_id, d.dep_name, a.academy_id, a.ac_name '
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
					$data['title']	  = $r->fullname;
					$data['email']	  = $r->email;
					$data['department'] = array($r->dep_id => $r->dep_name);
					$data['academy']	= array($r->academy_id => $r->ac_name);
					$result['lecturer'][$r->lecturer_id] = $data;
					$count++;
				}
			} else { // end sql
				if (mysql_error())
					return json_encode( array('type' => 'error', 'errtype' => 'sql_error', 'errmsg' => mysql_error(), 'sql_statement' => $sql) );
			} // end !sql
			
		}
		
		// ## Get podcast result ##############################################
		if ($mediatypes['podcast'] && !$date) {
			
			$sql = 'SELECT f.feed_id, f.feed_url, f.series_id, ft.feedtype_desc, s.name, s.thumbnail_url '
				  .'FROM feeds f '
				  .'left outer join feedtype ft on f.feedtype_id = ft.feedtype_id '
				  .'left outer join series s on s.series_id = f.series_id';
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
							$lecturer[$r2->lecturer_id] = $r2->fullname;
							$department[$r2->dep_id]	= $r2->dep_name;
							$academy[$r2->academy_id]   = $r2->ac_name;
						}

						$data['lecturer']   = $lecturer;
						$data['department'] = $department;
						$data['academy']	= $academy;
						
					} // end sql2
					
					if ($insert_this_dataset) {
					
						$data['title']	 = $r->name;
						$data['url']	   = $r->feed_url;
						$data['series_id'] = $r->series_id;
						$data['type']	  = $r->feedtype_desc;
						$data['img']	   = $r->thumbnail_url;
						$result['podcast'][$r->feed_id] = $data;
						$count++;
					
					}
				}
			} else { // end sql
				if (mysql_error())
					return json_encode( array('type' => 'error', 'errtype' => 'sql_error', 'errmsg' => mysql_error(), 'sql_statement' => $sql) );
			} // end !sql
		}
		
		// ## Get slides result ###############################################
		if ($mediatypes['slides']) {

			$sql = 'SELECT m.object_id, m.title, m.description, m.series_id, m.date, m.url, '
				  .'m.thumbnail_url, m.duration, f.name as formatname, f.mimetype, s.name as seriesname '
				  .'FROM mediaobject m NATURAL JOIN format f left outer join series s on s.series_id = m.series_id '
				  .'where (f.mimetype like "%virtpresenter%") and (m.access_id = 1)'; // access_id 1 is public
			if ($filter) {
				$sql .= ' and ( (m.title like "%'.$filter.'%") or (m.description like "%'.$filter.'%") '
					   .'or (s.name like "%'.$filter.'%") )';
			}
			if ($date) {
				$sql .= ' and ( DATE(m.date) = "'.$date.'" )';
			}

			if ( $rs = Lernfunk::query($sql) ) {

				$result['slides'] = array();
				
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
							$lecturer[$r2->lecturer_id] = $r2->fullname;
							$department[$r2->dep_id]	= $r2->dep_name;
							$academy[$r2->academy_id]   = $r2->ac_name;
						}

						$data['lecturer']   = $lecturer;
						$data['department'] = $department;
						$data['academy']	= $academy;
						
					} // end sql2
					
					if ($insert_this_dataset) {
						$data['title']	 = $r->title;
						$data['desc']	  = $r->description;
						$data['date']      = $r->date;
						$data['img']	   = $r->thumbnail_url;
						$data['duration']  = $r->duration;
						$data['format']	= $r->formatname;
						$data['mimetype']  = $r->mimetype;
						$data['series']	= $r->seriesname;
						$data['series_id'] = $r->series_id;
						$result['slides'][$r->object_id] = $data;
						$count++;
					}
					
				}
			} else { // end sql
				if (mysql_error())
					return json_encode( array('type' => 'error', 'errtype' => 'sql_error', 'errmsg' => mysql_error(), 'sql_statement' => $sql) );
			} // end !sql
		}

		// ## Get series result ###############################################
		if ($mediatypes['series'] && !$date) {

			$sql = 'SELECT s.series_id, s.name, s.description_sh, s.description, s.thumbnail_url, s.term_id, t.term_lg '
				.'FROM series s natural join terms t ';
			if ($filter) {
				$sql .= ' where s.name like "%'.$filter.'%"';
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
					
					if ($insert_this_dataset) {
						$data['title']   = $r->name;
						$data['term']    = $r->term_lg;
						$data['term_id'] = $r->term_id;
						$data['desc']    = $r->description;
						$data['desc_sh'] = $r->description_sh;
						$data['img']     = $r->thumbnail_url;
						$result['series'][$r->series_id] = $data;
						$count++;
					}
					
				}
			} else { // end sql
				if (mysql_error())
					return json_encode( array('type' => 'error', 'errtype' => 'sql_error', 'errmsg' => mysql_error(), 'sql_statement' => $sql) );
			} // end !sql
		}

		if (__DEBUG__)
			print_r($result);
		return json_encode( array('type' => 'result', 'count' => $count, 'data' => $result) );
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
		
		// ## MEDIATYPE = VIDEO ###############################################
		if ($mediatype == 'video') {
			
			$sql = 'SELECT m.object_id, m.title, m.description, m.series_id, m.date, m.url, '
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
				  .'where m.object_id = '.$identifier.' and f.mimetype like "%video%" and m.access_id = 1 '
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
					return json_encode( array('type' => 'error', 'errtype' => 'sql_error', 'errmsg' => mysql_error(), 'sql_statement' => $sql) );
			} // end !sql

		// ## MEDIATYPE = LECTURER ############################################
		} elseif ($mediatype == 'lecturer') {

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
						.'where lecturer_id = '.$r->lecturer_id.' '
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
					return json_encode( array('type' => 'error', 'errtype' => 'sql_error', 'errmsg' => mysql_error(), 'sql_statement' => $sql) );
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
					return json_encode( array('type' => 'error', 'errtype' => 'sql_error', 'errmsg' => mysql_error(), 'sql_statement' => $sql) );
			} // end !sql

		// ## MEDIATYPE = SLIDES ##############################################
		} elseif ($mediatype == 'slides') {
			
			$sql = 'SELECT m.object_id, m.title, m.description, m.series_id, m.date, m.url, '
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
				  .'where m.object_id = '.$identifier.' and f.mimetype like "%virtpresenter%" and m.access_id = 1 '
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
					return json_encode( array('type' => 'error', 'errtype' => 'sql_error', 'errmsg' => mysql_error(), 'sql_statement' => $sql) );
			} // end !sql
				
		// ## MEDIATYPE = SERIES ##############################################
		} elseif ($mediatype == 'series') {
			
			$sql = 'SELECT s.series_id, s.name, s.description, s.description_sh, '
				  .'s.thumbnail_url, s.add_url, s.add_url_text, '
				  .'s.keywords, t.term_sh, t.term_lg, c.category '
				  .'FROM series s '
				  .'left outer join terms t on s.term_id = t.term_id '
				  .'left outer join category c on s.cat_id = c.cat_id '
				  .'where s.series_id = '.$identifier.' and s.access_id = 1 '
				  .'limit 0,1;';
				  
			if ( $rs = Lernfunk::query($sql) ) {

				$result['details'] = array();
				
				foreach ($rs as $r) {

					$data = array();
					
					$data['id']		   = $r->series_id;
					$data['name']		 = $r->name;
					$data['desc']		 = $r->description;
					$data['desc_sh']	  = $r->description_sh;
					$data['thumb']		= $r->thumbnail_url;
					$data['add_url']	  = $r->add_url;
					$data['add_urk_text'] = $r->add_url_text;
					$data['keywords']	 = $r->keywords;
					$data['cat']		  = $r->category;
					$data['term']		 = $r->term_lg;
					$data['term_sh']	  = $r->term_sh;
					
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

					$sql3 = 'select m.object_id, m.title, m.description, f.mimetype '
						.'from mediaobject m '
						.'natural join format f '
						.'where (series_id = '.$identifier.') '
						.'order by date asc;';

					if ( $rs3 = Lernfunk::query($sql3) ) {

						$slides = array();
						$videos = array();
				
						foreach ($rs3 as $r3) {
							if ( strpos( $r3->mimetype, 'video' ) === FALSE ) {
								$slides[$r3->object_id] = array(
										'title' => $r3->title,
										'desc'  => $r3->description
									);
							} else {
								$videos[$r3->object_id] = array(
										'title' => $r3->title,
										'desc'  => $r3->description
									);
							}
						}

						$data['videos'] = $videos;
						$data['slides'] = $slides;
						
					} // end sql3
				}
				
				$result['details'] = $data;
				$result['type'] = 'result';
				
				if (__DEBUG__)
					print_r($result);
				return json_encode( $result );
				
			} else { // end sql
				if (mysql_error())
					return json_encode( array('type' => 'error', 'errtype' => 'sql_error', 'errmsg' => mysql_error(), 'sql_statement' => $sql) );
			} // end !sql

		// ## UNKNOWN MEDIATYPE ###############################################
		} else {
			return json_encode( array('type' => 'error', 
									  'errtype' => 'invalid_mediatype', 
									  'errmsg' => '\''.$mediatype.'\' is not a valid mediatype.')
							  );
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
			return json_encode( array('type' => 'result', 'departments' => $result) );

		} else { // end sql
			if (mysql_error())
				return json_encode( array('type' => 'error', 'errtype' => 'sql_error', 'errmsg' => mysql_error(), 'sql_statement' => $sql) );
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
				return json_encode( array('type' => 'error', 'errtype' => 'sql_error', 'errmsg' => mysql_error(), 'sql_statement' => $sql) );
		} // end !sql
		
	}

/*****************************************************************************/
/*****************************************************************************/

	public static function getvideoformats($args) {
		
		$sql = 'select name from format where mimetype like "%video%" order by name asc;';
		if ( $rs = Lernfunk::query($sql) ) {

			$result = array();
			
			foreach ($rs as $r) {
				$result[] = $r->name;
			}

			if (__DEBUG__)
				print_r($result);
			return json_encode( array('type' => 'result', 'tags' => $result) );

		} else { // end sql
			if (mysql_error())
				return json_encode( array('type' => 'error', 'errtype' => 'sql_error', 'errmsg' => mysql_error(), 'sql_statement' => $sql) );
		} // end !sql
		
	}

/*****************************************************************************/
/*****************************************************************************/

	public static function getdbdata($args) {
		return '{}';
	}

}

?>
