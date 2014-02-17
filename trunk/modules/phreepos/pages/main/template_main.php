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
//  Path: /modules/phreepos/pages/main/template_main.php
//
 
// start the form
echo html_form('pos', FILENAME_DEFAULT, gen_get_all_get_params(array('action'))) . chr(10);
$hidden_fields = NULL;

// include hidden fields
echo html_hidden_field('id',                 $order->id) . chr(10); // db journal entry id, null = new entry; not null = edit
echo html_hidden_field('bill_acct_id',       $order->bill_acct_id) . chr(10);	// id of the account in the bill to/remit to
echo html_hidden_field('bill_address_id',    $order->bill_address_id) . chr(10);
echo html_hidden_field('currencies_code',    $order->currencies_code) . chr(10);
echo html_hidden_field('printed',            $order->printed) . chr(10);
echo html_hidden_field('purchase_invoice_id',$order->purchase_invoice_id) . chr(10);
echo html_hidden_field('post_date',          $order->post_date) . chr(10);
echo html_hidden_field('gl_acct_id',         $order->gl_acct_id) . chr(10);
echo html_hidden_field('store_id',           $order->store_id) . chr(10);
if (!ENABLE_MULTI_CURRENCY) echo html_hidden_field('display_currency', DEFAULT_CURRENCY) . chr(10);
if (!ENABLE_MULTI_CURRENCY) echo html_hidden_field('currencies_value', '1') . chr(10);

// customize the toolbar actions
$toolbar->icon_list['cancel']['params'] = 'onclick="location.href = \'' . html_href_link(FILENAME_DEFAULT, '', 'SSL') . '\'"';
$toolbar->icon_list['open']['show']     = false;
$toolbar->icon_list['delete']['show']   = false;
$toolbar->icon_list['save']['show']     = false;
$toolbar->icon_list['print']['show']    = false;
$toolbar->add_icon('new', 'onclick="location.href = \'' . html_href_link(FILENAME_DEFAULT, gen_get_all_get_params(array('action', 'jID')) . '&jID='.JOURNAL_ID, 'SSL') . '\'"', 2);
if ($security_level > 1) {
  $toolbar->add_icon('pos_return', 'onclick="location.href = \'' . html_href_link(FILENAME_DEFAULT, gen_get_all_get_params(array('action', 'jID')) . '&action=pos_return&jID='.JOURNAL_ID, 'SSL') . '\'"', 50);
  $toolbar->icon_list['pos_return']['icon'] = 'actions/document-save-as.png';
  $toolbar->icon_list['pos_return']['text'] = TEXT_RETURN;
}
// print previous receipt
  $toolbar->add_icon('previous_print', 'onclick="GetPrintPreviousReceipt()"', 50);
  $toolbar->icon_list['previous_print']['icon'] = 'actions/go-previous.png';
  $toolbar->icon_list['previous_print']['text'] = TEXT_PRINT_PREVIOUS;
// open drawer 
if (defined('PHREEPOS_ENABLE_DIRECT_PRINTING') && PHREEPOS_ENABLE_DIRECT_PRINTING  == true){
  $toolbar->add_icon('open_drawer', 'onclick="OpenDrawer()"', 50);
  $toolbar->icon_list['open_drawer']['icon'] = 'actions/go-bottom.png';
  $toolbar->icon_list['open_drawer']['text'] = TEXT_OPEN_DRAWER;
}  
// pull in extra toolbar overrides and additions
if (count($extra_toolbar_buttons) > 0) {
  foreach ($extra_toolbar_buttons as $key => $value) $toolbar->icon_list[$key] = $value;
}
echo $toolbar->build_toolbar();

// Build the page
?>
<img id='curr_image'>
                     
