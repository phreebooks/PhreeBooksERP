<?
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
//  Path: /soap/phreebooksAPI.php
//
ini_set('display_errors', true); // @todo should be removed
ini_set('display_startup_errors', true); // @todo should be removed

require_once ('application_top.php');
gen_pull_language('contacts');
gen_pull_language('phreebooks');
require_once ('classes/parser.php');
require_once ('classes/phreebooksAPI.php'); // soap required classes
require_once (DIR_FS_MODULES . 'phreebooks/functions/phreebooks.php');
require_once (DIR_FS_MODULES . 'phreebooks/classes/gen_ledger.php');
require_once (DIR_FS_MODULES . 'phreebooks/classes/orders.php');
// set some defaults
define('DEF_INV_GL_ACCT', AR_DEF_GL_SALES_ACCT);
$order = new xml_orders();
$order->processXML();
die;
?>