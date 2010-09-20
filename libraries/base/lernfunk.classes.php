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


/* Base class for nearly all lernfunk classes
 */
class DatabaseObject {

    protected $id;

    public function set_id($id) {
        $this->id = $id;
    }

    public function get_id() {
        return $this->id;
    }

    public function db_tablename() {
        return $this->TABLE;
    }

    public function db_keyname() {
        return $this->KEYNAME;
    }

    public function toArray() {
        return array( 'id' => $this->id );
    }
}

class Series extends DatabaseObject {

    protected $TABLE = 'series';
    protected $KEYNAME = 'series_id';

    private $name;
    private $description;
    private $description_short;
    private $term;
    private $course;
    private $access;
    private $portal_url;
    private $classification;
    private $preview_url;
    private $lms_course;
    private $add_url;
    private $add_url_text;
    private $lrs_series;
    private $social_web;
    private $keywords;
    private $category;
    private $default_playlist;

    // collections
    private $playlists;
    private $lecturers;
    private $feeds;
    private $lms_connectors;

    function __construct($id, $name, $description, $description_short, $term, $course, $access, $portal_url, $classification, $preview_url, $lms_course, $add_url, $add_url_text, $lrs_series, $social_web, $keywords, $category, $default_playlist) {
        $this->set_id($id);
        $this->name = $name;
        $this->description = $description;
        $this->description_short = $description_short;
        $this->term = $term;
        $this->course = $course;
        $this->access = $access;
        $this->portal_url = $portal_url;
        $this->classification = $classification;
        $this->preview_url = $preview_url;
        $this->lms_course = $lms_course;
        $this->add_url = $add_url;
        $this->add_url_text = $add_url_text;
        $this->lrs_series = $lrs_series;
        $this->social_web = $social_web;
        $this->keywords = $keywords;
        $this->category = $category;
        $this->default_playlist = $default_playlist;
        $this->lecturers = array();
        $this->playlists = array();
        $this->feeds = array();
        $this->lms_connectors = array();
    }

    public function get_default_playlist() {
        if ($this->default_playlist instanceof Playlist)
            return $this->default_playlist;
        else {
            $d = Lernfunk::load_playlist($this->default_playlist);
            if ($d instanceof Playlist) {
                $this->default_playlist = $d;
                return $d;
            } else
                return '';
        }
    }

    public function set_default_playlist($pl) {
        $this->default_playlist = $pl;
    }

    public function get_lms_connectors() {
        return $this->lms_connectors;
    }

    public function set_lms_connectors($lms) {
        $this->lms_connectors = $lms;
    }

    public function add_lms_connector($lms) {
        $this->lms_connectors[] = $lms;
        $lms->set_series($this);
    }

    public function remove_lms_connector($l) {
        $new = array();
        foreach ($lmss as $lms)
            if ($lms != $l)
                $new[] = $lms;
        $lms->set_series(NULL);
        $this->lms_connectors = $new;
    }

    public function get_playlists() {
        return $this->playlists;
    }

    public function set_playlists($pls) {
        $this->playlists = $pls;
    }

    public function add_playlist($pl) {
        $this->playlists[] = $pl;
    }

    public function remove_playlist($pl) {
        for ($i=0; $i < count($this->playlists); $i++) 
            if ($this->playlists[$i] != $pl) 
                $new[] = $this->playlists[$i];
        $this->playlists = $new;
    }
    
    public function get_lecturers() {
        return $this->lecturers;
    }

    public function set_lecturers($lecturers) {
        $this->lecturers = $lecturers;
    }

    public function add_lecturer($l, $role) {
        $this->lecturers[$l->get_id()] = array( 'obj' => $l, 'role' => $role);
    }

    public function get_lecturer_role($lecturer) {
        if (array_key_exists($lecturer->get_id(), $this->lecturers)) {
            return $this->lecturers[$lecturer->get_id()]['role'];
        } else
            return false;
    }

    public function set_lecturer_role($lecturer, $role) {
        if (array_key_exists($lecturer->get_id(), $this->lecturers)) {
            $this->lecturers[$lecturer->get_id()]['role'] = $role;
            return true;
        } else
            return false;
    }

    public function remove_lecturer($l) {
        $id = $l->get_id();
        foreach ($this->lecturers as $lecturer)
            if ($id != $lecturer['obj']->get_id())
                $new[$lecturer['obj']->get_id()] = array( 'obj' => $lecturer['obj'], 'role' => $lecturer['role']);
        $this->lecturers = $new;
    }
    
    public function get_feeds() {
        return $this->feeds;
    }

