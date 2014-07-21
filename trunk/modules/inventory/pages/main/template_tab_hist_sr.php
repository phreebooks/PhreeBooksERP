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
//  Path: /modules/inventory/pages/main/template_tab_history_sr.php
//

?>
<div id="tab_orderhist">
<h1><?php echo PAGE_TITLE; ?></h1>
<table class="ui-widget" style="border-collapse:collapse;width:100%">
 <thead class="ui-widget-header">
  <tr>
   <th><?php echo TEXT_DATE; ?></th>
   <th><?php echo "Invoice #"; ?></th>
   <th><?php echo "Customer Name"; ?></th>
   <th><?php echo "Serial Number"; ?></th>
   <th><?php echo TEXT_PAID; ?></th>
   <th><?php echo TEXT_AMOUNT; ?></th>
   <th><?php echo TEXT_ACTION; ?></th>
  </tr>
 </thead>
 <tbody>
<?php
  $odd = true;
  while (!$cInfo->orderHistory->EOF) {
	$oID          = $cInfo->orderHistory->fields['id'];
	$post_date    = gen_locale_date($cInfo->orderHistory->fields['post_date']);
	$reference_id = htmlspecialchars($cInfo->orderHistory->fields['purchase_invoice_id']);
	$primary_name = htmlspecialchars($cInfo->orderHistory->fields['bill_primary_name']);
	$closed       = $cInfo->orderHistory->fields['closed'] ? TEXT_YES : '';
	$total_amount = $currencies->format_full($cInfo->orderHistory->fields['total_amount']);
    $link_page    = html_href_link(FILENAME_DEFAULT, "module=phreebooks&amp;page=orders&amp;oID=$oID&amp;action=edit&amp;jID=12", 'SSL');
?>
  <tr class="<?php echo $odd?'odd':'even'; ?>" style="cursor:pointer">
	<td onclick="window.open('<?php echo $link_page; ?>','_blank')"><?php echo $post_date; ?></td>
	<td onclick="window.open('<?php echo $link_page; ?>','_blank')"><?php echo $reference_id; ?></td>
	<td<?php echo $bkgnd; ?> onclick="window.open('<?php echo $link_page; ?>','_blank')"><?php echo $primary_name; ?></td>
	<td onclick="window.open('<?php echo $link_page; ?>','_blank')"><?php echo $cInfo->orderHistory->fields['serialize_number']; ?></td>
	<td onclick="window.open('<?php echo $link_page; ?>','_blank')"><?php echo $closed; ?></td>
	<td align="right" onclick="window.open('<?php echo $link_page; ?>','_blank')"><?php echo $total_amount; ?></td>
	<td align="right">
<?php // build the action toolbar
	echo html_icon('actions/document-print.png',    TEXT_PRINT,'small', 'onclick="printOrder('.$oID.')"') . chr(10);
	echo html_icon('actions/edit-find-replace.png', TEXT_EDIT, 'small', 'onclick="window.open(\''.$link_page.'\',\'_blank\')"') . chr(10);
?>
	</td>
  </tr>
<?php
	  $cInfo->orderHistory->MoveNext();
	  $odd = !$odd;
	}
?>
 </tbody>
</table>
</div>
