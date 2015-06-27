<?php
require_once(DIR_FS_MODULES . 'inventory/classes/inventory.php');
class mi extends inventory { //Master Stock Sub Item. child of ma (master assembly)
	public $inventory_type			= 'mi';
	public $title 					= INV_TYPES_MI;
	public $master					= '';
	public $account_sales_income	= INV_MASTER_STOCK_DEFAULT_SALES;
	public $account_inventory_wage	= INV_MASTER_STOCK_DEFAULT_INVENTORY;
	public $account_cost_of_sales	= INV_MASTER_STOCK_DEFAULT_COS;	
	public $attr_array0 			= array();
	public $attr_array1 			= array();
	public $ms_attr_0				= '';
	public $ms_attr_1				= '';
	public $attr_name_0				= '';
	public $attr_name_1				= '';
	public $cost_method				= INV_MASTER_STOCK_DEFAULT_COSTING;
	public $child_array 			= array();
	public $edit_ms_list			= false;
	
	function __construct(){
		parent::__construct();
		$this->tab_list['master'] = array('file'=>'template_tab_ms',	'tag'=>'master',    'order'=>30, 'text'=>INV_MS_ATTRIBUTES);
	}
	
	function get_item_by_id($id){
		parent::get_item_by_id($id);
		$this->get_ms_list();
	}
	
	function get_item_by_sku($sku){
		parent::get_item_by_sku($sku);
		$this->get_ms_list();
	}
	
	function copy($id, $newSku) {
		global $messageStack;
		$messageStack->add(INV_ERROR_CANNOT_COPY, 'error');
		return false;
	}
	
	function check_remove($id){ // this is disabled in the form but just in case, error here as well
		global $messageStack;
		$messageStack->add('Master Stock Sub Items are not allowed to be deleted separately!','error');
		return false;
	}
	
	function get_ms_list(){
		global $db;
		$master = explode('-',$this->sku); 
		$this->master = $master[0];
		$result = $db->Execute("select * from " . TABLE_INVENTORY_MS_LIST . " where sku = '" . $this->master . "'");
	  	$this->ms_attr_0   = ($result->RecordCount() > 0) ? $result->fields['attr_0'] : '';
	  	$this->attr_name_0 = ($result->RecordCount() > 0) ? $result->fields['attr_name_0'] : '';
	  	$this->ms_attr_1   = ($result->RecordCount() > 0) ? $result->fields['attr_1'] : '';
	  	$this->attr_name_1 = ($result->RecordCount() > 0) ? $result->fields['attr_name_1'] : '';
		if ($this->ms_attr_0) {
			$temp = explode(',', $this->ms_attr_0);
			for ($i = 0; $i < count($temp); $i++) {
			  $code = substr($temp[$i], 0, strpos($temp[$i], ':'));
			  $desc = substr($temp[$i], strpos($temp[$i], ':') + 1);
			  $this->attr_array0[] = array('id' => $code . ':' . $desc, 'text' => $code . ' : ' . $desc);
			  $temp_ms0[$code] = $desc;
			}
		}
		if ($this->ms_attr_1) {
			$temp = explode(',', $this->ms_attr_1);
			for ($i = 0; $i < count($temp); $i++) {
			  $code = substr($temp[$i], 0, strpos($temp[$i], ':'));
			  $desc = substr($temp[$i], strpos($temp[$i], ':') + 1);
			  $this->attr_array1[] = array('id' => $code . ':' . $desc, 'text' => $code . ' : ' . $desc);
			  $temp_ms1[$code] = $desc;
			}
		}
		$result = $db->Execute("select * from " . TABLE_INVENTORY . " where sku like '" . $this->master . "-%' and inventory_type = 'mi' and sku<>'".$this->sku."'");
		$i = 0;
		while(!$result->EOF){
			$temp = explode('-',$result->fields['sku']); 
			$this->child_array[$i] = array(	'id'       		=> $result->fields['id'],
											'sku'      		=> $result->fields['sku'],
											'inactive' 		=> $result->fields['inactive'],
											'desc' 			=> $result->fields['description_short'],
											'0'				=> $temp_ms0[substr($temp[1],0,2)],
											'1'				=> (strlen($temp[1])>2)? $temp_ms1[substr($temp[1],2,4)] : '',
											'on_hand'		=> $result->fields['quantity_on_hand'],
											'on_order'		=> $result->fields['quantity_on_order'],
											'on_sales'		=> $result->fields['quantity_on_sales_order'],
											'min_stock'		=> $result->fields['minimum_stock_level'],
											'reorder_qty'	=> $result->fields['reorder_quantity'],
											'tax'			=> $result->fields['item_taxable'],
			);
			$temp =  inv_calculate_sales_price(1, $result->fields['id'], 0, 'v');
			$this->child_array[$i]['cost']	= $temp['price'];
			$temp =  inv_calculate_sales_price(1, $result->fields['id'], 0, 'c');
			$this->child_array[$i]['price']	= $temp['price'];
			$i++;
			$result->MoveNext();
		}
	}
}