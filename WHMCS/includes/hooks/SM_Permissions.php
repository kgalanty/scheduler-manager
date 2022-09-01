<?php

/*
 *	Schedule Manager 2021
 *	Hook handling Editors assignment in admin area staff page.
 *  WHMCS 7.10.2
 *
 */
use WHMCS\Database\Capsule as DB;
add_hook('AdminAreaFooterOutput', 1, function($vars) {


	if($_GET['subaction'] == 'sm_setCustomPerms' && $_GET['action_type'] && $_GET['ajax'] && $_SESSION['sm_permissions'])
	{
		switch($_GET['action_type'])
		{
			case 'REMOVE':
			$rows = DB::table('schedule_editors')->where('editor_id', (int)$_GET['id'])->delete();
			break;
			case 'ADD':
			$rows = DB::table('schedule_editors')->insert(['editor_id' => (int)$_GET['id']]);
		}
		//die($rows);
		echo json_encode(['status'=> 'success']); exit;

	}
	if($vars['filename'] == 'configadmins' && $_GET['action'] == 'manage' && $_GET['id'])
	{

	define('ALLOWED_ROLES', [1,19]);
	// const ALLOWED_ROLES = 
		$_SESSION['sm_permissions'] = DB::table('tbladmins')->where('id', $_SESSION['adminid'])->whereIn('roleid', ALLOWED_ROLES)->count()>0?true:false;
		//var_dump(constant(ALLOWED_ROLES));die;
		$perms = ['editor' => DB::table('schedule_editors')->where('editor_id', (int)$_GET['id'])->count()];
		$perms = json_encode($perms);
    return <<<HTML
<script type="text/javascript">

	function smaddOrRemovePerm(perm, action)
	{
		jQuery.ajax({
			async: false,
			type: 'GET',
			data: {
				ajax:       true,
				action_type: action,
				subaction:     'sm_setCustomPerms',
				perm_id:   perm
				},
				success: function(response_str) {
					let response = JSON.parse(response_str)
					if (response.status == 'success') {
							jQuery.growl.notice({ title: 'Success', message: '' });

						//perms = response.perms
						}else {
							console.log(response);
							alert('Something wrong with load custom permissions... (Look console)')
						}
						},

						});

	}


$(function() { 
	$(".sm_perm").click( function(){
			if( $(this).is(':checked') ) {
								 smaddOrRemovePerm($(this).val(), 'ADD');
								} else {
								 smaddOrRemovePerm($(this).val(), 'REMOVE');
								}
});});
let perms = {$perms};
$('form > table').after('<table width="100%" class="form"><tr><td width="20%" class="fieldlabel">Schedule Manager Permissions</td><td class="fieldarea"><label class="checkbox-inline"><input type="checkbox" name="sm_editor" class="sm_perm" value="'+perms.editor+'" '+(perms.editor ? 'checked=checked' : '')+'> Editor</label></td></tr></table>');
    //custom javascript here
</script>
HTML;
}
});