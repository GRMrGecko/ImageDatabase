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
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" lang="en">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
	<title><?=$_MGM['title']?></title>
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<meta name="description" content="">
	<meta name="author" content="">
	
	<link href="<?=$_MGM['installPath']?>css/bootstrap.min.css" rel="stylesheet">
	<style>
	body {
		padding-top: 60px;
	}
	</style>
	<link href="<?=$_MGM['installPath']?>css/bootstrap-responsive.min.css" rel="stylesheet">
	<script type="text/javascript" src="<?=$_MGM['installPath']?>js/jquery.min.js"></script>
	<script type="text/javascript" src="<?=$_MGM['installPath']?>js/bootstrap.min.js"></script>
	<script type="text/javascript" src="<?=$_MGM['installPath']?>js/date.js"></script>
</head>

<body>
	<div class="navbar navbar-inverse navbar-fixed-top">
		<div class="navbar-inner">
			<div class="container">
				<button type="button" class="btn btn-navbar" data-toggle="collapse" data-target=".nav-collapse">
					<span class="icon-bar"></span>
					<span class="icon-bar"></span>
					<span class="icon-bar"></span>
				</button>
				<a class="brand" href="<?=$_MGM['installPath']?>">Image Database</a>
				<div class="nav-collapse collapse">
					<ul class="nav">
						<?if (isset($_MGM['user'])) {?>
							<li><a href="<?=$_MGM['installPath']?>upload">Upload</a></li>
							<li><a href="<?=$_MGM['installPath']?>tagless/">Tagless Images</a></li>
							<?if ($_MGM['user']['level']==1) {?>
								<li><a href="<?=$_MGM['installPath']?>users/">User Management</a></li>
							<?}?>
						<?}?>
						<?if (isset($_MGM['user'])) {?>
							<li><a href="<?=$_MGM['installPath']?>logout">Logout</a></li>
						<?} else {?>
							<li><a href="<?=$_MGM['installPath']?>login">Login</a></li>
						<?}?>
					</ul>
					<?if ($_MGM['path'][0]=="") {?>
		            <form class="navbar-form pull-right" id="filter_form">
		              <input class="search-query" type="text" placeholder="Filter" id="filter_field" name="filter" value="<?=htmlspecialchars($_REQUEST['filter'], ENT_COMPAT | ENT_HTML401, 'UTF-8', true)?>" />
		            </form>
					<?}?>
				</div>
			</div>
		</div>
	</div>
	
	<div class="container">