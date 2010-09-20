<?php
/* 
	Copyright (c) 2006 - 2010  Universitaet Osnabrueck, virtUOS 
	Authors: Benjamin Wulff, Lars Kiesow

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

include_once('lernfunk.cfg.php');
include_once('lernfunk.classes.php');

class Lernfunk {

    private static $cfg = NULL;
    private static $db  = NULL;

    /* ----------------------------------------------------------------
                               API functions 
       ---------------------------------------------------------------- */

    public static function get_db_name() {
        self::ensure_init();
        return self::$cfg->db_name;
    }
    
    public static function load_series($series_id) {
        
        if ($s = self::load_record('series', 'series_id', $series_id)) {
            
            $series = new Series( $s->series_id,
                                  $s->name,
                                  $s->description,
                                  $s->description_sh,
                                  $s->term_id,
                                  $s->course_id,
                                  $s->access_id,
                                  $s->portal_url,
                                  $s->clas_id,
                                  $s->preview_url,
                                  $s->lms_course_id,
                                  $s->add_url,
                                  $s->add_url_text,
                                  $s->lrs_series_id,
                                  $s->social_web,
                                  $s->keywords,
                                  $s->cat_id,
                                  $s->default_playlist_id);

            // get playlists
            $pls = self::$db->query("SELECT * FROM `playlist` LEFT JOIN `series` ON (`playlist`.`reciever_id`=`series`.`series_id`) WHERE `playlist`.`reciever_id` = '$series_id';");
            $playlists = array();
            foreach ($pls as $playlist) {
                $entries = array();
                $ents = self::$db->query("SELECT * FROM `playlist_entry` WHERE `playlist_entry`.`playlist_id`='".$playlist->playlist_id."' ORDER BY `playlist_entry`.`index_position` ASC;");
                foreach ($ents as $e)
                $entries[] = new PlaylistEntry($e->playlist_id, $e->object_id, $e->index_position, $e->access_id, $e->start_access, $e->stop_access);

                $newpl = new Playlist($playlist->playlist_id,
                                      $playlist->reciever_id,
                                      $playlist->description );
                $newpl->set_entries($entries);
                $playlists[] = $newpl;
            }
            $series->set_playlists($playlists);

            // get lecturers
            $sql = 'SELECT '.
                      '`lecturer_series`.`lecturer_id` AS `lecturer_id`,'.
                      '`lecturer_series`.`series_id` AS `series_id`,'.
                      '`lecturer_series`.`role` AS `role_id`,'.
                      '`lecturer`.`ac_title` AS `ac_title`,'.
                      '`lecturer`.`firstname` AS `firstname`,'.
                      '`lecturer`.`name` AS `name`,'.
                      '`lecturer`.`dep_id` AS `dep_id`,'.
                      '`lecturer`.`lms_lecturer_id` AS `lms_lecturer_id`,'.
                      '`lecturer`.`email` AS `email`,'.
                      '`role`.`role` AS `role_name` '.
                   'FROM `lecturer_series` '.
                      'LEFT JOIN `lecturer` ON (`lecturer_series`.`lecturer_id`=`lecturer`.`lecturer_id`) '.
                      'LEFT JOIN `role` ON (`lecturer_series`.`role`=`role`.`role_id`)'.
                   "WHERE `lecturer_series`.`series_id`='$series_id';";
            $lec = self::$db->query($sql);
            $lecturers = array();
            foreach ($lec as $lecturer) {
                $lecturers[$lecturer->lecturer_id]['obj'] = new Lecturer($lecturer->lecturer_id,
                                                                         $lecturer->ac_title,
                                                                         $lecturer->firstname,
                                                                         $lecturer->name,
                                                                         $lecturer->email,
                                                                         $lecturer->dep_id );
                 if ($lecturer->role_id)
                    $role = new Role($lecturer->role_id, $lecturer->role_name);
                 else
                    $role = NULL;
                 $lecturers[$lecturer->lecturer_id]['role'] = $role;
            }
            $series->set_lecturers($lecturers);

            // get feeds
            $fds = self::$db->query("SELECT * FROM `feeds` WHERE `feeds`.`series_id`='$series_id';");
            $feeds = array();
            foreach ($fds as $feed) {
                $feeds[] = new Feed($feed->feed_id,
                                    $feed->feed_url,
                                    $feed->series_id,
                                    $feed->feedtype_id);
            }
            $series->set_feeds($feeds);

            return $series;
        } else
            return false;
    }

    /* returns series id for an lrs id, false if no series with $lrs_id was found
     *
     */
    public static function find_series_by_lrs_id($lrs_id) {
        self::ensure_init();
        $rs = self::$db->query("SELECT `series_id` FROM `series` WHERE `series`.`lrs_series_id`='$lrs_id';");
        if (count($rs) > 0) {
            $rs = $rs[0];
            return $rs->series_id;
        } else
            return false;
    }

    /* returns associative array listing the access statuses currently defined in Lernfunk
     *
     */
    public static function list_access_status() {
        self::ensure_init();
        $out = array();
        $rs = self::$db->query("SELECT * FROM `access` WHERE 1;");
        if (count($rs) > 0)
            foreach ($rs as $r)
                $out[$r->access_id] = $r->status;
        return $out;
    }

    /* Return associative array listing the formats currently defined in Lernfunk, kyes are the formats ids.
     * 
     */
    public static function list_formats() {
        self::ensure_init();
        $out = array();
        $rs = self::$db->query("SELECT * FROM `format` WHERE 1;");
        if (count($rs) > 0)
            foreach ($rs as $r)
            $out[$r->format_id] = new Format($r->format_id, $r->mimetype, $r->name, $r->requirements);
        return $out;
    }

    /* Return associative array listing the terms currently defined in Lernfunk, kyes are the terms ids.
     *
     */
    public static function list_terms() {
        self::ensure_init();
        $out = array();
        $rs = self::$db->query("SELECT * FROM `terms` WHERE 1;");
        if (count($rs) > 0)
            foreach ($rs as $r)
            $out[$r->term_id] = new Term($r->term_id, $r->term_sh, $r->term_lg);
        return $out;
    }

    public static function list_languages() {
        self::ensure_init();
        $out = array();
        $rs = self::$db->query("SELECT * FROM `language` WHERE 1;");
        if (count($rs) > 0)
            foreach ($rs as $r)
            $out[$r->lang_id] = new Language($r->lang_id, $r->language_short, $r->language_long);
        return $out;
    }

    public static function load_playlist($playlist_id) {
        if ($d = self::load_record('playlist', 'playlist_id', $playlist_id)) {
            // get playlist entries
            $entries = array();
            $ents = self::$db->query("SELECT * FROM `playlist_entry` WHERE `playlist_entry`.`playlist_id`='$playlist_id' ORDER BY `playlist_entry`.`index_position` ASC;");
            foreach ($ents as $e)
                $entries[] = new PlaylistEntry($e->playlist_id, $e->object_id, $e->index_position, $e->access_id, $e->start_access, $e->stop_access);
            
            $playlist = new Playlist($d->playlist_id, $d->reciever_id, $d->description);
            $playlist->set_entries($entries);
            return $playlist;
        } else
            return false;
    }

    public static function load_playlistentry($playlist_id, $object_id) {
        self::ensure_init();
        $sql = "SELECT * FROM `playlist_entry` WHERE `playlist_entry`.`playlist_id`='$playlist_id' AND `playlist_entry`.`object_id`='$object_id';";
        if ($r = self::$db->query($sql)) {
            $r = $r[0];
            return new PlaylistEntry($r->playlist_id, 
                                     $r->object_id, 
                                     $r->index_position,
                                     $r->access_id,
                                     $r->start_access,
                                     $r->stop_access);
        } else
            return false;
    }

    public static function load_mediaobject($object_id) {
        if ($d = self::load_record('mediaobject', 'object_id', $object_id)) {
            return new Mediaobject($d->object_id,
                                   $d->title,
                                   $d->description,
                                   $d->series_id,
                                   $d->date,
                                   $d->lrs_object_id,
                                   $d->format_id,
                                   $d->url,
                                   $d->memory_size,
                                   $d->cou_id,
                                   $d->author,
                                   $d->thumbnail_url,
                                   $d->duration,
                                   $d->location,
                                   $d->add_url,
                                   $d->add_url_text,
                                   $d->access_id);
        } else
            return false;
    }

    public static function load_academy($academy_id) {
        if ($d = self::load_record('academy', 'academy_id', $academy_id)) {
            return new Academy($d->academy_id,
                               $d->ac_name,
                               $d->ac_contact,
                               $d->ac_contact_person,
                               $d->ac_email);
        } else
            return false;
    }

    public static function load_term($term_id) {
        if ($d = self::load_record('terms', 'term_id', $term_id)) {
            return new Term($d->term_id, $d->term_sh, $d->term_lg);
        } else
            return false;
    }

    public static function load_category($category_id) {
        if ($d = self::load_record('category', 'cat_id', $category_id)) {
            return new Category($d->cat_id, $d->category);
        } else
            return false;
    }

    public static function load_classification($clas_id) {
        if ($d = self::load_record('elan_classification', 'clas_id', $clas_id)) {
            return new Classification($d->clas_id, $d->classification, $d->description);
        } else
            return false;
    }

    public static function load_role($role_id) {
        if ($d = self::load_record('role', 'role_id', $role_id)) {
            return new Role($d->role_id, $d->role);
        } else
            return false;
    }

    public static function load_format($format_id) {
        if ($d = self::load_record('format', 'format_id', $format_id)) {
            return new Format($d->format_id, $d->mimetype, $d->name, $d->requirements);
        } else
            return false;
    }

    public static function load_language($lang_id) {
        if ($d = self::load_record('language', 'lang_id', $lang_id)) {
            return new Language($d->lang_id, $d->language_long, $d->language_short);
        } else
            return false;
    }

    public static function load_mediascene($scene_id) {
        if ($d = self::load_record('mediascene', 'scene_id', $scene_id)) {
            return new Mediascene($d->scene_id,
                                  $d->object_id,
                                  $d->title,
                                  $d->description,
                                  $d->msu_id);
        } else
            return false;
    }

    public static function load_access($access_id) {
        if ($d = self::load_record('access', 'access_id', $access_id)) {
            return new Access($d->access_id, $d->status);
        } else
            return false;
    }

    public static function load_feed($feed_id) {
        if ($d = self::load_record('feeds', 'feed_id', $feed_id)) {
            return new Feed($d->feed_id, $d->feed_url, $d->series_id, $d->feedtype_id);
        } else
            return false;
    }

    public static function load_feedtype($feedtype_id) {
        if ($d = self::load_record('feedtype', 'feedtype_id', $feedtype_id)) {
            return new FeedType($d->feedtype_id, $d->feedtype_desc);
        } else
            return false;
    }

    public static function load_audience($audience_id) {
        if ($l = self::load_record('audience', 'audience_id', $audience_id)) {
            return new Audience($l->audience_id, $l->name, $l->description);
        } else
            return false;
    }

    public static function load_lecturer($lecturer_id) {
        if ($l = self::load_record('lecturer', 'lecturer_id', $lecturer_id)) {
            return new Lecturer($l->lecturer_id,
                                $l->ac_title,
                                $l->firstname,
                                $l->name,
                                $l->email,
                                $l->dep_id);
        } else
            return false;
    }

    public static function load_department($dep_id) {
        if ($d = self::load_record('department', 'dep_id', $dep_id)) {
            return new Department($d->dep_id,
                                  $d->dep_name,
                                  $d->dep_description,
                                  $d->dep_number,
                                  $d->academy_id);
        } else
            return false;
    }

    public static function load_user($user_id) {
        if ($d = self::load_record('user', 'user_id', $user_id)) {
            return new User($d->user_id,
                            $d->user_name,
                            $d->password,
                            $d->lecturer_id,
                            $d->is_admin,
                            $d->is_pressoffice );
        } else
            return false;
    }

    public static function load_user_by_name($username) {
        if ($d = self::load_record('user', 'user_name', $username)) {
            return new User($d->id,
                            $d->user_name,
                            $d->password,
                            $d->lecturer_id,
                            $d->is_admin,
                            $d->is_pressoffice );
        } else
            return false;
    }

    public static function load_lms($lms_id) {
        if ($d = self::load_record('lms', 'lms_id', $lms_id)) {
            return new LMS($d->lms_id,
                           $d->name,
                           $d->contact_person,
                           $d->email,
                           $d->lms_identifier,
                           $d->lms_url);
        } else
            return false;
    }

    public static function load_lms_connector($connector_id) {
        if ($d = self::load_record('lms_connector', 'connector_id', $connector_id)) {
            return new LMS_Connector($d->connector_id,
                                     $d->series_id,
                                     $d->lms_id,
                                     $d->lms_course_id);
        } else
            return false;
    }

    /* Save an object to the Lernfunk database.
     * Returns true if object has been saved, false otherwise.
     */
    public static function save( $obj ) {

        self::ensure_init();

        if ($obj instanceof Series) {

            $ser_id = $obj->get_id();

            // save lms
            $lmss = $obj->get_lms();
            if (count($lmss) > 0) {
                self::$db->query("DELETE FROM `lms_connect` WHERE `lms_connect`.`series_id`='$ser_id';");
                foreach ($obj->get_lms_connectors() as $connector) {
                    $data = $connector->toArray();
                    $conn_id = $connector->get_id();
                    $keys = Lf_Database::make_lim_list(array_keys($data), '`');
                    $vals = Lf_Database::make_lim_list(array_values($data), "'");
                    if (!self::$db->query("INSERT INTO `lms_connector` ($keys) VALUES ($vals);"))
                        return false;
                }
            }

            // save playlists
            $playlists = $obj->get_playlists();
            if (count($playlists) > 0) {
                self::$db->query("DELETE FROM `playlist` WHERE `playlist`.`series_id`='$ser_id';");
                foreach ($playlists as $playlist) {
                    $playlist_id = $playlist->get_id();
                    $desc = $playlist->get_description();
                    $sql = 'INSERT INTO `playlist` '.
                             '(`playlist_id`,`reciever_id`,`description`) VALUES '.
                             "('$playlist_id','$ser_id','$desc');";
                    if (!self::$db->query($sql))
                        return false;
                }
            }

            // save feeds
            $feeds = $obj->get_feeds();
            if (count($feeds) > 0) {
                self::$db->query("DELETE FROM `feeds` WHERE `feeds`.`series_id`='$ser_id';");
                foreach ($obj->get_feeds() as $feed) {
                    $feed = $feed->toArray();
                    $keys = Lf_Database::make_lim_list(array_keys($feed),'`');
                    $vals = Lf_Database::make_lim_list(array_values($feed), "'");
                    $sql = 'INSERT INTO `feeds` '.
                             '($keys) VALUES '.
                             '($vals);';
                    if (!self::$db->query($sql))
                        return false;
                }
            }

            // save lecturers
            $lecturers = $obj->get_lecturers();
            if (count($lecturers) > 0) {
                self::$db->query("DELETE FROM `lecturer_series` WHERE `lecturer_series`.`series_id`='$ser_id';");
                foreach ($obj->get_lecturers() as $lec) {

                    $lecturer = $lec['obj'];
                    $role = $lec['role'];

                    if ($role instanceof Role)
                        $role_id = $role->get_id();
                    else
                        $role_id = '';

                    $lec_id = $lecturer->get_id();

                    $sql = 'INSERT INTO `lecturer_series` '.
                             '(`lecturer_id`,`series_id`,`role`) VALUES '.
                             "('$lec_id','$ser_id','$role_id');";
                    if (!self::$db->query($sql))
                        return false;
                }
            }

            if (!self::save_record($obj))
                return false;
            else
                return true;

        } elseif ($obj instanceof Playlist ) {
            $pl_id = $obj->get_id();
            $rc_id = $obj->get_reciever();
            $desc  = $obj->get_description();
            $sql = "INSERT INTO `playlist` ".
                   "(`playlist_id`,`reciever_id`,`description`) ".
                   "VALUES (`$pl_id`,'$rc_id','$desc') ".
                   "WHERE `playlist_id`='$pl_id' AND `reciever_id`='$rc_id' ".
                   'ON DUPLICATE KEY UPDATE;';

            if (!self::$db->query($sql))
                return false;
            else
                return true;

        } elseif ($obj instanceof PlaylistEntry) {
            $data = $obj->toArray();
            $pl_id = $data['playlist'];
            $ob_id = $data['object_id'];
            $sql = "INSERT INTO `playlist_entry` ".
                    '('.Lf_Database::make_lim_list(array_keys($data), '`').') '.
                      'VALUES ('.Lf_Database::make_lim_list(array_values($data), "'").') '.
                    'ON DUPLICATE KEY UPDATE '.
                      "`index_position`='".$data['index_position']."',".
                      "`access_id`='".$data['access_id']."',".
                      "`start_access`='".$data['stop_access']."',".
                      "`stop_access`='".$data['stop_access']."';";
            if (!self::$db->query($sql))
                return false;
            else
                return true;

        } elseif ($obj instanceof DatabaseObject) {
            return self::save_record($obj);

        } else
            return false;
    }

    /* ----------------------------------------------------------------
                            Internal functions
       ---------------------------------------------------------------- */

    // Called by every API function to ensure that config is loaded and database connection exists
    //
    private static function ensure_init() {
	if ( self::$cfg === NULL ) 			// Load config if not done yet
	    self::$cfg = lf_Config::get_Config();
	if ( self::$db === NULL ) 
	    self::$db = new lf_Database(self::$cfg);    // connect to database if not done yet
    }

    public static function query($sql) {
        self::ensure_init();
        return self::$db->query($sql);
    }

    private static function load_record($table, $keyname, $id) {
        self::ensure_init();
        $sql = "SELECT * FROM `$table` WHERE `$table`.`$keyname`='$id';";
        if (($l = self::$db->query($sql)) && (count($l) > 0)) {
            return $l[0];
        } else
            return false;
    }

    private static function save_record($obj) {
        $data = $obj->toArray();
        $kn = $obj->db_keyname();
       
        if (!$data[$kn]) {

            // INSERT values
            $sql = "INSERT INTO `".$obj->db_tablename()."` ".
                     '('.Lf_Database::make_lim_list(array_keys($data), '`').') '.
                     'VALUES ('.Lf_Database::make_lim_list(array_values($data), "'").');';
            if ( !self::$db->query($sql) )
                return false;
            $obj->set_id(mysql_insert_id());
            return true;
        } else {
            // UPDATE values
            foreach ($data as $key=>$val)
                if ($key != $kn)
                    $tmp[] = "`$key`='$val'";
            $sql = "UPDATE `".$obj->db_tablename()."` SET ".
                   implode($tmp, ',').
                   "WHERE `$kn`='".$data[$kn]."';";
            if ( !self::$db->query($sql) )
                return false;
            else
                return true;
        }
    }
}

