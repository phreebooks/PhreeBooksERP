<?php
namespace inventory\classes\type;
class lb extends \inventory\classes\inventory {//Labor
	public $inventory_type			= 'lb';
	public $title					= INV_TYPES_LB;
	public $account_sales_income	= INV_LABOR_DEFAULT_SALES;
	public $account_inventory_wage	= INV_LABOR_DEFAULT_INVENTORY;
	public $account_cost_of_sales	= INV_LABOR_DEFAULT_COS;
	public $cost_method				= 'f';
	public $posible_cost_methodes   = array('f');

	function update_inventory_status($sku, $field, $adjustment, $item_cost, $vendor_id, $desc){
		return true;
	}
}