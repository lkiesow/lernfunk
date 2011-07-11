<?php
/*
 * LernfunkWebservice.class.php
 *
 * communication with the lernfunk-webservice
 *
 * Copyright (C) 2010 - André Klaßen <aklassen@uos.de>
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 */

require_once('phpxmlrpc/xmlrpc.inc');

class LernfunkWebservice {
	var $api_key;
	var $lms_id;
	var $webservice_url;
	var $seminar_id;

	/**
	 * create a webservice instance for the seminar with the submitted id
	 *
	 * @TODO add a mechanism to set the api_key and lms_id in wordpress
	 * @param string $seminar_id the seminar to query the webservice for
	 */
	function __construct( $recording_id ) {
		//$webservice = LernfunkDB::getWebserviceInfo();
		$this->api_key = "YOUR API KEY";
		$this->lms_id = "YOUR LMS ID";
		$this->webservice_url = "YOUR WEBSERICE URL";

		$this->recording_id = $recording_id;
	}

	/**
	 * query an xml webservice
	 *
	 * @param string the method to call
	 * @param array an array of params to send
	 * @param debug 0, 1, 2 - zero means no debugging
	 *
	 * @return mixed the retrieved data 
	 */
	private function query($method, $params, $debug = 0) {
		$client =& new xmlrpc_client($this->webservice_url);
		$client->return_type = 'xmlrpcvals';
		$client->setDebug($debug);

		$msg =& new xmlrpcmsg($method);
		foreach ($params as $value => $type) {
			$param = new xmlrpcval($value, $type);
			$msg->addparam( $param );
		}

		$res =& $client->send($msg, 0, '');
		if ($res->faultcode()) return false; else return php_xmlrpc_decode($res->value());
	}


	/* * * * * * * * * * * * * * * * * * * * * * * * * * *
	 * *   W E B S E R V I C E   E N D - P O I N T S   * *
	 * * * * * * * * * * * * * * * * * * * * * * * * * * */

	public function get_recording() {
		$params[$this->api_key] = 'string';
		$params[$this->recording_id] = 'string';
		$params[$this->lms_id] = 'string';

		return $this->query( 'get_recording', $params );
	}
	
}