class lf_Database {

    private $conn;
    private $config;
    
    function __construct($conf) {
        $this->config = $conf;
        $this->conn = mysql_connect( $conf->db_server, $conf->db_user, $conf->db_passwd ) 
            or die('lf_Database.__construct(): Cannot connect database server '.$conf->db_server.'<br>'.mysql_error());
        mysql_select_db($conf->db_name, $this->conn)
            or die('lf_Database.__construct(): Cannot select database '.$conf->db_name.'<br>'.mysql_error());
    }
    
    function query($sql) {
	if ($this->config->debug == 'yes')
            print("<p><b>lf_Database->query(): performing query:</b><br>$sql</p>");
	//$sql = utf8_decode($sql);
	if ($rs = mysql_query($sql)) {
	    if ($rs === true)
		return true;
	    else {
		$out = array();
		while ($r = mysql_fetch_assoc($rs))
		    $out[] = new lf_Record($r);
		return $out;
	    }
	} elseif ($this->config->debug == 'yes')
            die(mysql_error().'<br>'.$sql);
        else
            return false;
    }

    function save($data, $table, $key_name) {
        if (!$data[$key_name]) {                // new record? -> INSERT
            $sql = "INSERT INTO `$table` ".
                     '('. make_lim_list(array_keys($data),'`') .')'.
                     ' VALUES '.
                    '('.make_lim_list(array_values($data,"'")).');';
        } else {                                // existing record -> UPDATE
            // create string list of field/value pairs
            foreach ($data as $key => $val)
                $tmp[] = "`$key`='$val'";
            $values = implode( ',', $tmp);
            $sql = "UPDATE `$table` SET $values WHERE `$table`.`$key_name` = '".$data[$keyname]."';";
            $id = $data[$key_name];
        }
        if (mysql_query($sql)) {
            if (!$id)
                return mysql_insert_id();
            return true;
        } else
            die(mysql_error().": ".$sql);
    }

    static function make_lim_list($data, $lim) {
        foreach ($data as $d)
            $tmp[] = $lim.$d.$lim;
        return implode( ',', $tmp);
    }
} 

class lf_Record {

    private $fieldnames = array();
    
    function __construct($a) {
	foreach ($a as $key => $val) {
	    //$val = utf8_encode($val);
	    //eval( '$this->'.$key." = '$val';" );
		$this->$key = utf8_encode($val);
	    $this->fieldnames[] = $key;
	}
    }
    
    public function toArray() {
    
	foreach ($this->fieldnames as $field)
	    //$out[$field] = eval('return $this->'.$field.';');
		$out[$field] = $this->$field;
	return $out;
    }
}
