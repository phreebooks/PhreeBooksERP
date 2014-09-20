<?php
// +-----------------------------------------------------------------+
// |                   PhreeBooks Open Source ERP                    |
// +-----------------------------------------------------------------+
// | Copyright(c) 2008-2014 PhreeSoft, LLC (www.PhreeSoft.com)       |
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
//  Path: /admin/soap/classes/products.php
//

require_once('classes/parser.php');

class xml_products extends parser {
  function __construct() {
  }

  function processXML($rawXML) {
	//$rawXML = str_replace('&', '&amp;', $rawXML); // this character causes parser to break
//echo '<pre>' . $rawXML . '</pre><br>';
//	if (!$this->parse($rawXML)) {
	if (!$objXML = $this->xml_to_object($rawXML)) {
//echo '<pre>' . $rawXML . '</pre><br>';
//echo 'parsed string at shopping cart = '; print_r($objXML); echo '<br>';
	  return false;  // parse the submitted string, check for errors
	}
	// try to determine the language used, default to en_us
	$this->language = $objXML->Request->Language;
	if (file_exists('language/' . $this->language . '/language.php')) {
	  require_once ('language/' . $this->language . '/language.php');
	} else {
	  require_once('language/en_us/language.php');
	}
	if (!$this->validateUser($objXML))           return false;
	if (!$product = $this->formatArray($objXML)) return false;
	if (!$this->updateDatabase($product))        return false;
	return true;
  }

