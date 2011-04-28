<?php
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

include_once(dirname(__FILE__).'/recordtypes.php');

class LFAdmin {

    /*************************************************************************/
    /*************************************************************************/
    /* RGeneral php functions                                                */
    /*************************************************************************/
    /*************************************************************************/

    /**
     * Load file and return contens as string
     * /params 
     *   filename : name of the file to load
     */
    public static function load_file($filename) {
        if (file_exists($filename)) {
            $handle = fopen($filename, 'r');
            $out = fread($handle, filesize($filename));
            fclose($handle);
            return $out;
        } else
            die("Error: Cannot load $filename");
    }

    /**
     * Get Template as string and substitute all given tags
     * /params
     *   template      : template as string
     *   substitutions : tags to replace
     */
    public static function process($template, $substitutions) {
        foreach (array_keys($substitutions) as $s)
            $m[] = '/\(:'.$s.':\)/';
        return preg_replace($m, array_values($substitutions), $template);
    }

    /**
     * Generate options for HTML-select-element from database table
     * /params
     *   table         : database table to get the data from
     *   id_field      : primary id field of the table
     *   desc          : array of fields to use as description
     *   selected_item : id of the item that should be selected by 
     *                   default (default: null)
     *   defaultvalue  : additional option that is added as first item 
     *                   and is selected by default if arg4 is null.
     *   arg4          : selected item
     *   arg5          : defaultvalue
     *   arg6          : additional sql
     */
    private static function make_options($table, $id_field, $desc) { 

        if (func_num_args() >= 4)
            $selected = func_get_arg(3);
        else
            $selected = false;

        if ((func_num_args() < 5) || (func_get_arg(4) == false))
            $out = '<option value="">(none)</option>';

        $add_sql = (func_num_args() >= 6) ? func_get_arg(5) : '';
            
        $sql = "SELECT $id_field,".implode(',', $desc)." FROM $table WHERE 1 ".$add_sql.";";
        $out = '';
        if ($rs = Lernfunk::query($sql)) {
            foreach ($rs as $r) {
                $r = $r->toArray();
                $out .= '<option value="'.$r[$id_field].'"';
                if (($selected) && ($r[$id_field] == $selected))
                    $out .= ' selected="true"';
                $out .= '>';
                foreach ($desc as $d)
                    //$out .= htmlentities($r[$d]).' ';
                    $out .= $r[$d].' ';
                $out .= '</option>'."\n";
            }
        }
        return $out;
    }

    /*************************************************************************/
    /*************************************************************************/
    /* Serieseditor                                                          */
    /*************************************************************************/
    /*************************************************************************/
    
    /**
     * Remove lecturer from series
     * /params
     *   params: array with field 'id' that specifies the lecturer
     */
    public static function ajax_series_remove_lecturer($params) {
        $id = $params['id'];
        $sql = "DELETE FROM `lecturer_series` WHERE `lecturer_series`.`lecturer_id`='$id';";
        if (Lernfunk::query($sql))
            return self::find_lecturers_for_series($id);
        else
            return 'ERROR: '.mysql_error()."<br>\n".$sql;
    }

    /**
     * Add lecturer from series
     * /params
     *   params['lecturer_id']: Identifier of lecturer to add
     *   params['series_id']  : identifier of series the lecturer should be added to
     */
    public static function ajax_series_add_lecturer($params) {
        $lecturer_id = $params['lecturer_id'];
        $series_id   = $params['series_id'];
        $sql = "INSERT INTO `lecturer_series` (`lecturer_id`,`series_id`) ".
                  "VALUES ('$lecturer_id','$series_id') ".
                  "ON DUPLICATE KEY UPDATE `lecturer_id`='$lecturer_id', `series_id`='$series_id';";
        if (Lernfunk::query($sql))
            return self::find_lecturers_for_series($series_id);
        else
            return 'ERROR: '.mysql_error()."<br>\n".$sql;
    }

    /**
     * Get options for lecturer-HTML-select-element of a specified series
     * /params
     *   params['lecturer_id']: Identifier of lecturer to add
     *   params['series_id']: identifier of series the lecturer should be added to
     */
    private static function find_lecturers_for_series($series_id) {
        $sql2 = 'SELECT * FROM `lecturer_series` '.
                      'LEFT JOIN `lecturer` ON (`lecturer_series`.`lecturer_id`=`lecturer`.`lecturer_id`) '.
                    "WHERE `lecturer_series`.`series_id`='$series_id';";
        $out = '';
        if ($rs2 = Lernfunk::query($sql2)) {
            foreach ($rs2 as $lecturer)
                $out .= '<option value="'.$lecturer->lecturer_id.'">'.$lecturer->ac_title.' '.$lecturer->firstname.' '.$lecturer->name.'</option>';
        }
        return $out;
    }

