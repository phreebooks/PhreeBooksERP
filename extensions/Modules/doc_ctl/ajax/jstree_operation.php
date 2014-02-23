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
//  Path: /modules/doc_ctl/ajax/jstree_operation.php
//
/**************   Check user security   *****************************/
$xml = NULL;
$security_level = \core\classes\user::validate();
/**************  include page specific files    *********************/
// jstree initialization
$db_config = array(
  "servername"=> DB_SERVER_HOST,
  "username"  => DB_SERVER_USERNAME,
  "password"  => DB_SERVER_PASSWORD,
  "database"  => DB_DATABASE,
);
if (extension_loaded("mysqli")) { require_once(DIR_FS_MODULES . "doc_ctl/includes/jstree/_lib/class._database_i.php"); }
else { require_once(DIR_FS_MODULES . "doc_ctl/includes/jstree/_lib/class._database.php"); }
require_once(DIR_FS_MODULES . "doc_ctl/functions/doc_ctl.php"); 
require_once(DIR_FS_MODULES . "doc_ctl/includes/jstree/_lib/class.tree.php"); 
/**************   page specific initialization  *************************/
$jstree = new json_tree();

if (DEBUG) $messageStack->write_debug();

if ($_REQUEST["operation"] && strpos($_REQUEST["operation"], "_") !== 0 && method_exists($jstree, $_REQUEST["operation"])) {
	header("HTTP/1.0 200 OK");
	header('Content-type: application/json; charset=utf-8');
	header("Cache-Control: no-cache, must-revalidate");
	header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
	header("Pragma: no-cache");
	echo $jstree->{$_REQUEST["operation"]}($_REQUEST);
	die();
}
header("HTTP/1.0 404 Not Found"); 

?>