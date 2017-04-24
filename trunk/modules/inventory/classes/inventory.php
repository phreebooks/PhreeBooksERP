<?php
// +-----------------------------------------------------------------+
// |                   PhreeBooks Open Source ERP                    |
// +-----------------------------------------------------------------+
// | Copyright(c) 2008-2015 PhreeSoft      (www.PhreeSoft.com)       |
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
//  Path: /modules/inventory/classes/inventory.php
//
namespace inventory\classes;
class inventory {
	public $inventory_type			= '';
	public $help_path   			= '07.04.01.02';
	public $title       			= '';
	public $auto_field    			= '';
	public $tab_list    			= array();
	public $account_sales_income	= INV_STOCK_DEFAULT_SALES;
	public $account_inventory_wage	= INV_STOCK_DEFAULT_INVENTORY;
	public $account_cost_of_sales	= INV_STOCK_DEFAULT_COS;
	public $item_taxable			= INVENTORY_DEFAULT_TAX;
	public $purch_taxable			= INVENTORY_DEFAULT_PURCH_TAX;
	public $store_stock 			= array();
	public $qty_table				= false;
	public $posible_transactions	= array('sell','purchase');
	public $purchase_array			= array();
	public $history 				= array();
	public $qty_per_store			= array();
	public $posible_cost_methodes   = array('f','l','a');
	public $not_used_fields			= array();
	public $attachments;
	public $assy_cost				= 0;
	public $remove_image			= false;
	public $purchases_history		= array();
	public $sales_history			= array();
	public $creation_date 			= 0;
	public $last_update   			= 0;

	/**
	 *
	 * this is the class construct
	 */
	public function __construct(){
		global $admin;
		\core\classes\messageStack::development("executing ".__METHOD__ );
		$this->security_level = \core\classes\user::validate(SECURITY_ID_MAINTAIN_INVENTORY); // in this case it must be done after the class is defined for
//		foreach ($_POST as $key => $value) $this->$key = $value;
	  	$this->fields 		 = new \inventory\classes\fields(false, $this->type);
		$this->tab_list['general']   = array('file'=>'template_tab_gen',	'tag'=>'general',  'order'=>10, 'text'=>TEXT_SYSTEM);
		$this->tab_list['history']   = array('file'=>'template_tab_hist',	'tag'=>'history',  'order'=>20, 'text'=>TEXT_HISTORY);
		$this->tab_list['connections'] = array('file'=>'template_connections',	'tag'=>'connections', 'order'=>22, 'text'=>TEXT_CONNECTIONS);
		$this->attachments = unserialize($this->attachments) !== false ? unserialize($this->attachments) : array();
	/*	if($this->auto_field){
			$result = $admin->DataBase->query("SELECT ".$this->auto_field." FROM ".TABLE_CURRENT_STATUS);
        	$this->new_sku = $result[$this->auto_field];
		}
		if ($this->id  != '') $this->getInventory();*/
	}

	/**
	 * this function gets inventory details from the database
	 */
	function getInventory(){
		\core\classes\messageStack::development("executing ".__METHOD__ );
		$this->purchases_history = null;
		$this->sales_history	 = null;
		$this->purchase_array	 = null;
		$this->attachments = $this['attachments'] ? unserialize($this['attachments']) : array();
		$this->remove_unwanted_keys();
		$this->get_qty();
		$this->assy_cost = $this->item_cost;
		$this->create_purchase_array();
	}

	/**
	 * this function gets inventory details from the database by id
	 * @param integer $id
	 */
	function get_item_by_id($id) {
		global $admin;
		\core\classes\messageStack::debug_log("executing ".__METHOD__." id = $id" );
		$this->purchases_history = null;
		$this->sales_history	 = null;
		$this->purchase_array	 = null;
		$this->id = $id;
		$result = $admin->DataBase->query("SELECT * FROM ".TABLE_INVENTORY." WHERE id = $id");
		$sql->execute();
		if ($sql->fetch(\PDO::FETCH_NUM) > 0) throw new \core\classes\userException(TEXT_NO_RECORD_FOUND);
		$result = $sql->fetch(\PDO::FETCH_ASSOC);
		foreach ($result as $key => $value) {
			if(is_null($value)) $this->$key = '';
			else $this->$key = $value;
		}
		$this->attachments = unserialize($this->attachments) !== false ? unserialize($this->attachments) : array();
		$this->remove_unwanted_keys();
		$this->get_qty();
		$this->assy_cost = $this->item_cost;
		$this->create_purchase_array();
		return true;
	}

	/**
	 * this function gets inventory details from the database by sku
	 * @param char $sku
	 */

