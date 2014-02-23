<?php
// +-----------------------------------------------------------------+
// |                   PhreeBooks Open Source ERP                    |
// +-----------------------------------------------------------------+
// | Copyright(c) 2008-2013 PhreeSoft, LLC (www.PhreeSoft.com)       |

// +-----------------------------------------------------------------+
// | This program is free software: you can redistribute it and/or   |
// | modify it under the terms of the GNU General Public License as  |
// | published by the Free Software Foundation, either version 3 of  |
// | the License, or any later version.                              |
// |                                                                 |
// | This program is distributed in the hope that it will be useful, |
// | but WITHOUT ANY WARRANTY; without even the implied warranty of  |
// | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the   |
// | GNU General Public License for more details.                    |
// +-----------------------------------------------------------------+
//  Path: /modules/phreedom/pages/main/js_include.php
//

?>
<script type="text/javascript">
<!--
// required function called with every page load
function init() {
<?php
  if ($_REQUEST['action'] == 'login') echo "  document.getElementById('admin_name').focus();";
?>
}

// required function called with every form submit. return true on success
function check_form() {
  return true;
}

function box_edit(boxId) {
  	document.getElementById(boxId+'_prop').style.display= '';
  	document.getElementById(boxId+'_hr').style.display  = '';
  	document.getElementById(boxId+'_can').style.display = '';
  	document.getElementById(boxId+'_add').style.display = 'none';
}

function box_cancel(boxId) {
  document.getElementById(boxId+'_prop').style.display= 'none';
  document.getElementById(boxId+'_hr').style.display  = 'none';
  document.getElementById(boxId+'_can').style.display = 'none';
  document.getElementById(boxId+'_add').style.display = '';
}

function del_box(boxId) {
	$.messager.confirm('Confirm','<?php echo JS_CTL_PANEL_DELETE_BOX; ?>',function(r){
		if (r){
			var formId = boxId + '_frm';
			var actionId = boxId + '_action';
			document.getElementById(actionId).value = 'delete';
			document.getElementById(formId).submit();
		}
	});
  	return false;
}

function move_box(boxId, direction) {
  var formId = boxId + '_frm';
  var actionId = boxId + '_action';
  switch(direction) {
    case 'move_left':  document.getElementById(actionId).value = 'move_left';  break;
    case 'move_right': document.getElementById(actionId).value = 'move_right'; break;
    case 'move_up':    document.getElementById(actionId).value = 'move_up';    break;
    case 'move_down':  document.getElementById(actionId).value = 'move_down';  break;
    default:
  }
  document.getElementById(formId).submit();
}

function del_index(boxId, index) {
  if (confirm('<?php echo JS_CTL_PANEL_DELETE_IDX; ?>')) {
	var formId = boxId + '_frm';
	var removeId = boxId + '_rId';
	document.getElementById(removeId).value = index;
	document.getElementById(formId).submit();
  }
  return false;
}

// -->
</script>