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

/* Lernfunk Configuration */

function Lernfunk_Configuration() {
	// return new lf_Config( $_SERVER['DOCUMENT_ROOT'].'/conf/lernfunk.conf' );
	return new lf_Config( dirname(__FILE__).'/lernfunk.conf' );
}


/* Lerfnunk Config class */

class lf_Config {

	public $db_server;
	public $db_name;
	public $db_user;
	public $db_passwd;
	
	// used by Lerfunk core to get the config 
	//
	static function get_Config() {
	$c = Lernfunk_Configuration();
	if (!$c instanceof lf_Config)
		die("lf_Config::get_Config(): Lernfunk_Configuration() did not return a valid config object.");
	else
		return $c;
	}

	// Constructor reading config from file
	//
	function __construct( $cfg_filename ) {
	
	if (file_exists($cfg_filename)) {
		$f = fopen($cfg_filename, 'r');
		$s = fread($f, filesize($cfg_filename));
		
		// read lines of config file
		foreach (explode("\n", $s) as $line) {
		$line = preg_replace( '/#(.*)$/', '', $line);	// remove comments
		$line = preg_replace( '/\s+/', ' ', $line);	 // remove multiple spaces
		if ( preg_match('/(.*)=(.*)$/', $line, $m) ) {  // scan for key/valp pairs 
			$key = trim($m[1]);
			$val = trim($m[2]);
			eval( '$this->'.$key.' = "'.$val.'";' );	// save parameters to this object
		}
		}
		fclose($f);
	}
		else die("lf_Config.__construct(): Error reading config file, $cfg_filename does not exist.");
	}
}

?>