    public function set_feeds($feeds) {
        $this->feeds = $feeds;
    }

    public function add_feed($feed) {
        $this->feeds[] = $feed;
        $feed->set_series($this);
    }

    public function remove_feed($feed) {
        for ($i=0; $i < count($this->feeds); $i++) 
            if ($this->feed[$i] != $feed) 
                $new[] = $this->feeds[$i];
        $this->feeds = $new;
    }

    public function get_name() {
        return $this->name;
    }

    public function get_description() {
        return $this->description;
    }

    public function get_description_short() {
        return $this->description_short;
    }

    public function get_term() {
        if ($this->term instanceof Term)
            return $this->term;
        else {
            $d = Lernfunk::load_term($this->term);
            if ($d instanceof Term) {
                $this->term = $d;
                return $d;
            } else
                return '';
        }
    }

    public function get_course() {
        return $this->course;
    }

    public function get_access() {
        if ($this->access instanceof Access)
            return $this->access;
        else {
            $d = Lernfunk::load_access($this->access);
            if ($d instanceof Access) {
                $this->access = $d;
                return $d;
            } else
                return '';
        }
    }

    public function get_portal_url() {
        return $this->portal_url;
    }

    public function get_classification() {
        if ($this->classification instanceof Classification)
            return $this->classification;
        else {
            $d = Lernfunk::load_classification($this->classification);
            if ($d instanceof Classification) {
                $this->classification = $d;
                return $d;
            } else
                return '';
        }
    }

    public function get_preview_url() {
        return $this->preview_url;
    }

    public function get_lms_course() {
        return $this->lms_course;
    }

    public function get_add_url() {
        return $this->add_url;
    }

    public function get_add_url_text() {
        return $this->add_url_text;
    }

    public function get_lrs_series() {
        return $this->lrs_series;
    }

    public function get_social_web() {
        return $this->social_web;
    }

    public function get_keywords() {
        return $this->keywords;
    }

    public function get_category() {
        if ($this->category instanceof Category)
            return $this->category;
        else {
            $d = Lernfunk::load_category($this->category);
            if ($d instanceof Category) {
                $this->category = $d;
                return $d;
            } else
                return '';
        }
    }

    public function set_name($v) {
        $this->name = $v;
    }

    public function set_description($v) {
        $this->description = $v;
    }

    public function set_description_short($v) {
        $this->description_short = $v;
    }

    public function set_term($v) {
        $this->term = $v;
    }

    public function set_course($v) {
        $this->course = $v;
    }

    public function set_access($v) {
        $this->access = $v;
    }

    public function set_portal_url($v) {
        $this->portal_url = $v;
    }

    public function set_classification($v) {
        $this->classification = $v;
    }

    public function set_preview_url($v) {
        $this->preview_url = $v;
    }

    public function set_lms_course($v) {
        $this->lms_course = $v;
    }

    public function set_add_url($v) {
        $this->add_url = $v;
    }

    public function set_add_url_text($v) {
        $this->add_url_text = $v;
    }

    public function set_lrs_series($v) {
        $this->lrs_series = $v;
    }

    public function set_social_web($v) {
        $this->social_web = $v;
    }

    public function set_keywords($v) {
        $this->keywords = $v;
    }

    public function set_category($v) {
        $this->category = $v;
    }

    public function toArray() {
        if ($this->term instanceof Term)
            $term_id = $this->term->get_id();
        else
            $term_id = $this->term;

        if ($this->access instanceof Access)
            $access_id = $this->access->get_id();
        else
            $access_id = $this->access;

        if ($this->classification instanceof Classification)
            $clas_id = $this->classification->get_id();
        else
            $clas_id = $this->classification;

        if ($this->category instanceof Category)
            $cat_id = $this->category->get_id();
        else
            $cat_id = $this->category;

        if ($this->default_playlist instanceof Playlist)
            $default_playlist_id = $this->default_playlist->get_id();
        else
            $default_playlist_id = $this->default_playlist;

        return array( 'series_id' => $this->id,
                      'name' => $this->name,
                      'description' => $this->description,
                      'description_sh' => $this->description_short,
                      'term_id' => $term_id,
                      'course_id' => $this->course,
                      'access_id' => $access_id,
                      'portal_url' => $this->portal_url,
                      'clas_id' => $clas_id,
                      'preview_url' => $this->preview_url,
                      'lms_course_id' => $this->lms_course,
                      'add_url' => $this->add_url,
                      'add_url_text' => $this->add_url_text,
                      'lrs_series_id' => $this->lrs_series,
                      'social_web' => $this->social_web,
                      'keywords' => $this->keywords,
                      'cat_id' => $cat_id,
                      'default_playlist_id' => $default_playlist_id);
    }
}

