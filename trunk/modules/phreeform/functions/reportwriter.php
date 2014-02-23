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
//  Path: /modules/phreeform/functions/reportwriter.php
//

// This set of functions converts old style reportwriter reports to Phreeform format

// This function preps a stored report for conversion by making it look like a already stored report
function PrepReport($ReportID) {
	global $db, $messageStack;
	$crlf = chr(10);
	$CSVOutput = array();
	$CSVOutput[] = '/* Report Builder Export Tool */' . $crlf;
	$CSVOutput[] = 'version:1.0' . $crlf;
	// Fetch the core report data from table reports
	$sql = "select * from " . TABLE_REPORTS . " where id = " . $ReportID;
	$result = $db->Execute($sql);
	$myrow = $result->fields;
	// Fetch the language dependent db entries
	$ReportName = $myrow['description'];
	// Enter some export file info for language translation
	$CSVOutput[] = '/* Report Name: ' . $ReportName . ' */' . $crlf;
	$CSVOutput[] = '/* Export File Generated: : ' . date('Y-m-d h:m:s', time()) . ' */' . $crlf;
	$CSVOutput[] = '/* Language Fields. */' . $crlf;
	$CSVOutput[] = '/* Only modify the language portion between the single quotes after the colon. */' . $crlf;
	$CSVOutput[] = '/* Report Name */' . $crlf;
	$CSVOutput[] = "ReportName:'" . addslashes($ReportName) . "'" . $crlf;
	$sql = "select params from " . TABLE_REPORT_FIELDS . " where reportid = " . $ReportID . " and entrytype = 'pagelist'";
	$result = $db->Execute($sql);
	$params = unserialize($result->fields['params']);
	$CSVOutput[] = "ReportNarr:'" . addslashes($params['narrative']) . "'" . $crlf;
	$CSVOutput[] = "EmailMsg:'"   . addslashes($params['email_msg']) . "'" . $crlf;
	
	// Now add the report fields
	$CSVOutput[] = '/* Report Field Description Information */' . $crlf;
	$sql = "select * from " . TABLE_REPORT_FIELDS . " where reportid = " . $ReportID . " order by entrytype, seqnum";
	$result = $db->Execute($sql);
	$i = 0;
	$skip_array = array('dateselect', 'trunclong', 'pagelist', 'security');
	while (!$result->EOF) {
		if (!in_array($result->fields['entrytype'], $skip_array)) {
			$CSVOutput[] .= "FieldDesc" . $i . ":'" . addslashes($result->fields['displaydesc']) . "'" . $crlf;
		}
		$sql = 'FieldData' . $i . ':';
		foreach ($result->fields as $key => $value) {
			if ($key<>'id' && $key<>'reportid') $sql .= $key . "='" . addslashes($value) . "', ";
		}
		$sql = substr($sql,0,-2) . ";"; // Strip the last comma and space and add a semicolon
		$FieldData[$i] = $sql;
		$i++;
		$result->MoveNext();
	}
	$CSVOutput[] = '/* End of language fields. */' . $crlf;
	$CSVOutput[] = '/* DO NOT EDIT BELOW THIS LINE! */' . $crlf;
	$CSVOutput[] = '/* SQL report data. */' . $crlf;
	// Build the report sql string
	$RptData = 'ReportData:';
	foreach ($myrow as $key => $value) if ($key <> 'id') $RptData .= $key . "='" . addslashes($value) . "', ";
	$RptData = substr($RptData, 0, -2) . ";"; // Strip the last comma and space and add a semicolon
	$CSVOutput[] = $RptData . $crlf . $crlf;
	$CSVOutput[] = '/* SQL field data. */' . $crlf;
	for ($i = 0; $i < count($FieldData); $i++) $CSVOutput[] = $FieldData[$i] . $crlf;
	$CSVOutput[] = '/* End of Export File */' . $crlf;
	return $CSVOutput;
}

