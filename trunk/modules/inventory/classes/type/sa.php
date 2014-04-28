<?php
require_once(DIR_FS_MODULES . 'inventory/classes/inventory.php');
class sa extends inventory {//Serialized Assembly
	public $inventory_type			= 'sa';
	public $title 					= INV_TYPES_SA;
	public $serialize 				= 1;
    public $account_sales_income	= INV_SERIALIZE_DEFAULT_SALES;
	public $account_inventory_wage	= INV_SERIALIZE_DEFAULT_INVENTORY;
	public $account_cost_of_sales	= INV_SERIALIZE_DEFAULT_COS; 
	public $cost_method				= 'f';
	public $bom		 				= array();
	public $allow_edit_bom			= true;
	public $posible_cost_methodes   = array('f');
	
	function __construct(){
		parent::__construct();
		$this->tab_list['bom'] = array('file'=>'template_tab_bom',	'tag'=>'bom',    'order'=>30, 'text'=>INV_BOM);
	}
	
	function get_item_by_id($id){
		parent::get_item_by_id($id);
		$this->get_bom_list();
		$this->get_sr_list();
		$this->allow_edit_bom = (($this->last_journal_date == '0000-00-00 00:00:00' || $this->last_journal_date == '') && ($this->quantity_on_hand == 0|| $this->quantity_on_hand == '')) ? true : false;
	}
	
	function get_item_by_sku($sku){
		parent::get_item_by_sku($sku);
		$this->get_bom_list();
		$this->get_sr_list();
		$this->allow_edit_bom = (($this->last_journal_date == '0000-00-00 00:00:00' || $this->last_journal_date == '') && ($this->quantity_on_hand == 0|| $this->quantity_on_hand == '')) ? true : false;
	}
	