	function get_item_by_sku($sku){
		global $admin;
		\core\classes\messageStack::debug_log("executing ".__METHOD__. " sku = $sku" );
		$this->purchases_history = null;
		$this->sales_history	 = null;
		$this->purchase_array	 = null;
		$this->sku = $sku;
		$sql = $admin->DataBase->prepare("SELECT * FROM " . TABLE_INVENTORY . " WHERE sku = '{$sku}'");
		$sql->execute();
		if ($sql->fetch(\PDO::FETCH_NUM) > 0) throw new \core\classes\userException(TEXT_NO_RECORD_FOUND);
		$result = $sql->fetch(\PDO::FETCH_ASSOC);
		foreach ($result as $key => $value) {
			if(is_null($value)) $this->$key = '';
			else $this->$key = $value;
		}
		// expand attachments
		$this->attachments = unserialize($this->attachments) !== false ? unserialize($this->attachments) : array();
		$this->remove_unwanted_keys();
		$this->get_qty();
		$this->assy_cost = $this->item_cost;
		$this->create_purchase_array();
		return true;
	}

	/**
	 * this function removes keys from this inventory type that we do not need.
	 */

	function remove_unwanted_keys(){
		\core\classes\messageStack::debug_log("executing ".__METHOD__ );
		$this->not_used_fields = $this->fields->unwanted_fields($this->inventory_type);
		foreach ($this->not_used_fields as $key => $value) {
			if(isset($this->$value)) unset($this->$value);
		}
	}

	function get_qty(){
		global $admin;
		\core\classes\messageStack::development("executing ".__METHOD__ );
		if(in_array('quantity_on_hand', $this->not_used_fields)) return;
		$raw_sql = " SELECT id, short_name, primary_name FROM " . TABLE_CONTACTS . " c JOIN " . TABLE_ADDRESS_BOOK . " a ON c.id = a.ref_id WHERE c.type = 'b' ORDER BY short_name ";
	  	$sql = $admin->DataBase->query($raw_sql);
	  	$sql->execute();
	  	$qty = $this->store_stock(0);
	  	$this->qty_per_store[0] = $qty;
	  	$this->quantity_on_hand = $qty;
	  	if(ENABLE_MULTI_BRANCH){
	  		$this->qty_table ='<table class="ui-widget" style="border-collapse:collapse;width:100%">'. chr(10);
			$this->qty_table .='  <thead class="ui-widget-header">'. chr(10);
		  	$this->qty_table .='	  <tr>';
		    $this->qty_table .='		<th>'. TEXT_STORE_ID.'</th>';
		    $this->qty_table .='		<th>'. TEXT_QUANTITY_IN_STOCK_SHORT .'</th>';
		  	$this->qty_table .='    </tr>'. chr(10);
		 	$this->qty_table .='  </thead>'. chr(10);
		 	$this->qty_table .='  <tbody class="ui-widget-content">'. chr(10);
		  	$this->qty_table .='    <tr>';
			$this->qty_table .='      <td>' . COMPANY_ID . '</td>';
			$this->qty_table .="      <td align='center'>{$qty}</td>";
		    $this->qty_table .='    </tr>' . chr(10);
		    while ($result = $sql->fetch(\PDO::FETCH_ASSOC)) {
		    	$qty = $this->store_stock($result['id']);
		  		$this->qty_per_store[$result['id']] = $qty;
		  		$this->quantity_on_hand += $qty;
		  		$this->qty_table .= '<tr>';
			  	$this->qty_table .= '  <td>' .$result['primary_name'] . '</td>';
			  	$this->qty_table .= '  <td align="center">' . $qty. '</td>';
		      	$this->qty_table .= '</tr>' . chr(10);
			}
	     	$this->qty_table .='  </tbody>'. chr(10);
	    	$this->qty_table .='</table>'. chr(10);
	  	}

	}

	function set_ajax_qty($branch_id){
		if(!isset($this->quantity_on_hand)) $this->quantity_on_hand = "NA";
		if(isset($this->qty_per_store[$branch_id])){
			$this->branch_qty_in_stock = $this->qty_per_store[$branch_id];
		}else{
			$this->branch_qty_in_stock = "NA";
		}
	}

	//this is to check if you are allowed to create a new product
	function check_create_new() {
		global $admin;
		\core\classes\messageStack::development("executing ".__METHOD__ );
		if (!$this->sku) $this->sku = $this->next_sku;
		$admin->classes['inventory']->validate_name($this->sku);
		return $this->create_new();
	}