// DONE
class Playlist extends DatabaseObject {

    protected $TABLE = 'playlist';
    protected $KEYNAME = 'playlist_id';

    private $reciever_id;
    private $description;

    private $entries;

    function __construct($id, $reciever_id, $description) {
        $this->set_id($id);
        $this->reciever_id = $reciever_id;
        $this->description = $description;
    }

    public function get_entry($num) {
        if ($num < count($this->entries))
            return $this->entries[$num];
        else
            return false;
    }

    public function set_entry($ent, $pos) {
        if ($pos < count($this->entries)) {
            $this->entries[$pos] = $ent;
            $ent->set_index_position = $pos;
        }
    }

    public function get_entries() {
        return $this->entries;
    }

    public function set_entries($ents) {
        $this->entries = $ents;
    }

    public function swap($pos1, $pos2) {
        if ( ($pos1 < count($this->entries)) && ($pos2 < count($this->entries)) ) {
            $tmp = $this->entries[$pos1];
            $this->entries[$pos1] = $this->entries[$pos2];
            $this->entries[$pos2] = $tmp;
            $this->entries[$pos1]->set_index_position($pos1);
            $this->entries[$pos2]->set_index_position($pos2);
        }
    }

    public function move_up($pos) {
        if ($pos < count($this->entries)-1) 
            $this->swap($pos, $pos++);
    }

    public function move_down($pos) {
        if ($pos > 0) 
            $this->swap($pos, $pos--);
    }

    public function append($ent) {
        $this->entries[] = $ent;
        $last = count($this->entries)-1;
        $this->entries[$last]->set_index_position($last);
    }

    public function insert($ent, $pos) {
        for ($i=0; $i < count($this->entries); $i++) {
            if ($i == $pos) {
                $new[] = $ent;
                $new[$pos]->set_index_position($pos);
            }
            $new[] = $this->entries[$i];
            if ($i >= $pos)
                $new[$i+1]->set_index_position($i+1);
        }
        $this->entries = $new;
    }

    public function remove($pos) {
        for ($i=0; $i < count($this->entries); $i++) {
            if ($pos != $i) {
                $new[] = $this->entries[$i];
                $new[$pos]->set_index_position($pos);
            }
            if ($i >= $pos)
                $new[$i]->set_index_position($i-1);
        }
        $this->entries = $new;
    }

    public function get_reciever() {
        return $this->reciever_id;
    }

    public function get_description() {
        return $this->description;
    }

    public function set_reciever($r) {
        $this->reciever_id = $r;
    }

    public function set_description($d) {
        $this->description = $d;
    }
}

// DONE, TESTED
class PlaylistEntry {

    private $playlist_id;
    private $object_id;
    private $index_pos;
    private $access_id;
    private $start;
    private $stop;

    function __construct($playlist_id, $object_id, $index_pos, $access, $start, $stop) {
        $this->playlist_id = $playlist_id;
        $this->object_id = $object_id;
        $this->index_pos = $index_pos;
        $this->access_id = $access;
        $this->start = $start;
        $this->stop = $stop;
    }

    public function get_playlist_id() {
        return $this->playlist_id;
    }

    public function get_object_id() {
        return $this->object_id;
    }

    public function get_index_position() {
        return $this->index_pos;
    }

    public function get_access_id() {
        return $this->access_id;
    }

    public function get_start() {
        return $start;
    }

    public function get_stop() {
        return $this->stop;
    }

    public function set_playlist_id($v) {
        $this->playlist_id = $v;
    }

    public function set_object_id($v) {
        $this->object_id = $v;
    }

    public function set_index_position($v) {
        $this->index_pos = $v;
    }

    public function set_access_id($v) {
        $this->access_id = $v;
    }

    public function set_start($v) {
        $this->start;
    }

    public function set_stop($v) {
        $this->stop;
    }

    public function toArray() {
        return array( 'playlist_id' => $this->playlist_id,
                      'object_id' => $this->object_id,
                      'index_position' => $this->index_pos,
                      'access_id' => $this->access_id,
                      'start_access' => $this->start,
                      'stop_access' => $this->stop );
    }
}

// DONE, TESTED
class Mediaobject extends DatabaseObject {

    protected $TABLE = 'mediaobject';
    protected $KEYNAME = 'object_id';

