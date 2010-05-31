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

class RecordTypes {

    private static $DISABLED = 'disabled="true"';

    public static $types = array(

        'mediaobject' => array( 'TABLE' => 'mediaobject',
                                'KEY' => 'object_id'
                              ),

        'academy' => array( 'TABLE' => 'academy',
                            'KEY' => 'academy_id',
                            'DESCRIPTION' => array('ac_name'),
                            'academy_id' => 'ID',
                            'ac_name' => 'Name',
                            'ac_contact' => 'Kontakt',
                            'ac_contact_person' => 'Kontaktperson',
                            'ac_email' => 'E-Mail' ) ,

        'access' => array( 'TABLE' => 'access',
                           'KEY' => 'access_id',
                           'DESCRIPTION' => array('status'),
                           'access_id' => 'ID',
                           'status' => 'Status' ) ,

        'audience' => array( 'TABLE' => 'audience',
                             'KEY' => 'audience_id',
                             'DESCRIPTION' => array('name'),
                             'audience_id' => 'ID',
                             'name' => 'Name',
                             'description' => 'Beschreibung'
                             ) ,

        'category' => array( 'TABLE' => 'category',
                             'KEY' => 'cat_id',
                             'DESCRIPTION' => array('category'),
                             'cat_id' => 'ID',
                             'category' => 'Name'
                             ) ,

        'feeds' => array( 'TABLE' => 'feeds',
                          'KEY' => 'feed_id',
                          'DESCRIPTION' => array('feed_url'),
                          'feed_id' => 'ID',
                          'feed_url' => 'URL',
                          'series_id' => 'Series',
                          'feedtype_id' => 'Typ',
                         'itunes_status' => 'iTunes-status'
                          ) ,
                          
        'feedtype' => array( 'TABLE' => 'feedtype',
                             'KEY' => 'feedtype_id',
                             'DESCRIPTION' => array('feedtype_desc'),
                             'feedtype_id' => 'ID',
                             'feedtype_desc' => 'Name'
                             ) ,

        'format' => array( 'TABLE' => 'format',
                           'KEY' => 'format_id',
                           'DESCRIPTION' => array('name'),
                           'format_id' => 'ID',
                           'mimetype' => 'Mime-Type',
                           'name' => 'Name',
                           'requirements' => 'Systemvoraussetzungen'
                           ) ,

        'language' => array( 'TABLE' => 'language',
                             'KEY' => 'lang_id',
                             'DESCRIPTION' => array('language_long'),
                             'lang_id' => 'ID',
                             'language_long' => 'Bezeichnung',
                             'language_short' => 'Kurzbezeichnung'
                             ) ,

        'lecturer' => array( 'TABLE' => 'lecturer',
                             'KEY' => 'lecturer_id',
                             'DESCRIPTION' => array('ac_title', 'firstname', 'name'),
                             'lecturer_id' => 'ID',
                             'ac_title' => 'Titel',
                             'firstname' => 'Vorname',
                             'name' => 'Name',
                             'email' => 'E-Mail',
                             'dep_id' => 'Department-ID',
                             'academy_id' => 'Academy-ID',
                             'lms_lecturer_id' => 'LMS-Lecturer-ID',
                             'lec_url' => 'lec_url'
                             ) ,

        'lms' => array( 'TABLE' => 'lms',
                        'KEY' => 'lms_id',
                        'DESCRIPTION' => array('name'),
                        'lms_id' => 'ID',
                        'name' => 'Name',
                        'contact_person' => 'Kontaktperson',
                        'email' => 'E-Mail',
                        'lms_identifier' => 'LMS Identifier',
                        'lms_url' => 'URL'
                        ) ,

        'lms_connect' => array( 'TABLE' => 'lms_connect',
                        'KEY' => 'connector_id',
                        'DESCRIPTION' => array('connector_id'),
                        'connector_id' => 'ID',
                        'series_id' => 'series_id',
                        'lms_id' => 'lms_id',
                        'lms_course_id' => 'lms_course_id'
                        ) ,

        'role' => array( 'TABLE' => 'role',
                         'KEY' => 'role_id',
                         'DESCRIPTION' => array('role'),
                         'role_id' => 'ID',
                         'role' => 'Rolle'
                         ) ,

        'series' => array( 'TABLE' => 'series',
                         'KEY' => 'series_id',
                         'DESCRIPTION' => array('name'),
                         'series_id' => 'ID',
                         'name' => 'Name',
                         'description' => array('val' => 'Desc.', 'args' => ''),
                         'description_sh' => 'Desc. (short)',
                         'term_id' => 'term_id',
                         'course_id' => 'course_id',
                         'access_id' => 'access_id',
                         'portal_url' => 'Portal-URL',
                         'clas_id' => 'clas_id',
                         'thumbnail_url' => 'Thumb-URL',
                         'lms_course_id' => 'lms_course_id',
                         'add_url' => 'add_url',
                         'add_url_text' => 'add_url_text',
                         'lrs_series_id' => 'lrs_series_id',
                         'social_web' => 'social_web',
                         'keywords' => 'keywords',
                         'cat_id' => 'cat_id',
                         'default_playlist_id' => 'default_playlist_id'
                         ) ,

        'terms' => array( 'TABLE' => 'terms',
                          'KEY' => 'term_id',
                          'DESCRIPTION' => array('term_sh', 'term_lg'),
                          'term_id' => 'ID',
                          'term_lg' => 'Bezeichnung',
                          'term_sh' => 'Kurzbezeichnung'
                          ),

    );

}
