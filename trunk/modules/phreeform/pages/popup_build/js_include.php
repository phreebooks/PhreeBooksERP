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
//  Path: /modules/phreeform/pages/popup_build/js_include.php
//

?>
<script type="text/javascript">
<!--
// pass any php variables generated during pre-process that are used in the javascript functions.
// Include translations here as well.
var action      = '<?php echo $_REQUEST['action']; ?>';
var tableInit   = new Array();
var dFields     = new Array(); // holds the field list which changes with table selection
var tableCount  = 0;
var rClick      = 0;
<?php 
echo "  tableInit[tableCount] = 'table_setup'; tableCount++;" . chr(10);
echo "  tableInit[tableCount] = 'sort_setup';  tableCount++;" . chr(10);
echo "  tableInit[tableCount] = 'crit_setup';  tableCount++;" . chr(10);
switch ($report->reporttype) {
  case 'frm': echo "  tableInit[tableCount] = 'field_setup_frm'; tableCount++;" . chr(10); break;
  case 'rpt': echo "  tableInit[tableCount] = 'field_setup';     tableCount++;" . chr(10);
			  echo "  tableInit[tableCount] = 'group_setup';     tableCount++;" . chr(10); break;
  } 
?>
var textPortrait  = '<?php echo TEXT_PORTRAIT; ?>';
var textLandscape = '<?php echo TEXT_LANDSCAPE; ?>';

function init() {
  if (action == 'preview') {
    var rID = document.getElementById('rID').value;
    var popupWin = window.open("index.php?module=phreeform&page=popup_gen&rID="+rID+"&action=open&preview=1","popup_gen","width=900,height=650,resizable=1,scrollbars=1,top=150,left=200");
  }
<?php 
  if ($self_close) {
    echo 'window.opener.location="index.php?module=phreeform&page=main";' . chr(10);
    echo 'self.close();' . chr(10); 
  }
  if ($report->reporttype == 'frm') {
    if (!$report->fieldlist)  echo '  rowAction("field_setup_frm", "add");' . chr(10);
  } else if ($report->reporttype == 'rpt') {
  	if (!$report->fieldlist)  echo '  rowAction("field_setup",     "add");' . chr(10);
    if (!$report->grouplist)  echo '  rowAction("group_setup",     "add");' . chr(10);
  } 
  if (!$report->sortlist)   echo '  rowAction("sort_setup", "add");' . chr(10);
  if (!$report->filterlist) echo '  rowAction("crit_setup", "add");' . chr(10);
  ?>
  $('table td img.delete').click(function(){ if (confirm('<?php echo TEXT_DELETE_ENTRY; ?>')) $(this).parent().parent().remove(); });

  for (var tables in tableInit) {
    var aTable = document.getElementById(tableInit[tables]);
    var tableDnD = new TableDnD();
    tableDnD.init(aTable);
  }
<?php if ($report->reporttype == 'rpt') echo '  calculateWidth();'; ?>
<?php if ($report->reporttype == 'frm') echo '  fieldLoad();'; ?>


}

function check_form() {
  return true;
}

// Insert other page specific functions here.
function pageReload() {
  submitToDo('new_dir');
}

/******************* BOF - Designer Functions **********************/
// ajax call to validate database db setup
function validateDB() {
  var table, tableCrit = '';
  var fields = '';
  var tableCnt = 0;
  for (i=0, j=1; i<document.getElementById('table_setup').rows.length - 2; i++, j++) {
    table   = document.forms[0].elements['table[]'][i].value;
    joinOpt = document.forms[0].elements['joinopt[]'][i].value;
    if (table) { 
      tableCrit = document.forms[0].elements['table_crit[]'][i].value;
      if (tableCrit || (table && i==0)) {
	    fields += '&joinopt'+j+'='+joinOpt+'&table'+j+'='+table+'&table'+j+'criteria='+tableCrit;
        tableCnt++;
	  }
	}
  }
  if (tableCnt < 2 || !tableCrit) {
	$.messager.alert('error','<?php echo PHREEFORM_JS_TABLE_CHECK_ERROR; ?>','error');
    return;
  }
  $.ajax({
    type: "GET",
    url: 'index.php?module=phreeform&page=ajax&op=validate_db'+fields,
    dataType: ($.browser.msie) ? "text" : "xml",
    error: function(XMLHttpRequest, textStatus, errorThrown) {
    	$.messager.alert("Ajax Error ", XMLHttpRequest.responseText + "\nTextStatus: " + textStatus + "\nErrorThrown: " + errorThrown, "error");
    },
	success: validateDbResp
  });
}

function validateDbResp(sXml) {
  var xml = parseXml(sXml);
  if (!xml) return;
  if ($(xml).find("message").text()) $.messager.alert('info',$(xml).find("message").text(),'info');
}
/**************************************************************************************/
// ajax call to load form field list after a table change
function fieldLoad() {
  var params = '';
  if (!document.forms[0].elements['table[]'][0].value) { // only one table defined
    params += '&t1=' + document.forms[0].elements['table[]'].value;
  } else {
    for (i=0, j=1; i<document.getElementById('table_setup').rows.length - 2; i++, j++) {
	  params += '&t'+j+'=' + document.forms[0].elements['table[]'][i].value;
    }
  }
  if (document.getElementById('special_class').value) params += '&sp=' + document.getElementById('special_class').value;
  $.ajax({
    type: "GET",
    url: 'index.php?module=phreeform&page=ajax&op=field_load'+params,
    dataType: ($.browser.msie) ? "text" : "xml",
    error: function(XMLHttpRequest, textStatus, errorThrown) {
    	$.messager.alert("Ajax Error ", XMLHttpRequest.responseText + "\nTextStatus: " + textStatus + "\nErrorThrown: " + errorThrown, "error");
    },
	success: fieldLoadResp
  });
}

