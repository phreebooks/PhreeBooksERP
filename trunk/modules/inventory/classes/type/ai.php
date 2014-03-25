<?php
require_once(DIR_FS_MODULES . 'inventory/classes/inventory.php');
class ai extends inventory { //Activity Item
	//cost_methods 'f' =( First-in, First-out),  'l' =( Last-in, First-out) , 'a' =( Average Costing )
	public $inventory_type			= 'ai';
	public $title 					= INV_TYPES_AI;
	public $account_sales_income	= INV_ACTIVITY_DEFAULT_SALES;
	public $account_inventory_wage	= INV_ACTIVITY_DEFAULT_INVENTORY;
	public $account_cost_of_sales	= null;
	public $cost_method				= 'f';
	public $posible_cost_methodes   = array();
	
	function update_inventory_status($sku, $field, $adjustment, $item_cost, $vendor_id, $desc){
		return true;
	}
}