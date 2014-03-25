<?php
require_once(DIR_FS_MODULES . 'inventory/classes/inventory.php');
class ds extends inventory {//Description
	public $inventory_type			= 'ds';
	public $title					= INV_TYPES_DS;
	public $account_sales_income	= INV_DESC_DEFAULT_SALES;
	public $account_inventory_wage	= INV_DESC_DEFAULT_INVENTORY;
	public $account_cost_of_sales	= null;
	public $cost_method				= 'f'; // should be null not sure what will happen if i set this to null
	public $posible_cost_methodes   = array();
	
	function update_inventory_status($sku, $field, $adjustment, $item_cost, $vendor_id, $desc) {
		return true;
	}
}