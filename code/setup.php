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
$result = databaseQuery("SELECT * FROM settings WHERE name='db_version'");
if ($result==NULL) {
	databaseQuery("CREATE TABLE settings (name TEXT, value TEXT)");
	databaseQuery("INSERT INTO settings (name, value) VALUES ('db_version',%d)", $_MGM['version']);
	databaseQuery("CREATE VIRTUAL TABLE images USING fts3(user_id INTEGER, hash TEXT, extension TEXT, name TEXT, file_size INTEGER, width INTEGER, height INTEGER, thumb_file_size INTEGER, thumb_width INTEGER, thumb_height INTEGER, tags TEXT, external_data TEXT, ocr TEXT, time INTEGER)");
	databaseQuery("CREATE TABLE users (docid INTEGER PRIMARY KEY AUTOINCREMENT, email TEXT, password TEXT, time INTEGER, level INTEGER)");

	require_once("header.php");
	?>
	<form action="<?=generateURL()?>" method="POST">
	<input type="hidden" name="create_user" value="first" />
	<input type="text" placeholder="Email" name="email" /><br />
	<input type="password" placeholder="Password" name="password" /><br />
	<input type="submit" value="Create Admin" class="btn" />
	</form>
	<?
	require_once("footer.php");
	exit();
}

if (isset($_REQUEST['create_user'])) {
	$email = (isset($_REQUEST['email']) ? trim($_REQUEST['email']) : "");
	$password = (isset($_REQUEST['password']) ? trim($_REQUEST['password']) : "");
	if ($_REQUEST['create_user']=="first") {
		$result = databaseQuery("SELECT COUNT(*) AS count FROM users");
		$count = databaseFetchAssoc($result);
		if ($count['count']==0 && !empty($email) && !empty($password)) {
			$salt = substr(sha1(rand()),0,12);
			$epassword = $salt.hash("sha512", $salt.hash("sha512", $password));
			databaseQuery("INSERT INTO users (email, password, time, level) VALUES (%s,%s,%d,1)", $email, $epassword, $_MGM['time']);
			setcookie("{$_MGM['CookiePrefix']}user_email", $email, $_MGM['time']+31536000, $_MGM['CookiePath'], $_MGM['CookieDomain']);
			setcookie("{$_MGM['CookiePrefix']}user_password", hash("sha512", $epassword.$_MGM['time']), $_MGM['time']+31536000, $_MGM['CookiePath'], $_MGM['CookieDomain']);
			header("location: ".generateURL());
			exit();
		}
	}
}
?>