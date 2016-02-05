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
//  Path: /modules/phreeform/pages/admin/pre_process.php
//
$security_level = \core\classes\user::validate(SECURITY_ID_CONFIGURATION);
/**************  include page specific files    *********************/
require_once(DIR_FS_WORKING . 'defaults.php');
require_once(DIR_FS_WORKING . 'functions/phreeform.php');
require_once(DIR_FS_MODULES . 'phreedom/functions/phreedom.php');
/**************   page specific initialization  *************************/
/***************   Act on the action request   *************************/
switch ($_REQUEST['action']) {
  case 'save':
	\core\classes\user::validate_security($security_level, 3);
  	// save general tab
	foreach ($admin->classes['phreeform']->keys as $key => $default) {
	  $field = strtolower($key);
      if (isset($_POST[$field])) $admin->DataBase->write_configure($key, $_POST[$field]);
    }
	\core\classes\messageStack::add(TEXT_CONFIGURATION_VALUES_HAVE_BEEN_SAVED, 'success');
	gen_redirect(html_href_link(FILENAME_DEFAULT, gen_get_all_get_params(array('action')), 'SSL'));
    break;
  case 'fix':
	// drop the database
	$admin->DataBase->query("truncate ".TABLE_PHREEFORM);
	// load all the install classes to re-build directory structure
	$admin->classes['phreeform']->load_reports('phreeform');
	foreach($admin->classes as $key => $class){
		if($class->installed && $key != 'phreeform'){
			if (file_exists(DIR_FS_MODULES . $key . '/config.php')) $class->load_reports();
		}
	}
	// load the files, parse, insert into db
	$rpt_cnt  = 0;
	$orph_cnt = 0;
	$name_map = array();
	$reports  = @scandir(PF_DIR_MY_REPORTS);
	if($reports === false) throw new \core\classes\userException("couldn't read or find directory ". PF_DIR_MY_REPORTS);
	foreach ($reports as $report) {
	  if (substr($report, 0, 3) <> 'pf_') continue;
	  $rpt_id = substr($report, 3);
	  if (($temp = @file_get_contents(PF_DIR_MY_REPORTS.$report)) === false)  throw new \core\classes\userException(sprintf(ERROR_READ_FILE, PF_DIR_MY_REPORTS.$report));
	  $rpt = xml_to_object($temp);
	  if ($rpt->PhreeformReport) $rpt = $rpt->PhreeformReport; // lose the container
	  if ($rpt->security == 'u:-1;g:-1') $rpt->security = 'u:'.$_SESSION['admin_id'].'g:-1'; // orphaned, set so current user can access
	  $result = $admin->DataBase->query("select id from ".TABLE_PHREEFORM." where doc_group = '".$rpt->groupname."' and doc_type = '0'");
	  if ($result->fetch(\PDO::FETCH_NUM) == 0) { // orphaned put into misc category
	  	$orph_cnt++;
	  	$search_type = $rpt->reporttype=='frm'?'misc:misc':'misc'; // put in misc
	    $result = $admin->DataBase->query("select id from ".TABLE_PHREEFORM." where doc_group = '".$search_type."' and doc_type = '0'");
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
	  $name_map[$rpt_id] = \core\classes\PDO::lastInsertId('id');
	  rename(PF_DIR_MY_REPORTS.$report, PF_DIR_MY_REPORTS.'tmp_'.$rpt_id);
	  $rpt_cnt++;
	}
	// remap the reports to the new db id's
	foreach ($name_map as $old => $new) {
	  rename(PF_DIR_MY_REPORTS.'tmp_'.$old, PF_DIR_MY_REPORTS.'pf_'.$new);
	}
	gen_add_audit_log(TEXT_PHREEFORM_STUCTURE_VERIFICATION_AND_REBUILD);
	\core\classes\messageStack::add(sprintf(PHREEFORM_TOOLS_REBUILD_SUCCESS, $rpt_cnt, $orph_cnt), 'success');
  	break;

/*** BOF - Added by PhreeSoft to convert PhreeBooks reports to phreeform format *************/
  // This script transfers stored reports from the reportwriter database used in PhreeBooks to phreeform
  case 'convert':
	require_once(DIR_FS_MODULES . 'phreeform/functions/reportwriter.php');
	$result = $admin->DataBase->query("select * from " . TABLE_REPORTS);
	$count = 0;
	while (!$result->EOF) {
	  try{
		  $report = PrepReport($result->fields['id']);
		  if (!$params = import_text_params($report)) throw new \core\classes\userException(sprintf(PB_CONVERT_ERROR, $result->fields['description']));
		  // fix some fields
		  $params->standard_report = $result->fields['standard_report'] ? 's' : 'c';
		  // error check
		  $duplicate = $admin->DataBase->query("select id from " . TABLE_PHREEFORM . "
		    where doc_title = '" . addslashes($params->title) . "' and doc_type <> '0'");
		  if ($duplicate->fetch(\PDO::FETCH_NUM) > 0) { // the report name already exists, error
		    throw new \core\classes\userException(sprintf(PHREEFORM_REPDUP, $params->title));
		  }

		  if (!$success = save_report($params)) throw new \core\classes\userException(sprintf(PB_CONVERT_SAVE_ERROR, $params->title));
		  $count++;
	  }catch(exception $e){
	  	\core\classes\messageStack::add($e->getMessage());
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
	if ($count) \core\classes\messageStack::add(sprintf(PB_CONVERT_SUCCESS, $count), 'success');
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
define('PAGE_TITLE', TEXT_PHREEFORM_ADMINISTRATION);

?>