<h1><?php echo PAGE_TITLE; ?></h1>
<h3 id='open_other_options' class="ui-state-default ui-corner-all"><?php echo OTHER_OPTIONS; ?></h3>
<div id='other_options' class="ui-state-default ui-corner-all">
<?php echo html_button_field('home', TEXT_HOME, 'onclick="location.href = \'' . html_href_link(FILENAME_DEFAULT, '', 'SSL') . '\'"'); ?><br/><br/>
<?php echo html_button_field('open_sales', POS_PRINT_OTHER,'onclick="OpenOrdrList(this)"');?><br/><br/>
<?php echo html_button_field('other_trans', TEXT_OTHER_TRANS,'onclick="ShowOtherTrans()"');?><br/><br/>
<?php if(in_array($_SESSION['admin_security'][SECURITY_ID_POS_CLOSING], array(1,2,3,4))) echo html_button_field('close_till', POS_HEADING_CLOSING, 'onclick="CloseTill()"' ).'<br/><br/>'; ?>
</div>
<fieldset id="search_part">
	<ol>
<?php if ($tills->showDropDown()) {	// show currency slection pulldown 
		echo '<li><label>' . TEXT_TILL . ' ' . html_pull_down_menu('till_id', $tills->till_array() , $order->till_id ? $order->till_id : $tills->default_till(), 'onchange="changeOfTill();"') . '</label></li>'; 
      }else{ 
		echo html_hidden_field('till_id', $tills->default_till()); 
	  }?> 
		<li><label> <?php echo TEXT_SALES_REP . ' ' . html_pull_down_menu('rep_id', gen_get_rep_ids($account_type), $order->rep_id ? $order->rep_id : $default_sales_rep); ?> </label></li>
<?php if (ENABLE_MULTI_CURRENCY) {	// show currency selection pulldown 
		echo '<li><label>' . TEXT_CURRENCY . ' ' . html_pull_down_menu('display_currency', gen_get_pull_down(TABLE_CURRENCIES, false, false, 'code', 'title'), $order->currencies_code, 'onchange="recalculateCurrencies();"'). '</label></li>'; 
		echo '<li><label>' . TEXT_EXCHANGE_RATE . ' ' . html_input_field('currencies_value', $order->currencies_value, 'readonly="readonly"'). '</label></li>'; 
 } ?>
 		<li><label> 
 <?php	echo TEXT_SKU . ' ' . html_input_field('sku', '', ' size="' . (MAX_INVENTORY_SKU_LENGTH + 1) . '" maxlength="' . MAX_INVENTORY_SKU_LENGTH . '" title="' . TEXT_SEARCH . '"  onchange ="loadSkuDetails(0, 0)"') . chr(10);
  		echo html_icon('actions/system-search.png', TEXT_SEARCH, 'small', 'id="sku_open" align="top" style="cursor:pointer" onclick="InventoryList(0)"') . chr(10);	?>
 		</label></li>
	</ol>
</fieldset>

<fieldset id="customer_div">
	<ol>
		<li><label>
<?php 
  echo ORD_ACCT_ID . ' ' . html_input_field('search', isset($order->short_name) ? $order->short_name : TEXT_SEARCH, 'size="21" maxlength="20" title="' . TEXT_SEARCH . '" onchange="accountGuess(true)"');
  echo '&nbsp;' . html_icon('actions/system-search.png', TEXT_SEARCH, 'small', 'align="top" style="cursor:pointer" onclick="accountGuess(true)"'); 
?>
	  	</label></li>
		<li><label>
<?php echo html_pull_down_menu('bill_to_select', gen_null_pull_down(), '', 'onchange="fillAddress(\'bill\')"'); ?>
		</label></li>
