<?php
/**
 * FusionForge PostgreSQL connection layer
 *
 * Copyright 1999-2001, VA Linux Systems, Inc.
 * Copyright 2002, GForge, LLC
 * Copyright 2009, Roland Mas
 * Copyright (c) 2011, 2012
 *	Thorsten Glaser <t.glaser@tarent.de>
 *
 * This file is part of FusionForge. FusionForge is free software;
 * you can redistribute it and/or modify it under the terms of the
 * GNU General Public License as published by the Free Software
 * Foundation; either version 2 of the Licence, or (at your option)
 * any later version.
 *
 * FusionForge is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along
 * with FusionForge; if not, write to the Free Software Foundation, Inc.,
 * 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301 USA.
 */

/**
 * pg_connectstring() - builds a postgres connection string.
 * Combines the supplied arguments into a valid, specific, postgresql
 * connection string. It only includes the host and port options
 * if specified. Without those options, it will use the unix domain
 * sockets to connect to the postgres server on the local machine.
 *
 * @author	Graham Batty graham@sandworm.ca
 * @param	dbname		The database to connect to. Required.
 * @param	user		The username used to connect. Required
 * @param	password	The password used to connect
 * @param	host		The hostname to connect to, if not localhost
 * @param	port		The port to connect to, if not 5432
 * @return	string		The connection string to pass to pg_connect()
 * @date	2003-02-12
 */
function pg_connectstring($dbname, $user, $password = "", $host = "", $port = "") {
	if ($dbname != ""){
		$string = "dbname=$dbname";
	} else {
		$string = "dbname=gforge";
	}
	if ($user != "")
		$string .= " user=$user";
	if ($password != "")
		$string .= " password=$password";
	if ($host != "") {
		$string .= " host=$host";
	}
	if ($port != "") {
		$string .= " port=$port";
	}
	return $string;
}


/**
 *  db_connect() - Connect to the database
 *  Notice the global vars that must be set up
 *  Sets up a global $gfconn variable which is used
 *  in other functions in this library.
 */
function db_connect() {
	global $gfconn,$sys_db_use_replication,$sys_dbreaddb,$sys_dbreadhost;

	//
	//	Connect to primary database
	//
	if (function_exists("pg_pconnect")) {
		$gfconn = pg_pconnect(pg_connectstring(forge_get_config('database_name'), forge_get_config('database_user'), forge_get_config('database_password'), forge_get_config('database_host'), forge_get_config('database_port')));
		if (!$gfconn) {
			print forge_get_config ('forge_name')." Could Not Connect to Database: ".db_error();
			exit;
		}
	} else {
		print("function pg_pconnect doesn't exist: no postgresql interface");
		exit;
	}

	//
	//	If any replication is configured, connect
	//
	if ($sys_db_use_replication) {
		$gfconn2 = pg_pconnect(pg_connectstring($sys_dbreaddb, forge_get_config('database_user'), forge_get_config('database_password'), $sys_dbreadhost, $sys_dbreadport));
	} else {
		$gfconn2 = $gfconn;
	}

	//
	//	Now map the physical database connections to the
	//	"virtual" list that is used to distribute load in db_query()
	//
	define('SYS_DB_PRIMARY', $gfconn);
	define('SYS_DB_STATS', $gfconn2);
	define('SYS_DB_TROVE', $gfconn2);
	define('SYS_DB_SEARCH', $gfconn2);

	$res = db_query_params ('SELECT set_config($1, $2, false)', 
				array('default_text_search_config',
				      'simple'));

	// Register top-level "finally" handler to abort current
	// transaction in case of error
	register_shutdown_function("system_cleanup");
}

/**
 *  db_connect_if_needed() - Set up the DB connection if it's unset
 */
function db_connect_if_needed() {
        global $gfconn;
        if (!isset ($gfconn)) {
                db_connect();
        }
}

function db_switcher($dbserver = NULL) {
	switch ($dbserver) {
		case NULL:
		case 'SYS_DB_PRIMARY': {
			$dbconn = SYS_DB_PRIMARY;
			break;
		}
		case 'SYS_DB_STATS': {
			$dbconn = SYS_DB_STATS;
			break;
		}
		case 'SYS_DB_TROVE': {
			$dbconn = SYS_DB_TROVE;
			break;
		}
		case 'SYS_DB_SEARCH': {
			$dbconn = SYS_DB_SEARCH;
			break;
		}
		default: {
			// Cope with $dbserver already being a connection
			if (pg_dbname($dbserver)) {
				$dbconn = $dbserver;
			} else {
				$dbconn = SYS_DB_PRIMARY;
			}
		}
	}

	return $dbconn;
}