    public static function ajax_get_last_recordings($params) {
        $count = $params['count'];
        $withoutseries = $params['withoutseries'];

        if ($withoutseries == 'false') {
            $sql = 'SELECT * FROM `mediaobject` ORDER BY `date` DESC LIMIT 0 , '.mysql_escape_string($count).';';
        } else {
            $sql = 'SELECT m.* FROM `mediaobject` m '
                  .'left outer join series s on m.series_id = s.series_id '
                  .'where s.series_id is NULL order by date desc limit 0, '.mysql_escape_string($count).';';
        }
        if ($rs = Lernfunk::query($sql)) {
            $result = '';
            foreach ($rs as $r) {
                $result .= '<div style="text-align:left; margin: 5px; padding: 5px; background-color: #eeeeee;"';
                $result .= 'onmouseover="this.style.backgroundColor = \'#cccccc\';" ';
                $result .= 'onmouseout="this.style.backgroundColor = \'#eeeeee\';" ';
                $result .= 'onmousedown="var obj = $(\'mediaobject_'.$r->object_id.'\'); obj.checked = !obj.checked;"> ';
                $result .= '<input onmousedown="this.checked = !this.checked;" id="mediaobject_'.$r->object_id.'" type="checkbox" name="mediaobject" value="'.$r->object_id.'" /> &nbsp; ';
                $result .= '<b>'.$r->title.'</b>, ';
                $result .= $r->date;
                $result .= '</div>'."\n";
            }
            return $result;
        }
        return 'ERROR!';
    }

    public static function ajax_get_seriescreator($params) {
        $out = self::load_file('templates/seriescreator.html');

        $subs['term_options']   = self::make_options('terms', 'term_id', array('term_sh', 'term_lg'), null, true);
        $subs['access_options'] = self::make_options('access', 'access_id', array('status'), null, true);
        $subs['clas_options']   = self::make_options('elan_classification', 'clas_id', array('classification'), null, true);
        $subs['cat_options']    = self::make_options('category', 'cat_id', array('category'), null, true);

        // find lecturers
        $subs['lecturer_options'] = self::make_options('lecturer', 'lecturer_id', array('ac_title', 'firstname', 'name'), null);

        return self::process($out, $subs);
    }

    public static function ajax_get_serieseditor($params) {
        $out = self::load_file('templates/serieseditor.html');
        $id = $params['id'];

        $sql = "SELECT * FROM `series` WHERE `series`.`series_id`='$id';";
        if ($rs = Lernfunk::query($sql)) {
            $r = $rs[0];
            $subs = $r->toArray();
            $subs['term_options'] = self::make_options('terms', 'term_id', array('term_sh', 'term_lg'), $r->term_id, true);
            $subs['access_options'] = self::make_options('access', 'access_id', array('status'), $r->access_id, true);
            $subs['clas_options'] = self::make_options('elan_classification', 'clas_id', array('classification'), $r->clas_id, true);
            $subs['cat_options'] = self::make_options('category', 'cat_id', array('category'), $r->cat_id, true);
            $subs['defaultplaylist_options'] = '';

            //self::make_options('playlist', 'playlist_id', array('pl_description'), $r->default_playlist_id, true);

            // make default playlist options
            $sql2 = 'SELECT playlist_id, pl_description FROM playlist WHERE reciever_id = '.$id.';';
            if ($rs2 = Lernfunk::query($sql2)) {
                foreach ($rs2 as $pl) {
                    if ($pl->playlist_id == $r->default_playlist_id) {
                        $subs['defaultplaylist_options'] .= '<option value="'.$pl->playlist_id.'" '
                            .'selected="true">'.$pl->pl_description.'</option>';
                    } else {
                        $subs['defaultplaylist_options'] .= '<option value="'
                            .$pl->playlist_id.'">'.$pl->pl_description.'</option>';
                    }
                }
            }

            // find lecturers
            $subs['lecturer_options'] = self::find_lecturers_for_series($id);
                       
            return self::process($out, $subs);
        } else
            return 'ERROR: Series not found (ID: '.$id;
    }

    public static function ajax_search($params) {
        $search = $params['search'];
        
        $term_sql = '(select t.term_sh from terms t where t.term_id = s.term_id)';
        if (strlen($search) > 1) {
            $sql = 'SELECT DISTINCT s.series_id, s.name, '.$term_sql.' as term FROM series s '
                  .'natural left outer join lecturer_series ls '
                  .'left outer join lecturer l on ls.lecturer_id = l.lecturer_id '
                  .'where (s.name like "%'.$search.'%") or (l.name like "%'.$search.'%") or (l.firstname like "%'.$search.'%") '
                  .'order by s.name, s.term_id asc;';
        } else {
            // show all if there is nothing to search for or search is too short
            $sql = 'SELECT DISTINCT s.series_id, s.name, '.$term_sql.' as term FROM series s '
                  .'order by s.name, s.term_id asc;';
        }      
        $rs = Lernfunk::query($sql);
        $out = '';
        $tmp = self::load_file('templates/searchresult.html');
        $i = 1;
        foreach ($rs as $r) {
            $short_name = htmlspecialchars((strlen($r->name) < 32) ? $r->name : substr($r->name, 0, 29).'...');
            $subs = array(
                          'name' => htmlspecialchars($r->name), 
                          'short_name' => $short_name, 
                          'term' => $r->term, 
                          'result_id' => $r->series_id);
            $out .= self::process($tmp, $subs);
        }
        return $out;
    }

