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
// |                                                                 |
// | The license that is bundled with this package is located in the |
// | file: /doc/manual/ch01-Introduction/license.html.               |
// | If not, see http://www.gnu.org/licenses/                        |
// +-----------------------------------------------------------------+
//  Path: /modules/audit/pages/main/pre_process.php
//


/**************   Check user security   *****************************/
$security_level = \core\classes\user::validate(SECURITY_ID_AUDIT);
/**************  include page specific files    *********************/
//gen_pull_language($module);
require_once(DIR_FS_WORKING . 'functions/audit.php');
/**************   page specific initialization  *************************/
$date_from = gen_db_date($_REQUEST['date_from']);//         ? db_prepare_input($_POST['date_from'])       : $_GET['date_from'];
$date_to   = gen_db_date($_REQUEST['date_to']);//           ? db_prepare_input($_POST['date_to'])         : $_GET['date_to'];
$select    = $_REQUEST['select'];//           ? db_prepare_input($_POST['date_to'])         : $_GET['date_to'];

/***************   hook for custom actions  ***************************/
$custom_path = DIR_FS_MODULES . 'audit/custom/pages/main/extra_actions.php';
if (file_exists($custom_path)) { include($custom_path); }

/***************   Act on the action request   *************************/
switch ($_REQUEST['action']) {
	case 'export_audit': //search for contacts, gl_accounts and journals
		try{
		  	build_audit_xml($date_from, $date_to, $select);
		  	libxml_use_internal_errors(true);
		  	$dates = gen_get_dates($date_from);
		  	$dom = new \DOMDocument('1.0', 'UTF-8');
		  	$dom->loadXML($output);
		  	$dom->schemaValidate(DIR_FS_MODULES.'audit/AuditfileFinancieelVersie3.1.xsd');
			header("Content-type: plain/txt;");
			header("Content-disposition: attachment; filename=aud_". $dates['ThisYear'].".xaf; size=" . strlen($output));
			header('Pragma: cache');
			header('Cache-Control: public, must-revalidate, max-age=0');
			header('Connection: close');
			header('Expires: ' . date('r', time()+3600));
			header('Last-Modified: ' . date('r'));
		  	echo $dom->saveXML(); 
			exit();
	/*	  	// hieronder werkt
			header("Content-type: plain/txt;");
			header("Content-disposition: attachment; filename=aud_". $dates['ThisYear'].".xaf; size=" . strlen($output));
			header('Pragma: cache');
			header('Cache-Control: public, must-revalidate, max-age=0');
			header('Connection: close');
			header('Expires: ' . date('r', time()+3600));
			header('Last-Modified: ' . date('r'));
			print $output;
			exit();*/
		}catch(Exception $e){
			$messageStack->add($e->getMessage());
		}
	default:
}

/*****************   prepare to display templates  *************************/
$sel_options = array(
 	array('id' => '0', 'text' => TEXT_ALL),
 	array('id' => '1', 'text' => TEXT_EXCLUDE),
);
$cal_from = array(
  'name'      => 'dateFrom',
  'form'      => 'site_search',
  'fieldname' => 'date_from',
  'imagename' => 'btn_date_1',
  'default'   => $_GET['date_from'],
  'params'    => array('align' => 'left'),
);
$cal_to = array(
  'name'      => 'dateTo',
  'form'      => 'site_search',
  'fieldname' => 'date_to',
  'imagename' => 'btn_date_2',
  'default'   => $_GET['date_to'],
  'params'    => array('align' => 'left'),
);


$include_header   = true;
$include_footer   = true;
$include_template = 'template_main.php';
define('PAGE_TITLE', HEADING_MODULE_AUDIT);

?>