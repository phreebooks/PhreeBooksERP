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
//  Path: /modules/assets/pages/main/js_include.php
//

?>
<script type="text/javascript">
<!--
// pass some php variables
<?php if ($_REQUEST['action'] == 'edit') {
echo js_calendar_init($cal_date1);
echo js_calendar_init($cal_date2);
echo js_calendar_init($cal_date3);
} ?>

// required function called with every page load
function init() {
  <?php if ($_REQUEST['action'] <> 'new' && $_REQUEST['action'] <> 'edit') { // set focus for main window
	echo "  document.getElementById('search_text').focus();";
	echo "  document.getElementById('search_text').select();";
  } ?>
  <?php if ($_REQUEST['action'] == 'new') { // set focus for main window
	echo "  document.getElementById('asset_id').focus();";
  } ?>
}

function check_form() {
  var error = 0;
  var error_message = "<?php echo JS_ERROR; ?>";

  if (error == 1) {
	$.messager.alert("Processing error",error_message,"error");
	return false;
  } else {
	return true;
  }
}

function deleteItem(id) {
	location.href = 'index.php?module=assets&page=main&action=delete&cID='+id;
}

function copyItem(id) {
	$.messager.prompt('<?php echo TEXT_COPY;?>', '<?php echo TEXT_COPY_TO; ?>', function(skuID){
		if (skuID){
			return location.href = 'index.php?module=assets&page=main&action=copy&cID='+id+'&asset_id='+skuID;
		}
		return false;
	});
}

function renameItem(id) {
	$.messager.prompt('<?php echo TEXT_RENAME;?>', '<?php echo TEXT_RENAME_TO; ?>', function(skuID){
		if (skuID){
			return location.href = 'index.php?module=assets&page=main&action=rename&cID='+id+'&asset_id='+skuID;
		}
		return false;
	});
}

function showImage() {
	$('#inv_image').window('open');	
}
// -->
</script>