    public static $format = array();
    public static function get_format($format_id, $key) {
        
        if (isset(self::$format[$format_id][$key]) && !empty(self::$format[$format_id][$key]))
            return self::$format[$format_id][$key];
            
        // get format definitions from db    
        $sql = 'select * from format order by format_id asc;';
        if ($rs = Lernfunk::query($sql)) {
            foreach ($rs as $r)
                self::$format[$r->format_id] = array('mimetype' => $r->mimetype, 'name' => $r->name, 'requirements' => $r->requirements);

            if (isset(self::$format[$format_id][$key]) && !empty(self::$format[$format_id][$key]))
                return self::$format[$format_id][$key];

        }    
            
        return '';
        
    }

    public static function ajax_get_result_info($params) {
        $id = $params['id'];

        // get mediaobjects
        $mediaobjects = array();
        $sql = 'select object_id, title, format_id, cou_id as obj_count ' // , count(cou_id)
              .'from mediaobject where series_id = '.$id.' '
              //.'group by title, date, cou_id '
              .'order by date, object_id asc';
        if ($rs = Lernfunk::query($sql)) {

            foreach ($rs as $r) {
                $t  = '<div style="padding-left: 15px; text-indent: -15px;">'
					 	.'<a href="javascript:mediaobjecteditor.load('.$r->object_id.');">▸ '.$r->title.'</a>';
                // add type
                $t .= '<div style="display: inline; font-weight: bold; font-family: monospace; '
                     .'font-size: smaller; text-transform: uppercase;" ';
                $t .= 'title="Format: '.self::get_format($r->format_id, 'name').'"> (T) ';
                $t .= '</div></div>';
                $mediaobjects[] = $t;
            }
        }

        // get playlists
        $playlists = array();
        $sql = 'SELECT playlist_id, pl_title, pl_description '
		  	.'FROM playlist WHERE playlist.reciever_id = "'.$id.'";';
        if ($rs = Lernfunk::query($sql)) {
            foreach ($rs as $r)
                $playlists[] = '<div style="padding-left: 15px; text-indent: -15px;">'
					 	.'<a href="javascript:playlisteditor.load('.$r->playlist_id.')">▸ '.$r->pl_description.'</a></div>';
        }

        // get feeds
        $feeds = array();
        $sql = 'select f.feed_id, t.feedtype_desc from feeds f natural left outer join feedtype t where f.series_id = "'.$id.'";';
        if ($rs = Lernfunk::query($sql)) {
            foreach ($rs as $r)
                $feeds[] = '<div style="padding-left: 15px; text-indent: -15px;">'
					 	.'<a href="javascript:feededitor.load('.$r->feed_id.')">▸ '.$r->feedtype_desc.'</a></div>';
        }

        // generate output
        $out = '';
        //if (count($mediaobjects) > 0) {
            $out .= '<div style="float: right; font-size: smaller; width: 20px;"><a href="javascript:mediaobjecteditor.add('.$id.');">add</a></div>';
            $out .= '<div style="font-weight: bold;">'.count($mediaobjects).'&nbsp;Medienobjekte</div>';
            foreach ($mediaobjects as $m)
                $out .= $m;
        //}

        //if (count($playlists) > 0) {
            $out .= '<p><div style="float: right; font-size: smaller;"><a href="javascript:playlisteditor.add('.$id.');">add</a></div>';
            $out .= '<div style="font-weight: bold;">'.count($playlists).' Playlists</div>';
            foreach ($playlists as $p)
                $out .= $p;
            $out .= '</p>';
        //}

        $out .= '<p><div style="float: right; font-size: smaller;"><a href="javascript:feededitor.add('.$id.');">add</a></div>';
        $out .= '<div style="font-weight: bold;">'.count($feeds).' Feeds</div>';
        foreach ($feeds as $f)
            $out .= $f;
        $out .= '</p>';
            
        return '<td colspan="2" style="border-left: 5px solid #dddddd; border-right: 15px solid #dddddd;">'.$out.'</td>';
    }

