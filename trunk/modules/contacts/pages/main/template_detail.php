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
echo "<div data-options=\"region:'center'\">";
echo "<form id='contacts' style='padding:10px 20px 10px 40px;' method='post' enctype='multipart/form-data'>";
// include hidden fields
echo html_hidden_field('action',        '') . chr(10);
echo html_hidden_field('id',  $basis->cInfo->contact->id) . chr(10);
echo html_hidden_field('payment_id',  '') . chr(10);

$basis->cInfo->contact->fields->display($basis->cInfo->contact);
// Build the page

$custom_path = DIR_FS_MODULES . 'contacts/custom/pages/main/extra_tabs.php';
if (file_exists($custom_path)) { include($custom_path); }

function tab_sort($a, $b) {
	if ($a['order'] == $b['order']) return 0;
	return ($a['order'] > $b['order']) ? 1 : -1;
}
usort($basis->cInfo->contact->tab_list, 'tab_sort');
?>
		<h1><?php echo $basis->page_title; ?></h1>
		<div class="easyui-tabs" id="contacts_tabs">
		<?php
		foreach ($basis->cInfo->contact->tab_list as $value) {
			if (file_exists(DIR_FS_MODULES . "contacts/custom/pages/main/{$value['file']}.php")) {
				include(DIR_FS_MODULES . "contacts/custom/pages/main/{$value['file']}.php");
		  	} else {
				include(DIR_FS_MODULES . "contacts/pages/main/{$value['file']}.php");
		  	}
		}
		echo $basis->cInfo->contact->fields->extra_tab_html;// user added extra tabs
		?>
		</div>
	</form>
</div>
