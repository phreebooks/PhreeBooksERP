<?php
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
//  Path: /modules/phreedom/pages/backup/pre_process.php
//
ini_set('memory_limit','256M');  // Set this big for memory exhausted errors
$security_level = \core\classes\user::validate(SECURITY_ID_BACKUP);
/**************  include page specific files    *********************/
require_once(DIR_FS_WORKING . 'functions/phreedom.php');
/**************   page specific initialization  *************************/
/***************   hook for custom actions  ***************************/
$custom_path = DIR_FS_WORKING . 'custom/pages/backup/extra_actions.php';
if (file_exists($custom_path)) { include($custom_path); }
/***************   Act on the action request   *************************/
switch ($_REQUEST['action']) {
  case 'save':
  	try{
	  	$conv_type  = $_POST['conv_type'];
		$dl_type    = $_POST['dl_type'];
		$save_local = (isset($_POST['save_local'])) ? true : false;
		// set execution time limit to a large number to allow extra time
		if (ini_get('max_execution_time') < 20000) set_time_limit(20000);
		$backup              = new \phreedom\classes\backup;
		$backup->db_filename = 'db-' . $_SESSION['company'] . '-' . date('Ymd');
		$backup->source_dir  = DIR_FS_MY_FILES . $_SESSION['company'] . '/';
		$backup->source_file = $backup->db_filename . '.sql';
		$backup->dest_dir    = DIR_FS_MY_FILES . 'backups/';
		$backup->dump_db_table($db, 'all', 'both');
		// compress the company directory
		switch ($conv_type) {
		  case 'bz2':
			if ($dl_type == 'file') {
			  $backup->dest_file = $backup->db_filename . '.bz2';
			} else {
			  $backup->dest_file = 'bu-' . $_SESSION['company'] . '-' . date('Ymd') . '.tar.bz2';
			}
		    $backup->make_bz2($dl_type);
			@unlink($backup->source_dir . $backup->source_file); // delete db sql file
			break;
		  case 'zip':
			if ($dl_type == 'file') {
			  $backup->dest_file = $backup->db_filename . '.zip';
			} else {
			  $backup->dest_file = 'bu-' . $_SESSION['company'] . '-' . date('Ymd') . '.zip';
			}
			$backup->make_zip($dl_type);
			@unlink($backup->source_dir . $backup->source_file); // delete db sql file
			break;
		  default:
			$backup->dest_file = $backup->source_file;
			@rename($backup->source_dir . $backup->source_file, $backup->dest_dir . $backup->dest_file);
			break;
		}
		gen_add_audit_log(TEXT_COMPANY_DATABASE_BACKUP);
		$backup->download($backup->dest_dir, $backup->dest_file, $save_local); // will not return if successful
	}catch(Exception $e){
		$messageStack->add($e->getMessage());
	}
	break;

  case 'backup_log':
  	try{
		if (ini_get('max_execution_time') < 20000) set_time_limit(20000);
		$backup              = new \phreedom\classes\backup;
		$backup->db_filename = 'log-' . $_SESSION['company'] . '-' . date('Ymd');
		$backup->source_dir  = DIR_FS_MY_FILES . $_SESSION['company'] . '/';
		$backup->source_file = $backup->db_filename . '.sql';
		$backup->dest_dir    = DIR_FS_MY_FILES . 'backups/';
		$backup->dest_file   = $backup->db_filename . '.zip';
		$backup->dump_db_table($db, TABLE_AUDIT_LOG, 'both');

		$backup->make_zip('file');
		if (file_exists($backup->source_dir . $backup->source_file)) unlink($backup->source_dir . $backup->source_file);
		gen_add_audit_log(TEXT_AUDIT_DB_DATA_BACKUP);
		$backup->download($backup->dest_dir, $backup->dest_file, false);
  	}catch(Exception $e){
		$messageStack->add($e->getMessage());
	}
    break;

	case 'clean_log':
  		$date = new \core\classes\DateTime();
  		$date->modify("-{$date->format('j')} day");
    	$result = $admin->DataBase->exec("delete from " . TABLE_AUDIT_LOG . " where action_date < '{$date->format('Y-m-d')}'");
    	$messageStack->add('The number of records deleted was:' . ' ' . $result->AffectedRows(),'success');
		gen_add_audit_log(TEXT_AUDIT_DB_DATA_CLEAN);
		break;

  default:
}

/*****************   prepare to display templates  *************************/
$include_header   = true;
$include_footer   = true;
switch ($_REQUEST['action']) {
  case 'restore':
    $custom_html      = true;
    $include_template = 'template_restore.php';
    define('PAGE_TITLE', TEXT_COMPANY_RESTORE);
    break;
  case 'save':
  default:
    $include_template = 'template_main.php';
    define('PAGE_TITLE', TEXT_COMPANY_BACKUP);
	break;
}

?>