    public static function ajax_get_editor($params) {
        $type = $params['type'];
        $id   = array_key_exists('id', $params) ? $params['id'] : '';

        if (array_key_exists($type, RecordTypes::$types)) {
            $rtype = RecordTypes::$types[$type];

            // get records
            $s['records'] = self::make_options($rtype['TABLE'], $rtype['KEY'], $rtype['DESCRIPTION'], $id, true, 
                'order by '.implode(', ', $rtype['DESCRIPTION']).' asc' );
            $s['fields'] = '';

            // create input fields
            foreach ($rtype as $field => $label) {
                
                if (!($field == 'TABLE' || $field == 'KEY' || $field == 'DESCRIPTION')) {
                    if (is_array($label)) {
                        // Spezielle Felder einfügen
                        $s['fields'] .= '<tr><td>'.$label['val'].':</td>'."\n"
                            .'<td><input '.(isset($label['args']) ? $label['args'] : '').' type="text" id="'.$field.'" name="'.$field.'" value="" '
                            .'style="width:500px;" onkeypress="recordeditor.toggle_savebutton();"></td></tr>';
                    } else if ( isset($rtype['KEY']) && ($rtype['KEY'] == $field) ) {
                        // Primärschlüssel: disabled="true"
                        $s['fields'] .= '<tr><td>'.$label.':</td>'."\n"
                            .'<td><input class="readonly" type="text" readonly="true" id="'.$field.'" name="'.$field.'" value="" '
                            .'style="width:500px;" onkeypress="recordeditor.toggle_savebutton();"></td></tr>';
                    } else {
                        // Einfaches Textfeld einfügen
                        $s['fields'] .= '<tr><td>'.$label.':</td>'."\n"
                            .'<td><input type="text" id="'.$field.'" name="'.$field.'" value="" '
                            .'style="width:500px;" onkeypress="recordeditor.toggle_savebutton();"></td></tr>';
                    }
                }

            }    

            $s['recordtype'] = $type;
            return self::process(self::load_file('templates/recordeditor.html'), $s);
        } else
            return '<p>Unknown record type: '.$type;
    }

    public static function ajax_get_option_list($params) {
        $type = $params['type'];

        if (array_key_exists($type, RecordTypes::$types)) {
            $rtype = RecordTypes::$types[$type];
            return self::make_options($rtype['TABLE'], $rtype['KEY'], $rtype['DESCRIPTION'], null, $params['supress_none_option']);
        } else
            return "<p>Unknown record type: $type";
    }

    public static function ajax_get_record($params) {
        $type = $params['type'];

        if (array_key_exists($type, RecordTypes::$types)) {
            $rtype = RecordTypes::$types[$type];
            $sql = "SELECT * FROM ".$rtype['TABLE']." WHERE ".$rtype['KEY']."='".$params['id']."';";
            if ($rs = Lernfunk::query($sql)) {
                $r = $rs[0]->toArray();
                return json_encode($r);
            } else
                return "No result <br> $sql";
        } else
            return "<p>Unknown record type: $type";
    }

    public static function ajax_save_record($params) {
        $type = $params['type'];
        $record = json_decode($params['record'], true);

        if (array_key_exists($type, RecordTypes::$types)) {
            $type = RecordTypes::$types[$type];
            $sql = "INSERT INTO ".$type['TABLE'].
                   " (".self::make_insert_field_string($record).") ".
                   " VALUES (".self::make_insert_data_string($record).")".
                   " ON DUPLICATE KEY UPDATE ".self::make_update_data_string($record).";";
            if (Lernfunk::query($sql))
                if ($params['type'] == 'mediaobject')
                    return 'OK';
                else
                    return self::ajax_get_option_list(array('type' => $params['type'], 'supress_none_option' => true));
            else
                return 'ERROR: '.mysql_error().'<br>'.$sql;
        } else
            return "<p>Unknown record type: $type";
    }

    public static function ajax_delete_record($params) {
        $type = $params['type'];
        $record = json_decode($params['record'], true);

        if (array_key_exists($type, RecordTypes::$types)) {
            $key   = RecordTypes::$types[$type]['KEY'];
            $table = RecordTypes::$types[$type]['TABLE'];
            $id    = $record[$key];

            $sql = "DELETE FROM `$table` WHERE `$table`.`$key`='$id';";
            if (Lernfunk::query($sql))
                return self::ajax_get_option_list(array('type' => $params['type'], 'supress_none_option' => true));
            else
                return 'ERROR';
        } else
            return "<p>Unknown record type: $type</p>";
    }
    
    // Playlist stuff
    
    public static function  ajax_get_series_mediaobjects() {
        
        $sql = 'select m.object_id, m.title, m.description, m.date, s.name, s.series_id '
              .'from mediaobject m, series s '
              .'where s.series_id = m.series_id '
              .'order by s.name asc;';
        if ($rs = Lernfunk::query($sql)) {
            $result = array();
            foreach ($rs as $r) {
                if (!array_key_exists($r->series_id, $result))
                    $result[$r->series_id] = array(); 
                $result[$r->series_id]['name'] = $r->name;
                if (!array_key_exists('obj', $result[$r->series_id]))
                    $result[$r->series_id]['obj'] = array();
                $result[$r->series_id]['obj'][$r->object_id] = array();
                $result[$r->series_id]['obj'][$r->object_id]['title'] = $r->title;
                $result[$r->series_id]['obj'][$r->object_id]['desc'] = $r->description;
                $result[$r->series_id]['obj'][$r->object_id]['date'] = $r->date;
            }
            return json_encode($result);
        } else
            return '<p>ERROR: '.mysql_error().'</p><p>'.$sql.'</p>';
            
    }