// This function imports old stored reports from reportwriter into phreeform xml format
function import_text_params($arrSQL) {
	$params = new \core\classes\objectInfo();
	$ValidReportSQL = false;
	if (is_array($arrSQL)) foreach ($arrSQL as $sql) { // find the main reports sql statement
	  if (strpos($sql, 'ReportData:') === 0) {
		$values = substr(trim($sql), 11);
		$values = substr($values, 0, strlen($values) - 1);
		$data_array = explode(',', $values);
		$sql_array = array();
		foreach ($data_array as $value) {
			$parts = explode('=', $value, 2);
			$sql_array[trim($parts[0])] = substr(trim($parts[1]), 1, -1);
		}
		$params->title           = $sql_array['description'];
		$params->reporttype      = $sql_array['reporttype'];
		$params->groupname       = $sql_array['groupname'];
		$params->standard_report = $sql_array['standard_report'];
		$params->special_class   = substr($sql_array['special_report'], 2);

		// move some report type to new locations, misc for unknow categories
		if ($params->groupname == 'ap:chk')  $params->groupname = 'bnk:chk';
		if ($params->groupname == 'ap:pur')  $params->groupname = 'vend:stmt';
		if ($params->groupname == 'ar:rcpt') $params->groupname = 'bnk:rcpt';
		if (substr($params->groupname, 0, 2) == 'ap') $params->groupname = str_replace('ap', 'vend', $params->groupname);
		if (substr($params->groupname, 0, 2) == 'ar') $params->groupname = str_replace('ar', 'cust', $params->groupname);
		// pull the table data
		$params->tables = array();
		for ($i = 1; $i < 6; $i++) {
		  if ($sql_array['table' . $i]) {
		    $params->tables[] = new \core\classes\objectInfo(array(
			  'tablename'    => $sql_array['table' . $i],
			  'relationship' => pb_replace_tables($sql_array, $sql_array['table' . $i . 'criteria']),
			));
		  }
		}
		$ValidReportSQL = true;
	  }
	}
	if (!$ValidReportSQL) return false; // no valid report sql statement found in the text file, error
	foreach ($arrSQL as $sql) { // fetch the translations for the field descriptions
	  if (strpos($sql,'FieldDesc') === 0) { // then it's a field description, find the index and save
		$sql = trim($sql);
		$FldIndex = substr($sql, 9, strpos($sql, ':') - 9);
		$Language[$FldIndex] = substr($sql, strpos($sql, ':') + 2, -1);
	  }
	}
	while($sql = array_shift($arrSQL)) {
	  if (strpos($sql, 'FieldData') === 0) { // a valid field, write it
	    while(true) { // handles multiline fields
		  if (substr(trim($sql), -2) == "';") break;
		  $sql .= "\n" . array_shift($arrSQL);
		  if (sizeof($arrSQL) < 1) break;
		}
		$sql = trim($sql);
		$entrytype   = substr($sql, strpos($sql, '=') + 2, strpos($sql, "',") - strpos($sql, '=') - 2);
		$sql = trim(substr($sql, strpos($sql, "',") + 2));
		$seqnum      = substr($sql, strpos($sql, '=') + 2, strpos($sql, "',") - strpos($sql, '=') - 2);
		$sql = trim(substr($sql, strpos($sql, "',") + 2));
		$fieldname   = substr($sql, strpos($sql, '=') + 2, strpos($sql, "',") - strpos($sql, '=') - 2);
		$sql = trim(substr($sql, strpos($sql, "',") + 2));
		$description = substr($sql, strpos($sql, '=') + 2, strpos($sql, "',") - strpos($sql, '=') - 2);
		$sql = trim(substr($sql, strpos($sql, "',") + 2));
		$visible     = substr($sql, strpos($sql, '=') + 2, strpos($sql, "',") - strpos($sql, '=') - 2);
		$sql = trim(substr($sql, strpos($sql, "',") + 2));
		$columnbreak = substr($sql, strpos($sql, '=') + 2, strpos($sql, "',") - strpos($sql, '=') - 2);
		$sql = trim(substr($sql, strpos($sql, "',") + 2));
		if ($entrytype == 'security') {
		  $params_arr = stripslashes(substr($sql, 8, strlen($sql) - 10));
		} else {
		  $sql = str_replace("\r", "", $sql);
		  $sql = str_replace("\n", "\r\n", $sql);
		  $params_arr  = unserialize(stripslashes(substr($sql, 8, strlen($sql) - 10)));
		}
		$temp = array(
		  'fieldname'   => pb_replace_tables($sql_array, $fieldname),
		  'description' => $description,
		  'visible'     => $visible,
		  'columnbreak' => $columnbreak,
		);
		if (is_array($params_arr)) {
		  foreach ($params_arr as $key => $value) $temp[$key] = pb_replace_tables($sql_array, $value);
		} elseif ($params_arr) {
		  $temp['params'] = $params_arr;
		}
		// clean out some un-needed data
		switch ($entrytype) {
		  case 'pagelist':
		    $params->description          = $params_arr['narrative'];
			$params->emailmessage         = $params_arr['email_msg'];
		    $params->datefield            = $params_arr['datefield'];
		    $params->datelist             = $params_arr['dateselect'];
		    $params->datedefault          = $params_arr['datedefault'];
			$params->filenameprefix       = $params_arr['filenameprefix'];
			$params->filenamefield        = $params_arr['filenamesource'];
			if ($params->reporttype == 'rpt') {
		      $params->truncate           = $params_arr['trunclong'];
		      $params->totalonly          = $params_arr['totalonly'];
		    }
			if ($params->reporttype == 'frm') {
			  $params->serialform         = $params_arr['serialform'];
			  $params->setprintedfield    = $params_arr['setprintedflag'];
			  $params->formbreakfield     = $params_arr['formbreakfield'];
			}
			$params->security             = 'u:0;g:0';
			$params->page->size           = $params_arr['papersize'];
			$params->page->orientation    = $params_arr['paperorientation'];
			$params->page->margin->top    = $params_arr['margintop'];
			$params->page->margin->bottom = $params_arr['marginbottom'];
			$params->page->margin->left   = $params_arr['marginleft'];
			$params->page->margin->right  = $params_arr['marginright'];
			if ($params->reporttype == 'rpt') {
			  $params->page->heading->show  = $params_arr['coynameshow'];
			  $params->page->heading->font  = $params_arr['coynamefont'];
			  $params->page->heading->size  = $params_arr['coynamefontsize'];
			  $params->page->heading->color = $params_arr['coynamefontcolor'];
			  $params->page->heading->align = $params_arr['coynamealign'];
			  $params->page->title1->show   = $params_arr['title1show'];
			  $params->page->title1->text   = $params_arr['title1desc'];
			  $params->page->title1->font   = $params_arr['title1font'];
			  $params->page->title1->size   = $params_arr['title1fontsize'];
			  $params->page->title1->color  = $params_arr['title1fontcolor'];
			  $params->page->title1->align  = $params_arr['title1fontalign'];
			  $params->page->title2->show   = $params_arr['title2show'];
			  $params->page->title2->text   = $params_arr['title2desc'];
			  $params->page->title2->font   = $params_arr['title2font'];
			  $params->page->title2->size   = $params_arr['title2fontsize'];
			  $params->page->title2->color  = $params_arr['title2fontcolor'];
			  $params->page->title2->align  = $params_arr['title2fontalign'];
			  $params->page->filter->font   = $params_arr['filterfont'];
			  $params->page->filter->size   = $params_arr['filterfontsize'];
			  $params->page->filter->color  = $params_arr['filterfontcolor'];
			  $params->page->filter->align  = $params_arr['filterfontalign'];
			  $params->page->data->font     = $params_arr['datafont'];
			  $params->page->data->size     = $params_arr['datafontsize'];
			  $params->page->data->color    = $params_arr['datafontcolor'];
			  $params->page->data->align    = $params_arr['datafontalign'];
			  $params->page->totals->font   = $params_arr['totalsfont'];
			  $params->page->totals->size   = $params_arr['totalsfontsize'];
			  $params->page->totals->color  = $params_arr['totalsfontcolor'];
			  $params->page->totals->align  = $params_arr['totalsfontalign'];
			}
			$temp = NULL;
			break;
		  case 'critlist':
			$temp['type'] = $temp['value'];
			unset($temp['value']);
			unset($temp['columnbreak']);
		    unset($temp['index']);
			$entrytype = 'filterlist';
			break;
		  case 'fieldlist':
			if ($params->reporttype == 'rpt') {
			  $temp['total']     = $temp['index']; // set total flag
		      unset($temp['index']);
			} elseif ($params->reporttype == 'frm') {
			  $temp = convertTemp($temp, $sql_array);
			}
			break;
		  case 'grouplist':
			unset($temp['columnbreak']);
			unset($temp['visible']);
		    unset($temp['index']);
			break;
		  case 'sortlist':
			unset($temp['columnbreak']);
			unset($temp['visible']);
		    unset($temp['index']);
			break;
		  case 'security':
			$params->security  = convert_security($params_arr);
			$temp = NULL;
			break;
		  default:
		}
		if ($temp) {
		  $objTemp = new \core\classes\objectInfo($temp);
		  if (!is_array($params->$entrytype)) $params->$entrytype = array(); 
		  array_push($params->$entrytype, $objTemp);
		}
	  }
	}
	// some cleanup and table mapping
	$params->datefield      = pb_replace_tables($sql_array, $params->datefield);
	$params->filenamefield  = pb_replace_tables($sql_array, $params->filenamefield);
	$params->formbreakfield = pb_replace_tables($sql_array, $params->formbreakfield);
//echo 'params = '; print_r($params); echo '<br>'; exit();
	return $params;
}

