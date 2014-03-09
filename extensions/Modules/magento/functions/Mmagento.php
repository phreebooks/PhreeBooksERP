<?php
function connect(){
	$client = new \SoapClient(MAGENTO_URL.'/soap/api/?wsdl');

	// If somestuff requires api authentification,
	// 	then get a session token
	$session = $client->login(MAGENTO_USERNAME, MAGENTO_PASSWORD);

	$result = $client->call($session, 'somestuff.method');
	$result = $client->call($session, 'somestuff.method', 'arg1');
	$result = $client->call($session, 'somestuff.method', array('arg1', 'arg2', 'arg3'));
	$result = $client->multiCall($session, array(
    	array('somestuff.method'),
     	array('somestuff.method', 'arg1'),
     	array('somestuff.method', array('arg1', 'arg2'))
	));


	// If you don't need the session anymore
	$client->endSession($session);
}
function update(){
	$proxy = new \SoapClient('http://magentohost/api/soap/?wsdl');
	$sessionId = $proxy->login('apiUser', 'apiKey');
	
	$attributeSets = $proxy->call($sessionId, 'product_attribute_set.list');
	$set = current($attributeSets);
	
	
	$newProductData = array(
	    'name'              => 'name of product',
	     // websites - Array of website ids to which you want to assign a new product
	    'websites'          => array(1), // array(1,2,3,...)
	    'short_description' => 'short description',
	    'description'       => 'description',
	    'status'            => 1,
	    'weight'            => 0,
	    'tax_class_id'      => 1,
	    'categories'    => array(3),    //3 is the category id
	    'price'             => 12.05
	);
	
	// Create new product
	$proxy->call($sessionId, 'product.create', array('simple', $set['set_id'], 'sku_of_product', $newProductData));
	$proxy->call($sessionId, 'product_stock.update', array('sku_of_product', array('qty'=>50, 'is_in_stock'=>1)));
	
	// Get info of created product
	var_dump($proxy->call($sessionId, 'product.info', 'sku_of_product'));
	
	// Update product name on german store view
	$proxy->call($sessionId, 'product.update', array('sku_of_product', array('name'=>'new name of product'), 'german'));
	
	// Get info for default values
	var_dump($proxy->call($sessionId, 'product.info', 'sku_of_product'));
	// Get info for german store view
	
	var_dump($proxy->call($sessionId, 'product.info', array('sku_of_product', 'german')));
	
	// Delete product
	$proxy->call($sessionId, 'product.delete', 'sku_of_product');
	
	try {
	    // Ensure that product deleted
	    var_dump($proxy->call($sessionId, 'product.info', 'sku_of_product'));
	} catch (SoapFault $e) {
	    echo "Product already deleted";
	}

}

 ?>