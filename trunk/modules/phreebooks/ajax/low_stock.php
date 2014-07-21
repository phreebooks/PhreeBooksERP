<?php
// +-----------------------------------------------------------------+
// |                   PhreeBooks Open Source ERP                    |
// +-----------------------------------------------------------------+
// | Copyright(c) 2008-2014 PhreeSoft      (www.PhreeSoft.com)       |

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
//  Path: /modules/phreebooks/ajax/low_stock.php
//
/**************   Check user security   *****************************/
$xml   = NULL;
$security_level = validate_ajax_user();
/**************  include page specific files    *********************/
/**************   page specific initialization  *************************/
$cID = db_prepare_input($_GET['cID']); //vendor
$sID = db_prepare_input($_GET['sID']); //store
$rID = db_prepare_input($_GET['rID']); //row
if (isset($sID) && ENABLE_MULTI_BRANCH) $where = " store_id = $sID and ";
$where .= " p.vendor_id = $cID";	
if (ENABLE_MULTI_BRANCH) {
  $quantity_where = "remaining";
  $sql = "select a.id, a.sku, full_price, item_weight, lead_time, reorder_quantity, sum(remaining) as 'quantity' , inactive, account_inventory_wage, p.item_cost, p.purch_taxable, p.description_purchase, description_short,
  	((minimum_stock_level + quantity_on_sales_order - quantity_on_order - quantity_on_allocation - quantity_on_hand )/purch_package_quantity) as qty_required, purch_package_quantity  
	from ".TABLE_INVENTORY. " a join " . TABLE_INVENTORY_HISTORY . "  c join " . TABLE_INVENTORY_PURCHASE . " p ON a.sku = p.sku and a.sku = c.sku
	where minimum_stock_level <> 0 and inactive = '0' and remaining < (minimum_stock_level + quantity_on_sales_order - quantity_on_order - quantity_on_allocation) and ".$where." 
	group by a.sku order by a.sku";
} else {
  $quantity_where = "a.quantity_on_hand";
  $sql = "select a.id, a.sku, full_price, item_weight, lead_time, reorder_quantity,  quantity_on_hand as 'quantity' , inactive, account_inventory_wage, p.item_cost, p.purch_taxable, p.description_purchase, description_short,
  	((minimum_stock_level + quantity_on_sales_order - quantity_on_order - quantity_on_allocation - quantity_on_hand )/purch_package_quantity) as qty_required, purch_package_quantity    
	from ".TABLE_INVENTORY."  a join " . TABLE_INVENTORY_PURCHASE . " p on a.sku = p.sku 
	where minimum_stock_level <> 0 and inactive = '0' and quantity_on_hand < (minimum_stock_level + quantity_on_sales_order - quantity_on_order - quantity_on_allocation) and ".$where." 
    order by a.sku";
}

$result = $db->Execute($sql);
while (!$result->EOF) {
	if($result->fields['purch_package_quantity'] == 0 ) $result->fields['purch_package_quantity'] = 1;
	if( $result->fields['reorder_quantity'] >= $result->fields['qty_required']){
		$qty = ceil(($result->fields['reorder_quantity'] / $result->fields['purch_package_quantity']));
	}else{
		$qty = ceil(($result->fields['qty_required'] / $result->fields['purch_package_quantity']));
	}
	if ($qty < 1) $qty = 1; 
	$xml .= "<LowStock>\n";
	$xml .= "\t" . xmlEntry("id",   				  $result->fields['id']);
	$xml .= "\t" . xmlEntry("sku",   				  $result->fields['sku']);
	$xml .= "\t" . xmlEntry("full_price",			  $result->fields['full_price'] * $result->fields['purch_package_quantity']);
	$xml .= "\t" . xmlEntry("item_weight",			  $result->fields['item_weight']);
	$xml .= "\t" . xmlEntry("quantity",				  $result->fields['quantity']);
	$xml .= "\t" . xmlEntry("lead_time",			  $result->fields['lead_time']);
	$xml .= "\t" . xmlEntry("inactive",				  $result->fields['inactive']);
	$xml .= "\t" . xmlEntry("reorder_quantity", 	  $qty);
	$xml .= "\t" . xmlEntry("account_inventory_wage", $result->fields['account_inventory_wage']);
	$xml .= "\t" . xmlEntry("item_cost",			  $result->fields['item_cost'] * $result->fields['purch_package_quantity']);
	$xml .= "\t" . xmlEntry("purch_taxable",		  $result->fields['purch_taxable']);
	$xml .= "\t" . xmlEntry("description_purchase",	  $result->fields['description_purchase']);
	$xml .= "\t" . xmlEntry("description_short",	  $result->fields['description_short']);
	$xml .= "\t" . xmlEntry("purch_package_quantity", $result->fields['purch_package_quantity']);
	$xml .= "\t" . xmlEntry("rID", 					  $rID++);
	$xml .= "</LowStock>\n";
	$result->MoveNext();
}
$xml .= xmlEntry('debug', $debug);
echo createXmlHeader() . $xml . createXmlFooter();
die;
	