<?php
//
//  Copyright (c) 2013 Mr. Gecko's Media (James Coleman). http://mrgeckosmedia.com/
//
//  Permission to use, copy, modify, and/or distribute this software for any purpose
//  with or without fee is hereby granted, provided that the above copyright notice
//  and this permission notice appear in all copies.
//
//  THE SOFTWARE IS PROVIDED "AS IS" AND THE AUTHOR DISCLAIMS ALL WARRANTIES WITH
//  REGARD TO THIS SOFTWARE INCLUDING ALL IMPLIED WARRANTIES OF MERCHANTABILITY AND
//  FITNESS. IN NO EVENT SHALL THE AUTHOR BE LIABLE FOR ANY SPECIAL, DIRECT, INDIRECT,
//  OR CONSEQUENTIAL DAMAGES OR ANY DAMAGES WHATSOEVER RESULTING FROM LOSS OF USE,
//  DATA OR PROFITS, WHETHER IN AN ACTION OF CONTRACT, NEGLIGENCE OR OTHER TORTIOUS
//  ACTION, ARISING OUT OF OR IN CONNECTION WITH THE USE OR PERFORMANCE OF THIS SOFTWARE.
//
function connectToDatabase() {
	global $_MGM;
	if (isset($_MGM['DBConnection'])) closeDatabase();
	$_MGM['DBConnection'] = NULL;
	$_MGM['DBConnection'] = new PDO("sqlite:".$_MGM['DBName']);
	$_MGM['DBConnection']->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
	if ($_MGM['DBPersistent'])
		$_MGM['DBConnection']->setAttribute(PDO::ATTR_PERSISTENT, TRUE);
	if ($_MGM['DBConnection']==NULL) error("Database Connection Failed");
}
function closeDatabase() {
	global $_MGM;
	if (isset($_MGM['DBConnection'])) {
		$_MGM['DBConnection'] = NULL;
	}
}
function escapeString($theString) {
	global $_MGM;
	return $_MGM['DBConnection']->quote($theString);
}
function quoteObject($theObject) {
	global $_MGM;
	if (is_null($theObject)) {
		return "NULL";
	} else if (is_string($theObject)) {
		return escapeString($theObject);
	} else if (is_float($theObject) || is_integer($theObject)) {
		return $theObject;
	} else if (is_bool($theObject)) {
		return ($theObject ? 1 : 0);
	}
	return "NULL";
}
function databaseQuery($format) {
	global $_MGM;
	$result = NULL;
	try {
		if (isset($_MGM['DBConnection'])) {
			$args = func_get_args();
			array_shift($args);
			$args = array_map("quoteObject", $args);
			$query = vsprintf($format, $args);
			
			$result = $_MGM['DBConnection']->query($query);
		}
		//if ($result==NULL) error("Failed to run query on database");
	} catch (Exception $e) {
		//echo $e->getMessage()."<br />\n";
		//error("Failed to run query on database");
	}
	return $result;
}
function databaseRowCount($theResult) {
	global $_MGM;
	if ($theResult==NULL)
		return 0;
	return $theResult->rowCount();
}
function databaseFieldCount($theResult) {
	global $_MGM;
	if ($theResult==NULL)
		return 0;
	return $theResult->columnCount();
}
function databaseLastID() {
	global $_MGM;
	$result = 0;
	if (isset($_MGM['DBConnection'])) {
		$result = $_MGM['DBConnection']->lastInsertId();
	}
	return $result;
}
function databaseFetch($theResult) {
	global $_MGM;
	return $theResult->fetch();
}
function databaseFetchNum($theResult) {
	global $_MGM;
	return $theResult->fetch(PDO::FETCH_NUM);
}
function databaseFetchAssoc($theResult) {
	global $_MGM;
	return $theResult->fetch(PDO::FETCH_ASSOC);
}
function databaseResultSeek($theResult, $theLocation) {
	global $_MGM;
	return false;
}
function databaseFreeResult($theResult) {
	global $_MGM;
	$theResult = NULL;
}
?>
