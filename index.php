<?
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

error_reporting(E_ALL & ~E_NOTICE & ~E_WARNING & ~E_STRICT);

$_MGM = array();
$_MGM['version'] = "2";
$_MGM['DBType'] = "SQLITE"; // MYSQL, POSTGRESQL, SQLITE.
$_MGM['DBPersistent'] = NO;
$_MGM['DBHost'] = "localhost";
$_MGM['DBUser'] = "";
$_MGM['DBPassword'] = "";
$_MGM['DBName'] = "databases/main.db"; // File location for SQLite.
$_MGM['DBPort'] = 0; // 3306 = MySQL Default, 5432 = PostgreSQL Default.
$_MGM['DBPrefix'] = "";
$_MGM['adminEmail'] = "default@domain.com";
require_once("db{$_MGM['DBType']}.php");

$_MGM['imagemagick'] = ""; // Path to ImageMagick bin folder.

putenv("TZ=US/Central");
$_MGM['time'] = time();
$_MGM['domain'] = $_SERVER['HTTP_HOST'];
$_MGM['domainname'] = str_replace("www.", "", $_MGM['domain']);
$_MGM['port'] = $_SERVER['SERVER_PORT'];
$_MGM['ssl'] = ($_MGM['port']==443);

if ($_SERVER['REMOTE_ADDR'])
	$_MGM['ip'] = $_SERVER['REMOTE_ADDR'];
if ($_SERVER['HTTP_PC_REMOTE_ADDR'])	
	$_MGM['ip'] = $_SERVER['HTTP_PC_REMOTE_ADDR'];
if ($_SERVER['HTTP_CLIENT_IP'])
	$_MGM['ip'] = $_SERVER['HTTP_CLIENT_IP'];
if ($_SERVER['HTTP_X_FORWARDED_FOR'])
	$_MGM['ip'] = $_SERVER['HTTP_X_FORWARDED_FOR'];

$_MGM['installPath'] = substr($_SERVER['SCRIPT_NAME'], 0, strlen($_SERVER['SCRIPT_NAME'])-strlen(end(explode("/", $_SERVER['SCRIPT_NAME']))));
if (!isset($_GET['d'])) {
	$tmp = explode("?", substr($_SERVER['REQUEST_URI'], strlen($_MGM['installPath'])));
	$tmp = urldecode($tmp[0]);
	if (substr($tmp, 0, 9)=="index.php")
		$tmp = substr($tmp, 10, strlen($tmp)-10);
	$_MGM['fullPath'] = $tmp;
} else {
	$tmp = $_GET['d'];
	if (substr($tmp, 0, 1)=="/")
		$tmp = substr($tmp, 1, strlen($tmp)-1);
	$_MGM['fullPath'] = $tmp;
}
if (strlen($_MGM['fullPath'])>255) error("The URI you entered is to large");
$_MGM['path'] = explode("/", strtolower($_MGM['fullPath']));

$_MGM['CookiePrefix'] = "";
$_MGM['CookiePath'] = $_MGM['installPath'];
$_MGM['CookieDomain'] = ".".$_MGM['domainname'];

function generateURL($path) {
	global $_MGM;
	return "http".($_MGM['ssl'] ? "s" : "")."://".$_MGM['domain'].(((!$_MGM['ssl'] && $_MGM['port']==80) || ($_MGM['ssl'] && $_MGM['port']==443)) ? "" : ":{$_MGM['port']}").$_MGM['installPath'].$path;
}

function hashPassword($password, $salt) {
	$hashed = hash("sha512", $salt.$password);
	for ($i=0; $i<10000; $i++) {
		$hashed = hash("sha512", $salt.hex2bin($hashed));
	}
	return $hashed;
}

connectToDatabase();

if (file_exists("code/setup.php")) {
	require("code/setup.php");
}

if (isset($_COOKIE["{$_MGM['CookiePrefix']}user_email"])) {
	$result = databaseQuery("SELECT * FROM users WHERE email=%s AND level!=0", $_COOKIE["{$_MGM['CookiePrefix']}user_email"]);
	$user = databaseFetchAssoc($result);
	if ($user!=NULL && hash("sha512", $user['password'].$user['time'])==$_COOKIE["{$_MGM['CookiePrefix']}user_password"]) {
		$_MGM['user'] = $user;
	}
}

if (!isset($_MGM['user']) && $_MGM['path'][0]=="login") {
	require("code/login.php");
}
if (isset($_MGM['user']) && $_MGM['path'][0]=="logout") {
	require("code/logout.php");
}

if ($_MGM['path'][0]=="re-ocr") {
	require("code/re-ocr.php");
}

if (isset($_MGM['user']) && $_MGM['path'][0]=="upload") {
	require("code/upload.php");
}

if ($_MGM['path'][0]=="api") {
	require("code/api.php");
}

if (isset($_MGM['user']) && $_MGM['user']['level']==1 && $_MGM['path'][0]=="users") {
	require("code/users.php");
}

if (isset($_MGM['user']) && $_MGM['path'][0]=="tagless") {
	require("code/tagless.php");
}

if ($_MGM['path'][0]=="hash") {
	require("code/hash.php");
}

if ($_MGM['path'][0]!="") {
	require("code/404.php");
}

require("code/index.php");
?>