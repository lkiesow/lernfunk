<?php

require_once( dirname(__FILE__).'/lf_sql.php' );


function lf_build_limit( $limit ) {

	if ( $limit && is_array($limit) 
		&& array_key_exists( 's', $limit ) 
		&& array_key_exists( 'c', $limit ) ) {
		return ' limit '.intval($limit['s']).', '.intval($limit['c']);
	}
	return '';

}


function lf_build_order( $order ) {

	if ( $order && is_array($order) 
		&& array_key_exists( 'f', $order ) 
		&& array_key_exists( 'o', $order ) ) {
		return ' order by '.mysql_escape_string($order['f']).' '.$order['o'];
	}
	return '';

}


function lf_build_filter( $filter ) {

	if ( !is_array( $filter ) ) {
		return '';
	}

	/* Check if is operator */
	if ( array_key_exists( 'o', $filter ) ) {
		switch ( $filter['o'] ) {
			case 'eq': 
				return $filter['k'] . ' = "' . $filter['v'] . '"';
			case 'neq': 
				return $filter['k'] . ' != "' . $filter['v'] . '"';
			case 'like': 
				return $filter['k'] . ' like "' . $filter['v'] . '"';
			case 'in': 
				return $filter['k'] . ' like "%' . $filter['v'] . '%"';
			case 'and':
				$result = '( ';
				$cn = '';
				foreach ( $filter['p'] as $op ) {
					$result .= $cn . lf_build_filter( $op );
					$cn = ' and ';
				}
				return $result . ' )';
			case 'and':
				$result = '( ';
				$cn = '';
				foreach ( $filter['p'] as $op ) {
					$result .= $cn . lf_build_filter( $op );
					$cn = ' or ';
				}
				return $result . ' )';
		}
	}
	return '';

}


/**
 * \brief Request data from table
 **/
function lf_request_table( $table, $fields, $filter = null, $limit = null, $order = null, $add_sql = null ) {

	$query = 'select '.implode( ', ', $fields ).' from '
		.$table.$filter_str.$limit_str.$order_str;
	return lf_request_custom( $query, $filter, $limit, $order, $add_sql );

}


function lf_request_custom( $sql, $filter = array(), $limit = null, $order = null, $add_sql = null ) {

	$filter_str = lf_build_filter( $filter );
	if ( $filter_str != '' ) {
		$filter_str = ' where ' . $filter_str;
	}
	$limit_str = lf_build_limit( $limit );
	$order_str = lf_build_order( $order );

	$result = array();
	$query = $sql.$filter_str.' '.$add_sql.$limit_str.$order_str;
	if ( $rs = lf_query( $query ) ) {
		while ( $r = mysql_fetch_array( $rs, MYSQL_ASSOC ) ) {
			$result[] = $r;
		}
	}
	return $result;

}


function lf_check_filter_json( $op ) {

	if ( !array_key_exists( 'o', $op ) ) { 
		/* Not a valid operator. */
		return null;
	}

	switch ( $op['o'] ) {
		
		case 'eq':
		case 'neq':
		case 'like':
		case 'in':
			if ( !array_key_exists( 'k', $op ) || !array_key_exists( 'v', $op ) ) {
				/* Operator must have two arguments k and v. */
				return null;
			}
			$op['k'] = mysql_escape_string( $op['k'] );
			$op['v'] = mysql_escape_string( $op['v'] );
			return $op;

		case 'and':
		case 'or':
			if ( !array_key_exists( 'p', $op ) || !is_array( $op['p'] ) ) {
				/* Operator must have array parameter p. */
				return null;
			}
			$ops = array();
			foreach ( $op['p'] as $o ) {
				$o = lf_check_filter_json( $o );
				if ( $o ) {
					$ops[] = $o;
				}
			}
			if ( empty( $ops ) ) {
				return null;
			}
			if ( count( $ops ) == 1 ) {
				return $ops[0];
			}
			$op['p'] = $ops;
			return $op;

	}

}


function lf_check_filter_xml( $op ) {
	
	switch ( $op->getName() ) {

		case 'eq':
		case 'neq':
		case 'like':
		case 'in':
			$k = null;
			$v = null;
			foreach ( $op->attributes() as $a => $b ) {
				switch ( $a ) {
					case 'k': $k = $b; break;
					case 'v': $v = $b; break;
					default : return null;
				}
			}
			if ( $k !== null && $v !== null ) {
				return array( 'o' => $op->getName(), 'k' => $k, 'v' => $v );
			}

		case 'and':
		case 'or':
			$ops = array();
			foreach ( $op->children() as $o ) {
				$o = lf_check_filter_xml( $o );
				if ( $o ) {
					$ops[] = $o;
				}
			}
			if ( empty( $ops ) ) {
				return null;
			}
			if ( count( $ops ) == 1 ) {
				return $ops[0];
			}
			return array( 'o' => $op->getName(), 'p' => $ops );

	}
	return null;

}


