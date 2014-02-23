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
//  Path: /modules/accounts/pages/admin/template_tab_inventory.php
//

?>
<div title="<?php echo MENU_HEADING_INVENTORY;?>" id="tab_inventory">
  <fieldset>
    <table>
	  <tr><th colspan="5"><?php echo TEXT_DEFAULT_GL_ACCOUNTS; ?></th></tr>
	  <tr>
	    <th><?php echo TEXT_INVENTORY_TYPES;   ?></th>
	    <th><?php echo TEXT_SALES_ACCOUNT;     ?></th>
	    <th><?php echo TEXT_INVENTORY_ACCOUNT; ?></th>
	    <th><?php echo TEXT_COGS_ACCOUNT;      ?></th>
	    <th><?php echo TEXT_COST_METHOD;       ?></th>
	  </tr>
	  <tr>
	    <td><?php echo TEXT_STOCK_ITEMS; ?></td>
	    <td align="center" nowrap="nowrap"><?php echo html_combo_box('inv_stock_default_sales', $inc_chart,  $_POST['inv_stock_default_sales'] ? $_POST['inv_stock_default_sales'] : INV_STOCK_DEFAULT_SALES, ''); ?></td>
	    <td align="center" nowrap="nowrap"><?php echo html_combo_box('inv_stock_default_inventory', $inv_chart,  $_POST['inv_stock_default_inventory'] ? $_POST['inv_stock_default_inventory'] : INV_STOCK_DEFAULT_INVENTORY, ''); ?></td>
	    <td align="center" nowrap="nowrap"><?php echo html_combo_box('inv_stock_default_cos', $cog_chart,  $_POST['inv_stock_default_cos'] ? $_POST['inv_stock_default_cos'] : INV_STOCK_DEFAULT_COS, ''); ?></td>
	    <td align="center"><?php echo html_pull_down_menu('inv_stock_default_costing',  $cost_methods, $_POST['inv_stock_default_costing'] ? $_POST['inv_stock_default_costing'] : INV_STOCK_DEFAULT_COSTING, ''); ?></td>
	  </tr>
	  <tr>
	    <td><?php echo TEXT_MS_ITEMS; ?></td>
	    <td align="center" nowrap="nowrap"><?php echo html_combo_box('inv_master_stock_default_sales', $inc_chart,  $_POST['inv_master_stock_default_sales'] ? $_POST['inv_master_stock_default_sales'] : INV_MASTER_STOCK_DEFAULT_SALES, ''); ?></td>
	    <td align="center" nowrap="nowrap"><?php echo html_combo_box('inv_master_stock_default_inventory', $inv_chart,  $_POST['inv_master_stock_default_inventory'] ? $_POST['inv_master_stock_default_inventory'] : INV_MASTER_STOCK_DEFAULT_INVENTORY, ''); ?></td>
	    <td align="center" nowrap="nowrap"><?php echo html_combo_box('inv_master_stock_default_cos', $cog_chart,  $_POST['inv_master_stock_default_cos'] ? $_POST['inv_master_stock_default_cos'] : INV_MASTER_STOCK_DEFAULT_COS, ''); ?></td>
	    <td align="center"><?php echo html_pull_down_menu('inv_master_stock_default_costing',  $cost_methods, $_POST['inv_master_stock_default_costing'] ? $_POST['inv_master_stock_default_costing'] : INV_MASTER_STOCK_DEFAULT_COSTING, ''); ?></td>
	  </tr>
	  <tr>
	    <td><?php echo TEXT_ASSY_ITEMS; ?></td>
	    <td align="center" nowrap="nowrap"><?php echo html_combo_box('inv_assy_default_sales', $inc_chart,  $_POST['inv_assy_default_sales'] ? $_POST['inv_assy_default_sales'] : INV_ASSY_DEFAULT_SALES, ''); ?></td>
	    <td align="center" nowrap="nowrap"><?php echo html_combo_box('inv_assy_default_inventory', $inv_chart,  $_POST['inv_assy_default_inventory'] ? $_POST['inv_assy_default_inventory'] : INV_ASSY_DEFAULT_INVENTORY, ''); ?></td>
	    <td align="center" nowrap="nowrap"><?php echo html_combo_box('inv_assy_default_cos', $cog_chart,  $_POST['inv_assy_default_cos'] ? $_POST['inv_assy_default_cos'] : INV_ASSY_DEFAULT_COS, ''); ?></td>
	    <td align="center"><?php echo html_pull_down_menu('inv_assy_default_costing',  $cost_methods, $_POST['inv_assy_default_costing'] ? $_POST['inv_assy_default_costing'] : INV_ASSY_DEFAULT_COSTING, ''); ?></td>
	  </tr>
	  <tr>
	    <td><?php echo TEXT_SERIAL_ITEMS; ?></td>
	    <td align="center" nowrap="nowrap"><?php echo html_combo_box('inv_serialize_default_sales', $inc_chart,  $_POST['inv_serialize_default_sales'] ? $_POST['inv_serialize_default_sales'] : INV_SERIALIZE_DEFAULT_SALES, ''); ?></td>
	    <td align="center" nowrap="nowrap"><?php echo html_combo_box('inv_serialize_default_inventory', $inv_chart,  $_POST['inv_serialize_default_inventory'] ? $_POST['inv_serialize_default_inventory'] : INV_SERIALIZE_DEFAULT_INVENTORY, ''); ?></td>
	    <td align="center" nowrap="nowrap"><?php echo html_combo_box('inv_serialize_default_cos', $cog_chart,  $_POST['inv_serialize_default_cos'] ? $_POST['inv_serialize_default_cos'] : INV_SERIALIZE_DEFAULT_COS, ''); ?></td>
	    <td><?php echo '&nbsp;'; ?></td>
	  </tr>
	  <tr>
	    <td><?php echo TEXT_NS_ITEMS; ?></td>
	    <td align="center" nowrap="nowrap"><?php echo html_combo_box('inv_non_stock_default_sales', $inc_chart,  $_POST['inv_non_stock_default_sales'] ? $_POST['inv_non_stock_default_sales'] : INV_NON_STOCK_DEFAULT_SALES, ''); ?></td>
	    <td align="center" nowrap="nowrap"><?php echo html_combo_box('inv_non_stock_default_inventory', $inv_chart,  $_POST['inv_non_stock_default_inventory'] ? $_POST['inv_non_stock_default_inventory'] : INV_NON_STOCK_DEFAULT_INVENTORY, ''); ?></td>
	    <td align="center" nowrap="nowrap"><?php echo html_combo_box('inv_non_stock_default_cos', $cog_chart,  $_POST['inv_non_stock_default_cos'] ? $_POST['inv_non_stock_default_cos'] : INV_SERIALIZE_DEFAULT_COS, ''); ?></td>
	    <td><?php echo '&nbsp;'; ?></td>
	  </tr>
	  <tr>
	    <td><?php echo TEXT_SRV_ITEMS; ?></td>
	    <td align="center" nowrap="nowrap"><?php echo html_combo_box('inv_service_default_sales', $inc_chart,  $_POST['inv_service_default_sales'] ? $_POST['inv_service_default_sales'] : INV_SERVICE_DEFAULT_SALES, ''); ?></td>
	    <td align="center" nowrap="nowrap"><?php echo html_combo_box('inv_service_default_inventory', $inv_chart,  $_POST['inv_service_default_inventory'] ? $_POST['inv_service_default_inventory'] : INV_SERVICE_DEFAULT_INVENTORY, ''); ?></td>
	    <td align="center" nowrap="nowrap"><?php echo html_combo_box('inv_service_default_cos', $cog_chart,  $_POST['inv_service_default_cos'] ? $_POST['inv_service_default_cos'] : INV_SERVICE_DEFAULT_COS, ''); ?></td>
	    <td><?php echo '&nbsp;'; ?></td>
	  </tr>
	  <tr>
	    <td><?php echo TEXT_LABOR_ITEMS; ?></td>
	    <td align="center" nowrap="nowrap"><?php echo html_combo_box('inv_labor_default_sales', $inc_chart,  $_POST['inv_labor_default_sales'] ? $_POST['inv_labor_default_sales'] : INV_LABOR_DEFAULT_SALES, ''); ?></td>
	    <td align="center" nowrap="nowrap"><?php echo html_combo_box('inv_labor_default_inventory', $inv_chart,  $_POST['inv_labor_default_inventory'] ? $_POST['inv_labor_default_inventory'] : INV_LABOR_DEFAULT_INVENTORY, ''); ?></td>
	    <td align="center" nowrap="nowrap"><?php echo html_combo_box('inv_labor_default_cos', $cog_chart,  $_POST['inv_labor_default_cos'] ? $_POST['inv_labor_default_cos'] : INV_LABOR_DEFAULT_COS, ''); ?></td>
	    <td><?php echo '&nbsp;'; ?></td>
	  </tr>
	  <tr>
	    <td><?php echo TEXT_ACT_ITEMS; ?></td>
	    <td align="center" nowrap="nowrap"><?php echo html_combo_box('inv_activity_default_sales', $inc_chart,  $_POST['inv_activity_default_sales'] ? $_POST['inv_activity_default_sales'] : INV_ACTIVITY_DEFAULT_SALES, ''); ?></td>
	    <td><?php echo '&nbsp;'; ?></td>
	    <td><?php echo '&nbsp;'; ?></td>
	    <td><?php echo '&nbsp;'; ?></td>
	  </tr>
	  <tr>
	    <td><?php echo TEXT_CHARGE_ITEMS; ?></td>
	    <td align="center" nowrap="nowrap"><?php echo html_combo_box('inv_charge_default_sales', $inc_chart,  $_POST['inv_charge_default_sales'] ? $_POST['inv_charge_default_sales'] : INV_CHARGE_DEFAULT_SALES, ''); ?></td>
	    <td><?php echo '&nbsp;'; ?></td>
	    <td><?php echo '&nbsp;'; ?></td>
	    <td><?php echo '&nbsp;'; ?></td>
	  </tr>

	  <tr><th colspan="5"><?php echo TEXT_PREFERENCES; ?></th></tr>
	  <tr>
	    <td colspan="4"><?php echo CD_05_50_DESC; ?></td>
	    <td nowrap="nowrap"><?php echo html_pull_down_menu('inventory_default_tax', $sel_sales_tax, $_POST['inventory_default_tax'] ? $_POST['inventory_default_tax'] : INVENTORY_DEFAULT_TAX, ''); ?></td>
	  </tr>
	  <tr>
	    <td colspan="4"><?php echo CD_05_52_DESC; ?></td>
	    <td nowrap="nowrap"><?php echo html_pull_down_menu('inventory_default_purch_tax', $sel_purch_tax, $_POST['inventory_default_purch_tax'] ? $_POST['inventory_default_purch_tax'] : INVENTORY_DEFAULT_PURCH_TAX, ''); ?></td>
	  </tr>
	  <tr>
	    <td colspan="4"><?php echo CD_05_55_DESC; ?></td>
	    <td><?php echo html_pull_down_menu('inventory_auto_add', $sel_yes_no, $_POST['inventory_auto_add'] ? $_POST['inventory_auto_add'] : INVENTORY_AUTO_ADD, ''); ?></td>
	  </tr>
	  <tr>
	    <td colspan="4"><?php echo CD_05_60_DESC; ?></td>
	    <td><?php echo html_pull_down_menu('inventory_auto_fill', $sel_yes_no, $_POST['inventory_auto_fill'] ? $_POST['inventory_auto_fill'] : INVENTORY_AUTO_FILL, ''); ?></td>
	  </tr>
	  <tr>
	    <td colspan="4"><?php echo CD_05_65_DESC; ?></td>
	    <td><?php echo html_pull_down_menu('ord_enable_line_item_bar_code', $sel_yes_no, $_POST['ord_enable_line_item_bar_code'] ? $_POST['ord_enable_line_item_bar_code'] : ORD_ENABLE_LINE_ITEM_BAR_CODE, ''); ?></td>
	  </tr>
	  <tr>
	    <td colspan="4"><?php echo CD_05_70_DESC; ?></td>
	    <td><?php echo html_input_field('ord_bar_code_length', $_POST['ord_bar_code_length'] ? $_POST['ord_bar_code_length'] : ORD_BAR_CODE_LENGTH, ''); ?></td>
	  </tr>
	  <tr>
	    <td colspan="4"><?php echo CD_05_75_DESC; ?></td>
	    <td><?php echo html_pull_down_menu('enable_auto_item_cost', $sel_item_cost, $_POST['enable_auto_item_cost'] ? $_POST['enable_auto_item_cost'] : ENABLE_AUTO_ITEM_COST, ''); ?></td>
	  </tr>
	</table>
  </fieldset>
</div>