	//this is the general create new inventory item
	function create_new() {
		\core\classes\messageStack::development("executing ".__METHOD__ );
		$sql_data_array = array(
	  		'sku'						=> $this->sku,
	  		'inventory_type'			=> $this->inventory_type,
	  		'cost_method'				=> $this->cost_method,
	  		'creation_date'				=> $this->creation_date,
	  		'last_update'				=> $this->last_update,
	  		'item_taxable'				=> $this->item_taxable,
	  		'purch_taxable'				=> $this->purch_taxable,
			'account_sales_income'   	=> $this->account_sales_income,
		    'account_inventory_wage'	=> $this->account_inventory_wage,
			'account_cost_of_sales'  	=> $this->account_cost_of_sales,
			'serialize'					=> $this->serialize,
			'creation_date'				=> date('Y-m-d H:i:s'),
			'last_update'				=> date('Y-m-d H:i:s'),
			);
		db_perform(TABLE_INVENTORY, $sql_data_array, 'insert');
		$this->get_item_by_id(\core\classes\PDO::lastInsertId('id'));
		$sql_data_array = array (
				'sku'					=> $this->sku,
				'description_purchase'	=> '',
				'purch_package_quantity'=> 1,
				'price_sheet_v'			=> '',
		);
		db_perform(TABLE_INVENTORY_PURCHASE, $sql_data_array, 'insert');
		gen_add_audit_log(TEXT_INVENTORY_ITEM . ' - '  . TEXT_ADD, TEXT_TYPE . ': ' . $this->inventory_type . ' - ' . $this->sku );
		return true;
	}

	/**
	 * This is to copy a product
	 * @param int $id
	 * @param string $newSku
	 * @throws Exception
	 */

	function copy($newSku) {
		global $admin;
		\core\classes\messageStack::development("executing ".__METHOD__ ." id = $this->id new sku = $newSku");
		if (!$newSku) $newSku = $this->next_sku;
		$admin->classes['inventory']->validate_name($newSku);
		if($this->id == '') throw new \core\classes\userException("id should be submitted in order to copy");
		$this->old_id					= $this->id;
		$this->old_sku					= $this->sku;
		$sql_data_array = array();
		$not_usable_keys = array('id','sku','last_journal_date','upc_code','image_with_path','quantity_on_hand','quantity_on_order','quantity_on_sales_order','quantity_on_allocation','creation_date','last_update','title','help_path','auto_field','tab_list','posible_transactions','posible_cost_methodes','store_stock','qty_table','purchase_array','history','qty_per_store','not_used_fields','assy_cost','remove_image','purchases_history','sales_history','security_level','fields','old_id','old_sku');
		foreach ($this as $key => $value) {
			if(!in_array($key, $not_usable_keys)) $sql_data_array[$key] = $value;
		}
		$this->sku 							= $newSku;
		$sql_data_array['sku']				= $newSku ;
		$sql_data_array['creation_date'] 	= date('Y-m-d H:i:s');
		$sql_data_array['last_update'] 		= date('Y-m-d H:i:s');
		$sql_data_array ['attachments'] 	= sizeof($this->attachments) > 0 ? serialize($this->attachments) : '';
		$sql_data_array['class'] 			= get_class($this);
		db_perform(TABLE_INVENTORY, $sql_data_array, 'insert');
		$this->id							= \core\classes\PDO::lastInsertId('id');
		$this->store_stock 					= array();
		$this->purchase_array				= array();
		$this->history 						= array();
		$this->qty_per_store				= array();
		$this->attachments					= array();
		$sql = $admin->DataBase->query("SELECT price_sheet_id, price_levels FROM " . TABLE_INVENTORY_SPECIAL_PRICES . " WHERE inventory_id = $id");
		$sql->execute();
  		while ($result = $sql->fetch(\PDO::FETCH_ASSOC)) {
  			$result['inventory_id'] = $this->id;
	  		db_perform(TABLE_INVENTORY_SPECIAL_PRICES, $output_array, 'insert');
		}
		$sql = $admin->DataBase->query("SELECT * FROM " . TABLE_INVENTORY_PURCHASE . " WHERE sku = '{$this->old_sku}'");
		$sql->execute();
  		while ($result = $sql->fetch(\PDO::FETCH_ASSOC)) {
			$sql_data_array = $result;
			$sql_data_array['sku'] = $this->sku;
			db_perform(TABLE_INVENTORY_PURCHASE, $sql_data_array, 'insert');
		}
		gen_add_audit_log(TEXT_INVENTORY_ITEM . ' - ' . TEXT_COPY, $this->old_sku . ' => ' . $this->sku);
		return true;
	}

	/*
 	* this function is for renaming
 	*/

