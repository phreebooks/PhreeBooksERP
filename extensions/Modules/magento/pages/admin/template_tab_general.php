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
//  Path: /modules/magento/pages/admin/template_tab_general.php
//

?>
<div title="<?php echo TEXT_GENERAL;?>" id="tab_general">
<fieldset>
  <legend></legend>
<table class="ui-widget" style="border-style:none;width:100%">
 <thead class="ui-widget-header">
  <tr><th colspan="2"><?php echo MODULE_MAGENTO_CONFIG_INFO; ?></th></tr>
 </thead>
 <tbody class="ui-widget-content">
	  
	  <tr>
	    <td colspan="4"><?php echo MAGENTO_ADMIN_URL; ?></td>
	    <td><?php echo html_input_field('magento_url', $_POST['magento_url'] ? $_POST['magento_url'] : MAGENTO_URL, 'size="64"'); ?></td>
	  </tr>
	  <tr>
	    <td colspan="4"><?php echo MAGENTO_ADMIN_USERNAME; ?></td>
	    <td><?php echo html_input_field('magento_username', $_POST['magento_username'] ? $_POST['magento_username'] : MAGENTO_USERNAME, ''); ?></td>
	  </tr>
	  <tr>
	    <td colspan="4"><?php echo MAGENTO_ADMIN_PASSWORD; ?></td>
	    <td><?php echo html_input_field('magento_password', $_POST['magento_password'] ? $_POST['magento_password'] : MAGENTO_PASSWORD, ''); ?></td>
	  </tr>
	  <tr>
	    <td colspan="4"><?php echo MAGENTO_TAX_CLASS; ?></td>
	    <td><?php echo html_input_field('magento_product_tax_class', $_POST['magento_product_tax_class'] ? $_POST['magento_product_tax_class'] : MAGENTO_PRODUCT_TAX_CLASS, ''); ?></td>
	  </tr>
	  <tr>
	    <td colspan="4"><?php echo MAGENTO_USE_PRICES; ?></td>
	    <td><?php echo html_pull_down_menu('magento_use_price_sheets', $sel_yes_no, $_POST['magento_use_price_sheets'] ? $_POST['magento_use_price_sheets'] : MAGENTO_USE_PRICE_SHEETS, 'onclick="togglePriceSheets()"'); ?></td>
	  </tr>
  	  <tr id="price_sheet_row">
	    <td colspan="4"><?php echo MAGENTO_TEXT_PRICE_SHEET; ?></td>
        <td><?php echo html_pull_down_menu('magento_price_sheet', pull_down_price_sheet_list(), $_POST['magento_price_sheet'] ? $_POST['magento_price_sheet'] : MAGENTO_PRICE_SHEET, ''); ?></td>
	  </tr>
	  <tr>
	    <td colspan="4"><?php echo MAGENTO_SHIP_ID; ?></td>
	    <td><?php echo html_input_field('magento_status_confirm_id', $_POST['magento_status_confirm_id'] ? $_POST['magento_status_confirm_id'] : MAGENTO_STATUS_CONFIRM_ID, ''); ?></td>
	  </tr>
	  <tr>
	    <td colspan="4"><?php echo MAGENTO_PARTIAL_ID; ?></td>
	    <td><?php echo html_input_field('magento_status_partial_id', $_POST['magento_status_partial_id'] ? $_POST['magento_status_partial_id'] : MAGENTO_STATUS_PARTIAL_ID, ''); ?></td>
	  </tr>
	</tbody>
</table>
</fieldset>
</div>