	function get_sr_list(){
		global $db;
		$branches = gen_get_store_ids();
		$this->quantity_on_hand = 0;
		$result = $db->Execute("select store_id, qty, serialize_number from " . TABLE_INVENTORY_HISTORY . " 
	  		where sku = '" . $this->sku . "' and remaining > 0 order by store_id");
  		$this->qty_table ='<table class="ui-widget" style="border-collapse:collapse;width:100%">'. chr(10);
		$this->qty_table .='  <thead class="ui-widget-header">'. chr(10);
	  	$this->qty_table .='	  <tr>';
	    $this->qty_table .='		<th>'. GEN_STORE_ID.'</th>';
	    $this->qty_table .='		<th>'. TEXT_QUANTITY.'</th>';
	    $this->qty_table .='		<th>'. INV_HEADING_SERIAL_NUMBER .'</th>';
	  	$this->qty_table .='    </tr>'. chr(10);
	 	$this->qty_table .='  </thead>'. chr(10);
	 	$this->qty_table .='  <tbody class="ui-widget-content">'. chr(10);
	    while (!$result->EOF) {
	  		$this->quantity_on_hand += $result->fields['qty'];
	  		$this->qty_table .= '<tr>';
		  	$this->qty_table .= '  <td>' .$branches[$result->fields['store_id']]['text'] . '</td>';
		  	$this->qty_table .= '  <td>' .$result->fields['qty'] . '</td>';
		  	$this->qty_table .= '  <td align="center">' . $result->fields['serialize_number']. '</td>';
	      	$this->qty_table .= '</tr>' . chr(10);
	      	$result->MoveNext();
		}
     	$this->qty_table .='  </tbody>'. chr(10);
    	$this->qty_table .='</table>'. chr(10);

    	$field_list  = array('m.id', 'm.post_date', 'm.purchase_invoice_id', 'm.closed', 'm.bill_primary_name', 'm.total_amount', 'i.serialize_number');
    	$sql   = "SELECT ".implode(', ', $field_list)." FROM ".TABLE_JOURNAL_MAIN." m JOIN ".TABLE_JOURNAL_ITEM." i on m.id=i.ref_id
    	WHERE m.journal_id=12 AND i.sku='$this->sku' ORDER BY m.purchase_invoice_id DESC";
    	$this->orderHistory = $db->Execute($sql);
    	$this->tab_list['orderhist'] = array('file'=>'template_tab_hist_sr', 'tag'=>'orderhist', 'order'=>40, 'text'=>'Unit History');
	}

	function get_bom_list(){
		global $db;
		$this->assy_cost = 0;
		$result = $db->Execute("select i.id as inventory_id, l.id, l.sku, l.description, l.qty from " . TABLE_INVENTORY_ASSY_LIST . " l join " . TABLE_INVENTORY . " i on l.sku = i.sku where l.ref_id = " . $this->id . " order by l.id");
		$x =0;
		while (!$result->EOF) {
	  		$this->bom[$x] = $result->fields;
	  		$prices = inv_calculate_sales_price(abs($result->fields['qty']), $result->fields['inventory_id'], 0, 'v');
			$this->bom[$x]['item_cost'] = strval($prices['price']);
			$this->assy_cost += $result->fields['qty'] * strval($prices['price']);
	  		$prices = inv_calculate_sales_price(abs($result->fields['qty']), $result->fields['inventory_id'], 0, 'c');
			$this->bom[$x]['full_price'] = strval($prices['price']);
	  		$x++;
	  		$result->MoveNext();
		}
	}
	
	function remove(){
		global $db;
		parent::remove();
		$db->Execute("delete from " . TABLE_INVENTORY_ASSY_LIST . " where sku = '" . $this->sku . "'");
	}
	
	function save(){
		global $db, $currencies, $messageStack;
		$bom_list = array();
		for($x=0; $x < count($_POST['assy_sku']); $x++) {
			$bom_list[$x] = array(
			  	'ref_id'      => $this->id,
			  	'sku'         => db_prepare_input($_POST['assy_sku'][$x]),
				'description' => db_prepare_input($_POST['assy_desc'][$x]),
				'qty'         => $currencies->clean_value(db_prepare_input($_POST['assy_qty'][$x])),
			);
		  	$result = $db->Execute("select id from " . TABLE_INVENTORY . " where sku = '". $_POST['assy_sku'][$x]."'" );
		  	if (($result->RecordCount() == 0 || $currencies->clean_value($_POST['assy_qty'][$x]) == 0) && $_POST['assy_sku'][$x] =! '') { 
		  		// show error, bad sku, negative quantity. error check sku is valid and qty > 0
				$error = $messageStack->add(INV_ERROR_BAD_SKU . db_prepare_input($_POST['assy_sku'][$x]), 'error');
		  	}else{
		  		$prices = inv_calculate_sales_price(abs($this->bom[$x]['qty']), $result->fields['id'], 0, 'v');
				$bom_list[$x]['item_cost'] = strval($prices['price']);
		  		$prices = inv_calculate_sales_price(abs($this->bom[$x]['qty']), $result->fields['id'], 0, 'c');
				$bom_list[$x]['full_price'] = strval($prices['price']);
		  	}
		}
		$this->bom = $bom_list;
		if (!parent::save()) return false;	
		$result = $db->Execute("select last_journal_date, quantity_on_hand  from " . TABLE_INVENTORY . " where id = " . $this->id);
		$this->allow_edit_bom = (($result->fields['last_journal_date'] == '0000-00-00 00:00:00' || $result->fields['last_journal_date'] == '') && ($result->fields['quantity_on_hand'] == 0|| $result->fields['quantity_on_hand'] == '')) ? true : false;
		if($error) return false;
	  	if ($this->allow_edit_bom == true) { // only update if no posting has been performed
	  		$result = $db->Execute("delete from " . TABLE_INVENTORY_ASSY_LIST . " where ref_id = " . $this->id);
			while ($list_array = array_shift($bom_list)) {
				unset($list_array['item_cost']);
				unset($list_array['full_price']);
				db_perform(TABLE_INVENTORY_ASSY_LIST, $list_array, 'insert');
			}
	  	}
	  	return true;
	}
}