function lf_parse_filter( $filter_str ) {

	if ( !$filter_str ) {
		return null;
	}

	/* Check if filter is XML or JSON */
	if ( strpos( $filter_str, '<' ) === 0 ) { /* XML */
		$xml = simplexml_load_string( $filter_str );
		if ( $xml ) {
			return lf_check_filter_xml( $xml );
		}
		
	} else { /* Should be JSON */
		$filter = json_decode( $filter_str, true );
		if ( is_array( $filter ) ) {
			return lf_check_filter_json( $filter );
		}
	}

	return null;

}


function lf_parse_limit( $limit_str ) {

	if ( !$limit_str ) {
		return null;
	}

	/* Check if filter is XML or JSON */
	if ( strpos( $limit_str, '<' ) === 0 ) { /* XML */
		$xml = simplexml_load_string( $limit_str );
		if ( $xml ) {
			$s = null;
			$c = null;
			foreach ( $xml->attributes() as $a => $b ) {
				switch ( $a ) {
					case 's': $s = $b; break;
					case 'c': $c = $b; break;
					default : return null;
				}
			}
			if ( $s !== null && $c !== null ) {
				return array( 's' => intval($s), 'c' => intval($c) );
			}
		}
		
	} else { /* Should be JSON */
		$l = json_decode( $limit_str, true );
		if ( is_array( $l ) && array_key_exists( 's', $l ) 
			&& array_key_exists( 'c', $l) && count( $l ) == 2 ) {
				return array( 's' => intval($l['s']), 'c' => intval($l['c']) );
		}
	}
	return null;

}


function lf_parse_order( $order_str ) {

	if ( !$order_str ) {
		return null;
	}

	/* Check if filter is XML or JSON */
	if ( strpos( $order_str, '<' ) === 0 ) { /* XML */
		$xml = simplexml_load_string( $order_str );
		if ( $xml ) {
			$f = null;
			$o = null;
			foreach ( $xml->attributes() as $a => $b ) {
				switch ( $a ) {
					case 'f': $f = $b; break;
					case 'o': 
						if ( strtolower($b) == 'asc' || strtolower($b) == 'desc' ) {
							$o = $b;
						}
						break;
					default : return null;
				}
			}
			if ( $f !== null && $o !== null ) {
				return array( 'f' => mysql_escape_string($f), 'o' => $o );
			}
		}
		
	} else { /* Should be JSON */
		$o = json_decode( $order_str, true );
		if ( is_array( $o ) && array_key_exists( 'o', $o ) 
			&& array_key_exists( 'f', $o) && count( $o ) == 2 
			&& ( strtolower($o['o']) == 'asc' || strtolower($o['o']) == 'desc' ) ) {
				return array( 'f' => mysql_escape_string($o['f']), 'o' => $o['o'] );
		}
	}
	return null;

}