	function rename($newSku){
		global $admin;
		\core\classes\messageStack::development("executing ".__METHOD__ . " new sku name = $newSku");
		if (!$newSku) $newSku = $this->next_sku;
		$admin->classes['inventory']->validate_name($newSku);
//		if(isset($id)) $this->get_item_by_id($id);
		$sku_list = array($this->sku);
		if (isset($this->edit_ms_list) && $this->edit_ms_list == true) { // build list of sku's to rename (without changing contents)
	  		$sql = $admin->DataBase->prepare("SELECT sku FROM " . TABLE_INVENTORY . " WHERE sku LIKE '{$this->sku}-%'");
	  		$sql->execute();
	  		while ($result = $sql->fetch(\PDO::FETCH_ASSOC)) $sku_list[] = $result['sku'];
		}
		// start transaction (needs to all work or reset to avoid unsyncing tables)
		$admin->DataBase->beginTransaction();
		// rename the afffected tables
		for ($i = 0; $i < count($sku_list); $i++) {
	  		$new_sku = str_replace($this->sku, $newSku, $sku_list[$i], $count = 1);
	  		$result = $admin->DataBase->exec("UPDATE " . TABLE_INVENTORY .           " SET sku = '{$new_sku}' WHERE sku = '{$sku_list[$i] }'");
	  		$result = $admin->DataBase->exec("UPDATE " . TABLE_INVENTORY_ASSY_LIST . " SET sku = '{$new_sku}' WHERE sku = '{$sku_list[$i] }'");
	  		$result = $admin->DataBase->exec("UPDATE " . TABLE_INVENTORY_COGS_OWED . " SET sku = '{$new_sku}' WHERE sku = '{$sku_list[$i] }'");
	  		$result = $admin->DataBase->exec("UPDATE " . TABLE_INVENTORY_HISTORY .   " SET sku = '{$new_sku}' WHERE sku = '{$sku_list[$i] }'");
	  		$result = $admin->DataBase->exec("UPDATE " . TABLE_INVENTORY_MS_LIST .   " SET sku = '{$new_sku}' WHERE sku = '{$sku_list[$i] }'");
	  		$result = $admin->DataBase->exec("UPDATE " . TABLE_JOURNAL_ITEM .        " SET sku = '{$new_sku}' WHERE sku = '{$sku_list[$i] }'");
	  		$result = $admin->DataBase->exec("UPDATE " . TABLE_INVENTORY_PURCHASE .  " SET sku = '{$new_sku}' WHERE sku = '{$sku_list[$i] }'");
		}
		$this->sku = $newSku;
		$admin->DataBase->commit();
		return true;
	}

	//this is to check if you are allowed to remove
	function check_remove() {
		global $admin;
		\core\classes\messageStack::development("executing ".__METHOD__ );
		if( $this->id == '' && $this->sku == '') throw new \core\classes\userException("id or sku should be submitted in order to delete");
		if( $this->sku == '' ) $this->get_item_by_id($this->id);
		// check to see if there is inventory history remaining, if so don't allow delete
		$result = $admin->DataBase->query("SELECT id FROM " . TABLE_INVENTORY_HISTORY . " WHERE sku = '{$this->sku}' AND remaining > 0");
		if ($result->fetch(\PDO::FETCH_NUM) > 0) throw new \core\classes\userException(INV_ERROR_DELETE_HISTORY_EXISTS);
		// check to see if this item is part of an assembly
		$result = $admin->DataBase->query("SELECT id FROM " . TABLE_INVENTORY_ASSY_LIST . " WHERE sku = '{$this->sku}'");
		if ($result->fetch(\PDO::FETCH_NUM) > 0) throw new \core\classes\userException(INV_ERROR_DELETE_ASSEMBLY_PART);
		$result = $admin->DataBase->query("SELECT id FROM " . TABLE_JOURNAL_ITEM . " WHERE sku = '{$this->sku}' LIMIT 1");
		if ($result->fetch(\PDO::FETCH_NUM) > 0) throw new \core\classes\userException(INV_ERROR_CANNOT_DELETE);
		$this->remove();
	  	return true;

	}

	// this is the general remove function
	// the function check_remove calls this function.
	function remove(){
		global $admin;
		\core\classes\messageStack::development("executing ".__METHOD__ );
		$admin->DataBase->exec("DELETE FROM " . TABLE_INVENTORY . " WHERE id = " . $this->id);
		if($this->image_with_path != '') {
			$result = $admin->DataBase->query("SELECT * FROM " . TABLE_INVENTORY . " WHERE image_with_path = '{$this->image_with_path}'");
	  		if ( $result->fetch(\PDO::FETCH_NUM) == 0) { // delete image
				$file_path = DIR_FS_MY_FILES . $_SESSION['user']->company . '/inventory/images/';
				if (file_exists($file_path . $this->image_with_path)) unlink ($file_path . $this->image_with_path);
	  		}
		}
	  	$admin->DataBase->exec("DELETE FROM " . TABLE_INVENTORY_SPECIAL_PRICES . " WHERE inventory_id = '{$this->id}'");
	  	$admin->DataBase->exec("DELETE FROM " . TABLE_INVENTORY_PURCHASE . " WHERE sku = '{$this->sku}'");
		gen_add_audit_log(TEXT_INVENTORY_ITEM . ' - ' . TEXT_DELETE, $this->sku);
	}

