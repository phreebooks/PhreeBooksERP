<?php
// +-----------------------------------------------------------------+
// |                   PhreeBooks Open Source ERP                    |
// +-----------------------------------------------------------------+
// | Copyright(c) 2008-2015 PhreeSoft      (www.PhreeSoft.com)       |
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
//  Path: /modules/contacts/pages/main/template_detail.php
//
echo html_form('contacts', FILENAME_DEFAULT, gen_get_all_get_params(array('action')), 'post', 'enctype="multipart/form-data"', true) . chr(10);
// include hidden fields
echo html_hidden_field('action',        '') . chr(10);
echo html_hidden_field('id',  $basis->cInfo->inventory->id) . chr(10);
echo html_hidden_field('del_crm_note','') . chr(10);
echo html_hidden_field('payment_id',  '') . chr(10);
// customize the toolbar actions
if ($basis->cInfo->inventory->action == 'editInventory') {
  $basis->toolbar->icon_list['cancel']['params'] = 'onclick="self.close()"';
  $basis->toolbar->icon_list['save']['show']     = false;
} else {
  $basis->toolbar->icon_list['cancel']['params'] = 'onclick="location.href = \'' . html_href_link(FILENAME_DEFAULT, "action=LoadContactMgrPage&amp;type={$basis->cInfo->inventory->type}&amp;list={$basis->cInfo->inventory->list}", 'SSL') . '\'"';
  if ((!isset($basis->cInfo->inventory->id) && $basis->cInfo->inventory->security_level < 2) || ($basis->cInfo->inventory->id && $basis->cInfo->inventory->security_level < 3)) {
    $basis->toolbar->icon_list['save']['show']   = false;
  } else {
    $basis->toolbar->icon_list['save']['params'] = 'onclick="submitToDo(\'SaveContact\')"';
  }
}
$basis->toolbar->icon_list['open']['show']       = false;
$basis->toolbar->icon_list['delete']['show']     = false;
$basis->toolbar->icon_list['print']['show']      = false;

// pull in extra toolbar overrides and additions
if (count($extra_toolbar_buttons) > 0) {
  foreach ($extra_toolbar_buttons as $key => $value) $basis->toolbar->icon_list[$key] = $value;
}

// add the help file index and build the toolbar
if( !$basis->cInfo->inventory->help == '' ) $basis->toolbar->add_help($basis->cInfo->inventory->help);
echo $basis->toolbar->build();
$basis->cInfo->inventory->fields->display($basis->cInfo->inventory, $basis->cInfo->inventory);
// Build the page

function tab_sort($a, $b) {
	if ($a['order'] == $b['order']) return 0;
	return ($a['order'] > $b['order']) ? 1 : -1;
}
usort($basis->cInfo->inventory->tab_list, 'tab_sort');
?>
		<div id="mainToolbar" data-options="region:'north'" >
		    <a href="#" class="easyui-linkbutton" iconCls="icon-cancel" onclick="location.href = 'index.php?action=LoadContactMgrPage&type=<?php echo $basis->cInfo->contact->type;?>'"><?php echo TEXT_UNDO;?></a>
		   	<a href="#" class="easyui-linkbutton" iconCls="icon-save"   onclick="$('#contacts').submit();"><?php echo TEXT_SAVE;?></a>
		   	<a href="#" class="easyui-linkbutton" iconCls="icon-help"   onclick="loadHelp('editContact');"><?php echo TEXT_HELP;?></a>
		   	<a href="#" class="easyui-linkbutton" iconCls="icon-email"  onclick="deleteContact()"><?php echo TEXT_MAIL;?></a>//@todo
		   	<h1><?php echo "{$basis->cInfo->inventory->title} - ({$basis->cInfo->inventory->sku}) "; ?></h1>
		</div>
		<div id="Tabs" class="easyui-tabs" border="false" plain="true" >
		<?php
		foreach ($basis->cInfo->inventory->tab_list as $value) {
		  	if (file_exists(DIR_FS_MODULES . "inventory/custom/pages/main/{$value['file']}.php")) {
				include(DIR_FS_MODULES . "inventory/custom/pages/main/{$value['file']}.php");
		  	} else {
				include(DIR_FS_MODULES . "inventory/pages/main/{$value['file']}.php");
		  	}
		}
		echo $basis->cInfo->inventory->fields->extra_tab_html;// user added extra tabs
		?>
		</div>
	</form>
</div>