function lf_parse_path( $path_str, $filter_str, $limit_str, $order_str, $detail = false ) {

	$path   = explode( '/', trim( $path_str, ' /' ) );
	$filter = lf_parse_filter( $filter_str );
	$limit  = lf_parse_limit(  $limit_str );
	$order  = lf_parse_order(  $order_str );
	$cnt    = count( $path );

	if ( $cnt > 0 ) {
		switch ( $path[0] ) {

			case 'academy':
				if ( $cnt == 1 || ( $cnt == 2 && is_numeric( $path[1] ) ) ) {
					if ( $cnt == 2 ) {
						$f = array( 'o' => 'eq', 'k' => 'academy_id', 'v' => intval($path[1]) );
						$filter = $filter ? array( 'o' => 'and', 'p' => array( $filter, $f ) ) : $f;
					}
					return lf_request_table( 'academy', array( '*' ), $filter, $limit, $order );
				}
				return null;


			case 'category':
				if ( $cnt == 1 || ( $cnt == 2 && is_numeric( $path[1] ) ) ) {
					if ( $cnt == 2 ) {
						$f = array( 'o' => 'eq', 'k' => 'cat_id', 'v' => intval($path[1]) );
						$filter = $filter ? array( 'o' => 'and', 'p' => array( $filter, $f ) ) : $f;
					}
					return lf_request_table( 'category', array( '*' ), $filter, $limit, $order );
				}
				return null;


			case 'department':
				if ( $cnt == 1 || ( $cnt == 2 && is_numeric( $path[1] ) ) ) {
					if ( $cnt == 2 ) {
						$f = array( 'o' => 'eq', 'k' => 'dep_id', 'v' => intval($path[1]) );
						$filter = $filter ? array( 'o' => 'and', 'p' => array( $filter, $f ) ) : $f;
					}
					return lf_request_table( 'department', array( '*' ), $filter, $limit, $order );
				} elseif ( $cnt == 3 && is_numeric( $path[1] ) && $path[2] == 'academy' ) {
					$f = array( 'o' => 'eq', 'k' => 'dep_id', 'v' => intval($path[1]) );
					$filter = $filter ? array( 'o' => 'and', 'p' => array( $filter, $f ) ) : $f;
					return lf_request_custom( 
						'select a.* from academy a '
						.'left outer join department d '
						.'on a.academy_id = d.academy_id', $filter, $limit, $order );
				}
				return null;


			case 'format':
				if ( $cnt == 1 || ( $cnt == 2 && is_numeric( $path[1] ) ) ) {
					if ( $cnt == 2 ) {
						$f = array( 'o' => 'eq', 'k' => 'format_id', 'v' => intval($path[1]) );
						$filter = $filter ? array( 'o' => 'and', 'p' => array( $filter, $f ) ) : $f;
					}
					return lf_request_table( 'format', array( '*' ), $filter, $limit, $order );
				}
				return null;


			case 'language':
				if ( $cnt == 1 || ( $cnt == 2 && is_numeric( $path[1] ) ) ) {
					if ( $cnt == 2 ) {
						$f = array( 'o' => 'eq', 'k' => 'lang_id', 'v' => intval($path[1]) );
						$filter = $filter ? array( 'o' => 'and', 'p' => array( $filter, $f ) ) : $f;
					}
					return lf_request_table( 'language', array( '*' ), $filter, $limit, $order );
				}
				return null;


			case 'lecturer':
				$result = null;
				if ( $cnt == 1 ) {
					$result = lf_request_table( 'lecturer', array( '*' ), $filter, $limit, $order );
				} elseif ( is_numeric( $path[1] ) ) {
					$f = array( 'o' => 'eq', 'k' => 'l.lecturer_id', 'v' => intval($path[1]) );
					$filter = $filter ? array( 'o' => 'and', 'p' => array( $filter, $f ) ) : $f;
					if ( $cnt == 2 ) {
						$result = lf_request_table( 'lecturer l', array( '*' ), $filter, $limit, $order );
					} elseif ( $cnt == 3 && $path[2] == 'academy' ) {
						return lf_request_custom( 
							'select a.* from academy a left outer join lecturer l '
							.'on a.academy_id = l.academy_id ', $filter, $limit, $order );
					} elseif ( $cnt == 3 && $path[2] == 'department' ) {
						return lf_request_custom( 
							'select d.* from department d left outer join lecturer l '
							.'on d.dep_id = l.dep_id ', $filter, $limit, $order );
					} elseif ( $cnt == 4 && $path[2] == 'department' && $path[3] == 'academy' ) {
						return lf_request_custom( 
							'select a.* from academy a left outer join department d '
							.'on a.academy_id = d.academy_id left outer join lecturer l '
							.'on d.dep_id = l.dep_id ', $filter, $limit, $order );
					} elseif ( $cnt == 3 && $path[2] == 'series' ) {
						$result = lf_request_custom( 
							'select s.* from lecturer_series l left outer join series s '
							.'on l.series_id = s.series_id', $filter, $limit, $order );
						if ( !$detail ) {
							return $result;
						}
						for ( $i = 0; $i < count($result); $i++ ) {
							$mobjs = array();
							$mpkgs = array();
							foreach ( lf_request_custom( 'select object_id, cou_id from mediaobject '
								.'where series_id = '.$result[$i]['series_id'] ) as $m ) {
								$mobjs[] = $m['object_id'];
								$mpkgs[ $m['cou_id'] ] = '';
							}
							$result[$i]['mediaobject'] = $mobjs;
							$result[$i]['mediapackage'] = array_keys( $mpkgs );
						}
						return $result;
					}
				}
				if ( $result && $detail ) {
					for ( $i = 0; $i < count($result); $i++ ) {
						$series = array();
						foreach ( lf_request_custom( 'select series_id from lecturer_series '
							.'where lecturer_id = '.$result[$i]['lecturer_id'] ) as $s ) {
							$series[] = $s['series_id'];
						}
						$result[$i]['series'] = $series;
					}
					return $result;
				}


			case 'lms':
				if ( $cnt == 1 ) {
					return lf_request_table( 'lms', 
						array( 'lms_identifier', 'name', 'contact_person', 'email', 'lms_url' ), 
						$filter, $limit, $order );
				} else {
					$f = array( 'o' => 'eq', 'k' => 'l.lms_identifier', 'v' => mysql_escape_string($path[1]) );
					$filter = $filter ? array( 'o' => 'and', 'p' => array( $filter, $f ) ) : $f;
					if ( $cnt == 2 ) {
						return lf_request_table( 'lms l', 
							array( 'lms_identifier', 'name', 'contact_person', 'email', 'lms_url' ), 
							$filter, $limit, $order );
					} elseif ( ( $cnt == 3 || $cnt == 4 ) && $path[2] == 'series' ) {
						if ( $cnt == 4 ) {
							$f = array( 'o' => 'eq', 'k' => 'c.lms_course_id', 'v' => mysql_escape_string($path[3]) );
							if ( $filter['o'] == 'and' ) {
								$filter['p'][] = $f;
							} else {
								$filter = array( 'o' => 'and', 'p' => array( $filter, $f ) );
							}
						}
						$result = lf_request_custom( 
							'select c.lms_course_id, s.* from series s '
							.'left outer join lms_connect c on s.series_id = c.series_id '
							.'left outer join lms l on l.lms_id = c.lms_id ',
							$filter, $limit, $order );
						if ( $result && $detail ) {
							for ( $i = 0; $i < count($result); $i++ ) {
								$mobjs = array();
								$mpkgs = array();
								foreach ( lf_request_custom( 'select object_id, cou_id from mediaobject '
									.'where series_id = '.$result[$i]['series_id'] ) as $m ) {
										$mobjs[] = $m['object_id'];
										$mpkgs[ $m['cou_id'] ] = '';
								}
								$result[$i]['mediaobject'] = $mobjs;
								$result[$i]['mediapackage'] = array_keys( $mpkgs );
								$lects = array();
								foreach ( lf_request_custom( 'select lecturer_id from lecturer_series '
									.'where series_id = '.$result[$i]['series_id'] ) as $l ) {
									$lects[] = $l['lecturer_id'];
								}
								$result[$i]['lecturer'] = $lects;
							}
						}
						return $result;
					} elseif ( $cnt == 3 && $path[2] == 'mediaobject' ) {
						return lf_request_custom( 
							'select m.* from mediaobject m '
							.'left outer join lms_connect c on m.series_id = c.series_id '
							.'left outer join lms l on l.lms_id = c.lms_id ',
							$filter, $limit, $order );
					} elseif ( $cnt == 3 && $path[2] == 'mediapackage' ) {
						return lf_request_custom( 
							'select p.* from mediapackage p '
							.'left outer join lms_connect c on p.series_id = c.series_id '
							.'left outer join lms l on l.lms_id = c.lms_id ',
							$filter, $limit, $order );
					}
				}


			case 'lms_connect':
				if ( $cnt == 1 ) {
					return lf_request_custom( 
						'select lms_identifier, series_id, lms_course_id from lms_connect '
						.'left outer join lms on lms_connect.lms_id = lms.lms_id ',
						$filter, $limit, $order );
				} else {
					$f = array( 'o' => 'eq', 'k' => 'lms_identifier', 'v' => mysql_escape_string($path[1]) );
					$filter = $filter ? array( 'o' => 'and', 'p' => array( $filter, $f ) ) : $f;
					if ( $cnt == 2 ) {
						return lf_request_custom( 
							'select lms_identifier, series_id, lms_course_id from lms_connect '
							.'left outer join lms on lms_connect.lms_id = lms.lms_id ',
							$filter, $limit, $order );
					} elseif ( ( $cnt == 3 || $cnt == 4 ) && $path[2] == 'series' ) {
						if ( $cnt == 4 ) {
							$f = array( 'o' => 'eq', 'k' => 'c.lms_course_id', 'v' => mysql_escape_string($path[3]) );
							if ( $filter['o'] == 'and' ) {
								$filter['p'][] = $f;
							} else {
								$filter = array( 'o' => 'and', 'p' => array( $filter, $f ) );
							}
						}
						$result = lf_request_custom( 
							'select c.lms_course_id, s.* from series s '
							.'left outer join lms_connect c on s.series_id = c.series_id '
							.'left outer join lms l on l.lms_id = c.lms_id ',
							$filter, $limit, $order );
							if ( $result && $detail ) {
								for ( $i = 0; $i < count($result); $i++ ) {
									$mobjs = array();
									$mpkgs = array();
									foreach ( lf_request_custom( 'select object_id, cou_id from mediaobject '
										.'where series_id = '.$result[$i]['series_id'] ) as $m ) {
											$mobjs[] = $m['object_id'];
											$mpkgs[ $m['cou_id'] ] = '';
									}
									$result[$i]['mediaobject'] = $mobjs;
									$result[$i]['mediapackage'] = array_keys( $mpkgs );
									$lects = array();
									foreach ( lf_request_custom( 'select lecturer_id from lecturer_series '
										.'where series_id = '.$result[$i]['series_id'] ) as $l ) {
										$lects[] = $l['lecturer_id'];
									}
									$result[$i]['lecturer'] = $lects;
								}
							}
							return $result;
					}
				}


			case 'series':
				$result = null;
				if ( $cnt == 1 ) {
					$result = lf_request_table( 'series', array( '*' ), $filter, $limit, $order );
				} elseif ( is_numeric( $path[1] ) ) {
					$f = array( 'o' => 'eq', 'k' => 's.series_id', 'v' => intval($path[1]) );
					$filter = $filter ? array( 'o' => 'and', 'p' => array( $filter, $f ) ) : $f;
					if ( $cnt == 2 ) {
						$result = lf_request_table( 'series s', array( '*' ), $filter, $limit, $order );
					} elseif ( $cnt == 3 && $path[2] == 'term' ) {
						return lf_request_custom( 
							'select t.* from terms t left outer join series s '
							.'on t.term_id = s.term_id ', $filter, $limit, $order );
					} elseif ( $cnt == 3 && $path[2] == 'mediaobject' ) {
						return lf_request_custom(
							'select m.* from mediaobject m left outer join series s '
							.'on s.series_id = m.series_id', $filter, $limit, $order );
					} elseif ( $cnt == 3 && $path[2] == 'mediapackage' ) {
						return lf_request_custom(
							'select m.* from mediapackage m left outer join series s '
							.'on s.series_id = m.series_id', $filter, $limit, $order );
					} elseif ( $cnt == 3 && $path[2] == 'lecturer' ) {
						return lf_request_custom(
							'select l.* from lecturer l left outer join lecturer_series s '
							.'on s.lecturer_id = l.lecturer_id', $filter, $limit, $order );
					}
				}
				if ( $result && $detail ) {
					for ( $i = 0; $i < count($result); $i++ ) {
						$mobjs = array();
						$mpkgs = array();
						foreach ( lf_request_custom( 'select object_id, cou_id from mediaobject '
							.'where series_id = '.$result[$i]['series_id'] ) as $m ) {
							$mobjs[] = $m['object_id'];
							$mpkgs[ $m['cou_id'] ] = '';
						}
						$result[$i]['mediaobject'] = $mobjs;
						$result[$i]['mediapackage'] = array_keys( $mpkgs );
						$lects = array();
						foreach ( lf_request_custom( 'select lecturer_id from lecturer_series '
							.'where series_id = '.$result[$i]['series_id'] ) as $l ) {
							$lects[] = $l['lecturer_id'];
						}
						$result[$i]['lecturer'] = $lects;
					}
				}
				return $result;


			case 'mediaobject':
				$result = null;
				if ( $cnt == 1 ) {
					return lf_request_table( 'mediaobject', array( '*' ), $filter, $limit, $order );
				} elseif ( is_numeric( $path[1] ) ) {
					$f = array( 'o' => 'eq', 'k' => 'm.object_id', 'v' => intval($path[1]) );
					$filter = $filter ? array( 'o' => 'and', 'p' => array( $filter, $f ) ) : $f;
					if ( $cnt == 2 ) {
						return lf_request_table( 'mediaobject m', array( '*' ), $filter, $limit, $order );
					} elseif ( $cnt == 3 && $path[2] == 'format' ) {
						return lf_request_custom( 
							'select f.* from format f left outer join mediaobject m '
							.'on m.format_id = f.format_id ', $filter, $limit, $order );
					} elseif ( $cnt == 3 && $path[2] == 'series' ) {
						$result = lf_request_custom(
							'select s.* from series s left outer join mediaobject m '
							.'on s.series_id = m.series_id', $filter, $limit, $order );
						if ( $result && $detail ) {
							for ( $i = 0; $i < count($result); $i++ ) {
								$mobjs = array();
								$mpkgs = array();
								foreach ( lf_request_custom( 'select object_id, cou_id from mediaobject '
									.'where series_id = '.$result[$i]['series_id'] ) as $m ) {
										$mobjs[] = $m['object_id'];
										$mpkgs[ $m['cou_id'] ] = '';
								}
								$result[$i]['mediaobject'] = $mobjs;
								$result[$i]['mediapackage'] = array_keys( $mpkgs );
								$lects = array();
								foreach ( lf_request_custom( 'select lecturer_id from lecturer_series '
									.'where series_id = '.$result[$i]['series_id'] ) as $l ) {
									$lects[] = $l['lecturer_id'];
								}
								$result[$i]['lecturer'] = $lects;
							}
						}
						return $result;
					}
				}


			case 'mediapackage':
				$result = null;
				if ( $cnt == 1 ) {
					return lf_request_table( 'mediapackage', array( '*' ), $filter, $limit, $order );
				} elseif ( $cnt == 2 && is_numeric( $path[1] ) ) {
					$f = array( 'o' => 'eq', 'k' => 'm.series_id', 'v' => intval($path[1]) );
					$filter = $filter ? array( 'o' => 'and', 'p' => array( $filter, $f ) ) : $f;
					return lf_request_table( 'mediapackage m', array( '*' ), $filter, $limit, $order );
				} elseif ( is_numeric( $path[1] ) ) {
					$f1 = array( 'o' => 'eq', 'k' => 'm.series_id', 'v' => intval($path[1]) );
					$f2 = array( 'o' => 'eq', 'k' => 'm.cou_id', 'v' => mysql_escape_string($path[2]) );
					$f3 = array( 'o' => 'and', 'p' => array( $f1, $f2 ) );
					$filter = $filter ? array( 'o' => 'and', 'p' => array( $filter, $f1, $f2 ) ) : $f3;
					if ( $cnt == 3 ) {
						return lf_request_table( 'mediapackage m', array( '*' ), $filter, $limit, $order );
					} elseif ( $cnt == 4 && $path[3] == 'mediaobject' ) {
						return lf_request_custom( 
							'select o.object_id, o.lrs_object_id, o.format_id, '
							.'o.url, o.memory_size, o.thumbnail_url, o.preview_url, '
							.'o.image_url, o.duration, o.location '
							.'from mediaobject o left outer join mediapackage m '
							.'on ( m.series_id = o.series_id and m.cou_id = o.cou_id ) ', $filter, $limit, $order );
					} elseif ( $cnt == 4 && $path[3] == 'series' ) {
						$result = lf_request_custom(
							'select s.* from series s left outer join mediapackage m '
							.'on s.series_id = m.series_id', $filter, $limit, $order );
						if ( $result && $detail ) {
							for ( $i = 0; $i < count($result); $i++ ) {
								$mobjs = array();
								$mpkgs = array();
								foreach ( lf_request_custom( 'select object_id, cou_id from mediaobject '
									.'where series_id = '.$result[$i]['series_id'] ) as $m ) {
										$mobjs[] = $m['object_id'];
										$mpkgs[ $m['cou_id'] ] = '';
								}
								$result[$i]['mediaobject'] = $mobjs;
								$result[$i]['mediapackage'] = array_keys( $mpkgs );
								$lects = array();
								foreach ( lf_request_custom( 'select lecturer_id from lecturer_series '
									.'where series_id = '.$result[$i]['series_id'] ) as $l ) {
									$lects[] = $l['lecturer_id'];
								}
								$result[$i]['lecturer'] = $lects;
							}
						}
						return $result;
					}
				}
				if ( $result ) {
					for ( $i = 0; $i < count($result); $i++ ) {
						$result[$i]['mediaobject'] =  lf_request_custom( 
							'select object_id, lrs_object_id, format_id, url, preview_url '
							.'from mediaobject where cou_id = "'.$result[$i]['cou_id'].'"' );
					}
				}
				return $result;

		}
	}

}


?>
