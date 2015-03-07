<?php
// +-----------------------------------------------------------------+
// |                   PhreeBooks Open Source ERP                    |
// +-----------------------------------------------------------------+
// | Copyright(c) 2008-2014 PhreeSoft      (www.PhreeSoft.com)       |
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
//  Path: /modules/inventory/pages/main/template_detail.php
//
// start the form
echo html_form('inventory', FILENAME_DEFAULT, gen_get_all_get_params(array('action', 'cID', 'sku', 'add', 'inventory_type')), 'post', 'enctype="multipart/form-data"');
// include hidden fields
echo html_hidden_field('action', '') . chr(10);
echo html_hidden_field('rowSeq', 	$cInfo->id) . chr(10);
echo html_hidden_field('id', 		$cInfo->id) . chr(10);
if(isset($cInfo->ms_attr_0)) echo html_hidden_field('ms_attr_0', $cInfo->ms_attr_0) . chr(10);
if(isset($cInfo->ms_attr_1)) echo html_hidden_field('ms_attr_1', $cInfo->ms_attr_1) . chr(10);
// customize the toolbar actions
if ($_REQUEST['action'] == 'properties') {
  echo html_hidden_field('search_text', '') . chr(10);
  $toolbar->icon_list['cancel']['params'] = 'onclick="self.close()"';
  $toolbar->icon_list['open']['show']     = false;
  $toolbar->icon_list['delete']['show']   = false;
  $toolbar->icon_list['save']['show']     = false;
  $toolbar->icon_list['print']['show']    = false;
} else {
  $toolbar->icon_list['cancel']['params'] = 'onclick="location.href = \'' . html_href_link(FILENAME_DEFAULT, gen_get_all_get_params(array('action','page')), 'SSL') . '\'"';
  $toolbar->icon_list['open']['show']     = false;
  if ($security_level > 3 && $cInfo->inventory_type != 'mi' && $cInfo->inventory_type != 'ia' && ($cInfo->last_journal_date != '0000-00-00 00:00:00' || $cInfo->last_journal_date != '') ){ 
  	$toolbar->icon_list['delete']['show']   = true;
  	$toolbar->icon_list['delete']['params'] = "onclick='if (confirm(\"". INV_MSG_DELETE_INV_ITEM . "\")) deleteItem({$cInfo->id},\"{$cInfo->inventory_type}\")'";
  }else{
  	$toolbar->icon_list['delete']['show']   = false;
  } 
  if ($security_level > 2 || $first_entry) {
    $toolbar->icon_list['save']['params'] = 'onclick="submitToDo(\'save\')"';
    $toolbar->add_icon ('post_next', 'onclick="submitToDo(\'post_next\')"', 11);
    $toolbar->icon_list['post_next']['icon'] = 'actions/go-next.png';
    $toolbar->icon_list['post_next']['text'] = TEXT_SAVE_AND_COPY_TO_NEW;
  } else {
    $toolbar->icon_list['save']['show']   = false;
  }
  $toolbar->icon_list['print']['show']    = false;
  $toolbar->add_help($cInfo->help_path);
}
// pull in extra toolbar overrides and additions
if (count($extra_toolbar_buttons_detail) > 0) {
  foreach ($extra_toolbar_buttons_detail as $key => $value) $toolbar->icon_list[$key] = $value;
}
echo $toolbar->build_toolbar(); 
$fields->set_fields_to_display($cInfo->inventory_type);

// pull in additional custom tabs
if (isset($extra_inventory_tabs) && is_array($extra_inventory_tabs)) foreach ($extra_inventory_tabs as $tab) {
	$cInfo->tab_list[$tab['id']] = array('file'=>$tab['filename'], 'tag'=>$tab['id'], 'order'=>$tab['order'], 'text'=>$tab['title']);
}

$temp = array();
foreach ($cInfo->tab_list as $key => $value) $temp[$key] = $value['order'];
array_multisort($temp, SORT_ASC, $cInfo->tab_list);

?>
<h1 id='heading_title'><?php echo MENU_HEADING_INVENTORY . ' - ' . TEXT_SKU . '# ' . $cInfo->sku . ' (' . $cInfo->description_short . ')'; ?></h1>
<div id="detailtabs">
<ul>
<?php // build the tab list's
  foreach ($cInfo->tab_list as $value) echo add_tab_list('tab_'.$value['tag'],  $value['text']);
  echo $fields->extra_tab_li . chr(10); // user added extra tabs
?>
</ul>
<?php
foreach ($cInfo->tab_list as $value) {
  if (file_exists(DIR_FS_WORKING . 'custom/pages/main/' . $value['file'] . '.php')) {
	include(DIR_FS_WORKING . 'custom/pages/main/' . $value['file'] . '.php');
  } else {
	include(DIR_FS_WORKING . 'pages/main/' . $value['file'] . '.php');
  }
}

//********************************* List Custom Fields Here ***********************************
echo $fields->extra_tab_html;
// pull in additional custom tabs
if (isset($extra_inventory_tabs) && is_array($extra_inventory_tabs)) {
  foreach ($extra_inventory_tabs as $tabs) {
	$file_path = DIR_FS_WORKING . 'custom/pages/main/' . $tabs['tab_filename'] . '.php';
	if (file_exists($file_path)) {
	  require($file_path);
	}
  }
}
?>
</div>
</form>