    public static function ajax_get_empty_playlisteditor($params) {

        $out = self::load_file('templates/playlisteditor.html');
        if (array_key_exists('series_id', $params)) {
            $ser_id = $params['series_id'];
            
            $sql = 'SELECT COLUMN_NAME FROM information_schema.COLUMNS WHERE table_name = "playlist" '
              .'AND TABLE_SCHEMA = "'.Lernfunk::get_db_name().'"';
            if ($rs = Lernfunk::query($sql)) {
                foreach ($rs as $r)
                    $subs[$r->COLUMN_NAME] = '';
            }
            $subs['reciever_id'] = $ser_id;


            // fill selects
            $subs['series_options'] = self::make_options('series', 'series_id', array('name'), $ser_id);
            $subs['mediaobject_options'] = self::make_options('mediaobject', 'object_id', array('title'), null);
            $subs['access_options'] = self::make_options('access', 'access_id', array('status'), null);
            $subs['playlist_entries'] = '';
        }

        return self::process($out, $subs);

    }

    public static function ajax_add_playlistentry($params) {
        $record = json_decode($params['record'], true);

        // remove empty
        $empty_fields = array();
        foreach ($record as $key => $val) {
            if (empty($val)) {
                unset($record[$key]);
                $empty_fields[$key] = $val;
            }
        }

        $sql = 'INSERT INTO playlist_entry '
              .'('.self::make_insert_field_string($record).') '
              .'VALUES ('.self::make_insert_data_string($record).');';
        //return $sql;
        if (Lernfunk::query($sql)) {
            $out = self::load_file('templates/playlistentryeditor.html');
            $subs = $record;
            $subs += $empty_fields;
            $subs['access_options'] = self::make_options('access', 'access_id', array('status'), $record['access_id']);
            $sql = 'SELECT title FROM mediaobject where object_id = '.$record['object_id'].';';
            if ($rs = Lernfunk::query($sql))
                $subs['object_title'] = $rs[0]->title;
            return self::process($out, $subs);
        }
        return '<p>ERROR: Could not save data!</p>'
              .'<p><a href="javascript: alert(\'MySQL-ERROR\n\nError message:\n'.str_replace("'", "\'", mysql_error())
              .'\n\nStatement:\n'.str_replace("'", "\'", $sql).'\');">Details...</a></p>';
    }

    public static function ajax_get_playlisteditor($params) {
        $out = self::load_file('templates/playlisteditor.html');
        if (array_key_exists('playlist_id', $params)) {
            $id = $params['playlist_id'];
            
            $sql = 'SELECT * FROM playlist WHERE playlist_id = "'.$id.'";';
            if ($rs = Lernfunk::query($sql)) {

                $pl = $rs[0];
                $subs = $rs[0]->toArray();

                // fill selects
                $subs['series_options'] = self::make_options('series', 'series_id', array('name'), $pl->reciever_id);
                $subs['mediaobject_options'] = self::make_options('mediaobject', 'object_id', array('title'), null);
                $subs['access_options'] = self::make_options('access', 'access_id', array('status'), null);

                // prepare mediaobject
                $sql = 'select object_id, title from mediaobject;'; // where series_id = '.$pl->reciever_id.';';
                if (!($rs = Lernfunk::query($sql)))
                    die('ERROR: Could not connect to database to get mediaobjects!');
                $mediaobjects = array();
                foreach ($rs as $r)
                    $mediaobjects[$r->object_id] = $r->title;

                // fill in entries
                $sql = 'SELECT * FROM playlist_entry WHERE playlist_id = "'.$id.'" order by index_position;';
                $playlist_entries = '';
                $max_index = 0;
                if ($rs = Lernfunk::query($sql)) {
                    $tmp = self::load_file('templates/playlistentryeditor.html');
                    foreach ($rs as $r) {
                        $entry_subs = $r->toArray();
                        $entry_subs['object_title'] = $mediaobjects[$r->object_id];
                        $entry_subs['access_options'] = self::make_options('access', 'access_id', array('status'), $r->access_id);
                        $playlist_entries .= self::process($tmp, $entry_subs);
                        $max_index = $r->index_position;
                    }
                }
                $subs['max_index'] = $max_index + 1;
                $subs['playlist_entries'] = $playlist_entries;
            } else {
                return 'ERROR: Playlist  not found (playlist_id: '.$id.')';
            }
        }

        return self::process($out, $subs);;
    }

    public static function ajax_save_playlist($params) {
        $record = json_decode($params['record'], true);

        if ( array_key_exists('playlist_id', $record) && !empty($record['playlist_id']) ) {
            $pl_id = $record['playlist_id'];
            unset($record['playlist_id']);
            $sql = 'UPDATE playlist SET '.self::make_update_data_string($record).' WHERE playlist_id = "'.$pl_id.'";';
        } else {
            unset($record['playlist_id']);
            $sql = 'INSERT INTO playlist '
                  .'('.self::make_insert_field_string($record).') '
                  .'VALUES ('.self::make_insert_data_string($record).');';
        }
        if (Lernfunk::query($sql))
            return '<p>Playlist successfully saved!</p>';
        else
            return '<p>ERROR: '.mysql_error().'</p><p>'.$sql.'</p>';
    }

