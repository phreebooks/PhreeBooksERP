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
//  Path: /modules/contacts/pages/popup_accts/template_main.php
//
echo html_form('popup_accts', FILENAME_DEFAULT, gen_get_all_get_params(array('action', 'list'))) . chr(10);
// include hidden fields
echo html_hidden_field('action', '')   . chr(10);
echo html_hidden_field('rowSeq', '') . chr(10);
// customize the toolbar actions
$basis->toolbar->icon_list['cancel']['params'] = 'onclick="self.close()"';
$basis->toolbar->icon_list['open']['show']     = false;
$basis->toolbar->icon_list['save']['show']     = false;
$basis->toolbar->icon_list['delete']['show']   = false;
$basis->toolbar->icon_list['print']['show']    = false;
// pull in extra toolbar overrides and additions
if (count($extra_toolbar_buttons) > 0) foreach ($extra_toolbar_buttons as $key => $value) $basis->toolbar->icon_list[$key] = $value;
// add the help file index and build the toolbar
if( $basis->cInfo->contacts_list[0]->help != '' ) $basis->toolbar->add_help($basis->cInfo->contacts_list[0]->help);
echo $basis->toolbar->build($add_search = true);
// Build the page
?>
<h1><?php echo TEXT_PLEASE_SELECT; ?></h1>
<div style="height:19px"><?php echo $basis->cInfo->query_split->display_count(TEXT_DISPLAY_NUMBER . $basis->cInfo->contacts_list[0]->title); ?>
<div style="float:right"><?php echo $basis->cInfo->query_split->display_links(); ?></div></div>
<table class="ui-widget" style="border-collapse:collapse;width:100%;">
 <thead class="ui-widget-header">
  <tr><?php echo $list_header;//@todo ?></tr>
 </thead>
 <tbody class="ui-widget-content">
 	<?php
 	$odd = true;
    foreach ($basis->cInfo->contacts_list as $contact) {
		$temp = $odd ? 'odd':'even';
		echo "<tr class='$temp' style='cursor:pointer'>";
			$contact->list_row("setReturnAccount");
		echo "<tr>";
      	$odd = !$odd;
    } ?>
 </tbody>
</table>
<div style="float:right"><?php echo $basis->cInfo->query_split->display_links(); ?></div>
<div><?php echo $basis->cInfo->query_split->display_count(TEXT_DISPLAY_NUMBER . $basis->cInfo->contacts_list[0]->title); ?></div>
</form>
