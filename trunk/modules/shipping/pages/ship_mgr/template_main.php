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
//  Path: /modules/shipping/pages/ship_mgr/template_main.php
//
echo html_form('ship_mgr', FILENAME_DEFAULT, gen_get_all_get_params(array('action')), 'post', 'enctype="multipart/form-data"', true) . chr(10);
// include hidden fields
echo html_hidden_field('action',   '')    . chr(10);
echo html_hidden_field('rowSeq', '')    . chr(10);
echo html_hidden_field('module_id', '') . chr(10);
// customize the toolbar actions
$toolbar->icon_list['cancel']['params'] = 'onclick="location.href = \'' . html_href_link(FILENAME_DEFAULT, '', 'SSL') . '\'"';
$toolbar->icon_list['open']['show']     = false;
$toolbar->icon_list['save']['show']     = false;
$toolbar->icon_list['delete']['show']   = false;
$toolbar->icon_list['print']['show']    = false;
if (count($extra_toolbar_buttons) > 0) foreach ($extra_toolbar_buttons as $key => $value) $toolbar->icon_list[$key] = $value;
$toolbar->add_help('09');
echo $toolbar->build_toolbar($add_search = false, false, $cal_ship); 
// Build the page
?>
<h1><?php echo BOX_SHIPPING_MANAGER; ?></h1>
<div class="easyui-tabs" id="shippingtabs">
 
<?php
  foreach ($admin_classes['shipping']->methods as $method) {
  	if (!$method->installed) continue;
	echo '<div title="'.$method->text.'" id="tab_' . $method->id . '">' . chr(10);
	include_once(DIR_FS_MODULES . 'shipping/methods/' . $method->id . '/ship_mgr.php');
	echo '</div>' . chr(10);
  }
?>
</div>
<div id="shipping_dialog">&nbsp;</div>
</form>
