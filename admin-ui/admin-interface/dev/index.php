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


// include configuration
include_once('php/config.php');

// include functionality
include_once('php/lfadmin.php');

$username = "admin";
$password = "admin";

//        if (isset($_SERVER['PHP_AUTH_USER'])) {
//
//            if (!$user = Lernfunk::load_user_by_name($_SERVER['PHP_AUTH_USER'])) access_denied();
//            if ($user->get_password() != $_SERVER['PHP_AUTH_PW']) access_denied();

$user = Lernfunk::load_user_by_name($username);

// AJAX or native request?
if (array_key_exists('action', $_REQUEST) && ($_REQUEST['action'] == 'ajax')) {
    $meth = $_REQUEST['cmd'];
    $code = 'return LFAdmin::ajax_'.$meth.'($_REQUEST);';
    print eval($code);
} else {
    print LFAdmin::load_file('templates/main.html');
}

//        } else
//            access_denied();

function access_denied() {
    header('WWW-Authenticate: Basic realm="Enter key-phrase for autodestruct sequence"');
    header('HTTP/1.0 401 Unauthorized');
    exit;
}