function fieldLoadResp(sXml) {
  var temp    = new Object();
  var dFields = new Array();
  var iIndex  = 0;
  var xml     = parseXml(sXml);
  if (!xml) return;
  if ($(xml).find("message").text()) {
	dFields = new Array();
    $(xml).find("option").each(function() {
	  temp.id   = $(this).find("id").text();
	  temp.text = $(this).find("text").text();
	  dFields[iIndex] = temp;
	  iIndex++;
    });
  }
}

/**************************************************************************************/
// ajax call to load form properties
function boxLoad(type, rowID) {
  var rID = document.getElementById('rID').value;
  $.ajax({
    type: "GET",
    url: 'index.php?module=phreeform&page=ajax&op=box_load&type='+type+'&rID='+rID+'&rowID='+rowID,
    dataType: ($.browser.msie) ? "text" : "xml",
    error: function(XMLHttpRequest, textStatus, errorThrown) {
    	$.messager.alert("Ajax Error ", XMLHttpRequest.responseText + "\nTextStatus: " + textStatus + "\nErrorThrown: " + errorThrown, "error");
    },
	success: boxLoadResp
  });
}

function boxLoadResp(sXml) {
  var xml = parseXml(sXml);
  if (!xml) return;
  if ($(xml).find("message").text()) {
    var rowID = $(xml).find("rowID").text();
    var boxRow = document.getElementById('fld_box_'+rowID);
	boxRow.innerHTML = $(xml).find("html").text();
//	boxRow.style.display = 'none';
  }
}

/**************************************************************************************/
function updateFieldList(objSelect) {
  if (!dFields.length) return;
  var id = objSelect.id;
  var fld_combo = id.search("fld_combo");
  var box_combo = id.search("box_fld");
  if (fld_combo == 0 || box_combo == 0) { // it's a combo box, replace with drop down select field
    objSelect = document.getElementById("combosel" + id);
  }
  var def = objSelect.value;
  while (objSelect.options.length) objSelect.remove(0);
  for (i=0; i<dFields.length; i++) {
	newOpt = document.createElement("option");
	newOpt.text = dFields[i].text;
	objSelect.options.add(newOpt);
	objSelect.options[i].value = dFields[i].id;
  }
  if (def != false) objSelect.value = def;
}

function calculateWidth() {	// total up the columns
	var brk = new Array();
	var maxColWidth = 0;
	var colWidth, temp;
	var totalWidth = parseFloat(document.getElementById('marginleft').value);
	var colCount   = 1;
	var rowColCnt  = new Array();
	var rowWidth   = new Array();

	for (seq=0; seq<document.getElementById('field_setup').rows.length - 2; seq++) {
      if (document.getElementById('field_setup').rows.length == 3) {
	    cValue = document.forms[0].elements['fld_clmn[]'].value;
		cVis   = document.forms[0].elements['fld_vis[]'].value;
		cBreak = document.forms[0].elements['fld_brk[]'].value;
	  } else {
	    cValue = document.forms[0].elements['fld_clmn[]'][seq].value;
		cVis   = document.forms[0].elements['fld_vis[]'][seq].value;
		cBreak = document.forms[0].elements['fld_brk[]'][seq].value;
	  }
	  if (cValue == '') cValue = '0';
	  colWidth = parseFloat(cValue);
	  if (isNaN(colWidth)) continue;
	  if (cVis == '1') {
		if (colWidth > maxColWidth) {
		  totalWidth += colWidth - maxColWidth;
		  maxColWidth = colWidth;
		  rowWidth[colCount] = totalWidth;
		}
		rowColCnt[seq] = colCount;
		if (cBreak = '1') {
		  colCount++;
		  maxColWidth = 0;
		}
	  } else {
	    rowColCnt[seq] = 0;
	  }
	}
	// set the page information
	for (var i = 0; i < document.forms[0].paperorientation.length; i++) if (document.forms[0].paperorientation[i].checked) break;
	var orientation  = document.forms[0].paperorientation[i].value;
	var orienText    = (orientation == 'P' ? textPortrait : textLandscape);
	var index        = document.getElementById('papersize').selectedIndex;
	var paperValue   = document.getElementById('papersize').options[index].value;
	var marginValues = paperValue.split(':');
	pageWidth = (orientation == 'P') ? marginValues[1] : marginValues[2];
	var pageProperties = '<?php echo PHREEFORM_FLDLIST; ?>';
	pageProperties += ' ('+'<?php echo TEXT_ORIEN; ?>'+': '+orienText;
	pageProperties += ', '+'<?php echo TEXT_WIDTH; ?>'+': '+pageWidth;
	pageProperties += ', '+'<?php echo PHREEFORM_PGMARGIN_L; ?>'+': '+document.getElementById('marginleft').value;
	pageProperties += ', '+'<?php echo PHREEFORM_PGMARGIN_R; ?>'+': '+document.getElementById('marginright').value+')';
	if (document.all) { // IE browsers
	  document.getElementById('fieldListHeading').innerText   = pageProperties;
	} else { //firefox
	  document.getElementById('fieldListHeading').textContent = pageProperties;
	}

	for (var seq = 0; seq < document.getElementById('field_setup').rows.length - 2; seq++) {
	  colCount = rowColCnt[seq];
	  if (colCount != 0) {
	    colWidth = rowWidth[colCount];
	  } else {
	    colWidth = '';
	  }
		if (document.all) { // IE browsers
		  document.getElementById('field_setup').rows[seq+2].cells[4].innerText   = isNaN(colWidth) ? '' : colWidth;
		} else { //firefox
		  document.getElementById('field_setup').rows[seq+2].cells[4].textContent = isNaN(colWidth) ? '' : colWidth;
		}
		document.getElementById('field_setup').rows[seq+2].cells[4].style.color = (colWidth > pageWidth) ? 'red' : '';
	}
}