	// this is the general save function.
	function save() {
		global $admin;
		\core\classes\messageStack::development("executing ".__METHOD__ );
	    $sql_data_array = $this->fields->what_to_save();
	    // handle the checkboxes
	    if ($this->id == '') $admin->classes['inventory']->validate_name($this->sku);
	    $sql_data_array['class'] = get_class($this);
	    $sql_data_array['inactive'] = isset($_POST['inactive']) ? $_POST['inactive'] : '0'; // else unchecked
	    foreach(array('quantity_on_hand', 'quantity_on_order', 'quantity_on_sales_order', 'quantity_on_allocation', 'creation_date', 'last_update', 'last_journal_date' ) as $key){
	    	unset($sql_data_array[$key]);
	    }
		$sql_data_array['last_update'] = date('Y-m-d H-i-s');
		$file_path = DIR_FS_MY_FILES . $_SESSION['user']->company . '/inventory/images';
		if ($this->remove_image == '1') { // update the image with relative path
	  		if ($this->image_with_path && file_exists($file_path . '/' . $this->image_with_path)) unlink ($file_path . '/' . $this->image_with_path);
	  		$this->image_with_path = '';
	  		$sql_data_array['image_with_path'] = '';
	  		unset($this->remove_image); // this is not a db field, just an action
		}
		if (is_uploaded_file($_FILES['inventory_image']['tmp_name'])) {
	  		if ($this->image_with_path && file_exists($file_path . '/' . $this->image_with_path)) unlink ($file_path . '/' . $this->image_with_path);
      		$this->inventory_path = str_replace('\\', '/', $this->inventory_path);
			// strip beginning and trailing slashes if present
			if (substr($this->inventory_path, 0, 1) == '/') $this->inventory_path = substr($this->inventory_path, 1);// remove leading '/' if there
	  		if (substr($this->inventory_path, -1, 1) == '/') $this->inventory_path = substr($this->inventory_path, 0, -1);// remove trailing '/' if there
	  		if ($this->inventory_path) $file_path .= '/' . $this->inventory_path;
	  		$temp_file_name = $_FILES['inventory_image']['tmp_name'];
	  		$file_name = $_FILES['inventory_image']['name'];
	  		validate_path($file_path);
	  		validate_upload('inventory_image', 'image', 'jpg');
	  		$result = $admin->DataBase->query("select * from " . TABLE_INVENTORY . " where image_with_path = '" . ($this->inventory_path ? ($this->inventory_path . '/') : '') . $file_name ."'");
	  		if ( $result->fetch(\PDO::FETCH_NUM) != 0) throw new \core\classes\userException(INV_IMAGE_DUPLICATE_NAME);
	  		if (!copy($temp_file_name, $file_path . '/' . $file_name)) throw new \core\classes\userException(INV_IMAGE_FILE_WRITE_ERROR);
			$this->image_with_path = ($this->inventory_path ? ($this->inventory_path . '/') : '') . $file_name;
		  	$sql_data_array['image_with_path'] = $this->image_with_path; // update the image with relative path
		}
		if ($this->id != ''){
			$result = $admin->DataBase->query("select attachments from ".TABLE_INVENTORY." where id = $this->id");
			$this->attachments = $result['attachments'] ? unserialize($result['attachments']) : array();
			$image_id = 0;
	  		while ($image_id < 100) { // up to 100 images
	    		if (isset($_POST['rm_attach_'.$image_id])) {
					@unlink(INVENTORY_DIR_ATTACHMENTS . "inventory_{$this->id}_{$image_id}.zip");
			  		unset($this->attachments[$image_id]);
	    		}
	    		$image_id++;
	  		}
	  		if (is_uploaded_file($_FILES['file_name']['tmp_name'])) { // find an image slot to use
	    		$image_id = 0;
	    		while (true) {
		    		if (!file_exists(INVENTORY_DIR_ATTACHMENTS."inventory_{$this->id}_{$image_id}.zip")) break;
		    		$image_id++;
	    		}
	    		saveUploadZip('file_name', INVENTORY_DIR_ATTACHMENTS, "inventory_{$this->id}_{$image_id}.zip");
	    		$this->attachments[$image_id] = $_FILES['file_name']['name'];
	  		}
	  		$sql_data_array ['attachments'] = sizeof($this->attachments) > 0 ? serialize($this->attachments) : '';
		}
		unset($sql_data_array['last_journal_date]']);
		$keys = array_keys($sql_data_array);
		$fields = implode(", ",$keys);
		$placeholder = "'".implode("', '",$sql_data_array)."'";
		unset($sql_data_array['id']);
		unset($sql_data_array['creation_date']);
		$output = implode(', ', array_map(
				function ($v, $k) { return sprintf("%s='%s'", $k, $v); },
				$sql_data_array,
				array_keys($sql_data_array)
				));
		$sql = $admin->DataBase->prepare("INSERT INTO ".TABLE_INVENTORY." ($fields) VALUES ($placeholder) ON DUPLICATE KEY UPDATE $output");//@todo
		$sql->execute();
		if ($this->id == '') $this->id = $admin->DataBase->lastInsertId();
		gen_add_audit_log(TEXT_INVENTORY_ITEM . ' - ' . TEXT_UPDATE, $this->sku . ' - ' . $sql_data_array['description_short']);
		return true;
	}