    public static function ajax_save_all_playlist($params) {
        $entries = json_decode($params['entries'], true);
        $pl_id = $entries['playlist_id'];
        $entries = $entries['entries'];
        foreach ($entries as $entry) {
            $obj_id = $entry['object_id'];
            unset($entry['object_id']);
            $sql = 'UPDATE playlist_entry SET '.self::make_update_data_string($entry)
                  .' WHERE playlist_id = "'.$pl_id.'" and object_id = "'.$obj_id.'";';
            if (!Lernfunk::query($sql))
                return '<p>ERROR: '.mysql_error().'</p><p>'.$sql.'</p>';
        }
        return '<p>Playlist successfully saved!</p>';
    }
    
    public static function ajax_delete_playlist($params) {
        if ($params['playlist_id']) {
            // delete entries
            $sql = 'DELETE FROM playlist_entry '
                  .'WHERE playlist_id = '.$params['playlist_id'].';';
            if (Lernfunk::query($sql)) {
                $sql = 'DELETE FROM playlist '
                      .'WHERE playlist_id = '.$params['playlist_id'].';';
                if (Lernfunk::query($sql))
                    return 'SUCCESS';
            }
            return 'ERROR: '.mysql_error()."\n".$sql;
        }
    }

    public static function ajax_delete_playlistentry($params) {
        if ($params['playlist_id'] && $params['object_id']) {
            $sql = 'DELETE FROM playlist_entry '
                  .'WHERE playlist_id = '.$params['playlist_id'].' '
                  .'and object_id = '.$params['object_id'].' LIMIT 1';
            if (Lernfunk::query($sql))
                return 'SUCCESS';
            else
                return 'ERROR: '.mysql_error()."\n".$sql;
        }
    }
    
    // Feed stuff

    public static function ajax_get_feededitor($params) {
        $out = self::load_file('templates/feededitor.html');
        if (array_key_exists('feed_id', $params)) {
            $id = $params['feed_id'];
            
            $sql = 'SELECT * FROM feeds WHERE feed_id = "'.$id.'";';
            if ($rs = Lernfunk::query($sql)) {

                $feed = $rs[0];
                $subs = $rs[0]->toArray();

                // fill selects
                $subs['series_options'] = self::make_options('series', 'series_id', array('name'), $feed->series_id);
                $subs['feedtype_options'] = self::make_options('feedtype', 'feedtype_id', array('feedtype_desc'), $feed->feedtype_id);
            } else {
                return 'ERROR: Feed  not found (feed_id: '.$id.')';
            }
        } elseif (array_key_exists('series_id', $params) && !empty($params['series_id'])) {
            $subs['feed_id'] = '';
            $subs['feed_url'] = '';
            $subs['itunes_status'] = '1';
            $subs['series_id'] = $params['series_id'];
            $subs['series_options'] = self::make_options('series', 'series_id', array('name'), $params['series_id']);
            $subs['feedtype_options'] = self::make_options('feedtype', 'feedtype_id', array('feedtype_desc'), null);
        }

        return self::process($out, $subs);;
    }

    public static function ajax_save_feed($params) {

        $sql = '';
        if ( array_key_exists('feed_id', $params) && !empty($params['feed_id']) ) {
            $sep = '';
            $sql = 'UPDATE `feeds` SET ';
            if (array_key_exists('feed_url', $params)) {
                $sql .= '`feed_url` = "'.$params['feed_url'].'" ';
                $sep = ', ';
            }
            if (array_key_exists('series_id', $params)) {
                $sql .= $sep.'`series_id` = "'.$params['series_id'].'" ';
                $sep = ', ';
            }
            if (array_key_exists('itunes_status', $params)) {
                $sql .= $sep.'`itunes_status` = "'.$params['itunes_status'].'" ';
                $sep = ', ';
            }
            if (array_key_exists('feedtype_id', $params))
                $sql .= $sep.'`feedtype_id` = "'.$params['feedtype_id'].'" ';
            $sql .= 'WHERE `feed_id` = '.$params['feed_id'].' LIMIT 1 ;';
        }  else {
            $sql = 'INSERT INTO `feeds` ( `feed_id` , `feed_url` , `series_id` , `feedtype_id`, `itunes_status` ) '
                  .'VALUES ( NULL , "'.$params['feed_url'].'", "'.$params['series_id'].'", "'.$params['feedtype_id']
                  .'", "'.$params['itunes_status'].'");';
        }
        if (Lernfunk::query($sql))
            return '<p>Feed erfolgreich gespeichert!</p>';
        else
            return '<p>ERROR: '.mysql_error().'</p><p>'.$sql.'</p>';
    }

    public static function ajax_delete_feed($params) {
        if ($params['feed_id']) {
            $sql = 'DELETE FROM `feeds` WHERE `feed_id` = '.$params['feed_id'].' LIMIT 1';
            if (Lernfunk::query($sql))
                return '<p style="text-align: center; margin: 35px;">Feed successfully deleted!</p>';
            else
                return '<p style="text-align: center; margin: 35px;">ERROR: '.mysql_error().'</p><p>'.$sql.'</p>';
        }
    }

    // Mediaobject stuff