function rowAction(idTable, action, boxID) {
//alert('table = '+idTable+' and rCLick = '+rClick+' and action = '+action+' box id = '+boxID);
  switch (action) {
	case 'add':
	  var newRow = buildRow(idTable, -1, boxID);
	  break;
  }
  return;
}

function buildRow(idTable, rIndex, boxID) {
  var newCell;
  var cell = new Array();
  var size = new Array();
  var attr = new Array();
  var wrap = new Array();
  var skipBuild  = false;
  var updateList = false;
  var tableInit  = true;
  if (!isNaN(boxID)) {
    var newRow = document.getElementById(idTable+boxID).insertRow(-1);
  } else {
    var newRow = document.getElementById(idTable).insertRow(-1);
  }
  rowCnt = newRow.rowIndex; // less header lines
//alert('idTable = '+idTable+' and rIndex = '+rIndex+' and boxID = '+boxID+' and rowCnt = '+rowCnt);

  switch (idTable) {
	case 'table_setup':
	  cell[0]  = '<?php echo str_replace("'", "\'", html_pull_down_menu('joinopt[]', $joinOptions, '', '')); ?>';
	  cell[1]  = '<?php echo str_replace("'", "\'", html_pull_down_menu('table[]', $kTables, '', 'onchange="fieldLoad()"')); ?>';
	  cell[2]  = '<?php echo " on " . html_input_field   ('table_crit[]', '', 'size="80"'); ?>';
	  temp     = '<?php echo str_replace("'", "\'", html_icon('actions/view-fullscreen.png',   TEXT_MOVE,   'small', 'style="cursor:move"', '', '', 'move_table_row_TBD')); ?>';
	  temp    += '<?php echo str_replace("'", "\'", html_icon('emblems/emblem-unreadable.png', TEXT_DELETE, 'small', 'onclick="if (confirm(\''.TEXT_DELETE_ENTRY.'\')) $(this).parent().parent().remove();"')); ?>';
	  cell[3]  = temp.replace(/row_TBD/g, rowCnt);
	  attr[3]  = 'right';
      break;
    case 'group_setup':
	  cell[0]  = '<?php echo str_replace("'", "\'", html_pull_down_menu('grp_fld[]',  $kFields, '', 'onclick="updateFieldList(this)"')); ?>';
	  cell[1]  = '<?php echo html_input_field   ('grp_desc[]'); ?>';
	  cell[2]  = '<?php echo str_replace("'", "\'", html_pull_down_menu('grp_def[]',  $nyChoice, '0')); ?>';
	  cell[3]  = '<?php echo str_replace("'", "\'", html_pull_down_menu('grp_brk[]',  $nyChoice, '0')); ?>';
	  cell[4]  = '<?php echo str_replace("'", "\'", html_pull_down_menu('grp_proc[]', $pFields)); ?>';
	  temp     = '<?php echo str_replace("'", "\'", html_icon('actions/view-fullscreen.png',   TEXT_MOVE,   'small', 'style="cursor:move"', '', '', 'move_grp_row_TBD')); ?>';
	  temp    += '<?php echo str_replace("'", "\'", html_icon('emblems/emblem-unreadable.png', TEXT_DELETE, 'small', 'onclick="if (confirm(\''.TEXT_DELETE_ENTRY.'\')) $(this).parent().parent().remove();"')); ?>';
	  cell[5]  = temp.replace(/row_TBD/g, rowCnt);
	  attr[5]  = 'right';
	  updateList = '';
      break;
	case 'sort_setup':
	  cell[0]  = '<?php echo str_replace("'", "\'", html_pull_down_menu('sort_fld[]', $kFields, '', 'onclick="updateFieldList(this)"')); ?>';
	  cell[1]  = '<?php echo html_input_field   ('sort_desc[]'); ?>';
	  cell[2]  = '<?php echo str_replace("'", "\'", html_pull_down_menu('sort_def[]', $nyChoice, '0')); ?>';
	  temp     = '<?php echo str_replace("'", "\'", html_icon('actions/view-fullscreen.png',   TEXT_MOVE,   'small', 'style="cursor:move"', '', '', 'move_sort_row_TBD')); ?>';
	  temp    += '<?php echo str_replace("'", "\'", html_icon('emblems/emblem-unreadable.png', TEXT_DELETE, 'small', 'onclick="if (confirm(\''.TEXT_DELETE_ENTRY.'\')) $(this).parent().parent().remove();"')); ?>';
	  cell[3]  = temp.replace(/row_TBD/g, rowCnt);
	  attr[3]  = 'right';
      break;
	case 'crit_setup':
	  cell[0]  = '<?php echo str_replace("'", "\'", html_pull_down_menu('crit_fld[]', $kFields, '', 'onclick="updateFieldList(this)"')); ?>';
	  cell[1]  = '<?php echo html_input_field   ('crit_desc[]'); ?>';
	  cell[2]  = '<?php echo str_replace("'", "\'", html_pull_down_menu('crit_vis[]', $nyChoice, '0')); ?>';
	  cell[3]  = '<?php echo str_replace("'", "\'", html_pull_down_menu('crit_def[]', crit_build_pull_down($CritChoices))); ?>';
	  cell[4]  = '<?php echo html_input_field   ('crit_min[]', '', 'size="10"'); ?>';
	  cell[5]  = '<?php echo html_input_field   ('crit_max[]', '', 'size="10"'); ?>';
	  temp     = '<?php echo str_replace("'", "\'", html_icon('actions/view-fullscreen.png',   TEXT_MOVE,   'small', 'style="cursor:move"', '', '', 'move_crit_row_TBD')); ?>';
	  temp    += '<?php echo str_replace("'", "\'", html_icon('emblems/emblem-unreadable.png', TEXT_DELETE, 'small', 'onclick="if (confirm(\''.TEXT_DELETE_ENTRY.'\')) $(this).parent().parent().remove();"')); ?>';
	  cell[6]  = temp.replace(/row_TBD/g, rowCnt);
	  attr[6]  = 'right';
      break;
	case 'field_setup':
	  var temp = '<?php echo str_replace("'", "\'", html_combo_box('fld_fld[]', $kFields, '', 'onclick="updateFieldList(this)"', '220px', '', 'fld_combo_tbd')); ?>';
	  cell[0]  = temp.replace(/fld_combo_tbd/g, "fld_combo_"+rowCnt);
	  cell[1]  = '<?php echo html_input_field   ('fld_desc[]'); ?>';
	  cell[2]  = '<?php echo str_replace("'", "\'", html_pull_down_menu('fld_brk[]',  $nyChoice, '0', 'onchange="calculateWidth()"')); ?>';
	  cell[3]  = '<?php echo html_input_field   ('fld_clmn[]', PF_DEFAULT_COLUMN_WIDTH, 'size="4" maxlength="3" onchange="calculateWidth()"'); ?>';
	  cell[4]  = '<?php echo '&nbsp;'; ?>';
	  cell[5]  = '<?php echo str_replace("'", "\'", html_pull_down_menu('fld_vis[]',  $nyChoice, '0', 'onchange="calculateWidth()"')); ?>';
	  cell[6]  = '<?php echo str_replace("'", "\'", html_pull_down_menu('fld_proc[]', $pFields)); ?>';
	  cell[7]  = '<?php echo str_replace("'", "\'", html_pull_down_menu('fld_tot[]',  $nyChoice)); ?>';
	  cell[8]  = '<?php echo str_replace("'", "\'", html_pull_down_menu('fld_algn[]', $kFontAlign)); ?>';
	  temp     = '<?php echo str_replace("'", "\'", html_icon('actions/view-fullscreen.png',   TEXT_MOVE,   'small', 'style="cursor:move"', '', '', 'move_fld_row_TBD')); ?>';
	  temp    += '<?php echo str_replace("'", "\'", html_icon('emblems/emblem-unreadable.png', TEXT_DELETE, 'small', 'onclick="if (confirm(\''.TEXT_DELETE_ENTRY.'\')) $(this).parent().parent().remove();" onclick="calculateWidth();"')); ?>';
	  cell[9]  = temp.replace(/row_TBD/g, rowCnt);
	  attr[4]  = 'center';
	  attr[9]  = 'right';
      break;
	case 'field_setup_frm':
      newCell = newRow.insertCell(-1);
	  newCell.colSpan = '8'; 
	  newCell.nowrap = 'nowrap'; 
	  var proptable   = document.createElement("div");
	  newCell.appendChild(proptable);
	  cell[0]  = '<?php echo html_input_field('fld_desc[]', '','size="20" maxlength="25"'); ?>';
	  cell[0] += '<?php echo html_input_field('fld_abs[]', '', 'size="6" maxlength="4"'); ?>';
	  cell[0] += '<?php echo html_input_field('fld_ord[]', '', 'size="6" maxlength="4"'); ?>';
	  cell[0] += '<?php echo html_input_field('fld_wid[]', '', 'size="6" maxlength="4"'); ?>';
	  cell[0] += '<?php echo html_input_field('fld_hgt[]', '', 'size="6" maxlength="4"'); ?>';
	  if (document.getElementById('serialform') && document.getElementById('serialform').checked) {
	    cell[0] += '<?php echo str_replace("'", "\'", html_pull_down_menu('fld_brk[]', $nyChoice, '1', '')); ?>';
	  }
	  temp     = '<?php echo str_replace("'", "\'", html_pull_down_menu('fld_type_row_TBD', gen_build_pull_down($FormEntries), '', 'onchange="boxLoad(this.value, row_TBD)"')); ?>';
	  temp    += '<?php echo html_hidden_field('row_id[]', 'row_TBD'); ?>';
	  temp    += '<?php echo '&nbsp;'.str_replace("'", "\'", html_icon('actions/view-fullscreen.png', TEXT_MOVE,       'small', 'style="cursor:move"', '', '', 'move_fld_row_TBD')) . '&nbsp;'; ?>';
	  temp    += '<?php echo str_replace("'", "\'", html_icon('emblems/emblem-unreadable.png',   TEXT_DELETE,     'small', 'onclick="if (confirm(\''.TEXT_DELETE_ENTRY.'\')) $(this).parent().parent().remove();"')) . '&nbsp;'; ?>';
	  temp    += '<?php echo str_replace("'", "\'", html_icon('actions/document-properties.png', TEXT_PROPERTIES, 'small', 'onclick="boxProperties(row_TBD)"')); ?>';
	  cell[0] += temp.replace(/row_TBD/g, rowCnt);
	  newCell.innerHTML = cell[0];
	  var boxDiv     = document.createElement("div");
	  boxDiv.id      = 'fld_box_'+rowCnt;
	  boxDiv.colSpan = '8'; 
	  boxDiv.bgColor = '#bbd8d8';
	  boxDiv.border  = 'solid 1px #000';
	  newCell.appendChild(boxDiv);
	  boxLoad('', rowCnt); // call ajax box build
	  skipBuild = true;
      break;
    case 'box_Cblk':
	  cell[0]  = '<?php echo str_replace("'", "\'", html_pull_down_menu('box_fld_boxTBD[]',  $cFields)); ?>';
	  cell[1]  = '<?php echo str_replace("'", "\'", html_pull_down_menu('box_proc_boxTBD[]', $tProcessing)); ?>';
	  cell[2]  = '<?php echo str_replace("'", "\'", html_pull_down_menu('box_fmt_boxTBD[]',  $pFields)); ?>';
	  temp     = '<?php echo str_replace("'", "\'", html_icon('actions/view-fullscreen.png',   TEXT_MOVE,   'small', 'style="cursor:move"', '', '', 'move_cblk_row_TBD')); ?>';
	  temp    += '<?php echo str_replace("'", "\'", html_icon('emblems/emblem-unreadable.png', TEXT_DELETE, 'small', 'onclick="if (confirm(\''.TEXT_DELETE_ENTRY.'\')) $(this).parent().parent().remove();"')); ?>';
	  cell[3]  = temp.replace(/row_TBD/g, boxID);
	  attr[3]  = 'right';
 	  for (var i=0; i<cell.length; i++) cell[i] = cell[i].replace(/boxTBD/g, boxID);
     break;
	case 'box_Tbl':
	  var temp = '<?php echo str_replace("'", "\'", html_combo_box('box_fld_boxTBD[]', CreateFieldTblDropDown($report), '', 'onclick="updateFieldList(this)"', '220px', '', 'box_combo_tbd')); ?>';
	  cell[0]  = temp.replace(/box_combo_tbd/g, "box_combo_"+boxID+rowCnt);
	  cell[1]  = '<?php echo html_input_field ('box_desc_boxTBD[]', '', 'size="15"'); ?>';
	  cell[2]  = '<?php echo str_replace("'", "\'", html_pull_down_menu('box_proc_boxTBD[]', $pFields)); ?>';
	  cell[3]  = '<?php echo str_replace("'", "\'", html_pull_down_menu('box_fnt_boxTBD[]',  $kFonts)); ?>';
	  cell[4]  = '<?php echo str_replace("'", "\'", html_pull_down_menu('box_size_boxTBD[]', $kFontSizes)); ?>';
	  cell[5]  = '<?php echo str_replace("'", "\'", html_pull_down_menu('box_aln_boxTBD[]',  $kFontAlign)); ?>';
	  cell[6]  = '<?php echo str_replace("'", "\'", html_pull_down_menu('box_clr_boxTBD[]',  $kFontColors)); ?>';
	  cell[7]  = '<?php echo html_input_field ('box_wid_boxTBD[]',  '', 'size="4" maxlength="4"'); ?>';
	  temp     = '<?php echo str_replace("'", "\'", html_icon('actions/view-fullscreen.png',   TEXT_MOVE,   'small', 'style="cursor:move"', '', '', 'move_tbl_row_TBD')); ?>';
	  temp    += '<?php echo str_replace("'", "\'", html_icon('emblems/emblem-unreadable.png', TEXT_DELETE, 'small', 'onclick="if (confirm(\''.TEXT_DELETE_ENTRY.'\')) $(this).parent().parent().remove();"')); ?>';
	  cell[8]  = temp.replace(/row_TBD/g, boxID);
	  attr[4]  = 'center';
	  attr[8]  = 'right';
	  wrap[0]  = 'nowrap';
	  wrap[8]  = 'nowrap';
 	  for (var i=0; i<cell.length; i++) cell[i] = cell[i].replace(/boxTBD/g, boxID);
      break;
	case 'box_LtrData':
	  var temp = '<?php echo str_replace("'", "\'", html_combo_box('box_fld_boxTBD[]', CreateFieldTblDropDown($report), '', 'onclick="updateFieldList(this)"', '220px', '', 'box_combo_tbd')); ?>';
	  cell[0]  = temp.replace(/box_combo_tbd/g, "box_combo_"+boxID+rowCnt);
	  cell[1]  = '<?php echo html_input_field ('box_desc_boxTBD[]', '', 'size="15"'); ?>';
	  cell[2]  = '<?php echo str_replace("'", "\'", html_pull_down_menu('box_proc_boxTBD[]', $pFields)); ?>';
	  temp     = '<?php echo str_replace("'", "\'", html_icon('actions/view-fullscreen.png',   TEXT_MOVE,   'small', 'style="cursor:move"', '', '', 'move_tbl_row_TBD')); ?>';
	  temp    += '<?php echo str_replace("'", "\'", html_icon('emblems/emblem-unreadable.png', TEXT_DELETE, 'small', 'onclick="if (confirm(\''.TEXT_DELETE_ENTRY.'\')) $(this).parent().parent().remove();"')); ?>';
	  cell[3]  = temp.replace(/row_TBD/g, boxID);
	  attr[3]  = 'right';
	  wrap[0]  = 'nowrap';
	  wrap[3]  = 'nowrap';
	  for (var i=0; i<cell.length; i++) cell[i] = cell[i].replace(/boxTBD/g, boxID);
	  break;
    case 'box_Tblk':
	  var temp = '<?php echo str_replace("'", "\'", html_combo_box('box_fld_boxTBD[]',       $kFields, '', 'onclick="updateFieldList(this)"', '220px', '', 'fld_combo_tbd')); ?>';
	  cell[0]  = temp.replace(/box_combo_tbd/g, "box_combo_"+boxID+rowCnt);
	  cell[1]  = '<?php echo str_replace("'", "\'", html_pull_down_menu('box_proc_boxTBD[]', $tProcessing)); ?>';
	  cell[2]  = '<?php echo str_replace("'", "\'", html_pull_down_menu('box_fmt_boxTBD[]',  $pFields)); ?>';
	  temp     = '<?php echo str_replace("'", "\'", html_icon('actions/view-fullscreen.png',   TEXT_MOVE,   'small', 'style="cursor:move"', '', '', 'move_tblk_row_TBD')); ?>';
	  temp    += '<?php echo str_replace("'", "\'", html_icon('emblems/emblem-unreadable.png', TEXT_DELETE, 'small', 'onclick="if (confirm(\''.TEXT_DELETE_ENTRY.'\')) $(this).parent().parent().remove();"')); ?>';
	  cell[3]  = temp.replace(/row_TBD/g, boxID);
	  attr[3]  = 'right';
 	  for (var i=0; i<cell.length; i++) cell[i] = cell[i].replace(/boxTBD/g, boxID);
      break;
    case 'box_Ttl':
	  var temp = '<?php echo str_replace("'", "\'", html_combo_box('box_fld_boxTBD[]',       $kFields, '', 'onclick="updateFieldList(this)"', '220px', '', 'box_combo_tbd')); ?>';
	  cell[0]  = temp.replace(/fld_combo_tbd/g, "fld_combo_"+boxID+rowCnt);
	  cell[1]  = '<?php echo str_replace("'", "\'", html_pull_down_menu('box_proc_boxTBD[]', $pFields)); ?>';
	  temp     = '<?php echo str_replace("'", "\'", html_icon('actions/view-fullscreen.png',   TEXT_MOVE,   'small', 'style="cursor:move"', '', '', 'move_ttl_row_TBD')); ?>';
	  temp    += '<?php echo str_replace("'", "\'", html_icon('emblems/emblem-unreadable.png', TEXT_DELETE, 'small', 'onclick="if (confirm(\''.TEXT_DELETE_ENTRY.'\')) $(this).parent().parent().remove();"')); ?>';
	  cell[2]  = temp.replace(/row_TBD/g, boxID);
	  attr[2]  = 'right';
 	  for (var i=0; i<cell.length; i++) cell[i] = cell[i].replace(/boxTBD/g, boxID);
      break;
  }
  if (!skipBuild) for (var i=0; i<cell.length; i++) {
    newCell = newRow.insertCell(-1);
	newCell.innerHTML = cell[i];
	if (attr[i]) newCell.align  = attr[i]; 
	if (wrap[i]) newCell.nowrap = wrap[i];
  }
  if (updateList) {
    updateFieldList(document.getElementById(updateList));
  }
  if (tableInit) {
    if (!isNaN(boxID)) idTable = idTable + boxID;
    var table = document.getElementById(idTable);
    var tableDnD = new TableDnD();
    tableDnD.init(table);
  }
}

