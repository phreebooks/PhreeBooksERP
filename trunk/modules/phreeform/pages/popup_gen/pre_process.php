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
//  Path: /modules/phreeform/pages/popup_gen/pre_process.php
//
$security_level = validate_user(SECURITY_ID_PHREEFORM);
/**************  include page specific files    *********************/
require_once(DIR_FS_WORKING . 'defaults.php');
require_once(DIR_FS_WORKING . 'functions/phreeform.php');
/***************   hook for custom actions  ***************************/
$custom_path = DIR_FS_WORKING . 'custom/pages/popup_gen/extra_actions.php';
if (file_exists($custom_path)) { include($custom_path); }
/**************   page specific initialization  *************************/
$error  = false;
$r_list = false;
$rID    = $_REQUEST['rID'];
$gID    = isset($_GET ['gID'])  ? $_GET ['gID']  : false;

$IncludePage = 'template_main.php'; // default unless overwritten

if ($rID) {
  $report = get_report_details($rID);
//echo 'rID found report->filterlist = '; print_r($report->filterlist); echo '<br>';
  if (isset($_GET['date'])) $report->datedefault = $_GET['date'] . ':' . $_GET['df'] . ':' . $_GET['dt'];
  if (is_array($report->filterlist)) foreach ($report->filterlist as $key => $value) { // fill the overrides
	if (isset($_GET['cr'  . $key])) $value->default = $_GET['cr'  . $key];
	if (isset($_GET['min' . $key])) $value->min_val = $_GET['min' . $key];
	if (isset($_GET['max' . $key])) $value->max_val = $_GET['max' . $key];
	$report->filterlist[$key] = $value;
  }
  if (isset($_GET['xfld'])) { // check for extra filters
    if (isset($_GET['xfld'])) $report->xfilterlist[0]->fieldname = $_GET['xfld'];
    if (isset($_GET['xcr']))  $report->xfilterlist[0]->default   = $_GET['xcr'];
    if (isset($_GET['xmin'])) $report->xfilterlist[0]->min_val   = $_GET['xmin'];
    if (isset($_GET['xmax'])) $report->xfilterlist[0]->max_val   = $_GET['xmax'];
  }
  $title = $report->title;
} elseif ($gID) {
  $result = $db->Execute("select id, doc_title from " . TABLE_PHREEFORM . " 
    where doc_group = '" . $gID . "' and (doc_ext = 'rpt' || doc_ext = 'frm') order by doc_title");
  if ($result->RecordCount() == 1) {
    $rID    = $result->fields['id']; // only one form available, use it
    $report = get_report_details($rID);
    $title  = $report->title;
	if (isset($_GET['date'])) $report->datedefault = $_GET['date'] . ':' . $_GET['df'] . ':' . $_GET['dt'];
	if (is_array($report->filterlist)) foreach ($report->filterlist as $key => $value) { // fill the overrides
	  if (isset($_GET['cr'  . $key])) $value->default = $_GET['cr'  . $key];
	  if (isset($_GET['min' . $key])) $value->min_val = $_GET['min' . $key];
	  if (isset($_GET['max' . $key])) $value->max_val = $_GET['max' . $key];
	  $report->filterlist[$key] = $value;
	}
    if (isset($_GET['xfld'])) { // check for extra filters
      if (isset($_GET['xfld'])) $report->xfilterlist[0]->fieldname = $_GET['xfld'];
      if (isset($_GET['xcr']))  $report->xfilterlist[0]->default   = $_GET['xcr'];
      if (isset($_GET['xmin'])) $report->xfilterlist[0]->min_val   = $_GET['xmin'];
      if (isset($_GET['xmax'])) $report->xfilterlist[0]->max_val   = $_GET['xmax'];
    }
  } else {
    $frm_grp = $db->Execute("select doc_title from " . TABLE_PHREEFORM . " 
    where doc_group = '" . $gID . "' and (doc_ext = 'ff' || doc_ext = 'ff') limit 1");
  	$title  = $frm_grp->fields['doc_title'];
    $r_list = array();
    while(!$result->EOF) {
      $r_list[] = array('id' => $result->fields['id'], 'text' => $result->fields['doc_title']);
	  $result->MoveNext();
    }
  }
} else {
  $error = true;
  $messageStack->add(PHREEFORM_NORPT, 'error');
}
//echo 'post override report->filterlist = '; print_r($report->filterlist); echo '<br>';

if (isset($_GET['xfld']) && strpos($_GET['xfld'], 'journal_main') !== false) { // try to extract email info from the journal
  $result         = $db->Execute("select bill_primary_name, bill_email from " . TABLE_JOURNAL_MAIN . " where id = '" . $_GET['xmin'] . "'");
  $_GET['rName']  = $result->fields['bill_primary_name'];
  $_GET['rEmail'] = $result->fields['bill_email'];
}

$delivery_method = $_POST['delivery_method'] ? $_POST['delivery_method'] : 'I';
$from_email      = $_POST['from_email']      ? $_POST['from_email']      : $_SESSION['admin_email'];
$from_name       = $_POST['from_name']       ? $_POST['from_name']       : $_SESSION['display_name'];
$to_email        = $_POST['to_email']        ? $_POST['to_email']        : $_GET['rEmail'];
$to_name         = $_POST['to_name']         ? $_POST['to_name']         : $_GET['rName'];
$cc_email        = $_POST['cc_email']        ? $_POST['cc_email']        : '';
$cc_name         = $_POST['cc_name']         ? $_POST['cc_name']         : $cc_address;
$message_subject = $title . ' ' . TEXT_FROM . ' ' . COMPANY_NAME;
$message_subject = $_POST['message_subject'] ? $_POST['message_subject'] : $message_subject;
$message_body    = $report->emailmessage     ? TextReplace($report->emailmessage) : sprintf(PHREEFORM_EMAIL_BODY, $title, COMPANY_NAME);
$email_text      = $_POST['message_body']    ? $_POST['message_body']    : $message_body;

if (!$error) switch ($_REQUEST['action']) {
  case 'save':
  case 'save_as':
  case 'exp_csv':
  case 'exp_xml':
  case 'exp_pdf':
  case 'exp_html':
	// read in user data and merge with report defaults
	if ($report->reporttype == 'rpt') {
	  $report->grpbreak             = isset($_POST['grpbreak'])    ? '1' : '0';
	  $report->truncate             = isset($_POST['deftrunc']) ? $_POST['deftrunc'] : '0';
	  $report->page->size           = $_POST['papersize'];
	  $report->page->orientation    = $_POST['paperorientation'];
	  $report->page->margin->top    = $_POST['margintop'];
	  $report->page->margin->bottom = $_POST['marginbottom'];
	  $report->page->margin->left   = $_POST['marginleft'];
	  $report->page->margin->right  = $_POST['marginright'];
	  $report->page->heading->show  = isset($_POST['coynameshow']) ? '1' : '0';
	  $report->page->heading->font  = $_POST['coynamefont'];
	  $report->page->heading->size  = $_POST['coynamesize'];
	  $report->page->heading->color = $_POST['coynamecolor'];
	  $report->page->heading->align = $_POST['coynamealign'];
	  $report->page->title1->show   = isset($_POST['title1show']) ? '1' : '0';
	  $report->page->title1->text   = $_POST['title1desc'];
	  $report->page->title1->font   = $_POST['title1font'];
	  $report->page->title1->size   = $_POST['title1size'];
	  $report->page->title1->color  = $_POST['title1color'];
	  $report->page->title1->align  = $_POST['title1align'];
	  $report->page->title2->show   = isset($_POST['title2show']) ? '1' : '0';
	  $report->page->title2->text   = $_POST['title2desc'];
	  $report->page->title2->font   = $_POST['title2font'];
	  $report->page->title2->size   = $_POST['title2size'];
	  $report->page->title2->color  = $_POST['title2color'];
	  $report->page->title2->align  = $_POST['title2align'];
	  $report->page->filter->font   = $_POST['filterfont'];
	  $report->page->filter->size   = $_POST['filtersize'];
	  $report->page->filter->color  = $_POST['filtercolor'];
	  $report->page->filter->align  = $_POST['filteralign'];
	  $report->page->data->font     = $_POST['datafont'];
	  $report->page->data->size     = $_POST['datasize'];
	  $report->page->data->color    = $_POST['datacolor'];
	  $report->page->data->align    = $_POST['dataalign'];
	  $report->page->totals->font   = $_POST['totalsfont'];
	  $report->page->totals->size   = $_POST['totalssize'];
	  $report->page->totals->color  = $_POST['totalscolor'];
	  $report->page->totals->align  = $_POST['totalsalign'];
	  // read the field listings
	  $report->fieldlist = array();
	  if ($_POST['fld_fld']) foreach ($_POST['fld_fld'] as $key => $value) {
	    $report->fieldlist[] = new objectInfo(array(
	      'fieldname'   => db_prepare_input($_POST['fld_fld'][$key]),
	      'description' => db_prepare_input($_POST['fld_desc'][$key]),
	      'visible'     => db_prepare_input($_POST['fld_vis'][$key]),
	      'columnwidth' => db_prepare_input($_POST['fld_clmn'][$key]),
	      'columnbreak' => db_prepare_input($_POST['fld_brk'][$key]),
		  'processing'  => db_prepare_input($_POST['fld_proc'][$key]),
	      'align'       => db_prepare_input($_POST['fld_algn'][$key]),
		  'total'       => db_prepare_input($_POST['fld_tot'][$key]),
	    ));
	  }
	  $i = 0;
	  while(true) {
	    if (!isset($_POST['field_' . $i])) break;
	    $report->fieldlist[$_POST['seq_' . $i]] = new objectInfo(
		  array(
		    'fieldname'   => $_POST['field_' . $i],
		    'description' => $_POST['desc_' . $i],
		    'visible'     => isset($_POST['show_'  . $i]) ? '1' : '0',
		    'columnbreak' => isset($_POST['break_' . $i]) ? '1' : '0',
		    'columnwidth' => $_POST['width_' . $i],
		    'align'       => $_POST['align_' . $i],
	      )
		);
	    $i++;
	  }
	  ksort($report->fieldlist);
	  if (is_array($report->grouplist)) foreach ($report->grouplist as $key => $value) {
	    $report->grouplist[$key]->default = ($_POST['defgroup'] == ($key+1)) ? 1 : 0;
	  }
	}
	if (isset($_POST['datedefault'])) $report->datedefault = $_POST['datedefault'] . ':' . $_POST['date_from'] . ':' . $_POST['date_to'];
// BOF - added by PhreeSoft for PhreeBooks module
	$report->period = $_POST['period'] ? $_POST['period'] : 0;
	if (isset($_POST['period'])) $report->datedefault = 'z:' . $report->period;
// EOF - added by PhreeSoft for PhreeBooks
	if (is_array($report->sortlist)) foreach ($report->sortlist as $key => $value) {
	  $report->sortlist[$key]->default = ($_POST['defsort'] == ($key+1)) ? 1 : 0;
	}
	// Criteria Field Selection
	if (is_array($report->filterlist)) foreach ($report->filterlist as $key => $value) {
	  if (isset($_POST['defcritsel'. $key])) $value->default = $_POST['defcritsel'. $key];
	  if (isset($_POST['fromvalue' . $key])) $value->min_val = $_POST['fromvalue' . $key];
	  if (isset($_POST['tovalue'   . $key])) $value->max_val = $_POST['tovalue'   . $key];
	  $report->filterlist[$key] = $value;
	}
	if ($_REQUEST['action'] == 'save' && $report->standard_report <> 's') { // Update the main report record
	  $output = object_to_xml($report);
	  $filename = PF_DIR_MY_REPORTS . 'pf_' . $rID;
	  if (!$handle = @fopen($filename, 'w')) {
	    $db->Execute("delete from " . TABLE_PHREEFORM . " where id = " . $rID);
	    $messageStack->add(sprintf(PHREEFORM_WRITE_ERROR, $filename), 'error');
	    break;
	  }
	  fwrite($handle, $output);
	  fclose($handle);
	  $messageStack->add(TEXT_REPORT . $report->description . PHREEFORM_WASSAVED . $report->title, 'success');
	  break; // we're done
	} elseif ($_REQUEST['action'] == 'save') {
	  $messageStack->add(PHREEFORM_CANNOT_EDIT,'caution');
	  break; // we're done
	} elseif ($_REQUEST['action'] == 'save_as') {
	  $result = $db->Execute("select * from " . TABLE_PHREEFORM . " where id = " . $rID);
	  $sql_array = array(
		'parent_id'   => $result->fields['parent_id'],
		'doc_type'    => 'c', // custom document
		'doc_title'   => $_POST['title'],
		'doc_group'   => $result->fields['doc_group'],
		'doc_ext'     => $result->fields['doc_ext'],
		'security'    => 'u:' . $_SESSION['admin_id'] . ';g:-1', // only the current user can see this
		'create_date' => date('Y-m-d'),
	  );
	  db_perform(TABLE_PHREEFORM, $sql_array);
	  $rID = db_insert_id();
	  $output = object_to_xml($report);
	  $filename = PF_DIR_MY_REPORTS . 'pf_' . $rID;
	  if (!$handle = @fopen($filename, 'w')) {
	    $db->Execute("delete from " . TABLE_PHREEFORM . " where id = " . $rID);
	    $messageStack->add(sprintf(PHREEFORM_WRITE_ERROR, $filename), 'error');
	    break;
	  }
	  fwrite($handle, $output);
	  fclose($handle);
	  $messageStack->add(TEXT_REPORT . $report->description . PHREEFORM_WASSAVED . $report->title, 'success');
	  break; // we're done
	}
	if ($error) break;

	// if we are here, the user wants to generate output
	switch ($report->reporttype) {
	  case 'frm':
	    $output = BuildForm($report, $delivery_method);
		if ($output === true) $error = true;
	    break;
	  case 'rpt':
	    $ReportData = '';
	    $success = BuildSQL($report);
	    if ($success['level'] == 'success') { // Generate the output data array
		  $sql = $success['data'];
		  $report->page->filter->text = $success['description']; // fetch the filter message
		  if (!$ReportData = BuildDataArray($sql, $report)) {
		    $messageStack->add(PHREEFORM_NODATA . ' The sql was: ' . $sql, 'caution');
		    $error = true;
		    break;
		  }
		  // Check for the report returning with data
		  if (!$ReportData) {
		    $messageStack->add(PHREEFORM_NODATA . ' The failing sql= ' . $sql, 'caution');
		    $error = true;
		  } else {
		    if ($_REQUEST['action'] == 'exp_csv')  $output = GenerateCSVFile ($ReportData, $report, $delivery_method);
		    if ($_REQUEST['action'] == 'exp_xml')  $output = GenerateXMLFile ($ReportData, $report, $delivery_method);
		    if ($_REQUEST['action'] == 'exp_html') $output = GenerateHTMLFile($ReportData, $report, $delivery_method);
		    if ($_REQUEST['action'] == 'exp_pdf')  $output = GeneratePDFFile ($ReportData, $report, $delivery_method);
		  }
	    } else { // Houston, we have a problem
		  $messageStack->add($success['message'], $success['level']);
		  $error = true;
	    }
	    break;
	}
	// if we are here, delivery method was email
	if (!$error && $output) {
		$temp_file = DIR_FS_MY_FILES . $_SESSION['company'] . '/temp/' . $output['filename'];
		$handle = fopen($temp_file, 'w');
		fwrite($handle, $output['pdf']);
		fclose($handle);
		$block = array();
		if ($cc_email) {
		  $block['EMAIL_CC_NAME']    = $cc_name;
		  $block['EMAIL_CC_ADDRESS'] = $cc_email;
		}
		$attachments_list['file'] = $temp_file;
		$success = validate_send_mail($to_name, $to_email, $message_subject, $email_text, $from_name, $from_email, $block, $attachments_list);
		if ($success) $messageStack->add(EMAIL_SEND_SUCCESS, 'success');
		unlink($temp_file);
	}
  default:
}

/*****************   prepare to display templates  *************************/
$DateArray = explode(':', $report->datedefault);
if (!isset($DateArray[1])) $DateArray[1] = '';
if (!isset($DateArray[2])) $DateArray[2] = '';
$ValidDateChoices = array();
foreach ($DateChoices as $key => $value) {
 if (strpos($report->datelist, $key) !== false) $ValidDateChoices[$key] = $value;
}

$tab_list = array();
if ($report->reporttype == 'frm' && sizeof($r_list) > 0) {
  $tab_list['crit']  = TEXT_CRITERIA;
} elseif ($report->reporttype == 'rpt') {
  $tab_list['crit']  = TEXT_CRITERIA;
  $tab_list['field'] = TEXT_FIELDS;
  $tab_list['page']  = TEXT_PAGE_SETUP;
}
$custom_path = DIR_FS_WORKING . 'custom/pages/popup_gen/extra_tabs.php';
if (file_exists($custom_path)) { include($custom_path); }

if ($report->reporttype == 'rpt') {
  $i = 1;
  $group_list = array(array('id' => '0', 'text' => TEXT_NONE));
  if (is_array($report->grouplist)) foreach ($report->grouplist as $group) {
    if ($group->default    == '1') $group_default = $i;
    if ($group->page_break == '1') $group_break   = true;
    $group_list[] = array('id' => $i, 'text' => $group->description);
    $i++;
  }
  $i = 1;
  $sort_list = array(array('id' => '0', 'text' => TEXT_NONE));
  if (is_array($report->sortlist)) foreach ($report->sortlist as $sortitem) {
    if ($sortitem->default    == '1') $sort_default = $i;
    $sort_list[] = array('id' => $i, 'text' => $sortitem->description);
    $i++;
  }
}

$kFonts      = gen_build_pull_down($Fonts);
$kFontSizes  = gen_build_pull_down($FontSizes);
$kFontColors = gen_build_pull_down($FontColors);
$kFontAlign  = gen_build_pull_down($FontAlign);
$nyChoice    = gen_build_pull_down($NoYesChoice);
$pFields     = gen_build_pull_down($FormProcessing);

$jsArray  = 'var fieldIdx = new Array();' . chr(10);
for ($i = 0; $i < sizeof($report->fieldlist); $i++) $jsArray .= 'fieldIdx[' . $i . '] = ' . $i . ';' . chr(10);

$cal_from = array(
  'name'      => 'dateFrom',
  'form'      => 'popup_gen',
  'fieldname' => 'date_from',
  'imagename' => 'btn_date_1',
  'default'   => isset($DateArray[1]) ? gen_locale_date($DateArray[1]) : date(DATE_FORMAT),
  'params'    => array('align' => 'left'),
);
$cal_to = array(
  'name'      => 'dateTo',
  'form'      => 'popup_gen',
  'fieldname' => 'date_to',
  'imagename' => 'btn_date_2',
  'default'   => isset($DateArray[2]) ? gen_locale_date($DateArray[2]) : date(DATE_FORMAT),
  'params'    => array('align' => 'left'),
);

$include_header   = false;
$include_footer   = false;
$include_template = $IncludePage;
define('PAGE_TITLE', PHREEFORM_REPORT_GEN);

?>
