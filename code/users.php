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
<div id="user_edit" class="modal hide fade" tabindex="-1" role="dialog" style="width: 260px; margin-left: -130px;">
	<div class="modal-header">
		<button type="button" class="close" data-dismiss="modal">×</button>
		<h3 id="myModalLabel">Edit User</h3>
	</div>
	<div class="modal-body">
		<div style="display: none;" id="user_edit_id"></div>
		<input type="text" id="user_edit_email" placeholder="Email" /><br />
		<input type="password" id="user_edit_password" placeholder="Password" /><br />
		<select id="user_edit_level">
			<option value="4">Normal</option>
			<option value="3">Tagger</option>
			<option value="2">Moderator</option>
			<option value="1">Administrator</option>
			<option value="0">Disabled</option>
		</select>
		<div style="display: none;" id="user_edit_load"></div>
	</div>
	<div class="modal-footer">
		<button class="btn" data-dismiss="modal">Cancel</button>
		<button class="btn btn-primary" data-dismiss="modal" id="user_edit_save">Save</button>
	</div>
</div>
<div id="user_add" class="modal hide fade" tabindex="-1" role="dialog" style="width: 260px; margin-left: -130px;">
	<div class="modal-header">
		<button type="button" class="close" data-dismiss="modal">×</button>
		<h3 id="myModalLabel">Create User</h3>
	</div>
	<div class="modal-body">
		<input type="text" id="user_add_email" placeholder="Email" /><br />
		<input type="password" id="user_add_password" placeholder="Password" /><br />
		<select id="user_add_level">
			<option value="4">Normal</option>
			<option value="3">Tagger</option>
			<option value="2">Moderator</option>
			<option value="1">Administrator</option>
			<option value="0">Disabled</option>
		</select>
		<div style="display: none;" id="user_add_load"></div>
	</div>
	<div class="modal-footer">
		<button class="btn" data-dismiss="modal">Cancel</button>
		<button class="btn btn-primary" data-dismiss="modal" id="user_add_create">Create</button>
	</div>
</div>

<button class="btn btn-primary" id="add_user">Create User</button><br /><br />
<table class="table table-striped table-bordered table-hover" id="users_list">
	<thead>
		<tr><th>#</th><th>Email</th><th>Level</th></tr>
	</thead>
	<tbody>
		
	</tbody>
</table>
<script type="text/javascript">
function loadUsers() {
	$("#users_list tbody").load("<?=generateURL("api/users/list")?>/");
}
$(document).ready(function() {
	$("#users_list").on("click", "tbody tr", function() {
		$("#user_edit_id").text($(this).find(".id").text());
		$("#user_edit_email").val($(this).find(".email").text());
		$("#user_edit_level").val($(this).find(".level").attr("value"));
		$("#user_edit").modal();
	});
	$("#user_edit_save").click(function() {
		$("#user_edit_load").load("<?=generateURL("api/users/update")?>/", {id: $("#user_edit_id").text(), email: $("#user_edit_email").val(), password: $("#user_edit_password").val(), level: $("#user_edit_level").val()}, function(response, status, xhr) {
			loadUsers();
		});
	});
	$("#add_user").click(function() {
		$("#user_add").modal();
	});
	$("#user_add_create").click(function() {
		$("#user_edit_load").load("<?=generateURL("api/users/create")?>/", {email: $("#user_add_email").val(), password: $("#user_add_password").val(), level: $("#user_add_level").val()}, function(response, status, xhr) {
			loadUsers();
		});
	});
	loadUsers();
});
</script>
<?
require_once("footer.php");
exit();
?>