function boxProperties(id) {
  if (document.getElementById('fld_box_'+id).style.display) {
    document.getElementById('fld_box_'+id).style.display = '';
  } else {
    document.getElementById('fld_box_'+id).style.display = 'none';
  }
}

/********************************** Begin Color Wheel ***********************************/
addary    = new Array();
addary[0] = new Array(0,1,0);
addary[1] = new Array(-1,0,0);
addary[2] = new Array(0,0,1);
addary[3] = new Array(0,-1,0);
addary[4] = new Array(1,0,0);
addary[5] = new Array(0,0,-1);
addary[6] = new Array(255,1,1);
clrary    = new Array(360);
for (whl_i = 0; whl_i < 6; whl_i++) {
  for (whl_j = 0; whl_j < 60; whl_j++) {
    clrary[60 * whl_i + whl_j] = new Array(3);
    for (whl_k = 0; whl_k < 3; whl_k++) {
      clrary[60 * whl_i + whl_j][whl_k] = addary[6][whl_k];
      addary[6][whl_k] += (addary[whl_i][whl_k] * 4);
    }
  }
}

function moved(e, idWheel, idTD, idField) {
  if (pbBrowser == 'IE') {
    y = 4 * event.offsetX;
    x = 4 * event.offsetY;
  } else {
    var posX = 0;
    var posY = 0;
    layobj = document.getElementById(idWheel);
    while (layobj != null){
      posX  += layobj.offsetLeft;
      posY  += layobj.offsetTop;
      layobj = layobj.offsetParent;
    }
    if (e.pageX || e.pageY) {
      var mX = e.pageX;
	  var mY = e.pageY;
    }
    y = 4 * (mX - posX);
    x = 4 * (mY - posY);
  }
  sx = x - 512;
  sy = y - 512;
  qx = (sx < 0) ? 0 : 1;
  qy = (sy < 0) ? 0 : 1;
  q = 2 * qy + qx;
  quad = new Array(-180,360,180,0);
  xa = Math.abs(sx);
  ya = Math.abs(sy);
  d = ya * 45 / xa;
  if (ya > xa) d = 90 - (xa * 45 / ya);
  deg = Math.floor(Math.abs(quad[q] - d));
  n = 0;
  sx = Math.abs(x - 512);
  sy = Math.abs(y - 512);
  r = Math.sqrt((sx * sx) + (sy * sy));
  if (x == 512 & y == 512) {
    c = "000000";
  } else {
    for (i = 0; i < 3; i++) {
      r2 = clrary[deg][i] * r / 256;
      if (r > 256) r2 += Math.floor(r - 256);
      if (r2 > 255) r2 = 255;
      n = 256 * n + Math.floor(r2);
    }
    c = n.toString(16);
    while (c.length < 6) c = "0" + c;
  }
  document.getElementById(idTD).style.backgroundColor = "#" + c;
  var color = h2d(c.substr(0, 2)) + ':' + h2d(c.substr(2, 2)) + ':' + h2d(c.substr(4, 2)); 
  document.getElementById(idField).value = color;
  return false;
}