	function create_purchase_array(){
		global $admin;
		\core\classes\messageStack::debug_log("executing ".__METHOD__ );
		if(!in_array('purchase',$this->posible_transactions)) return;
		$sql = $admin->DataBase->prepare("select * from " . TABLE_INVENTORY_PURCHASE . " where sku = '{$this->sku}'");
		$sql->execute();
		$this->purchase_array = $sql->fetchAll(\PDO::FETCH_ASSOC);
		\core\classes\messageStack::debug_log("found ".print_r($this->purchase_array, true) );
	}

	function store_purchase_array(){
		global $admin;
		$lowest_cost = isset($this->item_cost) ? $this->item_cost : 99999999999;
		$this->backup_purchase_array = array();
		$result = $admin->DataBase->query("SELECT * FROM ".TABLE_INVENTORY_PURCHASE." WHERE sku='$this->sku'");
		while(!$result->EOF){
			$this->backup_purchase_array[$result['id']]= array (
				'id'						=> $result['id'],
				'vendor_id' 				=> $result['vendor_id'],
				'description_purchase'		=> $result['description_purchase'],
				'item_cost'	 				=> $result['item_cost'],
				'purch_package_quantity'	=> $result['purch_package_quantity'],
				'purch_taxable'	 			=> $result['purch_taxable'],
				'price_sheet_v'				=> $result['price_sheet_v'],
				'action'					=> 'delete',
			);// mark delete by default overwrite later if
			$result->MoveNext();
		}
		$i = 0;
		if($_POST['vendor_id_array']) foreach ($_POST['vendor_id_array'] as $key => $value) {
			$sql_data_array = array ();
			if($_POST['vendor_id_array'][$key] == '' && $_POST['description_purchase_array'][$key] == '' && $admin->currencies->clean_value($_POST['item_cost_array'][$key]) == 0) break;
			$sql_data_array['sku'] = $this->sku;
			$this->purchase_array[$i]['id']	= isset($_POST['row_id_array'][$key]) ? $_POST['row_id_array'][$key] : '';
			if(isset($_POST['vendor_id_array'][$key])) {
				$sql_data_array['vendor_id'] 					= $_POST['vendor_id_array'][$key];
				$this->purchase_array[$i]['vendor_id'] 				= $_POST['vendor_id_array'][$key];
			}
			if(isset($_POST['description_purchase_array'][$key])){
				$sql_data_array['description_purchase']			= $_POST['description_purchase_array'][$key];
				$this->purchase_array[$i]['description_purchase']	= $_POST['description_purchase_array'][$key];
			}
			if(isset($_POST['item_cost_array'][$key])) {
				$sql_data_array['item_cost']	 				= $admin->currencies->clean_value($_POST['item_cost_array'][$key]);
				$this->purchase_array[$i]['item_cost']	 			= $admin->currencies->clean_value($_POST['item_cost_array'][$key]);
			}
			if(isset($_POST['purch_package_quantity_array'][$key])){
				$sql_data_array['purch_package_quantity']		= $_POST['purch_package_quantity_array'][$key];
				$this->purchase_array[$i]['purch_package_quantity']	= $_POST['purch_package_quantity_array'][$key];
			}
			if(isset($_POST['purch_taxable_array'][$key]))	{
				$sql_data_array['purch_taxable']	 			= $_POST['purch_taxable_array'][$key];
				$this->purchase_array[$i]['purch_taxable']	 		= $_POST['purch_taxable_array'][$key];
			}
			if(isset($_POST['price_sheet_v_array'][$key])){
				$sql_data_array['price_sheet_v']				= $_POST['price_sheet_v_array'][$key];
				$this->purchase_array[$i]['price_sheet_v']			= $_POST['price_sheet_v_array'][$key];
			}
			if(!empty($sql_data_array)){
				if(isset($_POST['row_id_array'][$key]) && $_POST['row_id_array'][$key] != ''){//update
					$this->backup_purchase_array[$_POST['row_id_array'][$key]]['action'] = 'update';
					db_perform(TABLE_INVENTORY_PURCHASE, $sql_data_array, 'update', "id = " . $_POST['row_id_array'][$key]);
				}else{//insert
					db_perform(TABLE_INVENTORY_PURCHASE, $sql_data_array, 'insert');
					$id = \core\classes\PDO::lastInsertId('id');
					$this->backup_purchase_array[$id]= array (
						'id'						=> $id,
						'vendor_id' 				=> $_POST['vendor_id_array'][$key],
						'description_purchase'		=> $_POST['description_purchase_array'][$key],
						'item_cost'	 				=> $_POST['item_cost_array'][$key],
						'purch_package_quantity'	=> $_POST['purch_package_quantity_array'][$key],
						'purch_taxable'	 			=> $_POST['purch_taxable_array'][$key],
						'price_sheet_v'				=> $_POST['price_sheet_v_array'][$key],
						'action'					=> 'insert',
					);// mark delete by default overwrite later if
				}
				$lowest_cost = min($lowest_cost, $sql_data_array['item_cost']);
				if ($lowest_cost == $sql_data_array['item_cost']) $this->min_vendor_id = $sql_data_array['vendor_id'];
			}
			$i++;
		}
		foreach($this->backup_purchase_array as $key => $value){
			if($value['action'] == 'delete') $result = $admin->DataBase->exec("delete from " . TABLE_INVENTORY_PURCHASE . " where id = '" . $value['id'] . "'");
		}
		return $lowest_cost == 99999999999 ? 0 : $lowest_cost; //added in case no purchase data entered when creating new product
	}

/*******************************************************************************************************************/
// START Journal Functions
/*******************************************************************************************************************/