    private $title;
    private $description;
    private $series;
    private $date;
    private $lrs_object_id;
    private $format_id;
    private $url;
    private $memorysize;
    private $cou_id;
    private $author;
    private $thumbnail_url;
    private $preview_url;
    private $image_url;
    private $duration;
    private $location;
    private $add_url;
    private $add_url_text;
    private $access;

    function __construct($id, $title, $description, $series, $date, $lrs_object_id, $format_id, $url, $memorysize, $cou_id, $author, $thumbnail_url, $image_url, $preview_url, $duration, $location, $add_url, $add_url_text, $access_id) {
        
        $this->set_id($id);
        $this->title = $title;
        $this->description = $description;
        $this->series = $series;
        $this->date = $date;
        $this->lrs_object_id = $lrs_object_id;
        $this->format_id = $format_id;
        $this->url = $url;
        $this->memorysize = $memorysize;
        $this->cou_id = $cou_id;
        $this->author = $author;
        $this->thumbnail_url = $thumbnail_url;
        $this->image_url = $image_url;
        $this->preview_url = $preview_url;
        $this->duration = $duration;
        $this->location = $location;
        $this->add_url = $add_url;
        $this->add_url_text = $add_url_text;
        $this->access = $access_id;
    }

    public function get_title() {
        return $this->title;
    }

    public function get_description() {
        return $this->description;
    }

    public function get_series() {
        if ($this->series instanceof Series)
            return $this->series;
        else {
            $d = Lernfunk::load_Series($this->series);
            if ($d instanceof Series) {
                $this->series = $d;
                return $d;
            } else
                return '';
        }
    }

    public function get_date() {
        return $this->date;
    }

    public function get_lrs_object_id() {
        return $this->lrs_object_id;
    }

    public function get_format() {
        if ($this->format instanceof Format)
            return $this->format;
        else {
            $d = Lernfunk::load_format($this->format);
            if ($d instanceof Format) {
                $this->format = $d;
                return $d;
            } else
                return '';
        }
    }

    public function get_url() {
        return $this->url;
    }

    public function get_memorysize() {
        return $this->memorysize;
    }

    public function get_cou_id() {
        return $this->cou_id;
    }

    public function get_author() {
        return $this->author;
    }

    public function get_thumbnail_url() {
        return $this->thumbnail_url;
    }

    public function get_image_url() {
        return $this->image_url;
    }

    public function get_preview_url() {
        return $this->preview_url;
    }

    public function get_duration() {
        return $this->duration;
    }

    public function get_location() {
        return $this->location;
    }

    public function get_add_url() {
        return $this->add_url;
    }

    public function get_add_url_text() {
        return $this->add_url_text;
    }

    public function get_access() {
       if ($this->access instanceof Access)
            return $this->access;
        else {
            $d = Lernfunk::load_access($this->access);
            if ($d instanceof Access) {
                $this->access = $d;
                return $d;
            } else
                return '';
        }
    }

    public function set_title($title) {
        $this->title = $title;
    }

    public function set_description($v) {
        $this->description = $v;
    }

    public function set_series($v) {
        $this->series = $v;
    }

    public function set_date($v) {
        $this->date = $v;
    }

    public function set_lrs_object_id($v) {
        $this->lrs_object_id = $v;
    }

    public function set_format($v) {
        $this->format = $v;
    }

    public function set_url($v) {
        $this->url = $v;
    }

    public function set_memorysize($v) {
        $this->memorysize = $v;
    }

    public function set_cou_id($v) {
        $this->cou_id = $v;
    }

    public function set_author($v) {
        $this->author = $v;
    }

    public function set_thumbnail_url($v) {
        $this->thumbnail_url = $v;
    }

    public function set_image_url($v) {
        $this->image_url = $v;
    }

    public function set_preview_url($v) {
        $this->preview_url = $v;
    }

    public function set_duration($v) {
        $this->duration = $v;
    }

    public function set_location($v) {
        $this->location = $v;
    }

    public function set_add_url($v) {
        $this->add_url = $v;
    }

    public function set_add_url_text($v) {
        $this->add_url_text = $v;
    }

    public function set_access($v) {
        $this->access = $v;
    }