function colorSet(idSel, idTD, idField) {
  var color = document.getElementById(idSel).value;
  if (color != 'custom') {
    document.getElementById(idField).value = color;
    var parts = color.split(":");
	var hexColor = d2h(parts[0]) + d2h(parts[1]) + d2h(parts[2]);
    document.getElementById(idTD).style.backgroundColor = "#" + hexColor;
  }
}

function showCustom(idWheel, idSel) {
  if (document.getElementById(idWheel).style.display == 'none') {
    oEdit = document.getElementById(idSel);
    oSel  = document.getElementById(idSel);
    oMenu = document.getElementById(idWheel);
    nTop  = oEdit.offsetTop + oEdit.offsetHeight;
    nLeft = oEdit.offsetLeft;
    while (oEdit.offsetParent != document.body) {
	  oEdit  = oEdit.offsetParent;
	  nTop  += oEdit.offsetTop;
	  nLeft += oEdit.offsetLeft;
    }
    oMenu.style.display = "";
    oMenu.style.top     = nTop + 'px';
    oMenu.style.left    = (nLeft - oSel.offsetWidth) + 'px';
  } else {
    document.getElementById(idWheel).style.display = 'none';
  }
}

function setCustom(idWheel, idSel) {
  document.getElementById(idSel).value           = 'custom';
  document.getElementById(idWheel).style.display = 'none';
}

