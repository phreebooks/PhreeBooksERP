<?
// +-----------------------------------------------------------------+
// |                   PhreeBooks Open Source ERP                    |
// +-----------------------------------------------------------------+
// | Copyright(c) 2008-2015 PhreeSoft      (www.PhreeSoft.com)       |
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
//  Path: /soap/orders.php
//

require_once ('application_top.php');
require_once ('classes/parser.php');
require_once ('classes/orders.php'); // soap required classes
require_once (DIR_FS_MODULES . 'phreebooks/functions/phreebooks.php');
require_once (DIR_FS_MODULES . 'phreebooks/classes/gen_ledger.php');
require_once (DIR_FS_MODULES . 'phreebooks/classes/orders.php');
// set some defaults
define('DEF_INV_GL_ACCT', AR_DEF_GL_SALES_ACCT);
// retrieve the XML raw string
if (($temp = @file_get_contents("php://input")) === false)  throw new \core\classes\userException(sprintf(ERROR_READ_FILE, "php://input"));
$rawpost = urldecode($temp); // retrieve the XML raw string
$order = new xml_orders();
$order->processXML($rawpost);
require ('application_bottom.php');
?>