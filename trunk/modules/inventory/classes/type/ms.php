<?php
require_once(DIR_FS_MODULES . 'inventory/classes/inventory.php');
class ms extends inventory {//Master Stock Item parent of mi
	public $inventory_type			= 'ms';
	public $title       			= INV_TYPES_MS;
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
	public $edit_ms_list			= true;
	
	function __construct(){
		parent::__construct();
		$this->tab_list['master'] = array('file'=>'template_tab_ms',	'tag'=>'master',    'order'=>30, 'text'=>INV_MS_ATTRIBUTES);
	}
	
	function get_item_by_id($id){
		$this->child_array = null;
		parent::get_item_by_id($id);
		$this->get_ms_list();
	}
	
	function get_item_by_sku($sku){
		$this->child_array = null;
		parent::get_item_by_sku($sku);
		$this->get_ms_list();
	}
	
	//this is to copy a product
	function copy($id, $newSku) {
		global $db;
		if(parent::copy($id, $newSku)){
			$result = $db->Execute("select * from " . TABLE_INVENTORY_MS_LIST . " where sku = '" . $this->old_sku . "'");
			$data_array = array(
				'sku'			=> $this->sku,
				'attr_0'   		=> $result->fields['attr_0'],
				'attr_name_0'	=> $result->fields['attr_name_0'],
				'attr_1'   		=> $result->fields['attr_1'],
				'attr_name_1'	=> $result->fields['attr_name_1']);
			db_perform(TABLE_INVENTORY_MS_LIST, $data_array, 'insert');
			$this->get_ms_list();
			return true;
		}else{
			return false;
		}
	}
	