    public function toArray() {
        if ($this->series instanceof Series)
            $series_id = $this->series->get_id();
        else
            $series_id = $this->series;

        if ($this->format instanceof Format)
            $format_id = $this->format->get_id();
        else
            $format_id = $this->format;

        if ($this->access instanceof Access)
            $access_id = $this->access->get_id();
        else
            $access_id = $this->access;

        return array( 'object_id' => $this->id,
                      'title' => $this->title,
                      'description' => $this->description,
                      'series_id' => $series_id,
                      'date' => $this->date,
                      'lrs_object_id' => $this->lrs_object_id,
                      'format_id' => $format_id,
                      'url' => $this->url,
                      'memory_size' => $this->memorysize,
                      'cou_id' => $this->cou_id,
                      'author' => $this->author,
                      'thumbnail_url' => $this->thumbnail_url,
                      'image_url' => $this->image_url,
                      'preview_url' => $this->preview_url,
                      'duration' => $this->duration,
                      'location' => $this->location,
                      'add_url' => $this->add_url,
                      'add_url_text' => $this->add_url_text,
                      'access_id' => $access_id );
    }
}

// DONE, TESTED
class Mediascene extends DatabaseObject {

    protected $TABLE = 'mediascene';
    protected $KEYNAME = 'scene_id';

    private $object;
    private $title;
    private $description;
    private $msu_id;

    function __construct($id, $object, $title, $description, $msu_id) {
        $this->set_id($id);
        $this->object = $object;
        $this->title = $title;
        $this->description = $description;
        $this->msu_id = $msu_id;
    }

    public function get_title() {
        return $this->title;
    }

    public function set_title($title) {
        $this->title = $title;
    }

    public function get_object() {
        if ($this->object instanceof Mediaobject)
            return $this->object;
        else {
            $d = Lernfunk::load_mediaobject($this->object);
            if ($d instanceof Mediaobject) {
                $this->object = $d;
                return $d;
            } else
                return '';
        }
    }

    public function set_object($o) {
        $this->object = $o;
    }

    public function get_description() {
        return $this->description;
    }

    public function set_description($d) {
        $this->description = $d;
    }

    public function get_msu_id() {
        return $this->msu_id;
    }

    public function set_msu_id($id) {
        $this->msu_id = $id;
    }

    public function toArray() {
        return array( 'scene_id' => $this->id,
                      'object_id' => $this->object,
                      'title' => $this->title,
                      'description' => $this->description,
                      'msu_id' => $this->msu_id );
    }
}

// DONE, TESTED
class Lecturer extends DatabaseObject {

    protected $TABLE = 'lecturer';
    protected $KEYNAME = 'lecturer_id';

    private $title;
    private $firstname;
    private $name;
    private $email;
    private $department;

    function __construct( $id, $title, $firstname, $name, $email, $department, $role ) {
        $this->set_id($id);
        $this->firstname = $firstname;
        $this->name = $name;
        $this->title = $title;
        $this->email = $email;
        $this->department = $department;
        $this->role = $role;
    }

    function get_title() {
        return $this->title;
    }

    public function set_title($t) {
        $this->title = $t;
    }

    function get_firstname() {
        return $this->firstname;
    }

    public function set_firstname($n) {
        $this->firstname = $n;
    }

    function get_name() {
        return $this->firstname;
    }

    public function set_name($n) {
        $this->name = $n;
    }

    function get_email() {
        return $this->email;
    }

    function set_email($m) {
        $this->email = $m;
    }

    function get_department() {
        if ($this->department instanceof Department)
            return $this->department;
        else {
            $d = Lernfunk::load_department($this->department);
            if ($d instanceof Department) {
                $this->department = $d;
                return $d;
            } else
                return '';
        }
    }

    public function set_department($d) {
        $this->department = $d;
    }

    function toArray() {
        if ($this->department instanceof Department)
            $dep = $this->department->get_id();
        else
            $dep = $this->department;

        return array( 'lecturer_id' => $this->id,
                      'ac_title' => $this->title,
                      'firstname' => $this->firstname,
                      'name' => $this->name,
                      'email' => $this->email,
                      'dep_id' => $dep );
    }
}

// DONE, TESTED
class Format extends DatabaseObject {

    protected $TABLE = 'format';
    protected $KEYNAME = 'format_id';

    private $mimetype;
    private $name;
    private $requirements;

    function __construct($id, $mimetype, $name, $requirements) {
        $this->set_id($id);
        $this->mimetype = $mimetype;
        $this->name = $name;
        $this->requirements = $requirements;
    }

    public function get_mimetype() {
        return $this->mimetype;
    }

    public function set_mimetype($mt) {
        $this->mimetype = $mt;
    }

    public function get_name() {
        return $this->name;
    }

    public function set_name($name) {
        $this->name = $name;
    }

    function get_requirements() {
        return $this->requirements;
    }

    public function set_requirements($r) {
        $this->requirements = $r;
    }

    function toArray() {
        return array( 'format_id' => $this->id,
                      'mimetype' => $this->mimetype,
                      'name' => $this->name,
                      'requirements' => $this->requirements );
    }
}