<?php

  echo '<li><label>' . html_input_field('bill_primary_name',$order->bill_primary_name, 'size="33" maxlength="32" onfocus="clearField(\'bill_primary_name\', \'' . GEN_PRIMARY_NAME . '\')" onblur="setField(\'bill_primary_name\', \'' . GEN_PRIMARY_NAME . '\')"', true) . chr(10);
  echo 				   html_checkbox_field('bill_add_update', '1', ($order->bill_add_update) ? true : false, '', '') . TEXT_ADD_UPDATE . '</label></li>';
  echo '<li><label>' . html_input_field('bill_contact',     $order->bill_contact, 'size="33" maxlength="32" onfocus="clearField(\'bill_contact\', \'' . GEN_CONTACT . '\')" onblur="setField(\'bill_contact\', \'' . GEN_CONTACT . '\')"', ADDRESS_BOOK_CONTACT_REQUIRED) . '</label></li>';
  echo '<li><label>' . html_input_field('bill_address1',    $order->bill_address1, 'size="33" maxlength="32" onfocus="clearField(\'bill_address1\', \'' . GEN_ADDRESS1 . '\')" onblur="setField(\'bill_address1\', \'' . GEN_ADDRESS1 . '\')"', ADDRESS_BOOK_ADDRESS1_REQUIRED) . '</label></li>';
  echo '<li><label>' . html_input_field('bill_address2',    $order->bill_address2, 'size="33" maxlength="32" onfocus="clearField(\'bill_address2\', \'' . GEN_ADDRESS2 . '\')" onblur="setField(\'bill_address2\', \'' . GEN_ADDRESS2 . '\')"', ADDRESS_BOOK_ADDRESS2_REQUIRED) . '</label></li>';
  echo '<li><label>' . html_input_field('bill_city_town',   $order->bill_city_town, 'size="25" maxlength="24" onfocus="clearField(\'bill_city_town\', \'' . GEN_CITY_TOWN . '\')" onblur="setField(\'bill_city_town\', \'' . GEN_CITY_TOWN . '\')"', ADDRESS_BOOK_CITY_TOWN_REQUIRED) . chr(10);
  echo  			   html_input_field('bill_state_province', $order->bill_state_province, 'size="3" maxlength="5" onfocus="clearField(\'bill_state_province\', \'' . GEN_STATE_PROVINCE . '\')" onblur="setField(\'bill_state_province\', \'' . GEN_STATE_PROVINCE . '\')"', ADDRESS_BOOK_STATE_PROVINCE_REQUIRED) . chr(10);
  echo '<li><label>' . html_input_field('bill_postal_code', $order->bill_postal_code, 'size="11" maxlength="10" onfocus="clearField(\'bill_postal_code\', \'' . GEN_POSTAL_CODE . '\')" onblur="setField(\'bill_postal_code\', \'' . GEN_POSTAL_CODE . '\')"', ADDRESS_BOOK_POSTAL_CODE_REQUIRED) . '</label></li>';
  echo '<li><label>' . html_pull_down_menu('bill_country_code', gen_get_countries(), $order->bill_country_code ? $order->bill_country_code : COMPANY_COUNTRY) . '</label></li>'; 
  echo '<li><label>' . html_input_field('bill_telephone1',  $order->bill_telephone1, 'size="21" maxlength="20" onfocus="clearField(\'bill_telephone1\', \'' . GEN_TELEPHONE1 . '\')" onblur="setField(\'bill_telephone1\', \'' . GEN_TELEPHONE1 . '\')"', ADDRESS_BOOK_TELEPHONE1_REQUIRED) . chr(10);
  echo '<li><label>' . html_input_field('bill_email',       $order->bill_email, 'size="35" maxlength="48" onfocus="clearField(\'bill_email\', \'' . GEN_EMAIL . '\')" onblur="setField(\'bill_email\', \'' . GEN_EMAIL . '\')"', ADDRESS_BOOK_EMAIL_REQUIRED) . '</label></li>';
  ?>
	</ol>
