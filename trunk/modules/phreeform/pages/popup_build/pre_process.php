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
//  Path: /modules/phreeform/pages/popup_build/pre_process.php
//
$security_level = validate_user(SECURITY_ID_PHREEFORM);
/**************  include page specific files    *********************/
require_once(DIR_FS_WORKING . 'defaults.php');
require_once(DIR_FS_WORKING . 'functions/phreeform.php');
require_once(DIR_FS_MODULES . 'phreedom/functions/phreedom.php');
/***************   hook for custom actions  ***************************/
$custom_path = DIR_FS_MODULES . 'phreeform/custom/extra_phreeform.php';
if (file_exists($custom_path)) { include_once($custom_path); }
/**************   page specific initialization  *************************/
$error       = false;
$self_close  = false;
$id          = $_GET['id']           ? $_GET['id']       : $_POST['id'];
$rID         = $_GET['rID']          ? $_GET['rID']      : $_POST['rID'];
$parent_id   = $_GET['parent_id']    ? $_GET['parent_id']: $_POST['parent_id'];
$def_module  = $_POST['mod']         ? $_POST['mod']     : DEFAULT_MODULE;
$def_lang    = $_POST['lang']        ? $_POST['lang']    : DEFAULT_LANGUAGE;
$import_path = "modules/$def_module/language/$def_lang/reports/";
$report      = new objectInfo();
// load the directory tree to java array (use for page display and error checking on update
$js_dir = $db->Execute('select id, parent_id, doc_title from ' . TABLE_PHREEFORM . ' order by id');
$dir_tree = array();
while (!$js_dir->EOF) {
  $dir_tree[$js_dir->fields['id']] = $js_dir->fields['parent_id'];
  $js_dir->MoveNext();
}
/***************   Act on the action request   *************************/
switch ($_REQUEST['action']) {
  case 'save':
  case 'preview':
  	if (!isset($_POST['filename_prefix'])) { // check for truncated post vars
		$messageStack->add('The form was not submitted in full and cannot be saved properly. The most common solution to this problem is to set the max_input_vars above the standard 1000 in your php.ini configuration file.','error');
		break;
	}
	// hidden fields
    $rID                          = db_prepare_input($_POST['rID']);
	$report->reporttype           = db_prepare_input($_POST['reporttype']);
	// posted fields
	$report->title                = db_prepare_input($_POST['title']);
	if (!$report->title) $error = $messageStack->add(GEN_ERRMSG_NO_DATA . TEXT_TITLE, 'error');
	$report->groupname            = db_prepare_input($_POST['groupname']);
	$report->description          = db_prepare_input($_POST['description']);
	$report->emailmessage         = db_prepare_input($_POST['emailmessage']);
	$report->page->size           = db_prepare_input($_POST['papersize']);
	$report->page->orientation    = db_prepare_input($_POST['paperorientation']);
	if ($_POST['table']) foreach ($_POST['table'] as $key => $value) {
	  $report->tables[] = new objectInfo(array(
	    'joinopt'      => db_prepare_input($_POST['joinopt'][$key]),
	    'tablename'    => db_prepare_input($_POST['table'][$key]),
	    'relationship' => db_prepare_input($_POST['table_crit'][$key]),
	  ));
	}
	$report->special_class        = db_prepare_input($_POST['special_class']);
	$report->page->margin->top    = db_prepare_input($_POST['margintop']);
	$report->page->margin->bottom = db_prepare_input($_POST['marginbottom']);
	$report->page->margin->left   = db_prepare_input($_POST['marginleft']);
	$report->page->margin->right  = db_prepare_input($_POST['marginright']);
	if ($report->reporttype == 'rpt') {
	  $report->page->heading->show  = db_prepare_input($_POST['headingshow']);
	  $report->page->heading->font  = db_prepare_input($_POST['headingfont']);
	  $report->page->heading->size  = db_prepare_input($_POST['headingsize']);
	  $report->page->heading->color = db_prepare_input($_POST['headingcolor']);
	  $report->page->heading->align = db_prepare_input($_POST['headingalign']);
	  $report->page->title1->show   = db_prepare_input($_POST['title1show']);
	  $report->page->title1->text   = db_prepare_input($_POST['title1desc']);
	  $report->page->title1->font   = db_prepare_input($_POST['title1font']);
	  $report->page->title1->size   = db_prepare_input($_POST['title1size']);
	  $report->page->title1->color  = db_prepare_input($_POST['title1color']);
	  $report->page->title1->align  = db_prepare_input($_POST['title1align']);
	  $report->page->title2->show   = db_prepare_input($_POST['title2show']);
	  $report->page->title2->text   = db_prepare_input($_POST['title2desc']);
	  $report->page->title2->font   = db_prepare_input($_POST['title2font']);
	  $report->page->title2->size   = db_prepare_input($_POST['title2size']);
	  $report->page->title2->color  = db_prepare_input($_POST['title2color']);
	  $report->page->title2->align  = db_prepare_input($_POST['title2align']);
	  $report->page->filter->font   = db_prepare_input($_POST['filterfont']);
	  $report->page->filter->size   = db_prepare_input($_POST['filtersize']);
	  $report->page->filter->color  = db_prepare_input($_POST['filtercolor']);
	  $report->page->filter->align  = db_prepare_input($_POST['filteralign']);
	  $report->page->data->font     = db_prepare_input($_POST['datafont']);
	  $report->page->data->size     = db_prepare_input($_POST['datasize']);
	  $report->page->data->color    = db_prepare_input($_POST['datacolor']);
	  $report->page->data->align    = db_prepare_input($_POST['dataalign']);
	  $report->page->totals->font   = db_prepare_input($_POST['totalsfont']);
	  $report->page->totals->size   = db_prepare_input($_POST['totalssize']);
	  $report->page->totals->color  = db_prepare_input($_POST['totalscolor']);
	  $report->page->totals->align  = db_prepare_input($_POST['totalsalign']);
	  $report->truncate             = db_prepare_input($_POST['truncate']);
	  $report->totalonly            = db_prepare_input($_POST['totalonly']);
	  $report->fieldlist            = array();
	  if ($_POST['fld_fld']) foreach ($_POST['fld_fld'] as $key => $value) {
	    $report->fieldlist[] = new objectInfo(
		  array(
	        'fieldname'   => db_prepare_input($_POST['fld_fld'][$key]),
	        'description' => db_prepare_input($_POST['fld_desc'][$key]),
	        'visible'     => db_prepare_input($_POST['fld_vis'][$key]),
	        'columnwidth' => db_prepare_input($_POST['fld_clmn'][$key]),
	        'columnbreak' => db_prepare_input($_POST['fld_brk'][$key]),
		    'processing'  => db_prepare_input($_POST['fld_proc'][$key]),
	        'align'       => db_prepare_input($_POST['fld_algn'][$key]),
		    'total'       => db_prepare_input($_POST['fld_tot'][$key]),
	      )
		);
	  }
	}

	if ($report->reporttype == 'frm') {
	  $report->setprintedfield    = db_prepare_input($_POST['setprintedfield']);
	  $report->formbreakfield     = db_prepare_input($_POST['formbreakfield']);
	  $report->serialform         = isset($_POST['serialform']) ? '1' : '0';
	  $report->fieldlist          = array();
	  $cnt = 0;
	  while(true) {
	    if (!isset($_POST['row_id'][$cnt])) break;
		$key = $_POST['row_id'][$cnt];
	    $properties = new objectInfo();
	    $properties->description = db_prepare_input($_POST['fld_desc'][$cnt]);
	    $properties->abscissa    = db_prepare_input($_POST['fld_abs'][$cnt]);
	    $properties->ordinate    = db_prepare_input($_POST['fld_ord'][$cnt]);
	    $properties->width       = db_prepare_input($_POST['fld_wid'][$cnt]);
		$properties->height      = db_prepare_input($_POST['fld_hgt'][$cnt]);
		$properties->rowbreak    = db_prepare_input($_POST['fld_brk'][$cnt]);
	    $properties->type        = db_prepare_input($_POST['fld_type_'.$key]);
	    // check for image
	    if (isset($_POST['img_sel_' . $key])) {
		  if ($_POST['img_sel_' . $key] == 'U') { // upload
		    if (validate_upload('img_upload_' . $key, 'image', array('jpg', 'jpeg', 'png', 'gif'))) {
			  $properties->filename = $_FILES['img_upload_' . $key]['name'];
			  if (!@move_uploaded_file($_FILES['img_upload_' . $key]['tmp_name'], PF_DIR_MY_REPORTS . 'images/' . $properties->filename)) {
			    $messageStack->add(sprintf(PHREEFORM_IMAGE_MOVE_ERROR, PF_DIR_MY_REPORTS . 'images/' . $properties->filename), 'error');
			  }
		    } else {
			  $messageStack->add(PHREEFORM_IMAGE_UPLOAD_ERROR, 'error');
		    }
		  } else { // selected from the list
			$properties->filename = $_POST['img_file_' . $key];
		  }
		}
		// line
		if (isset($_POST['box_ltype_' . $key])) {
		  $properties->linetype = $_POST['box_ltype_' . $key];
		  if ($_POST['box_len_' .$key]) $properties->length      = $_POST['box_len_'  .$key];
		  if ($_POST['box_eabs_'.$key]) $properties->endabscissa = $_POST['box_eabs_' .$key];
		  if ($_POST['box_eord_'.$key]) $properties->endordinate = $_POST['box_eord_' .$key];
		}
		if ($_POST['box_txt_'   .$key]) $properties->text         = $_POST['box_txt_'  .$key];
		if ($_POST['box_trun_'  .$key]) $properties->truncate     = $_POST['box_trun_' .$key];
		if ($_POST['box_last_'  .$key]) $properties->display      = $_POST['box_last_' .$key];
		if ($_POST['box_fnt_'   .$key]) $properties->font         = $_POST['box_fnt_'  .$key];
		if ($_POST['box_size_'  .$key]) $properties->size         = $_POST['box_size_' .$key];
		if ($_POST['box_aln_'   .$key]) $properties->align        = $_POST['box_aln_'  .$key];
		if ($_POST['box_clr_'   .$key]) $properties->color        = $_POST['box_clr_'  .$key];
		if ($_POST['box_bdr_'   .$key]) $properties->bordershow   = $_POST['box_bdr_'  .$key] ? '1' : '0';
		if ($_POST['box_bsz_'   .$key]) $properties->bordersize   = $_POST['box_bsz_'  .$key];
		if ($_POST['box_bclr_'  .$key]) $properties->bordercolor  = $_POST['box_bclr_' .$key];
		if ($_POST['box_fill_'  .$key]) $properties->fillshow     = $_POST['box_fill_' .$key] ? '1' : '0';
		if ($_POST['box_fclr_'  .$key]) $properties->fillcolor    = $_POST['box_fclr_' .$key];
		if ($_POST['hbox_fnt_'  .$key]) $properties->hfont        = $_POST['hbox_fnt_' .$key];
		if ($_POST['hbox_size_' .$key]) $properties->hsize        = $_POST['hbox_size_'.$key];
		if ($_POST['hbox_aln_'  .$key]) $properties->halign       = $_POST['hbox_aln_' .$key];
		if ($_POST['hbox_clr_'  .$key]) $properties->hcolor       = $_POST['hbox_clr_' .$key];
		if ($_POST['hbox_bdr_'  .$key]) $properties->hbordershow  = $_POST['hbox_bdr_' .$key] ? '1' : '0';
		if ($_POST['hbox_bsz_'  .$key]) $properties->hbordersize  = $_POST['hbox_bsz_' .$key];
		if ($_POST['hbox_bclr_' .$key]) $properties->hbordercolor = $_POST['hbox_bclr_'.$key];
		if ($_POST['hbox_fill_' .$key]) $properties->hfillshow    = $_POST['hbox_fill_'.$key] ? '1' : '0';
		if ($_POST['hbox_fclr_' .$key]) $properties->hfillcolor   = $_POST['hbox_fclr_'.$key];
		$boxfield = array();
		for ($j = 0; $j < sizeof($_POST['box_fld_' . $key]); $j++) {
		  $temp = new objectInfo();
		  if ($_POST['box_fld_' .$key]) $temp->fieldname          = $_POST['box_fld_'  .$key][$j];
		  if ($_POST['box_desc_'.$key]) $temp->description        = $_POST['box_desc_' .$key][$j];
		  if ($_POST['box_proc_'.$key]) $temp->processing         = $_POST['box_proc_' .$key][$j];
		  if ($_POST['box_fmt_' .$key]) $temp->formatting         = $_POST['box_fmt_'  .$key][$j];
		  if ($_POST['box_fnt_' .$key]) $temp->font               = $_POST['box_fnt_'  .$key][$j];
		  if ($_POST['box_size_'.$key]) $temp->size               = $_POST['box_size_' .$key][$j];
		  if ($_POST['box_aln_' .$key]) $temp->align              = $_POST['box_aln_'  .$key][$j];
		  if ($_POST['box_clr_' .$key]) $temp->color              = $_POST['box_clr_'  .$key][$j];
		  if ($_POST['box_wid_' .$key]) $temp->width              = $_POST['box_wid_'  .$key][$j];
		  $boxfield[] = $temp;
		}
		$properties->boxfield = $boxfield;
	    $report->fieldlist[]  = $properties;
		$cnt++;
	  }
	}

	$users = 'u:-1';
	if (isset($_POST['user_all'])) $users = 'u:0';
	elseif (isset($_POST['users']) && $_POST['users'][0] <> '') $users = 'u:' . implode(':', $_POST['users']);
	$groups = 'g:-1';
	if (isset($_POST['group_all'])) $groups = 'g:0';
	elseif (isset($_POST['users']) && $_POST['groups'][0] <> '') $groups = 'g:' . implode(':', $_POST['groups']);
	$report->security = $users . ';' . $groups;
	// test for no access which will hide the report
	if ($report->security == 'u:-1;g:-1') {
		$messageStack->add(PHREEFORM_NO_ACCESS, 'error');
		$error = true;
	}
	$datelist = $_POST['date_range'] <> '' ? implode('', $_POST['date_range']) : ''; 
	$report->datelist       = isset($_POST['periods_only']) ? 'z' : $datelist;
	$report->datefield      = db_prepare_input($_POST['date_field']);
	$report->datedefault    = db_prepare_input($_POST['date_default']);
	$report->filenameprefix = db_prepare_input($_POST['filename_prefix']);
	$report->filenamefield  = db_prepare_input($_POST['filename_field']);
	$report->grouplist      = array();
	if ($_POST['grp_fld']) foreach ($_POST['grp_fld'] as $key => $value) {
	  $report->grouplist[]  = new objectInfo(array(
	    'fieldname'   => db_prepare_input($_POST['grp_fld'][$key]),
	    'description' => db_prepare_input($_POST['grp_desc'][$key]),
	    'default'     => db_prepare_input($_POST['grp_def'][$key]),
	    'page_break'  => db_prepare_input($_POST['grp_brk'][$key]),
		'processing'  => db_prepare_input($_POST['grp_prc'][$key]),
	  ));
	}
	$report->sortlist = array();
	if ($_POST['sort_fld']) foreach ($_POST['sort_fld'] as $key => $value) {
	  $report->sortlist[] = new objectInfo(array(
	    'fieldname'   => db_prepare_input($_POST['sort_fld'][$key]),
	    'description' => db_prepare_input($_POST['sort_desc'][$key]),
	    'default'     => db_prepare_input($_POST['srt_def'][$key]),
	  ));
	}
	$report->filterlist = array();
	if ($_POST['crit_fld']) foreach ($_POST['crit_fld'] as $key => $value) {
	  $report->filterlist[] = new objectInfo(array(
	    'fieldname'   => db_prepare_input($_POST['crit_fld'][$key]),
	    'description' => db_prepare_input($_POST['crit_desc'][$key]),
	    'visible'     => db_prepare_input($_POST['crit_vis'][$key]),
	    'type'        => db_prepare_input($_POST['crit_def'][$key]),
	    'min_val'     => db_prepare_input($_POST['crit_min'][$key]),
		'max_val'     => db_prepare_input($_POST['crit_max'][$key]),
	  ));
	}
	if (!$error) {
	  $success = save_report($report, $rID);
	  if ($_REQUEST['action'] == 'save' && $success) $self_close  = true;
	}
    break;
  case 'save_dir':
	$doc_title = db_prepare_input($_POST['doc_title']);
	$doc_ext   = db_prepare_input($_POST['doc_ext']);
	$doc_group = db_prepare_input($_POST['doc_group']);
	// check for valid folder name
	if (!$doc_title) {
	  $messageStack->add(PHREEFORM_FOLDER_BLANK_ERROR,'error');
	  break;
	}
	// check to see if the directory is being moved below itself
	if (!validate_dir_move($dir_tree, $id, $parent_id)) {
	  $messageStack->add(PHREEFORM_DIR_MOVE_ERROR,'error');
	  break;
	}
	$result = $db->Execute("select id from " . TABLE_PHREEFORM . " where doc_group = '" . $doc_group . "'");
	if ($result->RecordCount() > 0) {
	  if ($result->fields['id'] <> $id) {
	   $messageStack->add(PHREEFORM_DIR_GROUP_DUP_ERROR, 'error');
	    break;
	  }
	}
	// insert/update db
	$sql_array = array(
	  'parent_id'   => $parent_id,
	  'doc_title'   => $doc_title,
	  'doc_type'    => '0',
	  'doc_group'   => $doc_group,
	  'doc_ext'     => $doc_ext,
	  'security'    => 'u:0;g:0',
	  'create_date' => date('Y-m-d'),
	);
	if ($id) { // update
	  db_perform(TABLE_PHREEFORM, $sql_array, 'update', 'id = ' . $id);
	} else { // insert
	  db_perform(TABLE_PHREEFORM, $sql_array, 'insert');
	}
	$doc_title = ''; // clear the doc title
    break;
  case 'delete_dir':
	if (!$id) {
	  $messageStack->add(PHREEFORM_DIR_DELETE_ERROR,'error');
	  break;
	}
	// check for directory empty
	$result = $db->Execute("select id from " . TABLE_PHREEFORM . " where parent_id = " . $id);
	if ($result->RecordCount() > 0) {
	  $messageStack->add(PHREEFORM_DIR_NOT_EMPTY_ERROR,'error');
	  break;
	}
	$db->Execute("delete from " . TABLE_PHREEFORM . " where id = " . $id);
	$messageStack->add(PHREEFORM_DIR_DELETE_SUCCESS,'success');
    break;
  case 'import_one':
    if ($success = ImportReport($_POST['reportname'], $_POST['RptFileName'], $import_path)) {
	  $messageStack->add(PHREEFORM_IMPORT_SUCCESS, 'success');
	  $self_close = true;
	}
	break;
  case 'import_all':
    $output = array();
	if ($handle = opendir($import_path)) {
      while (false !== ($file = readdir($handle))) {
        if ($file <> "." && $file <> "..") $output[] = $file;
      }
	} else {
	  $messageStack->add('error opening the directory for reading!','error');
	  break;
	}
    closedir($handle);
	foreach ($output as $file) if (!$success = ImportReport(NULL, $file, $import_path)) $error = true;
    if (!$error) {
	  $messageStack->add(PHREEFORM_DIR_IMPORT_SUCCESS, 'success');
	  $self_close = true;
	}
	break;
  default:
}
/*****************   prepare to display templates  *************************/
$sel_yes_no = array(
 array('id' => '0', 'text' => TEXT_NO),
 array('id' => '1', 'text' => TEXT_YES),
);
$include_header   = false;
$include_footer   = false;
switch ($_REQUEST['action']) {
  case 'import':
  case 'import_one':
  case 'import_all':
  case 'refresh_dir':
    $sel_modules = array();
	foreach ($loaded_modules as $mod) $sel_modules[] = array('id' => $mod, 'text' => $mod);
	$sel_language = load_language_dropdown();
	define('PAGE_TITLE', PHREEFORM_DOC_IMPORT);
    $include_template = 'template_import.php';
    break;
  default:
  case 'new_rpt':
  case 'new_frm':
  case 'new_ltr':
  	$report = default_report();
	if ($_REQUEST['action'] == 'new_rpt') $report->reporttype = 'rpt';
	if ($_REQUEST['action'] == 'new_frm') $report->reporttype = 'frm';
	if ($_REQUEST['action'] == 'new_ltr') $report->reporttype = 'ltr';
	case 'save':
  case 'preview':
  case 'design':
	if ($rID) $report = get_report_details($rID);
	// extract the security settings
	$temp = explode(';', $report->security);
	$security = array();
	foreach ($temp as $value) {
      $member = explode(':', $value);
	  if ($member[1] == '-1') $member[1] = ''; // make it no access which is null string on pull down
	  $mbr_id = array_shift($member);
	  $security[$mbr_id] = $member;
	}
    $kFonts       = gen_build_pull_down($Fonts);
    $kFontSizes   = gen_build_pull_down($FontSizes);
	$kLineSizes   = gen_build_pull_down($LineSizes);
    $kFontColors  = gen_build_pull_down($FontColors);
    $kFontAlign   = gen_build_pull_down($FontAlign);
	$cFields      = CreateCompanyArray();
	$fFields      = crit_build_pull_down($CritChoices);
    $kFields      = CreateSpecialDropDown($report);
    $kTblFields   = CreateFieldTblDropDown($report);
	$kTables      = CreateTableList($report);
	$nyChoice     = gen_build_pull_down($NoYesChoice);
	$pFields      = gen_build_pull_down($FormProcessing);
	$tProcessing  = gen_build_pull_down($TextProcessing);
	$joinOptions  = gen_build_pull_down($joinSyntax);
	// build the groups list
	$report_groups = build_groups($report_groups);
	$rFields  = '<select name="groupname" id="groupname">' . chr(10);
	switch ($report->reporttype) {
		case 'rpt':
		  $rFields .= '<optgroup label="' . TEXT_REPORTS . '">' . chr(10);
		  foreach ($report_groups['reports'] as $key => $value) {
		    $selected = ($report->groupname == $key) ? ' selected="selected"' : '';
		    $rFields .= '<option value="' . $key . '"' . $selected . '>' . htmlspecialchars($value) . '</option>' . chr(10);
		  }
		  $rFields .= '</optgroup>' . chr(10);
		  break;
		case 'frm':
		  $rFields .= '<optgroup label="' . TEXT_FORMS . '">' . chr(10);
		  foreach ($report_groups['forms'] as $key => $value) {
		    $selected = ($report->groupname == $key) ? ' selected="selected"' : '';
		    $rFields .= '<option value="' . $key . '"' . $selected . '>' . htmlspecialchars($value) . '</option>' . chr(10);
		  }
		  $rFields .= '</optgroup>' . chr(10);
		  break;
	}
	$rFields .= '</select>'   . chr(10);
	define('PAGE_TITLE', PHREEFORM_DOC_DESIGN);
    $include_template = 'template_design.php';
    break;
}

?>