  function formatArray($objXML) { // specific to XML spec for a product upload
	// Here we map the received xml array to the pre-defined generic structure (application specific format later)
	$this->reference                  = $objXML->Request->Reference;
	$product['action']                = $objXML->Request->Action;
	$product['sku']                   = $objXML->Request->Product->SKU;
	$product['product_type']          = $objXML->Request->Product->ProductType;
	$product['product_model']         = $objXML->Request->Product->ProductModel;
	$product['product_name']          = $objXML->Request->Product->ProductName;
	$product['product_description']   = $objXML->Request->Product->ProductDescription;
	$product['product_url']           = $objXML->Request->Product->ProductURL;
	$product['image_directory']       = $objXML->Request->Product->ProductImageDirectory;
	$product['image_filename']        = $objXML->Request->Product->ProductImageFileName;
	$product['image_data']            = $objXML->Request->Product->ProductImageData;
	$product['product_taxable']       = $objXML->Request->Product->ProductTaxable;
	$product['msrprice']              = $objXML->Request->Product->ProductPrice->MSRPrice;
	$product['retail_price']          = $objXML->Request->Product->ProductPrice->RetailPrice;
	$product['price_discount_type']   = $objXML->Request->Product->ProductPrice->PriceDiscounts->PriceDiscountType;

	$itemArray = $objXML->Request->Product->ProductPrice->PriceDiscounts->PriceLevel;
	// if only one price level, make it an array since is is just an object
	if (isset($itemArray->Quantity)) $itemArray = array($itemArray);
	$product['price_levels'] = array();
	if (is_array($itemArray)) foreach ($itemArray as $level) {
	  $product['price_levels'][$level->DiscountLevel] = array('qty' => $level->Quantity, 'amount' => $level->Amount);
	}
	// Misc attributes
	$product['product_weight']        = $objXML->Request->Product->ProductWeight;
	$product['date_added']            = $objXML->Request->Product->DateAdded;
	$product['date_update']           = $objXML->Request->Product->DateUpdated;
	$product['date_available']        = $objXML->Request->Product->ProductDescription;
	$product['stock_level']           = $objXML->Request->Product->StockLevel;
	$product['manufacturer']          = $objXML->Request->Product->Manufacturer;
	// ZenCart specific
	$product['product_virtual']       = $objXML->Request->Product->ProductVirtual;
	$product['product_status']        = $objXML->Request->Product->ProductStatus;
	$product['product_free_shipping'] = $objXML->Request->Product->ProductFreeShipping;
	$product['product_hide_price']    = $objXML->Request->Product->ProductHidePrice;
	$product['product_category']      = $objXML->Request->Product->ProductCategory;
	$product['sort_order']            = $objXML->Request->Product->ProductSortOrder;

// Hook for including customization of product attributes
if (file_exists(DIR_FS_ADMIN . 'soap/extra_actions/extra_product_reads.php')) include (DIR_FS_ADMIN . 'soap/extra_actions/extra_product_reads.php');
// EOF - Hook for customization
	return $product;
  }

// The remaining functions are specific to ZenCart. they need to be modified for the specific application.
// It also needs to check for errors, i.e. missing information, bad data, etc.
  function updateDatabase($product) {
	global $db, $messageStack;
	// error check input
	if (!$product['sku']) return $this->responseXML('10', SOAP_NO_SKU, 'error');
	if ($product['action'] <> 'InsertUpdate') {
		return $this->responseXML('16', SOAP_BAD_ACTION, 'error');
	}

	// set some preliminary information
	// verify the submitted language exists on the Zencart side
	$languages_code = strtolower(substr($this->language, 0, 2)); // Take the first two characters of the language iso code (e.g. en_us)
	$result = $db->Execute("select languages_id from " . TABLE_LANGUAGES . " where code = '" . $languages_code . "'");
	if ($result->RecordCount() <> 1) {
		return $this->responseXML('11', SOAP_BAD_LANGUAGE_CODE . $product['language'], 'error');
	}
	$languages_id = $result->fields['languages_id'];

	// determine and verify the product_type
	$product_type_name = $product['product_type'];
	$result = $db->Execute("select type_id from " . TABLE_PRODUCT_TYPES . " where type_name = '" . $product_type_name . "'");
	if ($result->RecordCount() <> 1) {
		return $this->responseXML('12', sprintf(SOAP_BAD_PRODUCT_TYPE, $product_type_name, $product['sku']), 'error');
	}
	$product_type_id = $result->fields['type_id'];

	// manufacturer to id
	$manufacturer_name = $product['manufacturer'];
	$result = $db->Execute("select manufacturers_id from " . TABLE_MANUFACTURERS . " where manufacturers_name = '" . $manufacturer_name . "'");
	if ($result->RecordCount() <> 1) {
		return $this->responseXML('13', sprintf(SOAP_BAD_MANUFACTURER, $manufacturer_name, $product['sku']), 'error');
	}
	$manufacturers_id = $result->fields['manufacturers_id'];

	// categories need to be verified to be lowest level and fetch id
	$categories_name = $product['product_category'];
	$result = $db->Execute("select categories_id from " . TABLE_CATEGORIES_DESCRIPTION . "
		where categories_name = '" . $categories_name . "' and language_id = '" . $languages_id . "'");
	if ($result->RecordCount() <> 1) {
		return $this->responseXML('14', sprintf(SOAP_BAD_CATEGORY, $categories_name, $product['sku']), 'error');
	}
	$categories_id = $result->fields['categories_id'];
	// verify that it is the lowest level of category tree (required by zencart)
	$result = $db->Execute("select categories_id from " . TABLE_CATEGORIES . " where parent_id = '" . $categories_id . "'");
	if ($result->RecordCount() <> 0) {
		return $this->responseXML('15', sprintf(SOAP_BAD_CATEGORY_A, $categories_name, $product['sku']), 'error');
	}

	// verify the image and storage location - save image
	$image_directory = $product['image_directory'];
	// directory cannot be more than one level down
	if (strpos($image_directory, '/') !== false) {
	  $image_directory = substr($image_directory, 0, strpos($image_directory, '/'));
	}
	$image_filename = $product['image_filename'];
	$image_data = $product['image_data'];
	if ($image_data) {
		// the str_replace is to necessary to fix a PHP 5 issue with spaces in the base64 encode... see php.net
		$contents = base64_decode(str_replace(" ", "+", $image_data));
		if ($image_directory) {
			if (!is_dir(DIR_FS_CATALOG_IMAGES . $image_directory)) {
				mkdir(DIR_FS_CATALOG_IMAGES . $image_directory);
			}
			$full_path = $image_directory . '/' . $image_filename;
		} else {
			$full_path = $image_filename;
		}
		if (!$handle = fopen(DIR_FS_CATALOG_IMAGES . $full_path, 'wb')) {
			return $this->responseXML('21', SOAP_OPEN_FAILED . $full_path, 'error');
		}
		if (fwrite($handle, $contents) === false) {
			return $this->responseXML('22', SOAP_ERROR_WRITING_IMAGE, 'error');
		}
		fclose($handle);
	}

	// ************** prepare to write tables **************
	// build the products table data
	$sql_data_array = array(
	  'phreebooks_sku'       => $product['sku'],
	  'products_type'        => $product_type_id,
	  'manufacturers_id'     => $manufacturers_id,
	  'master_categories_id' => $categories_id,
	);

	if (isset($product['stock_level']))             $sql_data_array['products_quantity']       = $product['stock_level'];
	if (isset($product['product_model']))           $sql_data_array['products_model']          = $product['product_model'];
	if (isset($full_path))                          $sql_data_array['products_image']          = $full_path;
	if (isset($product['product_virtual']))         $sql_data_array['products_virtual']        = $product['product_virtual'];
	if (isset($product['date_added']))              $sql_data_array['products_date_added']     = $product['date_added'];
	if (isset($product['date_update']))             $sql_data_array['products_last_modified']  = $product['date_update'];
	if (isset($product['products_date_available'])) $sql_data_array['products_date_available'] = $product['products_date_available'];
	if (isset($product['product_weight']))          $sql_data_array['products_weight']         = $product['product_weight'];
	if (isset($product['product_status']))          $sql_data_array['products_status']         = $product['product_status'];
	if (isset($product['product_hide_price']))      $sql_data_array['product_is_call']         = $product['product_hide_price'];
	if (isset($product['product_free_shipping']))   $sql_data_array['product_is_always_free_shipping'] = $product['product_free_shipping'];
	if (isset($product['sort_order']))              $sql_data_array['products_sort_order']     = $product['sort_order'];
	if ($product['price_discount_type'] <> 0) {
		$sql_data_array['products_discount_type'] = $product['price_discount_type'];
		// set products price to level 1 price since zencart uses products_price for the first level.
		$sql_data_array['products_quantity_order_min'] = $product['price_levels'][1]['qty'];
		$sql_data_array['products_price'] = $product['price_levels'][1]['amount'];
	} else {
		$sql_data_array['products_discount_type'] = '0';
		if (isset($product['retail_price'])) $sql_data_array['products_price'] = $product['retail_price'];
	}
	// determine tax class
	$tax_class_id = $product['product_taxable'] ? ZENCART_PRODUCT_TAX_CLASS_ID : 0; // constant set in language file
	if ($tax_class_id) $sql_data_array['products_tax_class_id'] = $tax_class_id;

	// prepare the products_description data
	$prod_desc_data_array = array();
	if (isset($product['product_name']))        $prod_desc_data_array['products_name'] = $product['product_name'];
	if (isset($product['product_description'])) $prod_desc_data_array['products_description'] = $product['product_description'];
	if (isset($product['product_url']))         $prod_desc_data_array['products_url'] = str_replace('http://', '', $product['product_url']);

// Hook for including customization of product attributes
if (file_exists(DIR_FS_ADMIN . 'soap/extra_actions/extra_product_saves.php')) include (DIR_FS_ADMIN . 'soap/extra_actions/extra_product_saves.php');
// EOF - Hook for customization
	// write to the tables
	$upload_success = true;
	// determine if the SKU exists, if so update else insert the products table
	$result = $db->Execute("select products_id from " . TABLE_PRODUCTS . " where phreebooks_sku = '" . $product['sku'] . "'");
	if ($result->RecordCount() == 0) { // new product
		zen_db_perform(TABLE_PRODUCTS, $sql_data_array);
		$products_id = zen_db_insert_id();
		$result = $db->Execute("insert into " . TABLE_PRODUCTS_TO_CATEGORIES . " set categories_id = " . $categories_id . ", products_id = " . $products_id);
		$prod_desc_data_array['products_id'] = $products_id;
		$prod_desc_data_array['language_id'] = $languages_id;
		zen_db_perform(TABLE_PRODUCTS_DESCRIPTION, $prod_desc_data_array);
	} else { // update product
		$products_id = (int)$result->fields['products_id'];
		zen_db_perform(TABLE_PRODUCTS, $sql_data_array, 'update', "products_id = '" . $products_id . "'");
		$result = $db->Execute("update " . TABLE_PRODUCTS_TO_CATEGORIES . " set categories_id = " . $categories_id . " where products_id = " . $products_id);
		zen_db_perform(TABLE_PRODUCTS_DESCRIPTION, $prod_desc_data_array, 'update', "products_id = " . $products_id.' and language_id =' . $languages_id);
	}

// Hook for including additional product fields Rene
if (file_exists(DIR_FS_ADMIN . 'soap/extra_actions/additional_product_saves.php')) include (DIR_FS_ADMIN . 'soap/extra_actions/additional_product_saves.php');
// EOF - Hook for customization
	// Update the price levels, first clear out the current price level data
	$db->Execute("delete from " . TABLE_PRODUCTS_DISCOUNT_QUANTITY . " where products_id = '$products_id'" );
	// set the discount for each level from 2 on (level 1 set in base price)
	for ($i=1, $j=2; $i < count($product['price_levels']); $i++, $j++) {
	  if ($product['price_levels'][$j]['qty'])
		$db->Execute("insert into " . TABLE_PRODUCTS_DISCOUNT_QUANTITY . " set
		  discount_id = "    . $i . ",
		  products_id = "    . $products_id . ",
		  discount_qty = "   . (real)$product['price_levels'][$j]['qty'] . ",
		  discount_price = " . (real)$product['price_levels'][$j]['amount']);
	}

	// make sure everything went as planned
	if (!$upload_success) { // extract the error message from the messageStack and return with error
	  $text = strip_tags($messageStack->output());
	  return $this->responseXML('90', SOAP_PU_POST_ERROR . $text, 'error');
	}
// TBD - log the upload activity to the database

	$this->responseXML('0', sprintf(SOAP_PRODUCT_UPLOAD_SUCCESS, $product['sku']), 'success');
	return true;
  }

}
?>