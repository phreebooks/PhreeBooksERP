<?php
namespace inventory\classes\type;
class sr extends \inventory\classes\inventory {//Serialized Item
	public $inventory_type			= 'sr';
	public $title       			= TEXT_SERIALIZED_ITEM;
    public $serialize 				= 1;
    public $account_sales_income	= INV_SERIALIZE_DEFAULT_SALES;
	public $account_inventory_wage	= INV_SERIALIZE_DEFAULT_INVENTORY;
	public $account_cost_of_sales	= INV_SERIALIZE_DEFAULT_COS;
	public $cost_method				= 'f';
	public $posible_cost_methodes   = array('f');

	function __construct(){
		parent::__construct();
		$this->tab_list['orderhist'] = array('file'=>'template_tab_hist_sr', 'tag'=>'orderhist', 'order'=>40, 'text'=>'Unit History');
	}

	function get_item_by_id($id){
		parent::get_item_by_id($id);
		$this->get_sr_list();
	}

	function get_item_by_sku($sku){
		parent::get_item_by_sku($sku);
		$this->get_sr_list();
	}

	function get_sr_list(){
		global $admin;
		$branches = gen_get_store_ids();
		$this->quantity_on_hand = 0;
		$result = $admin->DataBase->query("select store_id, qty, serialize_number from " . TABLE_INVENTORY_HISTORY . "
	  		where sku = '" . $this->sku . "' and remaining > 0 order by store_id");
  		$this->qty_table ='<table class="ui-widget" style="border-collapse:collapse;width:100%">'. chr(10);
		$this->qty_table .='  <thead class="ui-widget-header">'. chr(10);
	  	$this->qty_table .='	  <tr>';
	    $this->qty_table .='		<th>'. TEXT_STORE_ID.'</th>';
	    $this->qty_table .='		<th>'. TEXT_QUANTITY.'</th>';
	    $this->qty_table .='		<th>'. TEXT_SERIAL_NUMBER .'</th>';
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
    	$this->orderHistory = $admin->DataBase->query($sql);
	}
}