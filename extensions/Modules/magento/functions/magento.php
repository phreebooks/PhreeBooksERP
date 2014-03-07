<?php
// +-----------------------------------------------------------------+
// |                   PhreeBooks Open Source ERP                    |
// +-----------------------------------------------------------------+
// | Copyright(c) 2008-2013 PhreeSoft, LLC (www.PhreeSoft.com)       |
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
//  Path: /modules/magento/functions/magento.php
//

class magento{
	private $client;
	private $session;
	private $categories	= array();
	private $description = "magento menu" ;
	private $group_by;
	private $sort_order;
	private $entry_type = 'multi_check_box';
	private $field_name = 'magneto_category_id';
	private $tab_id =1;
	
	function __construct(){
		if ($this->session == '') $this->login();
	}
	
	function login(){
		global $messageStack;
		try{
//			$messageStack->add('loggin in', 'caution');
			$this->client = new SoapClient(MAGENTO_URL.'/index.php/api/v2_soap/?wsdl'); 
			$this->session = $this->client->login(MAGENTO_USERNAME, MAGENTO_PASSWORD);
		}catch(SoapFault $exception) { 
			$messageStack->add($exception->getMessage(), 'error');
		} 
	}

	function get_menus(){
		global $messageStack;
		if ($this->session == '') $this->login();
		try{
			return $this->client->catalogCategoryTree($this->session);
		}catch(SoapFault $exception) { 
			$messageStack->add($exception->getMessage(), 'error');
			return false;
		} 
	} 
	
	function get_menus_options($id){
		global $messageStack;
		if ($this->session == '') $this->login();
		try{
			return $this->client->catalogCategoryInfo($this->session, $id);
		}catch(SoapFault $exception) { 
			$messageStack->add($exception->getMessage(), 'error');
			return false;
		} 
	}
	
	function update_inventory_catalog_options(){
		$this->flattenArray( $this->get_menus() );
		$params = array(
	  		'type'           => $this->entry_type,
	  		'inventory_type' => 'ai:ci:ds:ia:lb:ma:mb:mi:ms:ns:sa:sf:si:sr:sv',
			'default' 		 => db_prepare_input($this->createPathNames()),
		);
		$sql_data_array = array(
	  		'params'      => serialize($params),
		);
		return db_perform(TABLE_EXTRA_FIELDS, $sql_data_array, 'update', " field_name = '$this->field_name'" );
	}
	
	function flattenArray($value){
		$id = $value->category_id;
		$this->categories[$id] = $this->get_menus_options($id);
		foreach ($value->children as $child) {
			$this->flattenArray($child);
		}
	}
	
	function createPathNames(){
		$data = '';
		foreach($this->categories as $category){
			$temp = explode('/', $category->path);
			foreach ($temp as $value){
				if($value != 1) $this->categories[$category->category_id]->path_name .= "/". $this->categories[$value]->name;
			}
			if($category->category_id != 1 ) $data .= $category->category_id.":".$this->categories[$category->category_id]->path_name .":0,";
		}
		return $data;
	}

	function create_product($sku){
		global $messageStack;
		if ($this->session == '') $this->login();
		try{
			require_once(DIR_FS_WORKING . 'classes/inventory.php');
			// get attribute set
			$inventory = new \magento\inventory();
			$attributeSets = $this->client->catalogProductListOfAdditionalAttributes($this->session);
			$inventory->set_attributeSets($attributeSets);
			$inventory->get_item_by_sku($sku);
			//$attributeSet = current($attributeSets);
			
			//$result = $this->client->catalogProductCreate($this->session, 'simple', $attributeSet->set_id, 'product_sku', $inventory);
		 	if($result != false){
				$messageStack->add("uploaded with success assigend id $result", "success");
			}
			
		}catch(SoapFault $exception) { 
			$messageStack->add($exception->getMessage(), 'error');
		} 
	}
	
	/**
	 * this function searches for the pricesheet name in the costomer group list in the webshop
	 * @param string $pricesheet_name
	 * @return string pricesheet_id
	 */
	
	function get_pricesheet_id($pricesheet_name){
		if ($this->session == '') $this->login();
		try{
			$result = $this->client->customerGroupList($this->session);
			foreach ($result as $item){
				if( $item['customer_group_code'] == $pricesheet_name) return $item['customer_group_id']; 
			}
		}catch(SoapFault $exception) { 
			$messageStack->add($exception->getMessage(), 'error');
		} 
	}
	
	/**
	 * with this function you can send tier_pricing to magento webshop
	 * @param $sku
	 * @param $pricesheet_id use get_pricesheet_id to retrieve the id
	 * @param $array_qty_price this is a array of rows each row had qty and price
	 */
	
	function update_tier_price($sku, $pricesheet_id, $array_qty_price){
		foreach($array_qty_price as $item)
			$tierPrices = array(
				array('customer_group_id' => $pricesheet_id, 'website' => '0', 'qty' => $item['qty'], 'price' => $item['price'])
			);
		global $messageStack;
		if ($this->session == '') $this->login();
		try{
			if( $this->client->catalogProductAttributeTierPriceUpdate($this->session, $sku,	$tierPrices) == true){
				$messageStack->add("updated tier pricing for $sku with success", "success");
			}
		}catch(SoapFault $exception) { 
			$messageStack->add($exception->getMessage(), 'error');
		} 
	}
	
	function set_menu_for_sku($menuId, $sku){
		global $messageStack;
		if ($this->session == '') $this->login();
		try{
			if($this->client->catalogCategoryAssignProduct($this->session, $menuId, $sku, '', 'SKU') == true){
				$messageStack->add("assigned to webshop category $menuId with success", "success");
			}
		}catch(SoapFault $exception) { 
			$messageStack->add($exception->getMessage(), 'error');
		} 
	} 
	
	function delete_product($sku){
		global $messageStack;
		if ($this->session == '') $this->login();
		try{
			if ($proxy->catalogProductDelete($sessionId, $sku , 'SKU') == true){
				$messageStack->add("deleted from webshop with success", "success");
			}
		}catch(SoapFault $exception) { 
			$messageStack->add($exception->getMessage(), 'error');
		}
	}
	
	function __destruct(){
		global $messageStack;
		try{
			$this->client->endSession($this->session);
		}catch(SoapFault $exception) { 
			$messageStack->add($exception->getMessage(), 'error');
		}	
	}
	
}

?>