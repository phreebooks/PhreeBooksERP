<?php
namespace magento;
class inventory {
	public $name;
	public $description;
	public $short_description;
	public $weight;
	public $status;
	public $url_key;
	public $url_path;
	public $visibility;
	public $category_ids = array(); //Array of category IDs
	public $website_ids = array(); //Array of website IDs
	public $has_options = false;
	public $gift_message_available;
	public $price;
	public $special_price;
	public $special_from_date;
	public $special_to_date;
	public $tax_class_id;
	public $tier_price = array();//	Array of catalogProductTierPriceEntity
	public $meta_title;
	public $meta_keyword;
	public $meta_description;
	public $custom_design;
	public $custom_layout_update;
	public $options_container;
	public $additional_attributes = array();//	Array of catalogProductAdditionalAttributesEntity
	public $stock_data = array();//	Array of catalogInventoryStockItemUpdateEntity
	//private variables.
	private $attributeSets = array();

	function get_item_by_id(integer $id) {
		global $db;
		$result = $db->Execute("SELECT * FROM ".TABLE_INVENTORY." WHERE id = $id");
		if ($result->RecordCount() == 0) throw new \core\classes\userException("couldn't find inventory with id $id");
		$this->name 					= $result->fields['name'];
		$this->description 				= $result->fields['sales_description'];
		$this->short_description 		= $result->fields['short_description'];
		$this->weight 					= $result->fields['weight'];
		$this->status 					= $result->fields['inactive'] == 0;
		$this->url_key 					= $result->fields['url_key'];
		$this->url_path 				= $result->fields['url_path'];
		$this->visibility 				= $result->fields['magento_catalog'];
		$this->category_ids 			= explode(':', $result->fields['']); //Array of category IDs
		//$this->has_options 				= false;//$result->fields[''];
		$this->gift_message_available 	= $result->fields['gift_message_available'];
		$this->price 					= $result->fields['full_price_with_tax'];
		$this->special_price 			= $result->fields['special_price_with_tax'];
		$this->special_from_date		= $result->fields['special_from_date'];
		$this->special_to_date 			= $result->fields['special_to_date'];
		$this->tax_class_id 			= $result->fields['item_taxable'];
		$this->meta_title 				= $result->fields['meta_title'];
		$this->meta_keyword 			= $result->fields['meta_keyword'];
		$this->meta_description 		= $result->fields['meta_description'];
		$this->custom_design 			= $result->fields['custom_design'];
		$this->custom_layout_update 	= $result->fields['custom_layout_update'];
		//$this->options_container 		= $result->fields[''];
		
		foreach ($this->attributeSets as $key => $value) {
			if(isset($result->fields[$key])) $this->additional_attributes['multi_data'][] = array($key => $result->fields[$key]) ;
		}
		return true;
	}
	
	/** 
	 * this function gets inventory details from the database by sku
	 * @param char $sku
	 */
	
	function get_item_by_sku(char $sku){
		global $db;
		$result = $db->Execute("select * from " . TABLE_INVENTORY . " where sku = '$sku'");
		if ($result->RecordCount() != 0) throw new \core\classes\userException("couldn't find inventory with sku $sku");
		$this->name 					= $result->fields['name'];
		$this->description 				= $result->fields['sales_description'];
		$this->short_description 		= $result->fields['short_description'];
		$this->weight 					= $result->fields['weight'];
		$this->status 					= $result->fields['inactive'] == 0;
		$this->url_key 					= $result->fields['url_key'];
		$this->url_path 				= $result->fields['url_path'];
		$this->visibility 				= $result->fields['magento_catalog'];
		$this->category_ids 			= explode(':', $result->fields['']); //Array of category IDs
		//$this->has_options 				= false;//$result->fields[''];
		$this->gift_message_available 	= $result->fields['gift_message_available'];
		$this->price 					= $result->fields['full_price_with_tax'];
		$this->special_price 			= $result->fields['special_price_with_tax'];
		$this->special_from_date		= $result->fields['special_from_date'];
		$this->special_to_date 			= $result->fields['special_to_date'];
		$this->tax_class_id 			= $result->fields['item_taxable'];
		$this->meta_title 				= $result->fields['meta_title'];
		$this->meta_keyword 			= $result->fields['meta_keyword'];
		$this->meta_description 		= $result->fields['meta_description'];
		$this->custom_design 			= $result->fields['custom_design'];
		$this->custom_layout_update 	= $result->fields['custom_layout_update'];
		//$this->options_container 		= $result->fields[''];
			
		foreach ($this->attributeSets as $key => $value) {
			print("key = $key<br/>");print_r($value);
			if(isset($result->fields[$key])) $this->additional_attributes['multi_data'][] = array($key => $result->fields[$key]) ;
		}
		return true;
	}
	
	function set_attributeSets($attributeSets){
		$this->attributeSets = $attributeSets;
	}
	function __destruct(){
		print_r($this->attributeSets);
	}
}