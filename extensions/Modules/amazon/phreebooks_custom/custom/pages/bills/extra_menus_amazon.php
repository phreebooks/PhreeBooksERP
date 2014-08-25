<?php
// +-----------------------------------------------------------------+
// |                   PhreeBooks Open Source ERP                    |
// +-----------------------------------------------------------------+
// | Copyright(c) 2008-2013 PhreeSoft, LLC (www.PhreeSoft.com)       |
// +-----------------------------------------------------------------+
//  Path: /modules/phreebooks/custom/pages/bills/extra_menus.php
//

// This file contains the extra defines that can be used for customizing you output and 
// adding functionality to PhreeBooks

// Modified Language defines, used to over-ride the standard language for customization. These
// values are loaded prior to the standard language defines and take priority.

// Additional Toolbar buttons
$extra_toolbar_buttons = array();
if ($_SESSION['admin_security'][SECURITY_ID_CUSTOMER_RECEIPTS] > 3) {
	$extra_toolbar_buttons['amazon_fill'] = array(
		'show'   => true, 
		'icon'   => '../../../../modules/amazon/images/amazon.gif',
		'params' => 'onclick="amazonFillRequest()"', 
		'text'   => 'Fill Amazon Receipts', 
		'order'  => '69');
}

?>