	function get_ms_list(){
		global $db;
		$result = $db->Execute("select * from " . TABLE_INVENTORY_MS_LIST . " where sku = '" . $this->sku . "'");
	  	$this->ms_attr_0   = ($result->RecordCount() > 0) ? $result->fields['attr_0'] 		: '';
	  	$this->attr_name_0 = ($result->RecordCount() > 0) ? $result->fields['attr_name_0'] 	: '';
	  	$this->ms_attr_1   = ($result->RecordCount() > 0) ? $result->fields['attr_1'] 		: '';
	  	$this->attr_name_1 = ($result->RecordCount() > 0) ? $result->fields['attr_name_1'] 	: '';
		if ($this->ms_attr_0) {
			$temp = explode(',', $this->ms_attr_0);
			foreach ($temp as $key => $value) {
				if($value){
			  		$code = substr($value, 0, strpos($value, ':'));
			  		$desc = substr($value, strpos($value, ':') + 1);
			  		$this->attr_array0[$value] = array('id' => $value, 'text' => $code . ' : ' . $desc);
			  		$temp_ms0[$code] = $desc;
				}
			}
			ksort($this->attr_array0);
		}
		if ($this->ms_attr_1) {
			$temp = explode(',', $this->ms_attr_1);
			foreach ($temp as $key => $value) {
				if($value){
			  		$code = substr($value, 0, strpos($value, ':'));
			  		$desc = substr($value, strpos($value, ':') + 1);
			  		$this->attr_array1[$value] = array('id' => $value, 'text' => $code . ' : ' . $desc);
					$temp_ms1[$code] = $desc;
				}
			}
			ksort($this->attr_array1);
		}
		$result = $db->Execute("select * from " . TABLE_INVENTORY . " where sku like '" . $this->sku . "-%' and inventory_type = 'mi' order by sku asc");
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

	function check_remove($id){
		global $messageStack, $db;
		if(isset($id))$this->get_item_by_id($id);
		else return false;
		// check to see if there is inventory history remaining, if so don't allow delete
		$result = $db->Execute("select id from " . TABLE_INVENTORY_HISTORY . " where ( sku like '" . $this->sku  . "-%' or sku = '" . $this->sku  . "') and remaining > 0");
		if ($result->RecordCount() > 0) {
		 	$messageStack->add(INV_ERROR_DELETE_HISTORY_EXISTS, 'error');
		 	return false;
		}
		// check to see if this item is part of an assembly
		$result = $db->Execute("select id from " . TABLE_INVENTORY_ASSY_LIST . " where sku like '" . $this->sku  . "-%' or sku = '" . $this->sku  . "'");
		if ($result->RecordCount() > 0) {
	  		$messageStack->add(INV_ERROR_DELETE_ASSEMBLY_PART, 'error');
	  		return false;
		}
		$result = $db->Execute( "select id from " . TABLE_JOURNAL_ITEM . " where sku like '" . $this->sku  . "-%' or sku = '" . $this->sku  . "' limit 1");
		if ($result->Recordcount() > 0) {
			$messageStack->add(INV_ERROR_CANNOT_DELETE, 'error');
	  		return false;	
		}
		$this->remove();
	  	return true;
		
	}
	
	function remove(){
		global $db;
		$ms_array = $db->Execute("select * from " . TABLE_INVENTORY . " where sku like '" . $this->sku . "-%'");
		parent::remove();
		$db->Execute("delete from " . TABLE_INVENTORY_MS_LIST . " where sku = '" . $this->sku . "'");
		$db->Execute("delete from " . TABLE_INVENTORY . " where sku like '" . $this->sku . "-%'");
		$db->Execute("delete from " . TABLE_INVENTORY_PURCHASE . " where sku like '" . $this->sku . "-%'");
		while(!$ms_array->EOF){
			if($ms_array->fields['image_with_path'] != ''){
				$result = $db->Execute("select * from " . TABLE_INVENTORY . " where image_with_path = '" . $ms_array->fields['image_with_path'] ."'");
	  			if ( $result->RecordCount() == 0){ // delete image
					$file_path = DIR_FS_MY_FILES . $_SESSION['company'] . '/inventory/images/';
					if (file_exists($file_path . $ms_array->fields['image_with_path'])) unlink ($file_path . $ms_array->fields['image_with_path']);
	  			}
			}
			$ms_array->MoveNext();
		}
	}
	
	function save(){
		global $db, $messageStack, $security_level, $currencies;
		$current_situation = $db->Execute("select * from " . TABLE_INVENTORY . " where id = '{$this->id}'");
		$sql_data_array = parent::save();
		if ($sql_data_array == false) return false;
		$sql_data_array['inventory_type'] = 'mi';
		// 	split attributes
		$attr0 = array();
		$attr1 = array();
		if($this->ms_attr_0 != '') $attr0 = explode(',', $this->ms_attr_0);
		if($this->ms_attr_1 != '') $attr1 = explode(',', $this->ms_attr_1);
		if (empty($attr0)) {
			$this->get_ms_list();
			return false; // no attributes, nothing to do
		}
		// build skus
		$sku_list = array();
		for ($i = 0; $i < count($attr0); $i++) {
		 	$idx0 = explode(':', $attr0[$i]);
			if (empty($attr1)) {
				if($idx0[0] != ''){
					$sku_list[] = $this->sku . '-' . $idx0[0];
					$variables[$this->sku . '-' . $idx0[0]]['idx0'] = $idx0[1];
				}
			} else {
				for ($j = 0; $j < count($attr1); $j++) {
					$idx1 = explode(':', $attr1[$j]);
					if($idx0[0] != '' && $idx1[0] != '') { 
						$sku_list[] = $this->sku . '-' . $idx0[0] . $idx1[0];
						$variables[$this->sku . '-' . $idx0[0] . $idx1[0]]['idx0'] = $idx0[1];
						$variables[$this->sku . '-' . $idx0[0] . $idx1[0]]['idx1'] = $idx1[1];
					}
				}
			}
		}
		// either update, delete or insert sub skus depending on sku list
		$result = $db->Execute("select sku from " . TABLE_INVENTORY . " where inventory_type = 'mi' and sku like '{$this->sku}-%'");
		$existing_sku_list = array();
		while (!$result->EOF) {
			$existing_sku_list[] = $result->fields['sku'];
			$result->MoveNext();
		}
		$delete_list = array_diff($existing_sku_list, $sku_list);
		$update_list = array_intersect($existing_sku_list, $sku_list);
		$insert_list = array_diff($sku_list, $update_list);
		
		foreach($insert_list as $sku) { // first insert new sku's with all fields
			$sql_data_array['sku'] = $sku;
			$sql_data_array['description_short'] 	= sprintf($this->description_short, 	$variables[$sku]['idx0'], $variables[$sku]['idx1'] );
			$sql_data_array['description_purchase'] = sprintf($this->description_purchase, 	$variables[$sku]['idx0'], $variables[$sku]['idx1'] );
			$sql_data_array['description_sales'] 	= sprintf($this->description_sales, 	$variables[$sku]['idx0'], $variables[$sku]['idx1'] );
			db_perform(TABLE_INVENTORY, $sql_data_array, 'insert');
			foreach ($this->purchase_array as $purchase_row){
				$purchase_data_array = array (
					'sku'						=> $sku,
					'vendor_id' 				=> $purchase_row['vendor_id'],
					'description_purchase'		=> sprintf($purchase_row['description_purchase'], $variables[$sku]['idx0'], $variables[$sku]['idx1'] ),
					'item_cost'	 				=> $purchase_row['item_cost'],
					'purch_package_quantity'	=> $purchase_row['purch_package_quantity'],
					'purch_taxable'	 			=> $purchase_row['purch_taxable'],
					'price_sheet_v'				=> $purchase_row['price_sheet_v'],
				);
				db_perform(TABLE_INVENTORY_PURCHASE, $purchase_data_array, 'insert');
			}
		}
		if ($this->id != ''){ //only update fields that are changed otherwise fields in the child could be overwritten 
			foreach ($current_situation->fields as $key => $value) { // remove fields where the parent is unchanged because the childeren could have different values in these fields.
				switch($key){
					case 'description_short': 		if($this->description_short == $value) 		unset($sql_data_array[$key]); Break;
					case 'description_purchase': 	if($this->description_purchase == $value) 	unset($sql_data_array[$key]); Break;
					case 'description_sales': 		if($this->description_sales == $value) 		unset($sql_data_array[$key]); Break;
					case 'minimum_stock_level': 	if($currencies->clean_value($this->minimum_stock_level) == $currencies->clean_value($value)) 	unset($sql_data_array[$key]); Break;
					case 'reorder_quantity': 		if($currencies->clean_value($this->reorder_quantity) == $currencies->clean_value($value))		unset($sql_data_array[$key]); Break;
					default:						if($sql_data_array[$key] == $value) 		unset($sql_data_array[$key]);
				}
			}
		}
		foreach($update_list as $sku) { //update with reduced number of fields.
			$sql_data_array['sku'] = $sku; 
			if(isset($sql_data_array['description_short'])) 	$sql_data_array['description_short'] 	= sprintf($this->description_short, 	$variables[$sku]['idx0'], $variables[$sku]['idx1'] );
			if(isset($sql_data_array['description_purchase'])) 	$sql_data_array['description_purchase'] = sprintf($this->description_purchase, 	$variables[$sku]['idx0'], $variables[$sku]['idx1'] );
			if(isset($sql_data_array['description_sales'])) 	$sql_data_array['description_sales'] 	= sprintf($this->description_sales, 	$variables[$sku]['idx0'], $variables[$sku]['idx1'] );
			db_perform(TABLE_INVENTORY, $sql_data_array, 'update', "sku = '" . $sku . "'");
			foreach($this->backup_purchase_array as $backUpKey => $backUpRow) {
				$backUpRow['description_purchase'] = sprintf($backUpRow['description_purchase'], 	$variables[$sku]['idx0'], $variables[$sku]['idx1'] );
				$purchase_data_array = null;
				if($backUpRow['action'] == 'delete'){
					$result = $db->Execute("delete from " . TABLE_INVENTORY_PURCHASE . " where sku = '$sku' and vendor_id = '{$backUpRow['vendor_id']}'");
				} else if($backUpRow['action'] == 'insert'){
					$purchase_data_array = $backUpRow;
					unset($purchase_data_array['id']);
					unset($purchase_data_array['action']);
					$purchase_data_array['sku'] = $sku;
					db_perform(TABLE_INVENTORY_PURCHASE, $purchase_data_array, 'insert');
				}else{
					/*on purpose removed this part because iam not sure what to update and what not 
					 * $purchase_data_array = $backUpRow;
					unset($purchase_data_array['id']);
					unset($purchase_data_array['action']);
					foreach($backUpRow as $key => $value) {
						if($key == 'action' || $key == 'id' ) break;
						if($this->purchase_array[$backUpKey][$key] == $value){
							unset($purchase_data_array[$key]);
						}
					}
					db_perform(TABLE_INVENTORY_PURCHASE, $purchase_data_array, 'update', "sku = '" . $sku. "' and vendor_id = '".$backUpRow['vendor_id']."'");*/
				}
			}
		}
		if (count($delete_list) && $security_level < 4){
			$messageStack->add(ERROR_NO_PERMISSION,'error');
			$this->get_ms_list();
	  		return false;
		}
		foreach($delete_list as $sku) {
			$temp = $this->mi_check_remove($sku);
			if($temp === true){
				$result = $db->Execute("delete from " . TABLE_INVENTORY . " where sku = '$sku'");
				$result = $db->Execute("delete from " . TABLE_INVENTORY_PURCHASE . " where sku = '$sku'");
			}elseif ($temp === false){
				$result = $db->Execute("update " . TABLE_INVENTORY . " set inactive = '1' where sku = '$sku'");
			}
		}
		// update/insert into inventory_ms_list table
		$result = $db->Execute("select id from " . TABLE_INVENTORY_MS_LIST . " where sku = '{$this->sku}'");
		$exists = $result->RecordCount();	
		$data_array = array(
			'sku'         => $this->sku,
			'attr_0'      => $this->ms_attr_0,
			'attr_name_0' => $this->attr_name_0,
			'attr_1'      => $this->ms_attr_1,
			'attr_name_1' => $this->attr_name_1);
		if ($exists) {
			db_perform(TABLE_INVENTORY_MS_LIST, $data_array, 'update', "id = " . $result->fields['id']);
		} else {
			db_perform(TABLE_INVENTORY_MS_LIST, $data_array, 'insert');
		}
		$this->get_ms_list();
		return true;
	}
	
	function mi_check_remove($sku) {
		global $messageStack, $db;
		// check to see if there is inventory history remaining, if so don't allow delete
		$result = $db->Execute("select id from " . TABLE_INVENTORY_HISTORY . " where sku = '" . $sku . "' and remaining > 0");
		if ($result->RecordCount() > 0) {
			$messageStack->add(sprintf(INV_MS_ERROR_DELETE_HISTORY_EXISTS, $sku), 'caution');
		 	return 'remaining';
		}
		// check to see if this item is part of an assembly
		$result = $db->Execute("select id from " . TABLE_INVENTORY_ASSY_LIST . " where sku = '" . $sku . "'");
		if ($result->RecordCount() > 0) {
			$messageStack->add(sprintf(INV_MS_ERROR_DELETE_ASSEMBLY_PART, $sku), 'caution');
	  		return false;
		}
		$result = $db->Execute( "select id from " . TABLE_JOURNAL_ITEM . " where sku = '" . $sku . "' limit 1");
		if ($result->Recordcount() > 0) {
			$messageStack->add(sprintf(INV_MS_ERROR_CANNOT_DELETE, $sku), 'caution');
	  		return false;	
		}
	  	return true;
		
	}
}