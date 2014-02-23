<?php
namespace inventory\classes\type;
class ai extends \inventory\classes\inventory {//Activity Item
	//cost_methods 'f' =( First-in, First-out),  'l' =( Last-in, First-out) , 'a' =( Average Costing )
	public $inventory_type			= 'ai';
	public $title 					= INV_TYPES_AI;
	public $account_sales_income	= null;
	public $account_inventory_wage	= null;
	public $account_cost_of_sales	= null;	
	public $cost_method				= 'f';
	public $posible_cost_methodes   = array();
	
	function update_inventory_status($sku, $field, $adjustment, $item_cost, $vendor_id, $desc){
		return true;
	}
}