/**
 *  db_query_from_file() - Query the database, from a file.
 *
 *  @param string File that contains the SQL statements.
 *  @param int How many rows do you want returned.
 *  @param int Of matching rows, return only rows starting here.
 *  @param int ability to spread load to multiple db servers.
 *  @return int result set handle.
 */
function db_query_from_file($file,$limit='-1',$offset=0,$dbserver=NULL) {
	global $sysdebug_dbquery, $sysdebug_dberrors;

	db_connect_if_needed();
	$dbconn = db_switcher($dbserver) ;

	global $QUERY_COUNT;
	$QUERY_COUNT++;

	$qstring = file_get_contents($file);
	if (!$qstring) {
		if ($sysdebug_dbquery) {
			ffDebug("database",
			    "aborted call of db_query_from_file():",
			    "Cannot read file: " . $file .
			    "\n\n" . debug_string_backtrace());
		} else if ($sysdebug_dberrors) {
			ffDebug("database", "db_query_from_file() aborted (" .
			    "Cannot read file: " . $file . ")");
		} else {
			error_log("db_query_from_file(): Cannot read file: " . $file);
		}
		return false;
	}
	if (!$limit || !is_numeric($limit) || $limit < 0) {
		$limit=0;
	}
	if ($limit > 0) {
		if (!$offset || !is_numeric($offset) || $offset < 0) {
			$offset=0;
		}
		$qstring=$qstring." LIMIT $limit OFFSET $offset";
	}
	$res = @pg_query($dbconn, $qstring);
	if ($res) {
		if ($sysdebug_dbquery) {
			ffDebug("trace",
			    "successful call of db_query_from_file(), SQL: " .
			    $qstring, debug_string_backtrace());
		}
	} else if ($sysdebug_dbquery || $sysdebug_dberrors) {
		ffDebug("database", "db_query_from_file() failed (" .
		    db_error($dbserver) . "), SQL: " . $qstring,
		    $sysdebug_dbquery ? debug_string_backtrace() : false);
	} else {
		error_log('SQL: ' . preg_replace('/\n\t+/', ' ', $qstring));
		error_log('SQL> ' . db_error($dbserver));
	}
	return $res;
}

/**
 *  db_query_params() - Query the database, with parameters
 *
 *  @param text SQL statement.
 *  @param array parameters
 *  @param int How many rows do you want returned.
 *  @param int Of matching rows, return only rows starting here.
 *	@param int ability to spread load to multiple db servers.
 *	@return int result set handle.
 */
function db_query_params($qstring, $params, $limit = '-1', $offset = 0, $dbserver = NULL) {
	global $sysdebug_dbquery, $sysdebug_dberrors;

	db_connect_if_needed();
	$dbconn = db_switcher($dbserver) ;

	global $QUERY_COUNT;
	$QUERY_COUNT++;

	if (!$limit || !is_numeric($limit) || $limit < 0) {
		$limit=0;
	}
	if ($limit > 0) {
		if (!$offset || !is_numeric($offset) || $offset < 0) {
			$offset=0;
		}
		$qstring=$qstring." LIMIT $limit OFFSET $offset";
	}

	$res = @pg_query_params($dbconn, $qstring, $params);
	if ($res) {
		if ($sysdebug_dbquery) {
			ffDebug("trace",
			    "successful call of db_query_params():",
			    debug_string_backtrace());
		}
	} else if ($sysdebug_dbquery) {
		ffDebug("database", "failed call of db_query_params():",
		    db_error($dbserver) . "\n\n" . debug_string_backtrace());
	} else if ($sysdebug_dberrors) {
		ffDebug("database", "db_query_params() failed (" .
		    db_error($dbserver) . "), SQL: " . $qstring,
		    print_r(array("params" => $params), 1));
	} else {
		error_log('SQL: ' . preg_replace('/\n\t+/', ' ', $qstring));
		error_log('SQL> ' . db_error($dbserver));
	}
	return $res;
}

