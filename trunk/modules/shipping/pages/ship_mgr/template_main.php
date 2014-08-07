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
<div id="shippingtabs">
  <ul>
<?php 
	$image_types = array('gif', 'png', 'jpg', 'jpeg');
	$path = DIR_WS_MODULES . 'shipping/methods/';
	foreach ($installed_modules as $value) {
      $image_file = DIR_WS_MODULES . 'shipping/images/no_logo.png';
	  foreach ($image_types as $ext) {
	    if (file_exists($path . $value['id'] . '/images/logo.' . $ext)) {
		  $image_file = $path . $value['id'] . '/images/logo.' . $ext;
		  break;
		}
	  }
  	  echo add_tab_list('tab_'.$value['id'], html_image($image_file, $value['text'], 0, 30));
	}
?>
  </ul>
<?php
  foreach ($installed_modules as $value) {
    $method_id = $value['id'];
	echo '<div id="tab_' . $method_id . '">' . chr(10);
	include_once(DIR_FS_MODULES . 'shipping/methods/' . $method_id . '/ship_mgr.php');
	echo '</div>' . chr(10);
  }
?>
</div>
<div id="shipping_dialog">&nbsp;</div>
</form>