	function inventory_auto_add($sku, $desc, $item_cost, $full_price, $vendor_id){
		$sql_data_array = array(
	  		'sku'						=> $sku,
	  		'inventory_type'			=> $this->inventory_type,
			'description_short'      	=> $desc,
		  	'description_purchase'   	=> $desc,
		  	'description_sales'      	=> $desc,
			'vendor_id' 				=> $vendor_id,
	  		'cost_method'				=> $this->cost_method,
	  		'creation_date'				=> $this->creation_date,
	  		'last_update'				=> $this->last_update,
	  		'item_taxable'				=> $this->item_taxable,
	  		'purch_taxable'				=> $this->purch_taxable,
			'account_sales_income'   	=> $this->account_sales_income,
		    'account_inventory_wage'	=> $this->account_inventory_wage,
			'account_cost_of_sales'  	=> $this->account_cost_of_sales,
			'serialize'					=> $this->serialize,
			'item_cost'              	=> $item_cost,
			'full_price'             	=> $full_price,
			'creation_date'				=> date('Y-m-d H:i:s'),
			'last_update'				=> date('Y-m-d H:i:s'),
		);
		db_perform(TABLE_INVENTORY, $sql_data_array, 'insert');
		$this->get_item_by_id(\core\classes\PDO::lastInsertId('id'));
		$sql_data_array = array (
			'sku'						=> $sku,
			'vendor_id' 				=> $vendor_id,
			'description_purchase'		=> $desc,
			'item_cost'	 				=> $item_cost,
			'purch_package_quantity'	=> 1,
			'purch_taxable'	 			=> $this->purch_taxable,
		);
		db_perform(TABLE_INVENTORY_PURCHASE, $sql_data_array, 'insert');
		return true;
	}

	function update_inventory_status($sku, $field, $adjustment, $item_cost, $vendor_id, $desc){
		$sql = "update " . TABLE_INVENTORY_PURCHASE . " set item_cost = '$item_cost'";
	  	$sql .= " where sku = '$sku' and vendor_id = '$vendor_id'";
	  	$result = $admin->DataBase->query($sql);
		if($result->fetch(\PDO::FETCH_NUM) == 0) {
			$sql_data_array = array (
			  'sku'						=> $sku,
			  'vendor_id' 				=> $vendor_id,
			  'description_purchase'	=> $desc,
			  'item_cost'	 			=> $item_cost,
			  'purch_package_quantity'	=> 1,
			  'purch_taxable'	 		=> $this->purch_taxable,
			);
			db_perform(TABLE_INVENTORY_PURCHASE, $sql_data_array, 'insert');
		}
	  	$sql = "update " . TABLE_INVENTORY . " set $field = $field + $adjustment, ";
	  	$sql .= "last_journal_date = now() where sku = '$sku'";
	  	$result = $admin->DataBase->query($sql);
		return true;
	}
	
/*******************************************************************************************************************/
// END Journal Functions
/*******************************************************************************************************************/
	
	function store_stock($store_id) {
		global $admin;
		$sql = "SELECT sum(remaining) as remaining FROM " . TABLE_INVENTORY_HISTORY . " WHERE store_id = '$store_id' and sku = '$this->sku'";
		$store_bal = $admin->DataBase->query($sql);
		$sql = "SELECT sum(qty) as qty FROM " . TABLE_INVENTORY_COGS_OWED . " WHERE store_id = '$store_id' and sku = '$this->sku'";
		$qty_owed = $admin->DataBase->query($sql);
		return ($store_bal['remaining'] - $qty_owed['qty']);
	}
	
