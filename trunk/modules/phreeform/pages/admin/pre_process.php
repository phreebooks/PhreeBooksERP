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
//  Path: /modules/phreeform/pages/admin/pre_process.php
//
$security_level = validate_user(SECURITY_ID_CONFIGURATION);
/**************  include page specific files    *********************/
gen_pull_language($module, 'admin');
gen_pull_language('phreedom', 'admin');
require_once(DIR_FS_WORKING . 'defaults.php');
require_once(DIR_FS_WORKING . 'functions/phreeform.php');
require_once(DIR_FS_MODULES . 'phreedom/functions/phreedom.php');
require_once(DIR_FS_WORKING . 'classes/install.php');
/**************   page specific initialization  *************************/
$error   = false; 
$install = new phreeform_admin();
/***************   Act on the action request   *************************/
switch ($_REQUEST['action']) {
  case 'save': 
	validate_security($security_level, 3);
  	// save general tab
	foreach ($install->keys as $key => $default) {
	  $field = strtolower($key);
      if (isset($_POST[$field])) write_configure($key, $_POST[$field]);
    }
	$messageStack->add(GENERAL_CONFIG_SAVED, 'success');
	gen_redirect(html_href_link(FILENAME_DEFAULT, gen_get_all_get_params(array('action')), 'SSL'));
    break;
  case 'fix':
	// drop the database
	$db->Execute("truncate ".TABLE_PHREEFORM);
	// load all the install classes to re-build directory structure
  	$install_mod = new phreeform_admin;
	$install_mod->load_reports('phreeform');
	$contents    = scandir(DIR_FS_MODULES);
	foreach ($contents as $entry) { // install each module
	  if (!defined('MODULE_'.strtoupper($entry).'_STATUS')) continue; // skip uninstalled modules
	  if (!in_array($entry, array('.', '..', 'phreeform')) && is_dir(DIR_FS_MODULES . $entry)) {
	  	if (file_exists(DIR_FS_MODULES . $entry . '/config.php')) {
	  	  require_once (DIR_FS_MODULES . $entry . '/classes/install.php');
		  $classname   = $entry . '_admin';
		  $install_mod = new $classname;
		  $install_mod->load_reports($entry);
		}
	  }
	}
	// load the files, parse, insert into db
	$rpt_cnt  = 0;
	$orph_cnt = 0;
	$name_map = array();
	$reports  = scandir(PF_DIR_MY_REPORTS);
	foreach ($reports as $report) {
	  if (substr($report, 0, 3) <> 'pf_') continue;
	  $rpt_id = substr($report, 3);
	  $rpt = xml_to_object(file_get_contents(PF_DIR_MY_REPORTS.$report));
	  if ($rpt->PhreeformReport) $rpt = $rpt->PhreeformReport; // lose the container
	  if ($rpt->security == 'u:-1;g:-1') $rpt->security = 'u:'.$_SESSION['admin_id'].'g:-1'; // orphaned, set so current user can access
	  $result = $db->Execute("select id from ".TABLE_PHREEFORM." where doc_group = '".$rpt->groupname."' and doc_type = '0'");
	  if ($result->RecordCount() == 0) { // orphaned put into misc category
	  	$orph_cnt++;
	  	$search_type = $rpt->reporttype=='frm'?'misc:misc':'misc'; // put in misc
	    $result = $db->Execute("select id from ".TABLE_PHREEFORM." where doc_group = '".$search_type."' and doc_type = '0'");
	  }
	  $sql_array = array(
	    'parent_id'   => $result->fields['id'],
	    'doc_type'    => 's', // make them all standard reports for now
	    'doc_title'   => $rpt->title,
	    'doc_group'   => $rpt->groupname,
	    'doc_ext'     => $rpt->reporttype,
	    'security'    => $rpt->security,
	    'create_date' => date('Y-m-d'),
	  );
	  db_perform(TABLE_PHREEFORM, $sql_array);
	  $name_map[$rpt_id] = db_insert_id();
	  rename(PF_DIR_MY_REPORTS.$report, PF_DIR_MY_REPORTS.'tmp_'.$rpt_id);
	  $rpt_cnt++;
	}
	// remap the reports to the new db id's
	foreach ($name_map as $old => $new) {
	  rename(PF_DIR_MY_REPORTS.'tmp_'.$old, PF_DIR_MY_REPORTS.'pf_'.$new);
	}
	gen_add_audit_log(PHREEFORM_TOOLS_REBUILD_TITLE);
	$messageStack->add(sprintf(PHREEFORM_TOOLS_REBUILD_SUCCESS, $rpt_cnt, $orph_cnt), 'success');
  	break;

/*** BOF - Added by PhreeSoft to convert PhreeBooks reports to phreeform format *************/
  // This script transfers stored reports from the reportwriter database used in PhreeBooks to phreeform
  case 'convert':
	require_once(DIR_FS_MODULES . 'phreeform/functions/reportwriter.php');
	$result = $db->Execute("select * from " . TABLE_REPORTS);
	$count = 0;
	while (!$result->EOF) {
	  $skip_report = false;
	  $report = PrepReport($result->fields['id']);
	  if (!$params = import_text_params($report)) {
	    $messageStack->add(sprintf(PB_CONVERT_ERROR, $result->fields['description']), 'error');
		$skip_report = true;
	  }
	  // fix some fields
	  $params->standard_report = $result->fields['standard_report'] ? 's' : 'c';
	  // error check
	  $duplicate = $db->Execute("select id from " . TABLE_PHREEFORM . " 
	    where doc_title = '" . addslashes($params->title) . "' and doc_type <> '0'");
	  if ($duplicate->RecordCount() > 0) { // the report name already exists, error 
	    $messageStack->add(sprintf(PHREEFORM_REPDUP, $params->title), 'error');
	    $skip_report = true;
	  }
	  if (!$skip_report) {
	    if (!$success = save_report($params)) {
	      $messageStack->add(sprintf(PB_CONVERT_SAVE_ERROR, $params->title), 'error');
		}
		$count++;
	  }
	  $result->MoveNext();
	}
	// Copy the PhreeBooks images
	$dir_source = DIR_FS_MY_FILES . $_SESSION['company'] . '/images';
	$dir_dest   = PF_DIR_MY_REPORTS . 'images';
	$d = dir($dir_source);
	while (FALSE !== ($filename = $d->read())) {
	  if ($filename == '.' || $entry == '..') continue;
	  @copy($dir_source . '/' . $filename, $dir_dest . '/' . $filename);
	}
	$d->close();
	if ($count) $messageStack->add(sprintf(PB_CONVERT_SUCCESS, $count), 'success');
    break;
/*** EOF - Added by PhreeSoft to convert PhreeBooks reports to phreeform format *************/
  default:
}

/*****************   prepare to display templates  *************************/
$pdf_choices = array(
  array('id' => 'TCPDF', 'text' => 'TCPDF'),
  array('id' => 'FPDF',  'text' => 'FPDF'),
);

$include_header   = true;
$include_footer   = true;
$include_template = 'template_main.php';
define('PAGE_TITLE', BOX_PHREEFORM_MODULE_ADM);

?>
