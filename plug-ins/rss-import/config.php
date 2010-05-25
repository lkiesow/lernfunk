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

    // path to the lernfunk library
    $lflib_path = dirname(__FILE__).'/../lflib/'

	error_reporting(E_ERROR);
	ini_set("display_errors", TRUE);

	$vp_url = "http://vm056.rz.uos.de:8080/virtPresenterVerwalter/export/lernfunk.jsp";
	$logfile = null;

	$DEBUG = array_key_exists('debug', $_REQUEST) && ($_REQUEST['debug'] == 'true');
?>