/**
 *  db_query_qpa() - Query the database, with a query+params array
 *
 *  @param array array(query, array(parameters...))
 *  @param int How many rows do you want returned.
 *  @param int Of matching rows, return only rows starting here.
 *  @param int ability to spread load to multiple db servers.
 *  @return int result set handle.
 */
function db_query_qpa ($qpa,$limit='-1',$offset=0,$dbserver=NULL) {
	$sql = $qpa[0] ;
	$params = $qpa[1] ;
	return db_query_params ($sql, $params, $limit, $offset, $dbserver) ;
}

/**
 *  db_mquery() - Query the database.
 *
 *  @deprecated since 4.8. Use db_query_params() instead!
 *
 *  @param text SQL statement.
 *  @param int How many rows do you want returned.
 *  @param int Of matching rows, return only rows starting here.
 *	@param int ability to spread load to multiple db servers.
 *	@return int result set handle.
 */
function db_mquery($qstring,$limit='-1',$offset=0,$dbserver=NULL) {
	return db_query($qstring, $limit, $offset, $dbserver);
}

/**
 *  db_more_results() - Check if there are more unprocessed results.
 *
 *	@return bool true if there are more results..
 */
function db_more_results() {
	return false;
}

/**
 *  db_next_result() - Get the next result from query with multiple statements.
 *
 *  @param		string	SQL statement
 *  @param		int		How many rows do you want returned
 *  @param		int		Of matching rows, return only rows starting here
 */
function db_next_result() {
	return NULL;
}

/* Current transaction level, private variable */
/* FIXME: Having scalar variable for transaction level is
   no longer correct after multiple database (dbservers) support
   introduction. However, it is true that in one given PHP
   script, at most one db is modified, so this works for now. */
$_sys_db_transaction_level = 0;

/**
 *	db_begin() - Begin a transaction.
 *
 *	@param	constant	Database server ('SYS_DB_PRIMARY', 'SYS_DB_STATS', 'SYS_DB_TROVE', 'SYS_DB_SEARCH')
 *	@return	true.
 */
function db_begin($dbserver = NULL) {
	global $_sys_db_transaction_level;

	// start database transaction only for the top-level
	// programmatical transaction
	$_sys_db_transaction_level++;
	if ($_sys_db_transaction_level == 1) {
		return db_query_params ("BEGIN WORK", array(), -1, 0, $dbserver);
	}

	return true;
}

/**
 *	db_commit() - Commit a transaction.
 *
 *	@param	constant	Database server ('SYS_DB_PRIMARY', 'SYS_DB_STATS', 'SYS_DB_TROVE', 'SYS_DB_SEARCH')
 *	@return	true on success/false on failure.
 */
function db_commit($dbserver = NULL) {
	global $_sys_db_transaction_level;

	// check for transaction stack underflow
	if ($_sys_db_transaction_level == 0) {
		echo "COMMIT underflow<br />";
		return false;
	}

	// commit database transaction only when top-level
	// programmatical transaction ends
	$_sys_db_transaction_level--;
	if ($_sys_db_transaction_level == 0) {
		return db_query_params ("COMMIT", array(), -1, 0, $dbserver);
	}

	return true;
}

/**
 *	db_rollback() - Rollback a transaction.
 *
 *	@param	constant	Database server ('SYS_DB_PRIMARY', 'SYS_DB_STATS', 'SYS_DB_TROVE', 'SYS_DB_SEARCH')
 *	@return true on success/false on failure.
 */
function db_rollback($dbserver = NULL) {
	global $_sys_db_transaction_level;

	// check for transaction stack underflow
	if ($_sys_db_transaction_level == 0) {
		echo "ROLLBACK underflow<br />";
		return false;
	}

	// rollback database transaction only when top-level
	// programmatical transaction ends
	$_sys_db_transaction_level--;
	if ($_sys_db_transaction_level == 0) {
		return db_query_params ("ROLLBACK", array(), -1, 0, $dbserver);
	}

	return true;
}

/**
 *	db_numrows() - Returns the number of rows in this result set.
 *
 *	@param		int		Query result set handle.
 *	@return int number of rows.
 */

function db_numrows($qhandle) {
	return @pg_num_rows($qhandle);
}

