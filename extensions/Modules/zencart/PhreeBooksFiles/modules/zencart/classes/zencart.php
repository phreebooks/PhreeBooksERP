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
//  Path: /modules/zencart/classes/zencart.php
//
namespace zencart\classes;
class zencart {
  	var $arrOutput = array();
  	var $resParser;
  	var $strXML;

  	function submitXML($id, $action = '', $hide_success = false, $inc_image = true) {
		global $messageStack;
		$this->strXML = null;
		switch ($action) {
			case 'product_ul':
				$this->buildProductUploadXML($id, $inc_image);
				$url = 'products.php';
				break;
			case 'product_sync':
				$this->buildProductSyncXML();
				$url = 'sync.php';
				break;
			case 'confirm':
				$this->buildConfirmXML();
				$url = 'confirm.php';
				break;
			default:
				throw new \core\classes\userException(ZENCART_INVALID_ACTION);
		}
		$temp = ZENCART_URL;
		if(!defined('ZENCART_URL') || empty($temp) || $temp == 'http://') throw new \core\classes\userException("the Zen Cart url is empty");
//		echo 'Submit to ' . ZENCART_URL . '/soap/' . $url . ' and XML string = <pre>' . htmlspecialchars($this->strXML) . '</pre><br />';
		$this->response = doCURLRequest('POST', ZENCART_URL . '/soap/' . $url, $this->strXML);
//		echo 'XML response (at the PhreeBooks side from Zencart) => <pre>' . htmlspecialchars($this->response) . '</pre><br />' . chr(10);
		$results = xml_to_object($this->response);
//		echo 'Parsed string = '; print_r($results); echo '<br />';

		$this->result = $results->Response->Result;
		$this->code   = $results->Response->Code;
		$this->text   = $results->Response->Text;
		if ($this->code == 0) {
			if (!$hide_success) $messageStack->add($this->text, strtolower($this->result));
			return true;
		} else {
			$messageStack->add(ZENCART_TEXT_ERROR . $this->code . ' - ' . $this->text, strtolower($this->result));
			return false;
		}
	}

/*************************************************************************************/
//                           Product Upload XML string generation
/*************************************************************************************/
  function buildProductUploadXML($id, $inc_image = true) {
	global $db, $currencies;
	$result = $db->Execute("select * from " . TABLE_INVENTORY . " where id = " . $id);
	if ($result->RecordCount() <> 1) throw new \core\classes\userException(ZENCART_INVALID_SKU);
	$this->sku = $result->fields['sku'];
	if (ZENCART_USE_PRICE_SHEETS == '1') {
	  $sql = "select id, default_levels from " . TABLE_PRICE_SHEETS . "
		where '" . date('Y-m-d',time()) . "' >= effective_date
		and sheet_name = '" . ZENCART_PRICE_SHEET . "' and inactive = '0'";
	  $default_levels = $db->Execute($sql);
	  if ($default_levels->RecordCount() == 0) {
		throw new \core\classes\userException(ZENCART_ERROR_NO_PRICE_SHEET . ZENCART_PRICE_SHEET);
	  }
	  $sql = "select price_levels from " . TABLE_INVENTORY_SPECIAL_PRICES . "
		where inventory_id = " . $id . " and price_sheet_id = " . $default_levels->fields['id'];
	  $special_levels = $db->Execute($sql);
	  if ($special_levels->RecordCount() > 0) {
		$price_levels = $special_levels->fields['price_levels'];
	  } else {
		$price_levels = $default_levels->fields['default_levels'];
	  }
	}

	// prepare some information before assembling string
	if ($result->fields['image_with_path']) { // image file
	  // Zencart only support one level, so we'll use the first path dir and filename only
	  $temp = explode('/', $result->fields['image_with_path']);
	  if (sizeof($temp) > 1) {
		$image_path     = $temp[0];
		$image_filename = array_pop($temp);
	  } else {
		$image_path     = '';
		$image_filename = $temp[0];
	  }
	}
	if ($inc_image && $result->fields['image_with_path']) { // image file
	  $filename = DIR_FS_MY_FILES . $_SESSION['company'] . '/inventory/images/' . $result->fields['image_with_path'];
	  if (file_exists($filename)) {
		if (!$handle = @fopen($filename, "rb")) 				throw new \core\classes\userException(sprintf(ERROR_ACCESSING_FILE,	$filename));
		if (!$contents = @fread($handle, filesize($filename))) 	throw new \core\classes\userException(sprintf(ERROR_READ_FILE, 		$filename));
		if (!@fclose($handle)) 									throw new \core\classes\userException(sprintf(ERROR_CLOSING_FILE,	$filename));
		$image_data = base64_encode($contents);
	  } else {
		unset($image_data);
	  }
	}
	// url encode all of the values to avoid upload bugs
	foreach ($result->fields as $key => $value) $result->fields[$key] = urlencode($result->fields[$key]);
	$this->strXML  = '<?xml version="1.0" encoding="UTF-8" ?>' . chr(10);
	$this->strXML .= '<Request>' . chr(10);
	$this->strXML .= xmlEntry('Version', '2.00');
	$this->strXML .= xmlEntry('UserName', ZENCART_USERNAME);
	$this->strXML .= xmlEntry('UserPassword', ZENCART_PASSWORD);
	$this->strXML .= xmlEntry('Language', $_SESSION['language']);
	$this->strXML .= xmlEntry('Operation', 'ProductUpload');
	$this->strXML .= xmlEntry('Action', 'InsertUpdate');
	$this->strXML .= xmlEntry('Reference', 'Product Upload SKU: ' . $result->fields['sku']);
	$this->strXML .= '<Product>' . chr(10);
	$this->strXML .= xmlEntry('SKU', $result->fields['sku']);
// Specific to Zencart
	$this->strXML .= xmlEntry('ProductVirtual', '0');
	$this->strXML .= xmlEntry('ProductStatus', ($result->fields['inactive'] ? '0' : '1'));
	$this->strXML .= xmlEntry('ProductFreeShipping', '0');
	$this->strXML .= xmlEntry('ProductHidePrice', '0');
	$this->strXML .= xmlEntry('ProductCategory', $result->fields['category_id']);
	$this->strXML .= xmlEntry('ProductSortOrder', $result->fields['id']);
// End specific to Zencart
// TBD need to map ProductType
	$this->strXML .= xmlEntry('ProductType', 'Product - General');
	$this->strXML .= xmlEntry('ProductName', $result->fields['description_short']);
	$this->strXML .= xmlEntry('ProductDescription', $result->fields['description_sales']);
	if(isset($result->fields['ProductURL']))  $this->strXML .= xmlEntry('ProductURL', $result->fields['ProductURL']);
	if(isset($result->fields['ProductModel']))$this->strXML .= xmlEntry('ProductModel', $result->fields['ProductModel']);
	if (isset($image_data)) {
	  $this->strXML .= xmlEntry('ProductImageDirectory', $image_path);
	  $this->strXML .= xmlEntry('ProductImageFileName', $image_filename);
	  $this->strXML .= xmlEntry('ProductImageData', $image_data);
	}
// TBD need mappping for TaxClassType
	$this->strXML .= xmlEntry('ProductTaxable', ($result->fields['item_taxable'] ? 'True' : 'False'));
	$this->strXML .= xmlEntry('TaxClassType', ZENCART_PRODUCT_TAX_CLASS);
	// Price Levels
	$this->strXML .= '  <ProductPrice>' . chr(10);
	$this->strXML .= xmlEntry('MSRPrice', $result->fields['full_price']);
	$this->strXML .= xmlEntry('RetailPrice', $result->fields['full_price']);
	if (ZENCART_USE_PRICE_SHEETS) {
	  $this->strXML .= '    <PriceDiscounts>' . chr(10);
	  $this->strXML .= xmlEntry('PriceDiscountType', '2'); // set to actual price type
	  $prices = inv_calculate_prices($result->fields['item_cost'], $result->fields['full_price'], $price_levels);
	  foreach ($prices as $level => $amount) {
		$this->strXML .= '      <PriceLevel>' . chr(10);
	    $this->strXML .= xmlEntry('DiscountLevel', ($level + 1));
	    $this->strXML .= xmlEntry('Quantity', $amount['qty']);
	    $this->strXML .= xmlEntry('Amount', $currencies->clean_value($amount['price']));
		$this->strXML .= '      </PriceLevel>' . chr(10);
	  }
	  $this->strXML .= '    </PriceDiscounts>' . chr(10);
	} else {
	  $this->strXML .= '    <PriceDiscounts>' . chr(10);
	  $this->strXML .= xmlEntry('PriceDiscountType', '0'); // clear qty discount flag
	  $this->strXML .= '  </PriceDiscounts>' . chr(10);
	}
	$this->strXML .= '  </ProductPrice>' . chr(10);
	$this->strXML .= xmlEntry('ProductWeight', $result->fields['item_weight']);
	$this->strXML .= xmlEntry('DateAdded', $result->fields['creation_date']);
	if($result->fields['last_update'] < $result->fields['last_journal_date']){
		$this->strXML .= xmlEntry('DateUpdated', $result->fields['last_journal_date']);
	}else{
		$this->strXML .= xmlEntry('DateUpdated', $result->fields['last_update']);
	}
	$this->strXML .= xmlEntry('StockLevel', $result->fields['quantity_on_hand']);
	$this->strXML .= xmlEntry('Manufacturer', $result->fields['manufacturer']);
// Hook for including customiation of product attributes
if (file_exists(DIR_FS_MODULES . 'zencart/custom/extra_product_attrs.php')) {
  include      (DIR_FS_MODULES . 'zencart/custom/extra_product_attrs.php');
}
// EOF _ Hook for customization

	$this->strXML .= '</Product>' . chr(10);
	$this->strXML .= '</Request>' . chr(10);
	return true;
  }

/*************************************************************************************/
//                           Product Syncronizer string generation
/*************************************************************************************/
  function buildProductSyncXML() {
	global $db;
	$result = $db->Execute("select sku from " . TABLE_INVENTORY . " where catalog = '1'");
	if ($result->RecordCount() == 0) {
	  throw new \core\classes\userException(ZENCART_ERROR_NO_ITEMS);
	}
	$this->strXML  = '<?xml version="1.0" encoding="UTF-8" ?>' . chr(10);
	$this->strXML .= '<Request>' . chr(10);
	$this->strXML .= xmlEntry('Version', '2.00');
	$this->strXML .= xmlEntry('UserName', ZENCART_USERNAME);
	$this->strXML .= xmlEntry('UserPassword', ZENCART_PASSWORD);
	$this->strXML .= xmlEntry('Language', $_SESSION['language']);
	$this->strXML .= xmlEntry('Operation', 'ProductSync');
	$this->strXML .= xmlEntry('Action', 'Validate');
	$this->strXML .= xmlEntry('Reference', 'Product Syncronizer');
	$this->strXML .= xmlEntry('AutoDelete', $this->delete_zencart ? 'true' : 'false');
	$this->strXML .= '  <Product>' . chr(10);
	while(!$result->EOF) {
	  $this->strXML .= '    ' . xmlEntry('SKU', urlencode($result->fields['sku']));
	  $result->MoveNext();
	}
	$this->strXML .= '  </Product>' . chr(10);
	$this->strXML .= '</Request>' . chr(10);
	return true;
  }

/*************************************************************************************/
//                           Product Shipping Confirmation String Generation
/*************************************************************************************/
  function buildConfirmXML() {
    global $db;
	$methods = $this->loadShippingMethods();
	$this->strXML  = '<?xml version="1.0" encoding="UTF-8" ?>' . chr(10);
	$this->strXML .= '<Request>' . chr(10);
	$this->strXML .= xmlEntry('Version', '2.00');
	$this->strXML .= xmlEntry('UserName', ZENCART_USERNAME);
	$this->strXML .= xmlEntry('UserPassword', ZENCART_PASSWORD);
	$this->strXML .= xmlEntry('Language', $_SESSION['language']);
	$this->strXML .= xmlEntry('Operation', 'ShipConfirm');
	$this->strXML .= xmlEntry('Action', 'Confirm');
	$this->strXML .= xmlEntry('Reference', 'Order Ship Confirmation');
	// fetch every shipment for the given post_date
	$result = $db->Execute("select ref_id, carrier, method, tracking_id from " . TABLE_SHIPPING_LOG . "
	  where ship_date like '" . $this->post_date . " %'");
	if ($result->RecordCount() == 0) {
	  throw new \Exception(ZENCART_ERROR_CONFRIM_NO_DATA);
	}
	// foreach shipment, fetch the PO Number (it is the ZenCart order number)
	while (!$result->EOF) {
	  if (strpos($result->fields['ref_id'], '-') !== false) {
	    $purchase_invoice_id = substr($result->fields['ref_id'], 0, strpos($result->fields['ref_id'], '-'));
	  } else {
	    $purchase_invoice_id = $result->fields['ref_id'];
	  }
	  $details = $db->Execute("select so_po_ref_id from " . TABLE_JOURNAL_MAIN . "
	    where journal_id = 12 and purchase_invoice_id = '" . $purchase_invoice_id . "'
		order by id desc limit 1");
		// check to see if the order is complete
		if ($details->fields['so_po_ref_id']) {
		  $details = $db->Execute("select closed, purchase_invoice_id from " . TABLE_JOURNAL_MAIN . "
	        where id = '" . $details->fields['so_po_ref_id'] . "'");
		  if ($details->RecordCount() == 1) {
		    $message = sprintf(ZENCART_CONFIRM_MESSAGE, $this->post_date, $methods[$result->fields['carrier']]['title'], $methods[$result->fields['carrier']][$result->fields['method']], $result->fields['tracking_id']);
		    $this->strXML .= '<Order>' . chr(10);
			$this->strXML .= xmlEntry('ID', $details->fields['purchase_invoice_id']);
			$this->strXML .= xmlEntry('Status', $details->fields['closed'] ? ZENCART_STATUS_CONFIRM_ID : ZENCART_STATUS_PARTIAL_ID);
			$this->strXML .= xmlEntry('Message', $message);
		    $this->strXML .= '</Order>' . chr(10);
		  }
		}
		$result->MoveNext();
	}
	$this->strXML .= '</Request>' . chr(10);
	return true;
  }

/*************************************************************************************/
//                           Support Functions
/*************************************************************************************/
  function loadShippingMethods() {
    global $shipping_defaults;
	$method_array = array();
	// load standard shipping methods
	$methods = scandir(DIR_FS_MODULES . 'shipping/methods/');
	foreach ($methods as $method) {
	  if ($method <> '.' && $method <> '..' && defined('MODULE_SHIPPING_' . strtoupper($method) . '_STATUS')) {
	    $method_array[] = $method;
	  }
	}
	$output  = array();
	$choices = array_keys($shipping_defaults['service_levels']);
	if (sizeof($method_array) > 0) foreach ($method_array as $method) {
	  load_method_language(DIR_FS_MODULES . 'shipping/methods/', $method);
	  $output[$method]['title'] = constant('MODULE_SHIPPING_' . strtoupper($method) . '_TEXT_TITLE');
	  foreach ($choices as $value) {
		$output[$method][$value] = defined($method . '_' . $value) ? constant($method . '_' . $value) : "";
	  }
	}
	return $output;
  }

}
?>