	function calculate_sales_price($qty, $contact_id = 0, $type = 'c') {
		global $admin;
		$price_sheet = '';
		$contact_tax = 1;
		if ($contact_id) {
			$contact = $admin->DataBase->query("SELECT type, price_sheet, tax_id FROM " . TABLE_CONTACTS . " WHERE id = '$contact_id'");
			$type        = $contact['type'];
			$price_sheet = $contact['price_sheet'];
			$contact_tax = $contact['tax_id'];
		}
		// get the inventory prices
		if($type == 'v'){
			if ($contact_id) $inventory = $admin->DataBase->query("SELECT p.item_cost, a.full_price, a.price_sheet, p.price_sheet_v, a.item_taxable, p.purch_taxable FROM " . TABLE_INVENTORY . " a JOIN " . TABLE_INVENTORY_PURCHASE . " p ON a.sku = p.sku WHERE a.sku = '$this->sku' and p.vendor_id = '$contact_id'");
			else $inventory = $admin->DataBase->query("SELECT MAX(p.item_cost) as item_cost, a.full_price, a.price_sheet, p.price_sheet_v, a.item_taxable, p.purch_taxable FROM " . TABLE_INVENTORY . " a JOIN " . TABLE_INVENTORY_PURCHASE . " p ON a.sku = p.sku WHERE a.sku = '$this->sku'");
			$inv_price_sheet = $inventory['price_sheet_v'];
		}else{
			$inventory = $admin->DataBase->query("SELECT MAX(p.item_cost) as item_cost, a.full_price, a.price_sheet, p.price_sheet_v, a.item_taxable, p.purch_taxable FROM " . TABLE_INVENTORY . " a JOIN " . TABLE_INVENTORY_PURCHASE . " p ON a.sku = p.sku WHERE a.sku = '$this->sku'");
			$inv_price_sheet = $inventory['price_sheet'];
		}
		// set the default tax rates
		$purch_tax = ($contact_tax == 0 && $type=='v') ? 0 : $inventory['purch_taxable'];
		$sales_tax = ($contact_tax == 0 && $type=='c') ? 0 : $inventory['item_taxable'];
		// determine what price sheet to use, priority: customer, inventory, default
		if ($price_sheet <> '') {
			$sheet_name = $price_sheet;
		} elseif ($inv_price_sheet <> '') {
			$sheet_name = $inv_price_sheet;
		} else {
			$default_sheet = $admin->DataBase->query("select sheet_name from " . TABLE_PRICE_SHEETS . " where type = '$type' and default_sheet = '1'");
			$sheet_name = ($default_sheet->fetch(\PDO::FETCH_NUM) == 0) ? '' : $default_sheet['sheet_name'];
		}
		// determine the sku price ranges from the price sheet in effect
		$price = '0.0';
		$levels = false;
		if ($sheet_name <> '') {
			$sql = "SELECT id, default_levels FROM " . TABLE_PRICE_SHEETS . "
			WHERE inactive = '0' and type = '$type' and sheet_name = '$sheet_name' and
			(expiration_date is null or expiration_date = '0000-00-00' or expiration_date >= '" . date('Y-m-d') . "')";
			$price_sheets = $admin->DataBase->query($sql);
			// retrieve special pricing for this inventory item
			$raw_sql = "SELECT price_sheet_id, price_levels FROM " . TABLE_INVENTORY_SPECIAL_PRICES . " WHERE price_sheet_id = '{$price_sheets['id']}' and inventory_id = $this->id";
			$sql = $admin->DataBase->prepare($raw_sql);
			$sql->execute();
			$special_prices = array();
			while ($result = $sql->fetch(\PDO::FETCH_ASSOC)){
				$special_prices[$result['price_sheet_id']] = $result['price_levels'];
			}
			$levels = isset($special_prices[$price_sheets['id']]) ? $special_prices[$price_sheets['id']] : $price_sheets['default_levels'];
		}
		if ($levels) {
			$prices = inv_calculate_prices($inventory['item_cost'], $inventory['full_price'], $levels, $qty);
			if(is_array($prices)) foreach ($prices as $value) if ($qty >= $value['qty']) $price = $admin->currencies->clean_value($value['price']);
		} else {
			$price = ($type=='v') ? $inventory['item_cost'] : $inventory['full_price'];
		}
		if ($price == '' || $price == null) $price = 0.0;
		return array('price'=>$price, 'sales_tax'=>$sales_tax, 'purch_tax'=>$purch_tax);
	}
	

	function __destruct(){
//		if(DEBUG) print_r($this);
	}
}