function pb_replace_tables($sql_array, $criteria) {
  if ($criteria == '') return NULL;
  if ($sql_array['table1']) $criteria = str_replace('[table1]', $sql_array['table1'], $criteria);
  if ($sql_array['table2']) $criteria = str_replace('[table2]', $sql_array['table2'], $criteria);
  if ($sql_array['table3']) $criteria = str_replace('[table3]', $sql_array['table3'], $criteria);
  if ($sql_array['table4']) $criteria = str_replace('[table4]', $sql_array['table4'], $criteria);
  if ($sql_array['table5']) $criteria = str_replace('[table5]', $sql_array['table5'], $criteria);
  if ($sql_array['table6']) $criteria = str_replace('[table6]', $sql_array['table6'], $criteria);
  return $criteria;
}

function convert_security($params) {
  global $db;
  $output = array();
  $types = explode(';', $params);
  foreach ($types as $value) {
    $member = explode(':', $value);
	if ($member[1] == '') $member[1] = '-1'; // make it no access
	switch (array_shift($member)) {
	  case 'u':
	    $output[] = 'u:' . implode(':', $member);
	    break;
	  case 'd': // change to groups and find the id number from the departments table
		$temp = array('g');
		foreach ($member as $name) {
		  $result = $db->Execute("select id from " . TABLE_DEPARTMENTS . " where description_short = '" . $name . "'");
		  if ($result->RecordCount() == 0) {
		    $temp[] = $name;
		  } else {
		    $temp[] = $result->fields['id'];
		  }
		}
	    $output[] = implode(':', $temp);
	    break;
	  case 'e': // employees not used, skip
	    break;
	}  
  }
  $output = implode(';', $output);
//echo 'converting security started = ' . $params . ' and ended = ' . $output . '<br>';
  return $output;
}