// DONE, TESTED
class Language extends DatabaseObject {

    protected $TABLE = 'language';
    protected $KEYNAME = 'lang_id';

    private $long;
    private $short;

    function __construct($id, $long, $short) {
        $this->set_id($id);
        $this->long = $long;
        $this->short = $short;
    }

    function get_long() {
        return $this->long;
    }

    public function set_long($long) {
        $this->long = $long;
    }

    function get_short() {
        return $this->short;
    }

    function set_short($short) {
        $this->short = $short;
    }

    function toArray() {
        return array( 'lang_id' => $this->id,
                      'language_long' => $this->long,
                      'language_short' => $this->short );
    }
}

// DONE, TESTED
class Access extends DatabaseObject {

    protected $TABLE = "access";
    protected $KEYNAME = 'access_id';

    private $status;

    function __construct($id, $status) {
        $this->set_id($id);
        $this->status = $status;
    }

    public function get_status() {
        return $this->status;
    }

    public function set_status($status) {
        $this->status = $status;
    }

    public function toArray() {
        return array( 'access_id' => $this->id,
                      'status' => $this->status );
    }
}

// DONE, TESTED
class Term extends DatabaseObject {

    protected $TABLE = 'terms';
    protected $KEYNAME = 'term_id';

    private $short;
    private $long;

    function __construct($id, $short, $long) {
        $this->set_id($id);
        $this->short = $short;
        $this->long = $long;
    }

    public function get_short() {
        return $this->short;
    }

    public function set_short($short) {
        $this->short = $short;
    }

    public function get_long() {
        return $this->long;
    }

    public function set_long($long) {
        $this->long = $long;
    }

    function toArray() {
        return array( 'term_id' => $this->id,
                      'term_sh' => $this->short,
                      'term_lg' => $this->long );
    }
}

// DONE, TESTED
class Role extends DatabaseObject {

    protected $TABLE = "role";
    protected $KEYNAME = 'role_id';

    private $description;

    function __construct( $id, $description ) {
        $this->set_id($id);
        $this->description = $description;
    }

    function get_description() {
        return $this->description;
    }

    function set_description($desc) {
        $this->description = $desc;
    }

    function toArray() {
        return array( 'role_id' => $this->id,
                      'role' => $this->description );
    }
}

// DONE, TESTED
class Audience extends DatabaseObject {

    protected $TABLE = 'audience';
    protected $KEYNAME = 'audience_id';

    private $name;
    private $description;

    function __construct( $id, $name, $description ) {
        $this->set_id($id);
        $this->name = $name;
        $this->description = $description;
    }

    function get_name() {
        return $this->name;
    }

    function set_name($name) {
        $this->name = $name;
    }

    function get_description() {
        return $this->description;
    }

    function set_description($desc) {
        $this->description = $desc;
    }

    function toArray() {
        return array( 'audience_id' => $this->id,
                      'name' => $this->name,
                      'description' => $this->description );
    }
}

// DONE, TESTED
class Category extends DatabaseObject {

    protected $TABLE = 'category';
    protected $KEYNAME = 'category_id';

    private $name;

    function __construct( $id, $name ) {
        $this->set_id($id);
        $this->name = $name;
    }

    function get_name() {
        return $this->name;
    }

    function set_name($name) {
        $this->name = $name;
    }

    function toArray() {
        return array( 'cat_id' => $this->id,
                      'category' => $this->name );
    }
}

// DONE, TESTED
class Feed extends DatabaseObject {

    protected $TABLE = 'feeds';
    protected $KEYNAME = 'feed_id';

    private $url;
    private $series;
    private $feedtype;

    function __construct($id, $url, $series, $feedtype) {
        $this->set_id($id);
        $this->url = $url;
        $this->series = $series;
        $this->feedtype = $feedtype;
    }

    public function get_url() {
        return $this->url;
    }

    public function set_url($url) {
        $this->url = $url;
    }

    public function get_series() {
        if ($this->series instanceof Series)
            return $this->series;
        else {
            $d = Lernfunk::load_series($this->series);
            if ($d instanceof Series) {
                $this->series = $d;
                return $d;
            } else
                return '';
        }
    }

    public function set_series($series) {
        $this->series = $series;
    }

    public function get_feedtype() {
        if ($this->feedtype instanceof FeedType)
            return $this->feedtype;
        else {
            $d = Lernfunk::load_feedtype($this->feedtype);
            if ($d instanceof Feedtype) {
                $this->feedtype = $d;
                return $d;
            } else
                return '';
        }
    }