/**
 *  db_free_result() - Frees a database result properly.
 *
 *	@param		int		Query result set handle.
 */
function db_free_result($qhandle) {
	return @pg_free_result($qhandle);
}

/**
 *  db_result() - Returns a field from a result set.
 *
 *	@param		int		Query result set handle.
 *  @param		integer Row number.
 *  @param		string	Field name.
 *	@return contents of field from database.
 */
function db_result($qhandle,$row,$field) {
	return @pg_fetch_result($qhandle,$row,$field);
}

/**
 *  db_numfields() - Returns the number of fields in this result set.
 *
 *	@param		int		Query result set handle.
 */
function db_numfields($lhandle) {
	return @pg_num_fields($lhandle);
}

/**
 *  db_fieldname() - Returns the name of a particular field in the result set
 *
 *	@param		int		Query result set handle.
 *  @param		int		Column number.
 *	@return text name of the field.
 */
function db_fieldname($lhandle,$fnumber) {
	return @pg_field_name($lhandle,$fnumber);
}

/**
 *  db_affected_rows() - Returns the number of rows changed in the last query.
 *
 *	@param		int		Query result set handle.
 *	@return int number of affected rows.
 */
function db_affected_rows($qhandle) {
	return @pg_affected_rows($qhandle);
}

/**
 *  db_fetch_array() - Returns an associative array from
 *  the current row of this database result
 *
 *	@param		int		Query result set handle.
 *	@return associative array of fieldname/value key pairs.
 */
function db_fetch_array($qhandle, $row=false) {
	return @pg_fetch_array($qhandle);
}

/**
 *  db_fetch_array_by_row() - Returns an associative array from
 *  the given row of this database result
 *
 *	@param		int		Query result set handle.
 *	@param		int		Given row to fetch
 *	@return associative array of fieldname/value key pairs.
 */
function db_fetch_array_by_row($qhandle, $row) {
	return @pg_fetch_array($qhandle, $row);
}

/**
 *  db_insertid() - Returns the last primary key from an insert.
 *
 *	@param		int		Query result set handle.
 *  @param		string	table_name is the name of the table you inserted into.
 *  @param		string	pkey_field_name is the field name of the primary key.
 *  @param		string	Server to which original query was made
 *	@return int id of the primary key or 0 on failure.
 */
function db_insertid($qhandle,$table_name,$pkey_field_name,$dbserver=NULL) {
	$sql = "SELECT max($pkey_field_name) AS id FROM $table_name";
	$res = db_query_params ($sql, array(), -1, 0, $dbserver);
	if (db_numrows($res) >0) {
		return db_result($res,0,'id');
	} else {
		return 0;
	}
}

/**
 *  db_error() - Returns the last error from the database.
 *
 *  @param		constant		Database server ('DB_PRIMARY', 'DB_STATS', 'DB_TROVE', 'DB_SEARCH')
 *	@return text error message.
 */
function db_error($dbserver=NULL) {
	$dbconn = db_switcher($dbserver);

	return pg_last_error($dbconn);
}

/**
 *	system_cleanup() - In the future, we may wish to do a number
 *	of cleanup functions at script termination.
 *
 *	For now, we just abort any in-process transaction.
 */
function system_cleanup() {
	global $_sys_db_transaction_level;
	if ($_sys_db_transaction_level > 0) {
		echo "Open transaction detected!!!";
		db_query_params ("ROLLBACK", array ());
	}
}

function db_check_foo_exists ($name, $t) {
	$res = db_query_params ('SELECT COUNT(*) FROM pg_class WHERE relname=$1 and relkind=$2',
				array ($name, $t));
	if (!$res) {
		echo db_error();
		return false;
	}
	$count = db_result($res,0,0);
	return ($count != 0);
}

function db_check_table_exists ($name) {
	return db_check_foo_exists($name, 'r');
}

function db_check_sequence_exists ($name) {
	return db_check_foo_exists($name, 'S');
}

function db_check_view_exists ($name) {
	return db_check_foo_exists($name, 'v');
}

function db_check_index_exists ($name) {
	return db_check_foo_exists($name, 'i');
}

