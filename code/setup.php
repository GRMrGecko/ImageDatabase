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
	databaseQuery("INSERT INTO settings (name, value) VALUES ('db_version',%s)", $_MGM['version']);
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
} else {
	$info = databaseFetchAssoc($result);
	if ($info['value']<=1) {
		if (isset($_REQUEST['update_user'])) {
			$email = (isset($_REQUEST['email']) ? trim($_REQUEST['email']) : "");
			$password = (isset($_REQUEST['password']) ? trim($_REQUEST['password']) : "");
	
			$result = databaseQuery("SELECT * FROM users WHERE email=%s AND level!=0", $email);
			$user = databaseFetchAssoc($result);
			if ($user==NULL) {
				echo "Invalid login credentials.";
			} else if ($user['level']!=1) {
				echo "Account is not an administrator account.";
			} else {
				$salt = substr($user['password'], 0, 12);
				$epassword = $salt.hash("sha512", $salt.hash("sha512", $password));
				echo $epassword;
				if ($epassword!=$user['password']) {
					echo "Invalid login credentials.";
				} else {
					$epassword = $salt.hashPassword($password,hex2bin($salt));
					databaseQuery("UPDATE users SET password=%s WHERE email=%s", $epassword, $email);
					databaseQuery("UPDATE settings SET value=%s WHERE name='db_version'", $_MGM['version']);
					header("location: ".generateURL());
					exit();
				}
			}
		}
		
		require_once("header.php");
		?>
		<h1>Admin Password Update</h1>
		<p>Passwords hash system has been changed. Please enter an administrator email and password to update your account. You would be required to update any other account's password once you have completed this step as their account will not be able to function.</p>
		<form action="<?=generateURL()?>" method="POST">
		<input type="hidden" name="update_user" value="true" />
		<input type="text" placeholder="Email" name="email" /><br />
		<input type="password" placeholder="Password" name="password" /><br />
		<input type="submit" value="Fix Password" class="btn" />
		</form>
		<?
		require_once("footer.php");
		exit();
	}
}

if (isset($_REQUEST['create_user'])) {
	$email = (isset($_REQUEST['email']) ? trim($_REQUEST['email']) : "");
	$password = (isset($_REQUEST['password']) ? trim($_REQUEST['password']) : "");
	if ($_REQUEST['create_user']=="first") {
		$result = databaseQuery("SELECT COUNT(*) AS count FROM users");
		$count = databaseFetchAssoc($result);
		if ($count['count']==0 && !empty($email) && !empty($password)) {
			$salt = substr(sha1(rand()),0,12);
			$epassword = $salt.hashPassword($password,hex2bin($salt));
			databaseQuery("INSERT INTO users (email, password, time, level) VALUES (%s,%s,%d,1)", $email, $epassword, $_MGM['time']);
			setcookie("{$_MGM['CookiePrefix']}user_email", $email, $_MGM['time']+31536000, $_MGM['CookiePath'], $_MGM['CookieDomain']);
			setcookie("{$_MGM['CookiePrefix']}user_password", hash("sha512", $epassword.$_MGM['time']), $_MGM['time']+31536000, $_MGM['CookiePath'], $_MGM['CookieDomain']);
			header("location: ".generateURL());
			exit();
		}
	}
}
?>