</fieldset>
<fieldset id="totals_div">
	<ol>
		<li><label>
	<?php   echo TEXT_SUBTOTAL . ' ' . html_input_field('subtotal', $currencies->format($order->subtotal, true, $order->currencies_code, $order->currencies_value), 'readonly="readonly" size="10" maxlength="20"'); ?>
		</label></li>
	<?php if (ENABLE_ORDER_DISCOUNT) { 
			$hidden_fields .= html_hidden_field('disc_gl_acct_id', '') . chr(10); 
        	echo '<li><label>' . TEXT_DISCOUNT_PERCENT . ' ' . html_input_field('disc_percent', ($order->disc_percent ? number_format(100*$order->disc_percent,3) : $currencies->format(0)), 'size="10" maxlength="6" onchange="calculateDiscountPercent()" ') . '</label></li> '; 
			echo '<li><label>' . TEXT_DISCOUNT_AMOUNT . ' ' . html_input_field('discount', $currencies->format(($order->discount ? $order->discount : '0'), true, $order->currencies_code, $order->currencies_value), 'size="10" maxlength="20" onchange="calculateDiscount()"'). '</label></li> ';
		  } else {
  			$hidden_fields .= html_hidden_field('disc_gl_acct_id', '') . chr(10);
  			$hidden_fields .= html_hidden_field('discount',     '0')   . chr(10);
  			$hidden_fields .= html_hidden_field('disc_percent', '0')   . chr(10);
		  } ?>
		<li><label>
	<?php   echo ORD_SALES_TAX . ' ' . html_input_field('sales_tax', $currencies->format(($order->sales_tax ? $order->sales_tax : '0.00'), true, $order->currencies_code, $order->currencies_value), 'readonly="readonly" size="10" maxlength="20" onchange="updateTotalPrices()"'); ?>
		</label></li>			
	<?php if(!PHREEPOS_ROUNDING == 0) {
			echo '<li><label>' . TEXT_ROUNDING_OF . ' ' . html_input_field('rounded_of', $currencies->format($order->rounded_of, true, $order->currencies_code, $order->currencies_value), 'readonly="readonly" size="10" maxlength="20"') . '</label></li> ';
		 }?>	
		<li><label>
	<?php   echo TEXT_TOTAL . ' ' . html_input_field('total', $currencies->format($order->total_amount, true, $order->currencies_code, $order->currencies_value), 'readonly="readonly" size="10" maxlength="20"'); ?>
		</label></li>	
	</ol>
</fieldset>  

<table id="payment_table" class="ui-widget" style="border-collapse:collapse;">
	<caption class="ui-state-default ui-corner-all"><?php echo html_button_field('payment', TEXT_PAYMENT, 'onclick="popupPayment()"'); ?></caption>
	<thead class="ui-widget-header">
		<tr>
			<th class="dataTableHeadingContent"></th>
			<th class="dataTableHeadingContent"><?php echo TEXT_PAYMENT_METHOD; ?></th>
			<th class="dataTableHeadingContent"><?php echo TEXT_AMOUNT; ?></th>
			<th class="dataTableHeadingContent"></th>
		</tr>
   	</thead>
	<tbody id="payment_table_body">
	</tbody>
	<tfoot class="ui-widget-header">
		<tr>
			<th class="dataTableHeadingContent"></th>
			<th class="dataTableHeadingContent"><?php echo TEXT_AMOUNT_PAID; ?></th>
			<th class="dataTableHeadingContent"><?php echo html_input_field('pmt_recvd', $currencies->format($order->pmt_recvd), 'readonly="readonly" size="15" maxlength="20" style="text-align:right"'); ?></th>
			<th class="dataTableHeadingContent"></th>
		</tr>
		<tr>
			<th class="dataTableHeadingContent"></th>
			<th class="dataTableHeadingContent"><?php echo TEXT_BALANCE_DUE; ?></th>
			<th class="dataTableHeadingContent"><?php echo html_input_field('bal_due', $currencies->format($order->bal_due), 'readonly="readonly" size="15" maxlength="20" style="text-align:right"'); ?></th>
			<th class="dataTableHeadingContent"></th>
		</tr>
	</tfoot>
</table>





<div id="search_customer" >
<?php 
  echo ORD_ACCT_ID . ' ' . html_input_field('copy_search', isset($order->short_name) ? $order->short_name : TEXT_SEARCH, 'size="21" maxlength="20" title="' . TEXT_SEARCH . '" onchange="accountGuess(true)"');
  echo '&nbsp;' . html_icon('actions/system-search.png', TEXT_SEARCH, 'small', 'align="top" style="cursor:pointer" onclick="accountGuess(true)"').'<br>'. chr(10);  
  echo html_input_field('copy_bill_primary_name',$order->bill_primary_name, 'size="33" maxlength="32" onfocus="clearField(\'bill_primary_name\', \'' . GEN_PRIMARY_NAME . '\')" onblur="setField(\'bill_primary_name\', \'' . GEN_PRIMARY_NAME . '\')"', true).'<br>'. chr(10);
  echo html_button_field('customer_popup_buttom', TEXT_SELECT_CUSTOMER, 'onclick="popupContact()"').'<br>'. chr(10);?> 
</div>