function db_drop_table_if_exists ($name, $cascade=false) {
	if (!db_check_table_exists($name)) {
		return true;
	}
	$sql = "DROP TABLE $name";
	if ($cascade) {
		$sql .= " CASCADE";
	}
	$res = db_query_params ($sql, array ());
	if (!$res) {
		echo db_error();
		return false;
	}
	return true;
}

function db_drop_sequence_if_exists ($name) {
	if (!db_check_sequence_exists($name)) {
		return;
	}
	$sql = "DROP SEQUENCE $name";
	$res = db_query_params ($sql, array ());
	if (!$res) {
		echo db_error();
		return false;
	}
	return true;
}

function db_drop_view_if_exists ($name) {
	if (!db_check_view_exists($name)) {
		return true;
	}
	$sql = "DROP VIEW $name";
	$res = db_query_params ($sql, array ());
	if (!$res) {
		echo db_error();
		return false;
	}
	return true;
}

function db_drop_index_if_exists ($name) {
	if (!db_check_index_exists($name)) {
		return true;
	}
	$sql = "DROP INDEX $name";
	$res = db_query_params ($sql, array ());
	if (!$res) {
		echo db_error();
		return false;
	}
	return true;
}

function db_bump_sequence_to ($seqname, $target) {
	$current = -1;
	while ($current < $target) {
		$res = db_query_params('SELECT nextval($1)',
				       array($seqname));
		if (!$res || db_numrows($res) != 1) {
			echo db_error();
			return false;
		}
		$current = db_result($res,0,0);
	}
}

function db_int_array_to_any_clause ($arr) {
	$arr2 = array () ;
	foreach ($arr as $cur) {
		if (is_numeric($cur)) {
			$arr2[] = $cur ;
		}
	}
	$res = '{' . implode (',', $arr2) . '}' ;
	return $res ;
}

function db_string_array_to_any_clause ($arr) {
	$arr2 = array () ;
	foreach ($arr as $cur) {
		$arr2[] = pg_escape_string ($cur) ;
	}
	$res = '{"' . implode ('","', $arr2) . '"}' ;
	return $res ;
}

/**
 * db_construct_qpa() - Constructs a query+params array to be used by db_query_qpa()
 * Can be called several times in a row to extend the query, until db_query_qpa will be finally invoked.
 *  
 * @param array $old_qpa array(SQL query, array(parameters...), oldmax) of previous calls
 * @param string $new_sql SQL instructions added to the query
 * @param array $new_params new params matching the new query instructions
 * @return array  array(SQL query, array(parameters...), newmax)
 */
function db_construct_qpa ($old_qpa = false, $new_sql = '', $new_params = array ()) {
	
	// can be invoked for the first time, starting with no previous query
	if (!is_array($old_qpa) || count ($old_qpa) < 3) {
		$old_qpa = array ('', array(), 0) ;
	}
	$old_sql = $old_qpa[0] ;
	$old_params = $old_qpa[1] ;
	$old_max = $old_qpa[2] ;

	$sql = $old_sql ;
	$params = $old_params ;
	$max = $old_max ;

	// renumber the $n params substitution placeholders to be able to concatenate
	foreach ($new_params as $index => $value) {
		$i = count ($new_params) - $index ;
		$new_sql = preg_replace ('/\\$'.$i.'(?!\d)/', '$_'.($i + $old_max), $new_sql) ;
		$params[] = $value ;
		$max++ ;
	}
	$new_sql = str_replace ('$_', '$', $new_sql) ;

	$sql .= $new_sql ;

	return array ($sql, $params, $max) ;
}

function db_join_qpa ($old_qpa = false, $new_qpa = false) {
	return db_construct_qpa ($old_qpa, $new_qpa[0], $new_qpa[1]) ;
}

function db_query_to_string ($sql, $params = array()) {
	$sql = preg_replace('/\n/',' ',$sql);
	$sql = preg_replace('/\t/',' ',$sql);
	$sql = preg_replace('/\s+/',' ',$sql);
	foreach ($params as $index => $value) {
		$sql = preg_replace('/\\$'.($index+1).'(?!\d)/', "'".$value."'", $sql);
	}
	return $sql;
}

function db_qpa_to_string ($qpa) {
	return db_query_to_string($qpa[0], $qpa[1]);
}

// Local Variables:
// mode: php
// c-file-style: "bsd"
// End:

?>