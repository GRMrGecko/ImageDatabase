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
.image {
	width: 180px;
	height: 180px;
	display: inline-block;
	text-align: center;
	vertical-align: middle;
}
.image img {
	border:3px solid #e8e8e8;
	border-radius: 2px;
	box-shadow: 0 1px 3px rgba(0,0,0,0.055);
}
#imageViewer_sidebar {
	opacity: 0.9;
	position: fixed;
	top: 0;
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
#imageViewer_close {
	padding-right: 18px;
	padding-top: 2px;
}

#imageViewer_sidebar .basic_info {
	padding-top: 5px;
	padding-left: 5px;
	padding-right: 5px;
}
#imageViewer_sidebar .tags {
	padding-top: 5px;
	padding-left: 5px;
	padding-right: 5px;
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
#backdrop {
	opacity: 0.8;
	position: fixed;
	top: 0;
	right: 0;
	bottom: 0;
	left: 0;
	z-index: 1030;
	background-color: #000000;
}
</style>
<div id="images_loader" style="display: none;"></div>
<div id="images_main"></div>

<?if (isset($_MGM['user']) && $_MGM['user']['level']<=2) {?>
	<div id="imageViewer_confirmDelete" class="modal hide fade" tabindex="-1" role="dialog">
		<div class="modal-header">
			<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
			<h3>Delete Image</h3>
		</div>
		<div class="modal-body">
			<p>You are about to delete an image from the database.</p>
			<p>Are you sure you wish to do this?</p>
		</div>
		<div class="modal-footer">
			<button class="btn" data-dismiss="modal" aria-hidden="true">No</button>
			<button class="btn btn-danger" data-dismiss="modal" id="imageViewer_confirmDelete_yes">Yes</button>
		</div>
	</div>
<?}?>
<div id="imageViewer_sidebar" class="hide">
	<button type="button" class="btn" id="imageViewer_previousButton"><div class="icon-arrow-left"></div></button>
	<button type="button" class="btn" id="imageViewer_nextButton"><div class="icon-arrow-right"></div></button>
	<button type="button" class="close" id="imageViewer_close">&times;</button>
	<div class="basic_info"></div>
	<div class="tags"></div>
	<?if (isset($_MGM['user'])) {?>
		<textarea class="tags_edit hide"></textarea>
		<button type="button" class="btn" id="imageViewer_editTags">Edit Tags</button>
		<?if ($_MGM['user']['level']<=2) {?>
			<button type="button" class="btn btn-danger" id="imageViewer_delete">Delete</button>
		<?}?>
		<div id="imageViewer_apiloader" style="display: none;"></div>
	<?}?>
</div>
<div id="imageViewer_main" class="hide"></div>
<div id="backdrop" class="hide"></div>
<script type="text/javascript">
var filter = "";
var readyToLoad = false;
var loadingPage = false;
var currentPage = 0;
var currentState = 0;

function loadPage(page) {
	if (loadingPage) {
		return;
	}
	currentPage = page;
	loadingPage = true;
	//console.log("Loading "+page);
	$("#images_loader").load("<?=generateURL("api")?>/"+page, {filter: filter}, function(response, status, xhr) {
		$("#images_main").append($("#images_loader #content").html());
		$("#images_loader #content").html("");
		if ($("#images_loader #next_page").text()!="") {
			readyToLoad = true;
		}
		loadingPage = false;
	});
}

var imageViewing = "";
function repositionImage(animate) {
	if (imageViewing=="") {
		return;
	}
	var image = $("#images_main .image[hash='"+imageViewing+"']");
	var width = image.attr("image_width");
	var height = image.attr("image_height");
	
	var spaceWidth = ($(window).width()-$("#imageViewer_sidebar").width())-20;
	var spaceHeight = $(window).height()-20;
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
	
	if (animate==1) {
		$("#imageViewer_main").css({top: (newWidth*-1)-20, right: ((spaceWidth-newWidth)/2)+5, width: newWidth, height: newHeight});
		$("#imageViewer_main").animate({top: ((spaceHeight-newHeight)/2)+5});
	} else if (animate==2) {
		$("#imageViewer_main").css({top: ((spaceHeight-newHeight)/2)+5, right: ((spaceWidth-newWidth)/2)+5, width: newWidth, height: newHeight});
		$("#imageViewer_main").animate({top: spaceHeight+20}, {duration: 400, complete: function() {
			$("#imageViewer_main").addClass("hide");
		}});
	} else if (animate==3 || animate==4) {
		$("#imageViewer_main").css({top: ((spaceHeight-newHeight)/2)+5, right: ((spaceWidth-newWidth)/2)+5, width: newWidth, height: newHeight});
		$("#imageViewer_main").animate({right: (spaceWidth*-1)-20}, {duration: 400, complete: function() {
			if (animate==3) {
				loadImage(image.next(), 1);
			} else {
				loadImage(image.prev(), 1);
			}
		}});
	} else {
		$("#imageViewer_main").css({top: ((spaceHeight-newHeight)/2)+5, right: ((spaceWidth-newWidth)/2)+5, width: newWidth, height: newHeight});
	}
}

