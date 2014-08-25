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
//  Path: /modules/inventory/custom/pages/main/extra_actions.php
//

// This file contains the extra actions added to the maintain inventory module, it is executed
// before the standard switch statement
switch ($_REQUEST['action']) {
  case 'amazondump':
    require(DIR_FS_MODULES . 'amazon/classes/amazon.php');
    $upXML = new amazon();
    $upXML->dumpAmazon();
    $_REQUEST['action'] = '';
    break;
  default:
}
?>