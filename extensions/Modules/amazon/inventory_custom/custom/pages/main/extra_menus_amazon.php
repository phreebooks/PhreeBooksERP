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
//  Path: /modules/inventory/custom/pages/main/extra_menus.php
//
// This file contains the extra defines that can be used for customizing you output and 
// adding functionality to PhreeBooks
// Modified Language defines, used to over-ride the standard language for customization. These
// values are loaded prior to the standard language defines and take priority.
// Additional Toolbar buttons
$extra_toolbar_buttons = array();
if ($_SESSION['admin_security'][SECURITY_ID_MAINTAIN_INVENTORY] > 3) {
  $extra_toolbar_buttons['amazondump'] = array(
	'show'   => true, 
	'icon'   => '../../../../modules/amazon/images/amazon.gif',
	'params' => 'onclick="submitToDo(\'amazondump\', true)"', 
	'text'   => 'Download Amazon Products', 
	'order'  => '65',
  );
}

?>