function bytesToSize(bytes) {
	var size = bytes;
	var type = "Bytes";
	if (size>=1024) {
		size = size/1024;
		type = "KB";
	}
	if (size>=1024) {
		size = size/1024;
		type = "MB";
	}
	if (size>=1024) {
		size = size/1024;
		type = "GB";
	}
	return (Math.round(size*100)/100)+" "+type;
}

function loadImage(image, animate, urlHistory) {
	if (animate==0) {
		$("#backdrop").css("opacity", 0);
		$("#backdrop").removeClass("hide");
		$("#backdrop").animate({opacity: 0.8});
	
		$("#imageViewer_sidebar").css("left", "-210px");
		$("#imageViewer_sidebar").removeClass("hide");
		$("#imageViewer_sidebar").animate({left: 0});
	}
	
	imageViewing = image.attr("hash");
	$("#imageViewer_main").removeClass("hide");
	$("#imageViewer_main").html("<a href=\""+image.attr("original")+"\" target=\"blank\"><img src=\""+image.attr("original")+"\" /></a>");
	repositionImage(1);
	
	$("#imageViewer_sidebar .basic_info").html("");
	$("#imageViewer_sidebar .basic_info").append("<a href=\"<?=generateURL("hash/")?>"+image.attr("hash")+"/\">Image Link</a><br />");
	$("#imageViewer_sidebar .basic_info").append("Date: "+date("m/d/y h:i:s A", image.attr("time"))+"<br />");
	$("#imageViewer_sidebar .basic_info").append("Size: "+image.attr("image_width")+"x"+image.attr("image_height")+"<br />");
	$("#imageViewer_sidebar .basic_info").append("File Size: "+bytesToSize(image.attr("file_size"))+"<br />");
	
	$("#imageViewer_sidebar .tags").html("");
	var tagsEdit = "";
	var tags = image.attr("tags").split(" ");
	for (var i=0; i<tags.length; i++) {
		var tag = tags[i].replace(/_/g, " ");
		$("#imageViewer_sidebar .tags").append(tag+"<br />");
		tagsEdit += tag+"\n";
	}
	<?if (isset($_MGM['user'])) {?>
		$("#imageViewer_sidebar .tags_edit").val(tagsEdit);
	<?}?>
	
	<?if (isset($_MGM['user']) && $_MGM['user']['level']>=4) {?>
		if (image.attr("user")=="<?=$_MGM['user']['docid']?>") {
			$("#imageViewer_editTags").show();
		} else {
			$("#imageViewer_editTags").hide();
		}
	<?}?>
	
	if (image.prev().length!=0) {
		$("#imageViewer_previousButton").removeAttr("disabled");
	} else {
		$("#imageViewer_previousButton").attr("disabled","disabled");
	}
	if (image.next().length!=0) {
		$("#imageViewer_nextButton").removeAttr("disabled");
	} else {
		$("#imageViewer_nextButton").attr("disabled","disabled");
	}
	if (urlHistory==undefined) {
		window.history.pushState({image: imageViewing, state: currentState}, "<?=$_MGM['title']?>", "<?=$_MGM['installPath']?>hash/"+image.attr("hash")+"/");
	}
	currentState = 2;
}