function convertTemp($temp, $sql_array) {
  $temp['type']     = $temp['index']; // set the type
  $temp['abscissa'] = $temp['LineXStrt'];
  $temp['ordinate'] = $temp['LineYStrt'];
  $temp['width']    = $temp['BoxWidth'];
  $temp['height']   = $temp['BoxHeight'];
  unset($temp['index']);
  unset($temp['LineXStrt']);
  unset($temp['LineYStrt']);
  unset($temp['BoxWidth']);
  unset($temp['BoxHeight']);
  unset($temp['visible']);
  unset($temp['columnbreak']);
  unset($temp['ID']);
  unset($temp['SeqNum']);
  unset($temp['ReportID']);
  unset($temp['ReportName']);
  unset($temp['todo']);
  unset($temp['action']);
  switch ($temp['type']) {
	case 'Img':
      unset($temp['ImgFileName']);
      unset($temp['ImgChoice']);
      unset($temp['fieldname']);
      unset($temp['DisplayName']);
      break;
	case 'TDup':
      unset($temp['DisplayName']);
      unset($temp['fieldname']);
      break;
	case 'Ttl':
      unset($temp['TotalField']);
      unset($temp['FieldIndex']);
	  foreach ($temp['Seq'] as $key => $value) {
		$temp['Seq'][$key] = array(
		  'TblField'   => $value,
		  'Processing' => $temp['Processing'],
		);
	  }
	case 'CBlk':
	case 'CDta':
	case 'Tbl':
	case 'PgNum':
	case 'Text':
	case 'Rect':
	case 'Line':
      unset($temp['Processing']);
	case 'TBlk':
	case 'Data':
	case 'BarCode':
	  if (isset($temp['DataField'])) {
	    $temp['Seq'][] = array('TblField' => $temp['DataField'], 'Processing' => $temp['Processing']);
	  }
	  if (isset($temp['BarCodeType'])) $temp['barcodetype'] = $temp['BarCodeType'];
	  if (isset($temp['TextField']))   $temp['text']        = $temp['TextField'];
      if (isset($temp['Seq']))         $temp['boxfield']    = convertSeq($temp['Seq'], $temp['type'], $sql_array);
	  if (isset($temp['LastOnly']))    $temp['display']     = $temp['LastOnly'];
	  if (isset($temp['LineType'])) {
        switch ($temp['LineType']) {
		  case '1': // horizontal
		    $temp['linetype'] = 'H';
			$temp['length']   = $temp['HLength'];
			break;
		  case '2': // vertical
		    $temp['linetype'] = 'V';
			$temp['length']   = $temp['VLength'];
			break;
		  case '3': // custom
		    $temp['linetype']    = 'C';
            $temp['endabscissa'] = $temp['LineXEnd'];
            $temp['endordinate'] = $temp['LineYEnd'];
			break;
		}
        unset($temp['LineType']);
        unset($temp['HLength']);
        unset($temp['VLength']);
        unset($temp['LineXEnd']);
        unset($temp['LineYEnd']);
	  }
	  if (isset($temp['hFont'])) {
        $temp['headingfont']   = $temp['hFont'];
        $temp['headingsize']   = $temp['hFontSize'];
        $temp['headingalign']  = $temp['hFontAlign'];
        $temp['headingcolor']  = $temp['hFontColor'];
        $temp['hdbordersize']  = $temp['hLineSize'];
        switch ($temp['hLine']) {
		  case '1':
		    $temp['hdborder']      = '1';
            $temp['hdbordercolor'] = $temp['hBrdrColor'];
		    break;
		  case '2':
		    $temp['hdborder']      = '1';
            $temp['hdbordercolor'] = $temp['hBrdrRed'] . ':' . $temp['hBrdrGreen'] . ':' . $temp['hBrdrBlue'];
		    break;
		  default:
	    }
        switch ($temp['hFill']) {
		  case '1':
		    $temp['hdfill']      = '1';
            $temp['hdfillcolor'] = $temp['hFillColor'];
		    break;
		  case '2':
		    $temp['hdfill']      = '1';
            $temp['hdfillcolor'] = $temp['hFillRed'] . ':' . $temp['hFillGreen'] . ':' . $temp['hFillBlue'];
		    break;
		  default:
	    }
        unset($temp['hFont']);
        unset($temp['hFontSize']);
        unset($temp['hFontAlign']);
        unset($temp['hFontColor']);
        unset($temp['hLine']);
        unset($temp['hLineSize']);
        unset($temp['hBrdrColor']);
        unset($temp['hFill']);
        unset($temp['hFillColor']);
        unset($temp['SpecialFunc']);
        unset($temp['SpecialID']);
        unset($temp['hBrdrRed']);
        unset($temp['hBrdrGreen']);
        unset($temp['hBrdrBlue']);
        unset($temp['hFillRed']);
        unset($temp['hFillGreen']);
        unset($temp['hFillBlue']);
	  }
	  if (isset($temp['Font'])) {
        $temp['font']     = $temp['Font'];
        $temp['size']     = $temp['FontSize'];
        $temp['align']    = $temp['FontAlign'];
        $temp['color']    = $temp['FontColor'];
        $temp['truncate'] = $temp['Trunc'];
      }
      $temp['bordersize']  = $temp['LineSize'];
      switch ($temp['Line']) {
		case '1':
		  $temp['bordershow']  = '1';
          $temp['bordercolor'] = $temp['BrdrColor'];
		  break;
		case '2':
		  $temp['bordershow']  = '1';
          $temp['bordercolor'] = $temp['BrdrRed'] . ':' . $temp['BrdrGreen'] . ':' . $temp['BrdrBlue'];
		  break;
		default:
	  }
      switch ($temp['Fill']) {
		case '1':
		  $temp['fillshow']  = '1';
          $temp['fillcolor'] = $temp['FillColor'];
		  break;
		case '2':
		  $temp['fillshow']  = '1';
          $temp['fillcolor'] = $temp['FillRed'] . ':' . $temp['FillGreen'] . ':' . $temp['FillBlue'];
		  break;
		default:
	  }
      unset($temp['fieldname']);
      unset($temp['Font']);
      unset($temp['FontSize']);
      unset($temp['FontAlign']);
      unset($temp['FontColor']);
      unset($temp['Trunc']);
      unset($temp['Color']);
      unset($temp['Line']);
      unset($temp['LineSize']);
      unset($temp['BrdrColor']);
      unset($temp['DataField']);
      unset($temp['Processing']);
      unset($temp['BarCodeType']);
      unset($temp['TextField']);
      unset($temp['LastOnly']);
      unset($temp['Fill']);
      unset($temp['FillColor']);
	  unset($temp['DisplayName']);
      unset($temp['TblSeqNum']);
      unset($temp['TblField']);
      unset($temp['FontRed']);
      unset($temp['FontGreen']);
      unset($temp['FontBlue']);
      unset($temp['BrdrRed']);
      unset($temp['BrdrGreen']);
      unset($temp['BrdrBlue']);
      unset($temp['FillRed']);
      unset($temp['FillGreen']);
      unset($temp['FillBlue']);
      unset($temp['TblDesc']);
      unset($temp['TblColWidth']);
      unset($temp['TblShow']);
      unset($temp['rowSeq']);
	  unset($temp['custom_field']);
      unset($temp['Seq']);
      break;
  }
  return $temp;
}

