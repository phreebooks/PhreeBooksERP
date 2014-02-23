<?php
namespace inventory\classes\type;
class sv extends \inventory\classes\inventory {//Service
	public $title       			= INV_TYPES_SV;
	public $account_sales_income	= INV_SERVICE_DEFAULT_SALES;
	public $account_inventory_wage	= INV_SERVICE_DEFAULT_INVENTORY;
	public $account_cost_of_sales	= INV_SERVICE_DEFAULT_COS;
	public $cost_method				= 'f';
	public $posible_cost_methodes   = array('f');
	public $not_used_fields			= array('image_with_path', 'cost_method', 'item_weight', 'quantity_on_hand', 'quantity_on_order', 'quantity_on_sales_order', 'quantity_on_allocation', 'minimum_stock_level', 'reorder_quantity', 'upc_code', 'serialize');

	function update_inventory_status($sku, $field, $adjustment, $item_cost, $vendor_id, $desc){
		return true;
	}
}