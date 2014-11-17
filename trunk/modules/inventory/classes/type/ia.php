<?php
namespace inventory\classes\type;
class ia extends \inventory\classes\inventory { //Master Build Sub Item. child of mb (master assembly) combination of mi and ma
	public $inventory_type			= 'ia';
	public $title 					= TEXT_ITEM_ASSEMBLY_PART;
	public $edit_ms_list			= false;
	public $master					= '';
	public $account_sales_income	= INV_ASSY_DEFAULT_SALES;
	public $account_inventory_wage	= INV_ASSY_DEFAULT_INVENTORY;
	public $account_cost_of_sales	= INV_ASSY_DEFAULT_COS;
	public $cost_method				= INV_ASSY_DEFAULT_COSTING;
	public $attr_array0 			= array();
	public $attr_array1 			= array();
	public $ms_attr_0				= '';
	public $ms_attr_1				= '';
	public $attr_name_0				= '';
	public $attr_name_1				= '';
	public $child_array 			= array();
	public $bom		 				= array();
	public $allow_edit_bom			= true;
	public $posible_transactions	= array('sell');

	function __construct(){
		parent::__construct();
		$this->tab_list['master'] = array('file'=>'template_tab_ms',	'tag'=>'master',    'order'=>30, 'text'=>TEXT_MASTER_STOCK_ATTRIBUTES);
		$this->tab_list['bom'] = array('file'=>'template_tab_bom',	'tag'=>'bom',    'order'=>30, 'text'=>TEXT_BILL_OF_MATERIALS);
	}

	function get_item_by_id($id){
		parent::get_item_by_id($id);
		$this->get_ms_list();
		$this->get_bom_list();
		$this->allow_edit_bom = (($this->last_journal_date == '0000-00-00 00:00:00' || $this->last_journal_date == '') && ($this->quantity_on_hand == 0|| $this->quantity_on_hand == '')) ? true : false;
	}

	function get_item_by_sku($sku){
		parent::get_item_by_sku($sku);
		$this->get_ms_list();
		$this->get_bom_list();
		$this->allow_edit_bom = (($this->last_journal_date == '0000-00-00 00:00:00' || $this->last_journal_date == '') && ($this->quantity_on_hand == 0|| $this->quantity_on_hand == '')) ? true : false;
	}

	function get_bom_list(){
		global $admin;
		$this->assy_cost = 0;
		$result = $admin->DataBase->query("select i.id as inventory_id, l.id, l.sku, l.description, l.qty as qty from " . TABLE_INVENTORY_ASSY_LIST . " l join " . TABLE_INVENTORY . " i on l.sku = i.sku where l.ref_id = " . $this->id . " order by l.id");
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

	function copy($id, $newSku) {
		throw new \core\classes\userException(INV_ERROR_CANNOT_COPY);
	}

	function check_remove($id){ // this is disabled in the form but just in case, error here as well
		throw new \core\classes\userException('Master Stock Sub Items are not allowed to be deleted separately!');
	}

	function get_ms_list(){
		global $admin;
		$master = explode('-',$this->sku);
		$this->master = $master[0];
		$result = $admin->DataBase->query("select * from " . TABLE_INVENTORY_MS_LIST . " where sku = '" . $this->master . "'");
	  	$this->ms_attr_0   = ($result->rowCount() > 0) ? $result->fields['attr_0'] : '';
	  	$this->attr_name_0 = ($result->rowCount() > 0) ? $result->fields['attr_name_0'] : '';
	  	$this->ms_attr_1   = ($result->rowCount() > 0) ? $result->fields['attr_1'] : '';
	  	$this->attr_name_1 = ($result->rowCount() > 0) ? $result->fields['attr_name_1'] : '';
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
		$result = $admin->DataBase->query("select * from " . TABLE_INVENTORY . " where sku like '" . $this->master . "-%' and inventory_type = 'ia' and sku<>'".$this->sku."'");
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

	function save(){
		global $admin, $currencies;
		$bom_list = array();
		for($x=0; $x < count($_POST['assy_sku']); $x++) {
			$bom_list[$x] = array(
			  	'ref_id'      => $this->id,
			  	'sku'         => db_prepare_input($_POST['assy_sku'][$x]),
				'description' => db_prepare_input($_POST['assy_desc'][$x]),
				'qty'         => $currencies->clean_value(db_prepare_input($_POST['assy_qty'][$x])),
			);
		  	$result = $admin->DataBase->query("select id from " . TABLE_INVENTORY . " where sku = '". $_POST['assy_sku'][$x]."'" );
		  	if (($result->rowCount() == 0 || $currencies->clean_value($_POST['assy_qty'][$x]) == 0) && $_POST['assy_sku'][$x] =! '') {
		  		// show error, bad sku, negative quantity. error check sku is valid and qty > 0
				throw new \core\classes\userException(INV_ERROR_BAD_SKU . db_prepare_input($_POST['assy_sku'][$x]));
		  	}
		  	$prices = inv_calculate_sales_price(abs($this->bom[$x]['qty']), $result->fields['id'], 0, 'v');
			$bom_list[$x]['item_cost'] = strval($prices['price']);
		  	$prices = inv_calculate_sales_price(abs($this->bom[$x]['qty']), $result->fields['id'], 0, 'c');
			$bom_list[$x]['full_price'] = strval($prices['price']);
		}
		$this->bom = $bom_list;
		parent::save();
		$result = $admin->DataBase->query("select last_journal_date, quantity_on_hand  from " . TABLE_INVENTORY . " where id = " . $this->id);
		$this->allow_edit_bom = (($result->fields['last_journal_date'] == '0000-00-00 00:00:00' || $result->fields['last_journal_date'] == '') && ($result->fields['quantity_on_hand'] == 0|| $result->fields['quantity_on_hand'] == '')) ? true : false;
	  	if ($this->allow_edit_bom == true) { // only update if no posting has been performed
	  		$result = $admin->DataBase->query("delete from " . TABLE_INVENTORY_ASSY_LIST . " where ref_id = " . $this->id);
			while ($list_array = array_shift($bom_list)) {
				unset($list_array['item_cost']);
				unset($list_array['full_price']);
				db_perform(TABLE_INVENTORY_ASSY_LIST, $list_array, 'insert');
			}
	  	}
	  	return true;
	}
}