/**************************************************************************/
/** Keep hold of the current table being dragged */
var currenttable  = null;
var selDraggable = false;

/** Capture the onmousemove so that we can see if a row from the current table if any is being dragged. */
document.onmousemove = function(ev){
    if (currenttable && currenttable.dragObject) {
        ev   = ev || window.event;
        var mousePos = currenttable.mouseCoords(ev);
        var y = mousePos.y - currenttable.mouseOffset.y;
        if (y != currenttable.oldY) {
            // work out if we're going up or down...
            var movingDown = y > currenttable.oldY;
            // update the old value
            currenttable.oldY = y;
            // update the style to show we're dragging
            currenttable.dragObject.style.backgroundColor = "#eee";
            // If we're over a row then move the dragged row to there so that the user sees the
            // effect dynamically
            var currentRow = currenttable.findDropTargetRow(y);
            if (currentRow) {
                if (movingDown && currenttable.dragObject != currentRow) {
                    currenttable.dragObject.parentNode.insertBefore(currenttable.dragObject, currentRow.nextSibling);
                } else if (! movingDown && currenttable.dragObject != currentRow) {
                    currenttable.dragObject.parentNode.insertBefore(currenttable.dragObject, currentRow);
                }
            }
        }

        return false;
    }
}

// Similarly for the mouseup
document.onmouseup   = function(ev){
    if (currenttable && currenttable.dragObject) {
        var droppedRow = currenttable.dragObject;
        // If we have a dragObject, then we need to release it,
        // The row will already have been moved to the right place so we just reset stuff
        droppedRow.style.backgroundColor = 'transparent';
        currenttable.dragObject   = null;
        // And then call the onDrop method in case anyone wants to do any post processing
        currenttable.onDrop(currenttable.table, droppedRow);
        currenttable = null; // let go of the table too
    }
}


