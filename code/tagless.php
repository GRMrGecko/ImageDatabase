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
require_once("header.php");
?>
<style type="text/css">
#imageViewer_sidebar {
	opacity: 0.9;
	position: fixed;
	top: 41px;
	bottom: 0;
	left: 0;
	width: 200px;
	z-index: 1032;
	overflow: scroll;
	padding-top: 8px;
	background-color: #ffffff;
	-webkit-box-shadow: 0 1px 10px rgba(0,0,0,0.1);
	-moz-box-shadow: 0 1px 10px rgba(0,0,0,0.1);
	box-shadow: 0 1px 10px rgba(0,0,0,0.1);
}
@media only screen and (min-device-width : 320px) and (max-device-width : 480px) {
	#imageViewer_sidebar {
		width: 100px;
	}
}
#imageViewer_sidebar .tags_edit {
	width: 180px;
	height: 250px;
}
@media only screen and (min-device-width : 320px) and (max-device-width : 480px) {
	#imageViewer_sidebar .tags_edit {
		width: 80px;
		height: 150px;
	}
}

#imageViewer_main {
	position: fixed;
	z-index: 1031;
	border:4px solid #ffffff;
	border-radius: 4px;
	background-color: #ffffff;
	-webkit-box-shadow: 0 1px 10px rgba(0,0,0,0.1);
	-moz-box-shadow: 0 1px 10px rgba(0,0,0,0.1);
	box-shadow: 0 1px 10px rgba(0,0,0,0.1);
}
</style>
<div id="image_loader" style="display: none;"></div>

<div id="imageViewer_sidebar">
	<?if (isset($_MGM['user'])) {?>
		<textarea class="tags_edit"></textarea>
		<button type="button" class="btn" id="imageViewer_save">Save/Next</button>
		<div id="imageViewer_apiloader" style="display: none;"></div>
	<?}?>
</div>
<div id="imageViewer_main"></div>
<script type="text/javascript">
function loadNext() {
	$("#image_loader").load("<?=generateURL("api/tagless")?>/", function(response, status, xhr) {
		loadImage($("#image_loader #image"), 0);
	});
}

var imageViewing = "";
function repositionImage() {
	if (imageViewing=="") {
		return;
	}
	var image = $("#image_loader [hash='"+imageViewing+"']");
	var width = image.attr("image_width");
	var height = image.attr("image_height");
	
	var spaceWidth = ($(window).width()-$("#imageViewer_sidebar").width())-20;
	var spaceHeight = ($(window).height()-$(".navbar").height())-20;
	var newWidth = width;
	var newHeight = height;


	if (width>spaceWidth || height>spaceHeight) {
		var widthFactor = spaceWidth/width;
		var heightFactor = spaceHeight/height;
		var scaleFactor = 1;
	
		if (widthFactor<heightFactor)
			scaleFactor = widthFactor;
		else
			scaleFactor = heightFactor;
	
		newWidth = Math.round(width*scaleFactor);
		newHeight = Math.round(height*scaleFactor);
	}
	$("imageViewer_main img").css({width: newWidth, height: newHeight});

	$("#imageViewer_main").css({top: (((spaceHeight-newHeight)/2)+$(".navbar").height())+5, right: ((spaceWidth-newWidth)/2)+5, width: newWidth, height: newHeight});
}

function loadImage(image) {
	imageViewing = image.attr("hash");
	$("#imageViewer_main").html("<a href=\""+image.attr("original")+"\" target=\"blank\"><img src=\""+image.attr("original")+"\" /></a>");
	repositionImage();
	
	<?if (isset($_MGM['user'])) {?>
		$("#imageViewer_sidebar .tags_edit").val(image.attr("tags"));
		$("#imageViewer_sidebar .tags_edit").focus();
	<?}?>
}

$(document).ready(function() {
	$("#imageViewer_sidebar").css({top: $(".navbar").height()});
	$(window).resize(function() {
		repositionImage();
		$("#imageViewer_sidebar").css({top: $(".navbar").height()});
	});
	<?if (isset($_MGM['user'])) {?>
		$("#imageViewer_save").click(function() {
			if (imageViewing=="") {
				return;
			}
			$("#imageViewer_apiloader").load("<?=generateURL("api/save_tags")?>/", {hash: imageViewing, tags: $("#imageViewer_sidebar .tags_edit").val()}, function() {
				loadNext();
			});
		});
	<?}?>
	loadNext();
});
</script>
<?
require_once("footer.php");
exit();
?>