    public static function ajax_get_empty_mediaobjecteditor($params) {
        $out = self::load_file('templates/mediaobjecteditor.html');
        $ser_id = $params['series_id'];
        
        $subs['related_objects'] = '';

        $sql = 'SELECT COLUMN_NAME FROM information_schema.COLUMNS WHERE table_name = "mediaobject" '
              .'AND TABLE_SCHEMA = "'.Lernfunk::get_db_name().'"';
        if ($rs = Lernfunk::query($sql)) {
            foreach ($rs as $r)
                $subs[$r->COLUMN_NAME] = '';
        }
        $subs['series_id'] = $ser_id;
		  if (isset($_SERVER['PHP_AUTH_USER']))
			  $subs['author'] = $_SERVER['PHP_AUTH_USER'];

        // fill selects
        $subs['series_options'] = self::make_options('series', 'series_id', array('name'), $ser_id);
        $subs['language_options'] = self::make_options('language', 'lang_id', array('language_long'), null);
        $subs['format_options'] = self::make_options('format', 'format_id', array('name'), null);
        $subs['access_options'] = self::make_options('access', 'access_id', array('status'), null);

        // make date and time
        $date = getdate();
        $subs['day_options'] = self::make_range_options(1, 31, $date['mday']);
        $subs['month_options'] = self::make_month_options($date['mon']);
        $subs['year_options'] = self::make_range_options(1995, 2030, $date['year']);

        $subs['hour_options'] = self::make_range_options(0, 23, $date['hours']);
        $subs['minute_options'] = self::make_range_options(0, 59, $date['minutes']);
        $subs['second_options'] = self::make_range_options(0, 59, $date['seconds']);

        $out = self::process($out, $subs);

        return $out;
    }


    public static function ajax_get_mediaobjecteditor($params) {
        $out = self::load_file('templates/mediaobjecteditor.html');
        $obj_id = $params['object_id'];
        
        $sql = 'SELECT * FROM mediaobject WHERE object_id = "'.$obj_id.'";';
        if ($rs = Lernfunk::query($sql)) {

            $obj = $rs[0];
            $subs = $rs[0]->toArray();
				$subs['preview_url'] = htmlspecialchars($subs['preview_url']);

            // list mediaobjects with same cou_id
            $related_objects = '';
            if ($obj->cou_id && !empty($obj->cou_id)) {
                $sql = 'select object_id, title, format_id from mediaobject where cou_id = "'.$obj->cou_id.'";';
                if ($rs = Lernfunk::query($sql)) {
                    foreach ($rs as $r) {
                        $related_objects .= '<option value="'.$r->object_id.'"';
                        if ($r->object_id == $obj_id)
                            $related_objects .= ' selected="true"';
                        $related_objects .= '>'.self::get_format($r->format_id, 'name').' ('.$r->title.')</option>'."\n";
                    }
                }
            } else {
                $related_objects  = '<option value="'.$obj_id.'" selected="true">';
                $related_objects .= $obj->title.' ('.self::get_format($obj->format_id, 'name').')';
                $related_objects .= "</obtion>\n";
            }
            $subs['related_objects'] = $related_objects;

            // fill selects
            $subs['series_options'] = self::make_options('series', 'series_id', array('name'), $obj->series_id);
            $subs['language_options'] = self::make_options('language', 'lang_id', array('language_long'), $obj->language_id);
            $subs['format_options'] = self::make_options('format', 'format_id', array('name'), $obj->format_id);
            $subs['access_options'] = self::make_options('access', 'access_id', array('status'), $obj->access_id);

            // make date and time
            $date = getdate(strtotime($obj->date));
            $subs['day_options'] = self::make_range_options(1, 31, $date['mday']);
            $subs['month_options'] = self::make_month_options($date['mon']);
            $subs['year_options'] = self::make_range_options(1995, 2030, $date['year']);

            $subs['hour_options'] = self::make_range_options(0, 23, $date['hours']);
            $subs['minute_options'] = self::make_range_options(0, 59, $date['minutes']);
            $subs['second_options'] = self::make_range_options(0, 59, $date['seconds']);

            $out = self::process($out, $subs);

            return $out;
        } else
            return 'ERROR: Mediaobject not found (object_id: '.$obj_id.')';
    }

    public static function ajax_save_mediaobject($params) {
        $record = json_decode($params['record'], true);

        $tmp['date'] = $record['year'].'-'.$record['month'].'-'.$record['day'].' '.$record['hour'].':'.$record['minute'].':'.$record['second'];
        foreach ($record as $key => $value)
            if ($key != 'year' && $key != 'month' && $key != 'day' && $key != 'hour' 
                && $key != 'minute' && $key != 'second' && $key != 'old_series_id')
                $tmp[$key] = $value;
        $record = $tmp;

        if ( array_key_exists('object_id', $record) && !empty($record['object_id']) ) {
            $obj_id = $record['object_id'];
            unset($record['object_id']);
            $sql = 'UPDATE mediaobject SET '.self::make_update_data_string($record).' WHERE object_id = "'.$obj_id.'";';
        } else {
            unset($record['object_id']);
            $sql = 'INSERT INTO mediaobject '
                  .'('.self::make_insert_field_string($record).') '
                  .'VALUES ('.self::make_insert_data_string($record).');';
        }
        if (Lernfunk::query($sql))
            return '<p>Mediaoject successfully saved!</p>';
        else
            return '<p>ERROR: '.mysql_error().'</p><p>'.$sql.'</p>';
    }