/** get the source element from an event in a way that works for IE and Firefox and Safari
 * @param evt the source event for Firefox (but not IE - IE uses window.event) */
function getEventSource(evt) {
    if (window.event) {
        evt = window.event; // For IE
        return evt.srcElement;
    } else {
        return evt.target; // For Firefox
    }
}

/**
 * Encapsulate table Drag and Drop in a class. We'll have this as a Singleton
 * so we don't get scoping problems.
 */
function TableDnD() {
    /** Keep hold of the current drag object if any */
    this.dragObject = null;
    /** The current mouse offset */
    this.mouseOffset = null;
    /** The current table */
    this.table = null;
    /** Remember the old value of Y so that we don't do too much processing */
    this.oldY = 0;

    /** Initialise the drag and drop by capturing mouse move events */
    this.init = function(table) {
        this.table = table;
        var rows = table.getElementsByTagName("tr"); 
//alert('init table = '+table.id+' and number of rows = '+rows.length);
        for (var i=0; i<rows.length; i++) {
			// John Tarr: added to ignore rows that I've added the NoDnD attribute to (Category and Header rows)
			var nodrag = rows[i].getAttribute("NoDrag")
			if (nodrag == null || nodrag == "undefined") { //There is no NoDnD attribute on rows I want to drag
				this.makeDraggable(rows[i]);
			}
        }
    }

    /** This function is called when you drop a row, so redefine it in your code
        to do whatever you want, for example use Ajax to update the server */
    this.onDrop = function(table, droppedRow) {
//        rClick = droppedRow.rowIndex;
		selDraggable = false; // reset to make draggable
    }

	/** Get the position of an element by going up the DOM tree and adding up all the offsets */
    this.getPosition = function(e){
        var left = 0;
        var top  = 0;
		if (e.offsetHeight == 0) {
			/** Safari 2 doesn't correctly grab the offsetTop of a table row
			    this is detailed here:
			    http://jacob.peargrove.com/blog/2006/technical/table-row-offsettop-bug-in-safari/
			    the solution is likewise noted there, grab the offset of a table cell in the row - the firstChild.
			    note that firefox will return a text node as a first child, so designing a more thorough
			    solution may need to take that into account, for now this seems to work in firefox, safari, ie */
			e = e.firstChild; // a table cell
		}

        while (e.offsetParent){
            left += e.offsetLeft;
            top  += e.offsetTop;
            e     = e.offsetParent;
        }

        left += e.offsetLeft;
        top  += e.offsetTop;

        return {x:left, y:top};
    }

	/** Get the mouse coordinates from the event (allowing for browser differences) */
    this.mouseCoords = function(ev){
        if(ev.pageX || ev.pageY){
            return {x:ev.pageX, y:ev.pageY};
        }
        return {
            x:ev.clientX + document.body.scrollLeft - document.body.clientLeft,
            y:ev.clientY + document.body.scrollTop  - document.body.clientTop
        };
    }

	/** Given a target element and a mouse event, get the mouse offset from that element.
		To do this we need the element's position and the mouse position */
    this.getMouseOffset = function(target, ev){
        ev = ev || window.event;

        var docPos    = this.getPosition(target);
        var mousePos  = this.mouseCoords(ev);
        return {x:mousePos.x - docPos.x, y:mousePos.y - docPos.y};
    }

	/** Take an item and add an onmousedown method so that we can make it draggable */
    this.makeDraggable = function(item) {
        if (!item) return;
        var self = this; // Keep the context of the TableDnd inside the function
        item.onmousedown = function(ev) {
            // Need to check to see if we are an input or not, if we are an input, then
            // return true to allow normal processing
			if (selDraggable) return true; // handles nested tables
            var target = getEventSource(ev);
			if (target.tagName != 'IMG') return true; // make sure they are on an image to move
//alert('clicked id = '+target.tagName+' and index = '+this.rowIndex);
//			rClick = this.rowIndex;
			selDraggable = true;
			var iconID = target.id;
			if (iconID.substr(0, 5) != 'move_') return true;
//          if (target.tagName == 'INPUT' || target.tagName == 'SELECT') return true;
            currenttable = self;
            self.dragObject  = this;
            self.mouseOffset = self.getMouseOffset(this, ev);
            return false;
        }
        item.onmouseup = function(ev) {
			selDraggable = false;
		}
//		item.style.cursor = "move";
    }

    /** We're only worried about the y position really, because we can only move rows up and down */
    this.findDropTargetRow = function(y) {
        var rows = this.table.getElementsByTagName("tr");
		for (var i=0; i<rows.length; i++) {
			var row = rows[i];
			// John Tarr added to ignore rows that I've added the NoDnD attribute to (Header rows)
			var nodrop = row.getAttribute("NoDrop");
			if (nodrop == null || nodrop == "undefined") {  //There is no NoDnD attribute on rows I want to drag
				var rowY    = this.getPosition(row).y;
				var rowHeight = parseInt(row.offsetHeight)/2;
				if (row.offsetHeight == 0) {
					rowY = this.getPosition(row.firstChild).y;
					rowHeight = parseInt(row.firstChild.offsetHeight)/2;
				}
				// Because we always have to insert before, we need to offset the height a bit
				if ((y > rowY - rowHeight) && (y < (rowY + rowHeight))) {
					// that's the row we're over
					return row;
				}
			}
		}
		return null;
	}
}

// -->
</script>
