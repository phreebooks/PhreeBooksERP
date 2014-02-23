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
//  Path: /modules/phreebooks/pages/admin/template_tab_customers.php
//

?>
<div title="<?php echo MENU_HEADING_CUSTOMERS;?>" id="tab_customers">
  <fieldset>
	<table class="ui-widget" style="border-collapse:collapse;width:100%">
	 <thead class="ui-widget-header">
	  <tr><th colspan="4"><?php echo TEXT_DEFAULT_GL_ACCOUNTS; ?></th></tr>
	 </thead>
	 <tbody class="ui-widget-content">
	  <tr>
	    <td colspan="3"><?php echo CD_02_01_DESC; ?></td>
	    <td nowrap="nowrap"><?php echo html_combo_box('ar_default_gl_acct', $ar_chart, $_POST['ar_default_gl_acct'] ? $_POST['ar_default_gl_acct'] : AR_DEFAULT_GL_ACCT, ''); ?></td>
	  </tr>
	  <tr>
	    <td colspan="3"><?php echo CD_02_02_DESC; ?></td>
	    <td><?php echo html_combo_box('ar_def_gl_sales_acct', $inc_chart, $_POST['ar_def_gl_sales_acct'] ? $_POST['ar_def_gl_sales_acct'] : AR_DEF_GL_SALES_ACCT, ''); ?></td>
	  </tr>
	  <tr>
	    <td colspan="3"><?php echo CD_02_03_DESC; ?></td>
	    <td><?php echo html_combo_box('ar_sales_receipts_account', $cash_chart, $_POST['ar_sales_receipts_account'] ? $_POST['ar_sales_receipts_account'] : AR_SALES_RECEIPTS_ACCOUNT, ''); ?></td>
	  </tr>
	  <tr>
	    <td colspan="3"><?php echo CD_02_04_DESC; ?></td>
	    <td><?php echo html_combo_box('ar_discount_sales_account', $inc_chart, $_POST['ar_discount_sales_account'] ? $_POST['ar_discount_sales_account'] : AR_DISCOUNT_SALES_ACCOUNT, ''); ?></td>
	  </tr>
	  <tr>
	    <td colspan="3"><?php echo CD_02_05_DESC; ?></td>
	    <td><?php echo html_combo_box('ar_def_freight_acct', $inc_chart, $_POST['ar_def_freight_acct'] ? $_POST['ar_def_freight_acct'] : AR_DEF_FREIGHT_ACCT, ''); ?></td>
	  </tr>
	  <tr>
	    <td colspan="3"><?php echo CD_02_06_DESC; ?></td>
	    <td><?php echo html_combo_box('ar_def_deposit_acct', $cash_chart, $_POST['ar_def_deposit_acct'] ? $_POST['ar_def_deposit_acct'] : AR_DEF_DEPOSIT_ACCT, ''); ?></td>
	  </tr>
	  <tr>
	    <td colspan="3"><?php echo CD_02_07_DESC; ?></td>
	    <td><?php echo html_combo_box('ar_def_dep_liab_acct', $ocl_chart, $_POST['ar_def_dep_liab_acct'] ? $_POST['ar_def_dep_liab_acct'] : AR_DEF_DEP_LIAB_ACCT, ''); ?></td>
	  </tr>
	 </tbody>
	</table>
	<table class="ui-widget" style="border-collapse:collapse;width:100%">
	 <thead class="ui-widget-header">
	  <tr><th colspan="4"><?php echo TEXT_PAYMENT_TERMS; ?></th></tr>
	 </thead>
	 <tbody class="ui-widget-content">
	  <tr>
	    <td><?php echo CD_02_11_DESC; ?></td>
	    <td><?php echo html_checkbox_field('ar_use_credit_limit', $inc_chart, $_POST['ar_use_credit_limit'] ? $_POST['ar_use_credit_limit'] : AR_USE_CREDIT_LIMIT, ''); ?></td>
	    <td><?php echo sprintf(CD_02_12_DESC, DEFAULT_CURRENCY); ?></td>
	    <td><?php echo html_input_field('ar_credit_limit_amount', $_POST['ar_credit_limit_amount'] ? $_POST['ar_credit_limit_amount'] : AR_CREDIT_LIMIT_AMOUNT, 'style="text-align:right"'); ?></td>
	  </tr>
	  <tr>
	    <td colspan="2"><?php echo CD_02_10_DESC;  ?></td>
	    <td colspan="2">
		  <?php echo html_input_field('ar_prepayment_discount_percent', $_POST['ar_prepayment_discount_percent'] ? $_POST['ar_prepayment_discount_percent'] : AR_PREPAYMENT_DISCOUNT_PERCENT, 'size="10" style="text-align:right"'); ?>
	      <?php echo CD_02_13_DESC; ?>
		  <?php echo html_input_field('ar_prepayment_discount_days', $_POST['ar_prepayment_discount_days'] ? $_POST['ar_prepayment_discount_days'] : AR_PREPAYMENT_DISCOUNT_DAYS, 'size="5" style="text-align:right"'); ?>
	      <?php echo CD_02_14_DESC; ?>
		  <?php echo html_input_field('ar_num_days_due', $_POST['ar_num_days_due'] ? $_POST['ar_num_days_due'] : AR_NUM_DAYS_DUE, 'size="5" style="text-align:right"'); ?>
	      <?php echo CD_02_15_DESC; ?>
		</td>
	  </tr>
	  <tr>
	    <td colspan="3"><?php echo APPLY_CUSTOMER_CREDIT_LIMIT_DESC; ?></td>
	    <td><?php echo html_pull_down_menu('apply_customer_credit_limit', $sel_yes_no, $_POST['apply_customer_credit_limit'] ? $_POST['apply_customer_credit_limit'] : APPLY_CUSTOMER_CREDIT_LIMIT, ''); ?></td>
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
	    <td><?php echo html_input_field('ar_aging_heading_1', $_POST['ar_aging_heading_1'] ? $_POST['ar_aging_heading_1'] : AR_AGING_HEADING_1, ''); ?></td>
	    <td><?php echo CD_02_16_DESC; ?></td>
	    <td><?php echo html_pull_down_menu('ar_account_aging_start', $sel_inv_due, $_POST['ar_account_aging_start'] ? $_POST['ar_account_aging_start'] : AR_ACCOUNT_AGING_START, ''); ?></td>
	  </tr>
	  <tr>
	    <td><?php echo CD_02_21_DESC; ?></td>
	    <td><?php echo html_input_field('ar_aging_heading_2', $_POST['ar_aging_heading_2'] ? $_POST['ar_aging_heading_2'] : AR_AGING_HEADING_2, ''); ?></td>
	    <td><?php echo CD_02_17_DESC; ?></td>
	    <td><?php echo html_input_field('ar_aging_period_1', $_POST['ar_aging_period_1'] ? $_POST['ar_aging_period_1'] : AR_AGING_PERIOD_1, ''); ?></td>
	  </tr>
	  <tr>
	    <td><?php echo CD_02_22_DESC; ?></td>
	    <td><?php echo html_input_field('ar_aging_heading_3', $_POST['ar_aging_heading_3'] ? $_POST['ar_aging_heading_3'] : AR_AGING_HEADING_3, ''); ?></td>
	    <td><?php echo CD_02_18_DESC; ?></td>
	    <td><?php echo html_input_field('ar_aging_period_2', $_POST['ar_aging_period_2'] ? $_POST['ar_aging_period_2'] : AR_AGING_PERIOD_2, ''); ?></td>
	  </tr>
	  <tr>
	    <td><?php echo CD_02_23_DESC; ?></td>
	    <td><?php echo html_input_field('ar_aging_heading_4', $_POST['ar_aging_heading_4'] ? $_POST['ar_aging_heading_4'] : AR_AGING_HEADING_4, ''); ?></td>
	    <td><?php echo CD_02_19_DESC; ?></td>
	    <td><?php echo html_input_field('ar_aging_period_3', $_POST['ar_aging_period_3'] ? $_POST['ar_aging_period_3'] : AR_AGING_PERIOD_3, ''); ?></td>
	  </tr>
	 </tbody>
	</table>
	<table class="ui-widget" style="border-collapse:collapse;width:100%">
	 <thead class="ui-widget-header">
	  <tr><th colspan="4"><?php echo TEXT_PREFERENCES; ?></th></tr>
	 </thead>
	 <tbody class="ui-widget-content">
	  <tr>
	    <td colspan="3"><?php echo CD_02_24_DESC; ?></td>
	    <td><?php echo html_pull_down_menu('ar_calculate_finance_charge', $sel_yes_no, $_POST['ar_calculate_finance_charge'] ? $_POST['ar_calculate_finance_charge'] : AR_CALCULATE_FINANCE_CHARGE, ''); ?></td>
	  </tr>
	  <tr>
	    <td colspan="3"><?php echo CD_02_30_DESC; ?></td>
	    <td><?php echo html_pull_down_menu('ar_add_sales_tax_to_shipping', inv_calculate_tax_drop_down('c',true), $_POST['ar_add_sales_tax_to_shipping'] ? $_POST['ar_add_sales_tax_to_shipping'] : AR_ADD_SALES_TAX_TO_SHIPPING, ''); ?></td>
	  </tr>
	  <tr>
	    <td colspan="3"><?php echo CD_02_35_DESC; ?></td>
	    <td><?php echo html_pull_down_menu('auto_inc_cust_id', $sel_yes_no, $_POST['auto_inc_cust_id'] ? $_POST['auto_inc_cust_id'] : AUTO_INC_CUST_ID, ''); ?></td>
	  </tr>
	  <tr>
	    <td colspan="3"><?php echo CD_02_40_DESC; ?></td>
	    <td><?php echo html_pull_down_menu('ar_show_contact_status', $sel_yes_no, $_POST['ar_show_contact_status'] ? $_POST['ar_show_contact_status'] : AR_SHOW_CONTACT_STATUS, ''); ?></td>
	  </tr>
	  <tr>
	    <td colspan="3"><?php echo CD_02_50_DESC; ?></td>
	    <td><?php echo html_pull_down_menu('ar_tax_before_discount', $sel_yes_no, $_POST['ar_tax_before_discount'] ? $_POST['ar_tax_before_discount'] : AR_TAX_BEFORE_DISCOUNT, ''); ?></td>
	  </tr>
	 </tbody>
	</table>
  </fieldset>
</div>