function convertSeq($seq, $type, $sql_array) {
  $output = array();
  foreach($seq as $value) {
    switch ($type) {
	  case 'Ttl':
	  case 'BarCode':
	  case 'CDta':
	  case 'CBlk':
	  case 'Data':
	  case 'TBlk':
		$value['fieldname']  = pb_replace_tables($sql_array, $value['TblField']);
		$value['processing'] = $value['Processing'];
		unset($value['TblField']);
		unset($value['Processing']);
	    unset($value['TblSeqNum']);
	    unset($value['TblDesc']);
	    unset($value['TblColWidth']);
	    unset($value['TblShow']);
		unset($value['Font']);
		unset($value['FontSize']);
		unset($value['FontAlign']);
		unset($value['FontColor']);
		break;
	  case 'Tbl':
		$value['fieldname'] = pb_replace_tables($sql_array, $value['TblField']);
		$value['description'] = $value['TblDesc'];
		$value['processing'] = $value['Processing'];
		$value['font'] = $value['Font'];
		$value['size'] = $value['FontSize'];
		$value['align'] = $value['FontAlign'];
		$value['color'] = $value['FontColor'];
		$value['width'] = $value['TblColWidth'];
		$value['show'] = $value['TblShow'];
		unset($value['TblField']);
		unset($value['TblDesc']);
		unset($value['Processing']);
		unset($value['Font']);
		unset($value['FontSize']);
		unset($value['FontAlign']);
		unset($value['FontColor']);
		unset($value['TblColWidth']);
		unset($value['TblShow']);
		unset($value['TblSeqNum']);
		break;
	}
    $output[] = new \core\classes\objectInfo($value);
  }
  return $output;
}

?>