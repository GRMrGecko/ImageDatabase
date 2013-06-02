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
if (isset($_MGM['user']) && $_MGM['user']['level']==1 && $_MGM['path'][1]=="users") {
	if ($_MGM['path'][2]=="list") {
		$results = databaseQuery("SELECT * FROM users");
		while ($result = databaseFetchAssoc($results)) {
			$level = "Normal";
			if ($result['level']==0)
				$level = "Disabled";
			if ($result['level']==1)
				$level = "Administrator";
			if ($result['level']==2)
				$level = "Moderator";
			if ($result['level']==3)
				$level = "Tagger";
			?><tr><td class="id"><?=$result['docid']?></td><td class="email"><?=$result['email']?></td><td class="level" value="<?=$result['level']?>"><?=$level?></td></tr><?
		}
	}
	if ($_MGM['path'][2]=="update") {
		$id = (isset($_REQUEST['id']) ? trim($_REQUEST['id']) : "");
		$email = (isset($_REQUEST['email']) ? trim($_REQUEST['email']) : "");
		$password = (isset($_REQUEST['password']) ? trim($_REQUEST['password']) : "");
		$level = (isset($_REQUEST['level']) ? trim($_REQUEST['level']) : "");
		$results = databaseQuery("SELECT * FROM users WHERE docid=%s", $id);
		$result = databaseFetchAssoc($results);
		if ($result!=NULL) {
			if (empty($email))
				$email = $result['email'];
			$epassword = $result['password'];
			if (!empty($password)) {
				$salt = substr(sha1(rand()),0,12);
				$epassword = $salt.hash("sha512", $salt.hash("sha512", $password));
			}
			if ($level=="")
				$level = $result['level'];
			databaseQuery("UPDATE users SET email=%s,password=%s,level=%s WHERE docid=%s", $email, $epassword, $level, $id);
		}
	}
	if ($_MGM['path'][2]=="create") {
		$email = (isset($_REQUEST['email']) ? trim($_REQUEST['email']) : "");
		$password = (isset($_REQUEST['password']) ? trim($_REQUEST['password']) : "");
		$level = (isset($_REQUEST['level']) ? trim($_REQUEST['level']) : "");
		if (!empty($email) && !empty($level)) {
			$salt = substr(sha1(rand()),0,12);
			$epassword = $salt.hash("sha512", $salt.hash("sha512", $password));
			databaseQuery("INSERT INTO users (email, password, time, level) VALUES (%s,%s,%d,%s)", $email, $epassword, $_MGM['time'], $level);
		}
	}
	exit();
}
if (isset($_MGM['user']) && $_MGM['path'][1]=="save_tags") {
	$hash = (isset($_REQUEST['hash']) ? trim($_REQUEST['hash']) : "");
	$tags = (isset($_REQUEST['tags']) ? trim($_REQUEST['tags']) : "");
	$results = databaseQuery("SELECT * FROM images WHERE hash=%s", $hash);
	$result = databaseFetchAssoc($results);
	if ($result!=NULL) {
		if ($_MGM['user']['level']<=3 || $_MGM['user']['docid']==$result['user_id'])
			databaseQuery("UPDATE images SET tags=%s WHERE hash=%s", $tags, $hash);
	}
	exit();
}
if (isset($_MGM['user']) && $_MGM['user']['level']<=2 && $_MGM['path'][1]=="delete") {
	$hash = (isset($_REQUEST['hash']) ? trim($_REQUEST['hash']) : "");
	$results = databaseQuery("SELECT * FROM images WHERE hash=%s", $hash);
	$result = databaseFetchAssoc($results);
	if ($result!=NULL) {
		unlink("./data/".$result['hash'].".".$result['extension']);
		unlink("./thumbs/".$result['hash'].".".$result['extension']);
		databaseQuery("DELETE FROM images WHERE hash=%s", $hash);
	}
	exit();
}
if (isset($_MGM['user']) && $_MGM['path'][1]=="tagless") {
	$results = NULL;
	if ($_MGM['user']['level']<=3)
		$results = databaseQuery("SELECT * FROM images WHERE tags='' ORDER BY time ASC LIMIT 1");
	else
		$results = databaseQuery("SELECT * FROM images WHERE tags='' AND user=%s ORDER BY time ASC LIMIT 1", $_MGM['user']['docid']);
	$result = databaseFetchAssoc($results);
	if ($result!=NULL) {
		?>
		<span id="image" hash="<?=$result['hash']?>" user="<?=$result['user_id']?>" extension="<?=$result['extension']?>" tags="<?=$result['tags']?>" image_width="<?=$result['width']?>" image_height="<?=$result['height']?>" file_size="<?=$result['file_size']?>" time="<?=$result['time']?>" original="<?=generateURL("data/".$result['hash'].".".$result['extension'])?>"></span>
		<?
	}
	exit();
}
if ($_MGM['path'][1]=="hash") {
	$results = databaseQuery("SELECT * FROM images WHERE hash like %s ORDER BY time ASC LIMIT 1", $_MGM['path'][2]);
	$result = databaseFetchAssoc($results);
	if ($result!=NULL) {
		?>
		<span id="image" hash="<?=$result['hash']?>" user="<?=$result['user_id']?>" extension="<?=$result['extension']?>" tags="<?=$result['tags']?>" image_width="<?=$result['width']?>" image_height="<?=$result['height']?>" file_size="<?=$result['file_size']?>" time="<?=$result['time']?>" original="<?=generateURL("data/".$result['hash'].".".$result['extension'])?>"></span>
		<?
	}
	exit();
}
$limit = 96;
$page = (intval($_MGM['path'][1])==0 || empty($_MGM['path'][1]) ? 1 : intval($_MGM['path'][1]))-1;
$offset = $limit*$page;

$filter = (isset($_REQUEST['filter']) ? trim($_REQUEST['filter']) : "");
$results = NULL;
if (!empty($filter))
	$results = databaseQuery("SELECT * FROM images WHERE images MATCH %s LIMIT %d,%d", $filter, $offset, $limit);
else
	$results = databaseQuery("SELECT * FROM images ORDER BY time DESC LIMIT %d,%d", $offset, $limit);
?><div id="content"><?
$count = 0;
while ($result = databaseFetchAssoc($results)) {
	?>
	<span class="image" hash="<?=$result['hash']?>" user="<?=$result['user_id']?>" extension="<?=$result['extension']?>" tags="<?=$result['tags']?>" image_width="<?=$result['width']?>" image_height="<?=$result['height']?>" file_size="<?=$result['file_size']?>" time="<?=$result['time']?>" original="<?=generateURL("data/".$result['hash'].".".$result['extension'])?>"><img src="<?=generateURL("thumbs/".$result['hash'].".".$result['extension'])?>" width="<?=$result['thumb_width']?>" height="<?=$result['thumb_height']?>" /></span>
	<?
	$count++;
}
?>
</div>
<div id="count"><?=$count?></div>
<div id="limit"><?=$limit?></div>
<div id="page"><?=$page?></div>
<div id="offset"><?=$offset?></div>
<div id="next_page"><?=($count==$limit ? $page+2 : "")?></div>
<?
exit();
?>