    public function set_feedtype($ft) {
        $this->feedtype = $ft;
    }

    public function toArray() {
        return Array( 'feed_id' => $this->id,
                      'feed_url' => $this->url,
                      'series_id' => $this->series,
                      'feedtype_id' => $this->feedtype );
    }
}

// DONE, TESTED
class FeedType extends DatabaseObject {

    protected $TABLE = 'feedtype';
    protected $KEYNAME = 'feedtype_id';

    private $name;

    function __construct( $id, $name ) {
        $this->set_id($id);
        $this->name = $name;
    }

    function get_name() {
        return $this->name;
    }

    function set_name($name) {
        $this->name = $name;
    }

    function toArray() {
        return array( 'feedtype_id' => $this->id,
                      'feedtype_desc' => $this->name );
    }
}

// DONE, TESTED
class Department extends DatabaseObject {

    protected $TABLE = 'department';
    protected $KEYNAME = 'dep_id';

    private $name;
    private $description;
    private $number;
    private $academy;

    function __construct($id, $name, $description, $number, $academy) {
        $this->set_id($id);
        $this->name = $name;
        $this->description = $description;
        $this->number = $number;
        $this->academy = $academy;
    }

    public function get_name() {
        return $this->name;
    }

    public function get_description() {
        return $this->description;
    }

    public function get_number() {
        return $this->number;
    }

    public function get_academy() {
        if ($this->academy instanceof Academy)
            return $this->academy;
        else {
            $d = Lernfunk::load_academy($this->academy);
            if ($d instanceof Academy) {
                $this->academy = $d;
                return $d;
            } else
                return '';
        }
    }

    public function set_name($name) {
        $this->name = $name;
    }

    public function set_description($d) {
        $this->description = $d;
    }

    public function set_number($n) {
        $this->number = $n;
    }

    public function set_academy($academy) {
        $this->academy = $academy;
    }

    public function toArray() {
        if ($this->academy instanceof Academy)
            $academy_id = $this->academy->get_id();
        else
            $academy_id = $this->academy;

        return array( 'dep_id' => $this->id,
                      'dep_name' => $this->name,
                      'dep_description' => $this->description,
                      'dep_number' => $this->number,
                      'academy_id' => $academy_id);
    }
}

// DONE, TESTED
class Academy extends DatabaseObject {

    protected $TABLE = 'academy';
    protected $KEYNAME = 'academy_id';

    private $name;
    private $contact;
    private $cperson;
    private $email;

    function __construct($id, $name, $contact, $cperson, $email) {
        $this->set_id($id);
        $this->name = $name;
        $this->contact = $contact;
        $this->cperson = $cperson;
        $this->email = $email;
    }

    public function get_name() {
        return $this->name;
    }

    public function get_contact() {
        return $this->contact;
    }

    public function get_contact_person() {
        return $this->cperson;
    }

    public function get_email() {
        return $this->email;
    }

    public function set_name($v) {
        $this->name = $v;
    }

    public function set_contact($v) {
        $this->contact = $v;
    }

    public function set_contact_person($v) {
        $this->cperson = $v;
    }

    public function set_email($v) {
        $this->email = $v;
    }

    public function toArray() {
        return array( 'academy_id' => $this->id,
                      'ac_name' => $this->name,
                      'ac_contact' => $this->contact,
                      'ac_contact_person' => $this->cperson,
                      'ac_email' => $this->email );
    }
}

// DONE, TESTED
class Classification extends DatabaseObject {

    protected $TABLE = 'elan_classification';
    protected $KEYNAME = 'clas_id';

    private $name;
    private $description;

    function __construct($id, $name, $description) {
        $this->set_id($id);
        $this->name = $name;
        $this->description = $description;
    }

    public function get_name() {
        return $this->name;
    }

    public function get_description() {
        return $this->description;
    }

    public function set_name($name) {
        $this->name = $name;
    }

    public function set_description($desc) {
        $this->description = $desc;
    }

    public function toArray() {
        return array( 'clas_id' => $this->id,
                      'classification' => $this->name,
                      'description' => $this->description );
    }
}

class User extends DatabaseObject {

    protected $TABLE = 'user';
    protected $KEYNAME = 'user_id';

    private $username;
    private $password;
    private $lecturer;
    private $admin;
    private $pressoffice;

    function __construct($id, $username, $password, $lecturer, $admin, $pressoffice) {
        $this->set_id($id);
        $this->user_name = $username;
        $this->password = $password;
        $this->lecturer = $lecturer;
        $this->admin = $admin;
        $this->pressoffice = $pressoffice;
    }