$(document).ready(function() {
	function escapeListen(listen) {
		if (listen==false) {
			$(document).off("keyup.dismiss.modal");
		} else {
			$(document).on("keyup.dismiss.modal", function(event) {
				if (event.which==27) {
					closeImageViewer();
				}
			});
		}
	}
	$("#images_main").on("click", ".image", function() {
		loadImage($(this), 0);
		escapeListen(true);
	});
	$("#imageViewer_previousButton").click(function() {
		repositionImage(4);
	});
	$("#imageViewer_nextButton").click(function() {
		repositionImage(3);
	});
	function closeImageViewer(urlHistory) {
		$("#backdrop").animate({opacity: 0}, {duration: 400, complete: function() {
			$("#backdrop").addClass("hide");
		}});
		
		$("#imageViewer_sidebar").animate({left: "-210px"}, {duration: 400, complete: function() {
			$("#imageViewer_sidebar").addClass("hide");
		}});
		
		<?if (isset($_MGM['user'])) {?>
			$("#imageViewer_sidebar .tags").removeClass("hide");
			$("#imageViewer_sidebar .tags_edit").addClass("hide");
			$("#imageViewer_editTags").text("Edit Tags");
		<?}?>
		
		repositionImage(2);
		
		if (urlHistory==undefined) {
			if (filter!="") {
				window.history.pushState({state: currentState}, "<?=$_MGM['title']?>", "<?=$_MGM['installPath']?>?filter="+encodeURIComponent(filter));
			} else {
				window.history.pushState({state: currentState}, "<?=$_MGM['title']?>", "<?=$_MGM['installPath']?>");
			}
		}
		currentState = 2;
		escapeListen(false);
	}
	$("#backdrop, #imageViewer_close").click(closeImageViewer);
	$(window).resize(function() {
		repositionImage(0);
	});
	<?if (isset($_MGM['user'])) {?>
		$("#imageViewer_editTags").click(function() {
			if (imageViewing=="") {
				return;
			}
			if ($(this).text()=="Edit Tags") {
				$(this).text("Save Tags");
				$("#imageViewer_sidebar .tags").addClass("hide");
				$("#imageViewer_sidebar .tags_edit").removeClass("hide");
			} else {
				$(this).text("Edit Tags");
				$("#imageViewer_sidebar .tags").removeClass("hide");
				$("#imageViewer_sidebar .tags_edit").addClass("hide");
				
				var tagsToSave = "";
				var tags = $("#imageViewer_sidebar .tags_edit").val().split("\n");
				for (var i=0; i<tags.length; i++) {
					var tag = tags[i].replace(/\s/g, "_");
					if (tag=="") {
						continue;
					}
					if (tagsToSave!="") {
						tagsToSave += " ";
					}
					tagsToSave += tag;
				}
				
				$("#imageViewer_sidebar .tags").html("");
				var tags = tagsToSave.split(" ");
				for (var i=0; i<tags.length; i++) {
					var tag = tags[i].replace(/_/g, " ");
					$("#imageViewer_sidebar .tags").append(tag+"<br />");
				}
				
				$("#imageViewer_apiloader").load("<?=generateURL("api/save_tags")?>/", {hash: imageViewing, tags: tagsToSave});
			}
		});
		<?if ($_MGM['user']['level']<=2) {?>
			$("#imageViewer_delete").click(function() {
				if (imageViewing=="") {
					return;
				}
				$("#imageViewer_confirmDelete").modal();
				escapeListen(false);
			});
			$("#imageViewer_confirmDelete_yes").click(function() {
				$("#imageViewer_apiloader").load("<?=generateURL("api/delete")?>/", {hash: imageViewing});
				closeImageViewer();
				$("#images_main .image[hash='"+imageViewing+"']").remove();
			});
			$("#imageViewer_confirmDelete").on('hidden', function() {
				if (!$("#backdrop").hasClass("hide")) {
					escapeListen(true);
				}
			})
		<?}?>
	<?}?>
	
	filter = $("#filter_field").val();
	loadPage(0);
	
	$(window).scroll(function(){
		if (readyToLoad && $(window).scrollTop()>=($(document).height()-$(window).height())-300) {
			readyToLoad = false;
			loadPage($("#images_loader #next_page").text());
		}
	});
	
	window.onpopstate = function(event) {
		if (event.state!=undefined) {
			if (event.state.filter!=undefined) {
				if (currentState!=2) {
					filter = event.state.filter;
					$("#filter_field").val(filter);
					$("#images_main").html("");
					loadPage(0);
				} else {
					closeImageViewer(true);
				}
				currentState = event.state.state;
			} else if (event.state.image!=undefined) {
				var image = $("#images_main .image[hash='"+event.state.image+"']");
				if (image.length>0) {
					if ($("#backdrop").hasClass("hide")) {
						loadImage(image, 0, true);
						escapeListen(true);
					} else {
						loadImage(image, 1, true);
					}
				} else {
					closeImageViewer(true);
				}
			}
		} else {
			if (currentState==2) {
				closeImageViewer(true);
			} else {
				filter = "";
				$("#filter_field").val(filter);
				$("#images_main").html("");
				loadPage(0);
			}
			currentState = 0;
		}
	};
	
	$("#filter_form").submit(function() {
		filter = $("#filter_field").val();
		$("#images_main").html("");
		if (filter=="") {
			window.history.pushState({filter: filter, state: currentState}, "<?=$_MGM['title']?>", "<?=$_MGM['installPath']?>");
		} else {
			window.history.pushState({filter: filter, state: currentState}, "<?=$_MGM['title']?>", "<?=$_MGM['installPath']?>?filter="+encodeURIComponent(filter));
		}
		currentState = 1;
		loadPage(0);
		return false;
	});
});
</script>
<?
require_once("footer.php");
?>