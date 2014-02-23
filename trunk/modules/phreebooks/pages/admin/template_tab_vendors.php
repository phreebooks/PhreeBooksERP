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
//  Path: /modules/phreebooks/pages/admin/template_tab_vendors.php
//

?>
<div title="<?php echo MENU_HEADING_VENDORS;?>" id="tab_vendors">
  <fieldset>
	<table class="ui-widget" style="border-collapse:collapse;width:100%">
	 <thead class="ui-widget-header">
	  <tr><th colspan="4"><?php echo TEXT_DEFAULT_GL_ACCOUNTS; ?></th></tr>
	 </thead>
	 <tbody class="ui-widget-content">
	  <tr>
	    <td colspan="3"><?php echo CD_03_01_DESC; ?></td>
	    <td nowrap="nowrap"><?php echo html_combo_box('ap_default_inventory_account', $inv_chart, $_POST['ap_default_inventory_account'] ? $_POST['ap_default_inventory_account'] : AP_DEFAULT_INVENTORY_ACCOUNT, ''); ?></td>
	  </tr>
	  <tr>
	    <td colspan="3"><?php echo CD_03_02_DESC; ?></td>
	    <td><?php echo html_combo_box('ap_default_purchase_account', $ap_chart, $_POST['ap_default_purchase_account'] ? $_POST['ap_default_purchase_account'] : AP_DEFAULT_PURCHASE_ACCOUNT, ''); ?></td>
	  </tr>
	  <tr>
	    <td colspan="3"><?php echo CD_03_03_DESC; ?></td>
	    <td><?php echo html_combo_box('ap_purchase_invoice_account', $cash_chart, $_POST['ap_purchase_invoice_account'] ? $_POST['ap_purchase_invoice_account'] : AP_PURCHASE_INVOICE_ACCOUNT, ''); ?></td>
	  </tr>
	  <tr>
	    <td colspan="3"><?php echo CD_03_04_DESC; ?></td>
	    <td><?php echo html_combo_box('ap_def_freight_acct', $inv_chart, $_POST['ap_def_freight_acct'] ? $_POST['ap_def_freight_acct'] : AP_DEF_FREIGHT_ACCT, ''); ?></td>
	  </tr>
	  <tr>
	    <td colspan="3"><?php echo CD_03_05_DESC; ?></td>
	    <td><?php echo html_combo_box('ap_discount_purchase_account', $ap_chart, $_POST['ap_discount_purchase_account'] ? $_POST['ap_discount_purchase_account'] : AP_DISCOUNT_PURCHASE_ACCOUNT, ''); ?></td>
	  </tr>
	  <tr>
	    <td colspan="3"><?php echo CD_03_06_DESC; ?></td>
	    <td><?php echo html_combo_box('ap_def_deposit_acct', $cash_chart, $_POST['ap_def_deposit_acct'] ? $_POST['ap_def_deposit_acct'] : AP_DEF_DEPOSIT_ACCT, ''); ?></td>
	  </tr>
	  <tr>
	    <td colspan="3"><?php echo CD_03_07_DESC; ?></td>
	    <td><?php echo html_combo_box('ap_def_dep_liab_acct', $ocl_chart, $_POST['ap_def_dep_liab_acct'] ? $_POST['ap_def_dep_liab_acct'] : AP_DEF_DEP_LIAB_ACCT, ''); ?></td>
	  </tr>
	 </tbody>
	</table>
	<table class="ui-widget" style="border-collapse:collapse;width:100%">
	 <thead class="ui-widget-header">
	  <tr><th colspan="4"><?php echo TEXT_PAYMENT_TERMS; ?></th></tr>
	 </thead>
	 <tbody class="ui-widget-content">
	  <tr>
	    <td><?php echo CD_03_11_DESC; ?></td>
	    <td><?php echo html_checkbox_field('ap_use_credit_limit', $inc_chart, $_POST['ap_use_credit_limit'] ? $_POST['ap_use_credit_limit'] : AP_USE_CREDIT_LIMIT, ''); ?></td>
	    <td><?php echo sprintf(CD_03_12_DESC, DEFAULT_CURRENCY); ?></td>
	    <td><?php echo html_input_field('ap_credit_limit_amount', $_POST['ap_credit_limit_amount'] ? $_POST['ap_credit_limit_amount'] : AP_CREDIT_LIMIT_AMOUNT, 'style="text-align:right"'); ?></td>
	  </tr>
	  <tr>
	    <td colspan="2"><?php echo CD_02_10_DESC;  ?></td>
	    <td colspan="2">
		  <?php echo html_input_field('ap_prepayment_discount_percent', $_POST['ap_prepayment_discount_percent'] ? $_POST['ap_prepayment_discount_percent'] : AP_PREPAYMENT_DISCOUNT_PERCENT, 'size="10" style="text-align:right"'); ?>
	      <?php echo CD_02_13_DESC; ?>
		  <?php echo html_input_field('ap_prepayment_discount_days', $_POST['ap_prepayment_discount_days'] ? $_POST['ap_prepayment_discount_days'] : AP_PREPAYMENT_DISCOUNT_DAYS, 'size="5" style="text-align:right"'); ?>
	      <?php echo CD_02_14_DESC; ?>
		  <?php echo html_input_field('ap_num_days_due', $_POST['ap_num_days_due'] ? $_POST['ap_num_days_due'] : AP_NUM_DAYS_DUE, 'size="5" style="text-align:right"'); ?>
	      <?php echo CD_02_15_DESC; ?>
		</td>
	  </tr>
	 </tbody>
	</table>
	<table class="ui-widget" style="border-collapse:collapse;width:100%">
	 <thead class="ui-widget-header">
	  <tr><th colspan="4"><?php echo TEXT_ACCOUNT_AGING; ?></th></tr>
	 </thead>
	 <tbody class="ui-widget-content">
	  <tr>
	    <td><?php echo CD_02_20_DESC; ?></td>
	    <td><?php echo html_input_field('ap_aging_heading_1', $_POST['ap_aging_heading_1'] ? $_POST['ap_aging_heading_1'] : AP_AGING_HEADING_1, ''); ?></td>
	    <td><?php echo CD_02_16_DESC; ?></td>
	    <td><?php echo html_pull_down_menu('ap_aging_start_date', $sel_inv_due, $_POST['ap_aging_start_date'] ? $_POST['ap_aging_start_date'] : AP_AGING_START_DATE, ''); ?></td>
	  </tr>
	  <tr>
	    <td><?php echo CD_02_21_DESC; ?></td>
	    <td><?php echo html_input_field('ap_aging_heading_2', $_POST['ap_aging_heading_2'] ? $_POST['ap_aging_heading_2'] : AP_AGING_HEADING_2, ''); ?></td>
	    <td><?php echo CD_02_17_DESC; ?></td>
	    <td><?php echo html_input_field('ap_aging_date_1', $_POST['ap_aging_date_1'] ? $_POST['ap_aging_date_1'] : AP_AGING_DATE_1, ''); ?></td>
	  </tr>
	  <tr>
	    <td><?php echo CD_02_22_DESC; ?></td>
	    <td><?php echo html_input_field('ap_aging_heading_3', $_POST['ap_aging_heading_3'] ? $_POST['ap_aging_heading_3'] : AP_AGING_HEADING_3, ''); ?></td>
	    <td><?php echo CD_02_18_DESC; ?></td>
	    <td><?php echo html_input_field('ap_aging_date_2', $_POST['ap_aging_date_2'] ? $_POST['ap_aging_date_2'] : AP_AGING_DATE_2, ''); ?></td>
	  </tr>
	  <tr>
	    <td><?php echo CD_02_23_DESC; ?></td>
	    <td><?php echo html_input_field('ap_aging_heading_4', $_POST['ap_aging_heading_4'] ? $_POST['ap_aging_heading_4'] : AP_AGING_HEADING_4, ''); ?></td>
	    <td><?php echo CD_02_19_DESC; ?></td>
	    <td><?php echo html_input_field('ap_aging_date_3', $_POST['ap_aging_date_3'] ? $_POST['ap_aging_date_3'] : AP_AGING_DATE_3, ''); ?></td>
	  </tr>
	 </tbody>
	</table>
	<table class="ui-widget" style="border-collapse:collapse;width:100%">
	 <thead class="ui-widget-header">
	  <tr><th colspan="4"><?php echo TEXT_PREFERENCES; ?></th></tr>
	 </thead>
	 <tbody class="ui-widget-content">
	  <tr>
	    <td colspan="3"><?php echo CD_03_30_DESC; ?></td>
	    <td><?php echo html_pull_down_menu('ap_add_sales_tax_to_shipping', inv_calculate_tax_drop_down('v',true), $_POST['ap_add_sales_tax_to_shipping'] ? $_POST['ap_add_sales_tax_to_shipping'] : AP_ADD_SALES_TAX_TO_SHIPPING, ''); ?></td>
	  </tr>
	  <tr>
	    <td colspan="3"><?php echo CD_03_35_DESC; ?></td>
	    <td><?php echo html_pull_down_menu('auto_inc_vend_id', $sel_yes_no, $_POST['auto_inc_vend_id'] ? $_POST['auto_inc_vend_id'] : AUTO_INC_VEND_ID, ''); ?></td>
	  </tr>
	  <tr>
	    <td colspan="3"><?php echo CD_03_40_DESC; ?></td>
	    <td><?php echo html_pull_down_menu('ap_show_contact_status', $sel_yes_no, $_POST['ap_show_contact_status'] ? $_POST['ap_show_contact_status'] : AP_SHOW_CONTACT_STATUS, ''); ?></td>
	  </tr>
	  <tr>
	    <td colspan="3"><?php echo CD_03_50_DESC; ?></td>
	    <td><?php echo html_pull_down_menu('ap_tax_before_discount', $sel_yes_no, $_POST['ap_tax_before_discount'] ? $_POST['ap_tax_before_discount'] : AP_TAX_BEFORE_DISCOUNT, ''); ?></td>
	  </tr>
	 </tbody>
	</table>
  </fieldset>
</div>