    public function get_username() {
        return $this->username;
    }

    public function set_username($name) {
        $this->username = $name;
    }

    public function get_password() {
        return $this->password;
    }

    public function set_password($pass) {
        $this->password = $pass;
    }

    public function get_lecturer() {
        if ($this->lecturer instanceof Lecturer)
            return $this->lecturer;
        else {
            $d = Lernfunk::load_lecturer($this->lecturer);
            if ($d instanceof Lecturer) {
                $this->lecturer = $d;
                return $d;
            } else
                return '';
        }
    }

    public function set_lecturer($lec) {
        $this->lecturer = $lec;
    }

    public function is_admin() {
        return ($this->admin == '1');
    }

    public function set_admin($admin) {
        if ($admin == true)
            $this->admin = '1';
        else
            $this->admin = '0';
    }

    public function is_pressoffice() {
        return ($this->pressoffice == '1');
    }

    public function set_pressoffice($p) {
        if ($p == true)
            $this->pressoffice = '1';
        else
            $this->pressoffice = '0';
    }

    public function toArray() {
        if ($this->lecturer instanceof Lecturer)
            $lecturer_id = $this->lecturer->get_id();
        else
            $lecturer_id = $this->lecturer;

        return array( 'user_id' => $this->get_id(),
                      'user_name' => $this->username,
                      'password' => $this->password,
                      'lecturer_id' => $lecturer_id,
                      'is_admin' => $this->admin,
                      'is_pressoffice' => $this->pressoffice );
    }
}

class LMS extends DatabaseObject {

    protected $TABLE = 'lms';
    protected $KEYNAME = 'lms_id';

    private $name;
    private $contact_person;
    private $email;
    private $identifier;
    private $url;

    function __construct($id, $name, $contact_person, $email, $identifier, $url) {
        $this->set_id($id);
        $this->name = $name;
        $this->contact_person = $contact_person;
        $this->email = $email;
        $this->identifier = $identifier;
        $this->url = $url;
    }

    public function get_name() {
        return $this->name;
    }

    public function set_name($name) {
        $this->name = $name;
    }

    public function get_contact_person() {
        return $this->contact_person;
    }

    public function set_contact_person($cpserson) {
        $this->contact_person = $cpserson;
    }

    public function get_email() {
        return $this->email;
    }

    public function set_email($email) {
        $this->email = $email;
    }

    public function get_identifier() {
        return $this->identifier;
    }

    public function set_identifier($identifier) {
        $this->identifier = $identifier;
    }

    public function get_url() {
        $this->url = $url;
    }

    public function set_url($url) {
        $this->url = $url;
    }

    public function toArray() {
        return array( 'lms_id' => $this->get_id(),
                      'name' => $this->name,
                      'contact_person' => $this->contact_person,
                      'email' => $this->email,
                      'lms_identifier' => $this->identifier,
                      'lms_url' => $this->url );
    }
}

class LMS_Connector extends DatabaseObject {

    protected $TABLE = 'lms_connect';
    protected $KEYNAME = 'connector_id';

    private $series;
    private $lms;
    private $lms_course_id;

    function __construct($id, $series, $lms, $lms_course_id) {
        $this->set_id($id);
        $this->series = $series;
        $this->lms = $lms;
        $this->lms_course_id = $lms_course_id;
    }

    public function get_series() {
        if ($this->series instanceof Series)
            return $this->series;
        else {
            $d = Lernfunk::load_series($this->series);
            if ($d instanceof Series) {
                $this->series = $d;
                return $d;
            } else
                return '';
        }
    }

    public function get_lms() {
        if ($this->series instanceof LMS)
            return $this->lms;
        else {
            $d = Lernfunk::load_lms($this->lms);
            if ($d instanceof LMS) {
                $this->lms = $d;
                return $d;
            } else
                return '';
        }
    }

    public function get_lms_course_id() {
        return $this->lms_course_id;
    }

    public function set_series($s) {
        $this->series = $series;
    }

    public function set_lms($lms) {
        $this->lms = $lms;
    }

    public function set_lms_course_id($id) {
        $this->lms_course_id = $id;
    }

    public function toArray() {
        if ($this->series instanceof Series)
            $series_id = $this->series;
        else
            $series_id = $this->series;

        if ($this->lms instanceof LMS)
            $lms_id = $this->lms->get_id();
        else
            $lms_id = $this->lms;

        return array('connector_id' => $this->get_id(),
                     'series_id' => $series_id,
                     'lms_id' => $lms_id,
                     'lms_course_id' => $this->lms_course_id);
    }
}