<table id="item_table" class="ui-widget" style="border-collapse:collapse;">
 	<thead class="ui-widget-header">
		<tr>
			<th class="dataTableHeadingContent"></th>
			<th class="dataTableHeadingContent"><?php echo TEXT_QUANTITY; ?></th>
			<th class="dataTableHeadingContent"><?php echo TEXT_SKU; ?></th>
			<th class="dataTableHeadingContent"><?php echo TEXT_DESCRIPTION; ?></th>
			<th class="dataTableHeadingContent"><?php echo TEXT_UNIT_PRICE; ?></th>
			<th class="dataTableHeadingContent"><?php echo TEXT_AMOUNT; ?></th>
			<th class="dataTableHeadingContent"></th>
		</tr>
	</thead>
 	<tbody id="item_table_body">
	</tbody>
	<tfoot class="ui-widget-header">
		<tr>
			<th class="dataTableHeadingContent"></th>
			<th class="dataTableHeadingContent"></th>
			<th class="dataTableHeadingContent"></th>
			<th class="dataTableHeadingContent"></th>
			<th class="dataTableHeadingContent"></th>
			<th class="dataTableHeadingContent"></th>
			<th class="dataTableHeadingContent"></th>
		</tr>
	</tfoot>
</table>	 

<footer><?php echo "<b><u>" . TEXT_NOTES . "</u></b><br>" . PHREEPOS_ITEM_NOTES; ?></footer>
<?php // display the hidden fields that are not used in this rendition of the form
echo $hidden_fields;
if (defined('PHREEPOS_ENABLE_DIRECT_PRINTING') && PHREEPOS_ENABLE_DIRECT_PRINTING  == true){
?>
<!--
<object type="application/x-java-applet" width="16" height="16" id="qz" name="qz">
  	<param name="archive"   value="<?php echo DIR_WS_ADMIN . 'modules/phreedom/includes/jzebra/qz-print.jar'; ?>" />
  	<param name="code"      value="qz.PrintApplet.class" />
  	<param name="mayscript" value="true" />
  	<param name="permissions" value="all-permissions" />
</object>
-->
<applet id="qz" name="QZ Print Plugin" code="qz.PrintApplet.class" archive="<?php echo DIR_WS_ADMIN . 'modules/phreedom/includes/jzebra/qz-print.jar'; ?>" width="16px" height="16px">
	<param name="permissions" value="all-permissions" />
</applet>
<?php }else{
	echo html_hidden_field("qz");
}?>
<div id="popupPayment">
<?php 
$SeccondToolbar      = new toolbar;
$SeccondToolbar->icon_list['cancel']['params'] = 'onclick="disablePopup()"';
$SeccondToolbar->icon_list['open']['show']     = false;
$SeccondToolbar->icon_list['save']['params']   = 'onclick="SavePayment(\'save\')"';
$SeccondToolbar->icon_list['save']['show']     = true; 
$SeccondToolbar->icon_list['delete']['show']   = false;
$SeccondToolbar->icon_list['print']['params']  = 'onclick="SavePayment(\'print\')"';
$SeccondToolbar->icon_list['print']['show']    = true; 
// pull in extra toolbar overrides and additions
if (count($extra_SeccondToolbar_buttons) > 0) {
	foreach ($extra_SeccondToolbar_buttons as $key => $value) $SeccondToolbar->icon_list[$key] = $value;
}
// add the help file index and build the toolbar
echo $SeccondToolbar->build_toolbar(); 
 // Build the page
?>
	<h2 align="center"><?php echo PAYMENT_TITLE; ?></h2>
  
<?php
	echo '    <fieldset>';
    echo '    <legend>'. TEXT_PAYMENT_METHOD . '</legend>';
	echo '    <div style="position: relative; height: 150px;">';
	echo html_pull_down_menu('payment_method', $payment_modules, $order->shipper_code, 'onchange="activateFields()"') . chr(10);
	$count = 0;
	foreach ($payment_modules as $value) {
	  echo '      <div id="pm_' . $count . '" style="visibility:hidden; position:absolute; top:22px; left:1px">' . chr(10);
	  $pmt_class = $value['id'];
	  $disp_fields = $$pmt_class->selection();
	  for ($i=0; $i<count($disp_fields['fields']); $i++) {
		echo $disp_fields['fields'][$i]['title'] . '<br />' . chr(10);
		echo $disp_fields['fields'][$i]['field'] . '<br />' . chr(10);
	  }
	  echo '      </div>' . chr(10);
	  $count++;
	}
	echo '    </div>';
	echo '</fieldset>';