    public static function ajax_delete_mediaobject($params) {
        if ($params['object_id']) {
            $sql = 'DELETE FROM `mediaobject` WHERE `object_id` = '.$params['object_id'].' LIMIT 1';
            if (Lernfunk::query($sql))
                return '<p style="text-align: center; margin: 35px;">Mediaobject successfully deleted!</p>';
            else
                return '<p style="text-align: center; margin: 35px;">ERROR: '.mysql_error().'</p><p>'.$sql.'</p>';
        }
    }
    
    public static function ajax_save_new_series($params) {
        $record = json_decode($params['record'], true);
        $lecturer = array();
        if (array_key_exists('lecturer', $record)) {
            if ( is_array( $record['lecturer'] ) ) {
                $lecturer = $record['lecturer'];
            } else {
                $lecturer[] = $record['lecturer'];
            }
            unset($record['lecturer']);
        }
        $mediaobjects = array();
        if (array_key_exists('mediaobject', $record)) {
            if ( is_array( $record['mediaobject'] ) ) {
                $mediaobjects = $record['mediaobject'];
            } else {
                $mediaobjects[] = $record['mediaobject'];
            }
            unset($record['mediaobject']);
        }

        $sql = 'INSERT INTO series'.
               ' ('.self::make_insert_field_string($record).') '.
               ' VALUES ('.self::make_insert_data_string($record).')';
        if (Lernfunk::query($sql)) {
            $series_id = mysql_insert_id();
            foreach ($lecturer as $id) {
                $sql = 'insert into lecturer_series (lecturer_id, series_id) values ('.$id.', '.$series_id.');';
                if (!Lernfunk::query($sql))
                    return 'ERROR: '.mysql_error().'<br />'.$sql;
            }
            foreach ($mediaobjects as $id) {
                $sql = 'UPDATE mediaobject SET `series_id` = "'.$series_id.'" WHERE object_id = "'.$id.'";';
                if (!Lernfunk::query($sql))
                    return 'ERROR: '.mysql_error().'<br />'.$sql;
            }
            
            return 'OK';
        } else
            return 'ERROR: '.mysql_error().'<br>'.$sql;
    }

    public static function ajax_save_series($params) {
        $record = json_decode($params['record'], true);
        $id = $params['id'];
        $sql = "UPDATE series SET ".self::make_update_data_string($record)." WHERE series.series_id = '$id';";
        if (Lernfunk::query($sql))
            return 'OK';
        else
            return 'ERROR: '.mysql_error().'<br>'.$sql;
    }

    public static function ajax_get_couobjecteditor($params) {
        $id = $params['id'];
        $out = self::load_file('templates/cou_object_editor.html');
        
        $sql = "SELECT * FROM mediaobject WHERE mediaobject.object_id = '$id';";
        if ($rs = Lernfunk::query($sql)) {
            $subs = $rs[0]->toArray();
            $subs['access_options'] = self::make_options('access', 'access_id', array('status'), $rs[0]->access_id, true);
            return self::process($out, $subs);
        } else
            return "ERROR: Mediaobject not found (ID: $id)";
    }

    public static function ajax_save_cou_object($params) {
        $id = $params['id'];
        $record = json_decode($params['record'], true);
        $couid = $params['couid'];
       
        $sql = "UPDATE mediaobject SET ".self::make_update_data_string($record)." WHERE mediaobject.object_id = '$id';";
        if (Lernfunk::query($sql)) {
            return 'OK';
        } else
            return 'ERROR: '.mysql_error().'<br>'.$sql;

    }

    private static function make_month_options($selected_month) {
        $months = array('', 'Januar', 'Februar', 'M&auml;rz', 'April', 'Mai', 'Juni', 'Juli', 'August', 'September', 'Oktober', 'November', 'Dezember');
        $out = '';
        for ($i=1; $i<=12; $i++) {
            $out .= '<option value="'.$i.'"';
            if ($i == $selected_month)
                $out .= ' selected="true"';
            $out .= '>'.$months[$i].'</option>';
        }
        return $out;
    }

    private static function make_range_options($from, $to, $selected) {
        $out = '';
        for ($i=$from; $i<=$to; $i++) {
            $out .= '<option value="'.$i.'"';
            if ($i == $selected)
                $out .= ' selected="true"';
            $out .= '>'.$i.'</option>';
        }
        return $out;
    }

    private static function make_update_data_string($data) {
        foreach ($data as $key => $value)
            //$out[] = "`$key`='$value'";
            $out[] = "`$key`='".utf8_decode($value)."'";
        return implode(',', $out);
    }

    private static function make_insert_field_string($data) {
        foreach ($data as $key => $value)
            $out[] = "`$key`";
        return implode(',', $out);
    }

    private static function make_insert_data_string($data) {
        foreach ($data as $key => $value)
            //$out[] = "'$value'";
      $out[] = "'".utf8_decode($value)."'";
        return implode(',', $out);
    }

}