?>
	<table id="payment_extra_buttons">
	<tr><td> <?php echo html_icon('devices/media-floppy.png',		 TEXT_SAVE,  'large', 'onclick="SavePayment(\'save\')"' , 0, 0, 'btn_save').'</td><td>' . TEXT_SAVE  ; ?></td></tr>
	<tr><td> <?php echo html_icon('phreebooks/pdficon_large.gif', TEXT_PRINT, 'large', 'onclick="SavePayment(\'print\')"', 0, 0, 'btn_save')   .'</td><td>' . TEXT_PRINT ; ?></td></tr>
	</table>
	<?php echo TEXT_AMOUNT . ' ' . html_input_field('amount', $currencies->format($amount), 'size="15" maxlength="20" style="text-align:right; font-size: 1.5em"'); ?>
	<footer><?php echo PHREEPOS_PAYMENT_NOTES; ?> </footer>
</div>

<div id="backgroundPopup"></div>
</form>

<?php 
echo html_form('popupOtherTrans', FILENAME_DEFAULT) . chr(10);
$thirdToolbar      = new toolbar;
$thirdToolbar->icon_list['cancel']['params'] = 'onclick="disablePopup()"';
$thirdToolbar->icon_list['open']['show']     = false;
$thirdToolbar->icon_list['save']['params']   = 'onclick="SaveOt()"';
$thirdToolbar->icon_list['save']['show']     = true; 
$thirdToolbar->icon_list['delete']['show']   = false;
$thirdToolbar->icon_list['print']['show']    = false; 
// pull in extra toolbar overrides and additions
if (count($extra_ThirdToolbar_buttons) > 0) {
	foreach ($extra_ThirdToolbar_buttons as $key => $value) $thirdToolbar->icon_list[$key] = $value;
}
// add the help file index and build the toolbar
echo $thirdToolbar->build_toolbar(); 
 // Build the page
?>
	<h2 align="center"><?php echo TEXT_OTHER_TRANS; ?></h2>  
	<table id="ot_table" class="ui-widget" style="border-collapse:collapse; position:relative;">
	<caption><?php echo TEXT_TYPE_OF_TRANSACTION . '   ' . html_pull_down_menu('Other_trans_type', '', '', 'onchange="changeOfType();"'); ?></caption>
 	<thead class="ui-widget-header">
		<tr>
			<th class="dataTableHeadingContent ot_desc"><?php echo TEXT_DESCRIPTION; ?></th>
			<th class="dataTableHeadingContent ot_amount"><?php echo TEXT_AMOUNT; ?></th>
			<th class="dataTableHeadingContent ot_rate"><?php echo ORD_TAX_RATE; ?></th>
			<th class="dataTableHeadingContent ot_tax"><?php echo TEXT_TAX; ?></th>
		</tr>
	</thead>
 	<tbody id="ot_table_body">
 		<tr>
 			<td class="ot_desc">  <?php echo html_input_field('ot_desc', '', 'size="64" maxlength="64"  style="font-size: 1.5em"')?></td>
 			<td class="ot_amount"><?php echo html_input_field('ot_amount', $currencies->format(0), 'size="15" maxlength="20" style="text-align:right; font-size: 1.5em" onchange="updateOt()"'); ?></td>
 			<td class="ot_rate">  <?php echo html_pull_down_menu('ot_rate', $ot_tax_rates, '', 'onchange="updateOt()" style="font-size: 1.5em"')?></td>
 			<td class="ot_tax">   <?php echo html_input_field('ot_tax', $currencies->format(0), 'size="15" maxlength="20" style="text-align:right; font-size: 1.5em"'); ?></td>
 		</tr>
	</tbody>
</table>	 
<?php echo html_hidden_field('ot_till_id', 			$tills->default_till())   . chr(10);
      echo html_hidden_field('ot_currencies_value', '1') . chr(10);
      echo html_hidden_field('ot_currencies_code',    $order->currencies_code) . chr(10);?>
</form>

