<?php
/*
 * Support functions for PhreeForm
 *
 * NOTICE OF LICENSE
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.TXT.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/OSL-3.0
 *
 * DISCLAIMER
 * Do not edit or add to this file if you wish to upgrade Bizuno to newer
 * versions in the future. If you wish to customize Bizuno for your
 * needs please refer to http://www.phreesoft.com for more information.
 *
 * @name       Bizuno ERP
 * @author     Dave Premo, PhreeSoft <support@phreesoft.com>
 * @copyright  2008-2019, PhreeSoft, Inc.
 * @license    http://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 * @version    3.x Last Update: 2019-04-10
 * @filesource /controller/module/phreeform/functions.php
 */

namespace bizuno;

/**
 * !!! DEPRECATED !!! use /view/main.php function viewProcess
 *
 * Processes a string of data with a user specified process, returns unprocessed if function not found
 * @param mixed $strData - data to process
 * @param string $Process - process to apply to the data
 * @return mixed - processed string if process found, original string if not
 */
/*
function ProcessData($strData, $Process)
{
    if (getModuleCache('phreeform', 'processing', $Process, 'function')) {
        $func = getModuleCache('phreeform', 'processing')[$Process]['function'];
        $fqfn = "\\bizuno\\$func";
        $module = getModuleCache('phreeform', 'processing')[$Process]['module'];
        bizAutoLoad(getModuleCache($module, 'properties', 'path')."functions.php", $fqfn, 'function');
        return $fqfn($strData, $Process);
    }
    return $strData;
}
*/

/**
 * Adds a separator to the end of a string, if specified, from a module file, used for extensions and customization
 * @param mixed $value - value to add separator string
 * @param string $Process - process to apply to string
 * @return mixed - newly formed string
 */
function AddSep($value, $Process)
{
    if (getModuleCache('phreeform', 'separators', $Process, 'function')) {
        $func = getModuleCache('phreeform', 'separators')[$Process]['function'];
        $fqfn = "\\bizuno\\$func";
        $module = getModuleCache('phreeform', 'separators')[$Process]['module'];
        bizAutoLoad(BIZUNO_LIB."controller/module/$module/functions.php", $fqfn, 'function');
        return $fqfn($value, $Process);
    }
    return $value;
}

/**
 * used to build drop downs for filtering
 * @return type
 */
function viewDateChoices()
{
    return [
        ['id'=>'a', 'text'=>lang('all')],
        ['id'=>'b', 'text'=>lang('range')],
        ['id'=>'c', 'text'=>lang('today')],
        ['id'=>'d', 'text'=>lang('dates_this_week')],
        ['id'=>'e', 'text'=>lang('dates_wtd')],
        ['id'=>'l', 'text'=>lang('dates_this_period')],
        ['id'=>'f', 'text'=>lang('dates_month')],
        ['id'=>'g', 'text'=>lang('dates_mtd')],
        ['id'=>'h', 'text'=>lang('dates_quarter')],
        ['id'=>'i', 'text'=>lang('dates_qtd')],
        ['id'=>'j', 'text'=>lang('dates_this_year')],
        ['id'=>'k', 'text'=>lang('dates_ytd')],
        ['id'=>'m', 'text'=>lang('dates_lfy')],
        ['id'=>'n', 'text'=>lang('dates_lfytd')]];
}
/**
 * This function adds a separator to a string for formatting addresses, etc. These are the default separators
 * @param mixed $value - value to add separator to
 * @param string $Process - Process to apply
 * @return mixed result after processing
 */
function viewSeparator($value, $Process)
{
    switch ($Process) {
        case "sp":     return "$value ";
        case "2sp":    return "$value  ";
        case "comma":  return "$value,";
        case "com-sp": return "$value, ";
        case "nl":     return "$value\n";
        case "semi-sp":return "$value; ";
        case "del-nl": return $value=='' ? '' : "$value\n";
    }
    return $value;
}

/**
 * Replaces template fields with working data
 * @global object $report - current working report
 * @param string $text_string - string to apply substitution
 * @return string - modified string
 */
function TextReplace($text_string='', $xKeys=[], $xVals=[])
{
    global $report;
    $keys   = array_merge(['%date%', '%reportname%', '%company%'], $xKeys);
    $values = array_merge([viewDate(date('Y-m-d')), $report->title, getModuleCache('bizuno', 'settings', 'company', 'primary_name')], $xVals);
    return str_replace($keys, $values, $text_string); // if getting error here, probably $report->title is an array, problem converting
}

/**
 * Validates PhreeForm security to allow access to the report/form. Used for all actions
 * @param integer $security - encoded report security to process
 * @param type $include_all - [default true] set to false to not allow include all selection
 * @return boolean
 */
function phreeformSecurity($security='u:-1;g:-1', $include_all=true)
{
    $types    = explode(';', $security);
    msgDebug("\nSECURITY TYPES = $security");
    $settings = [];
    foreach ($types as $value) {
        $temp   = explode(':', $value);
        $type   = array_shift($temp);
        $settings[$type] = $temp;
    }
    if (!isset($settings['u']) || !isset($settings['g']) || !is_array($settings['u']) || !is_array($settings['g'])) {
        msgDebug("\nERROR IN SECURITY SETTINGS!");
        return;
    }
    if (in_array(getUserCache('profile', 'admin_id', false, 0), $settings['u']) || in_array(getUserCache('profile', 'role_id', false, 0), $settings['g'])) { return true; }
    if ($include_all && (in_array(-1, $settings['u']) || in_array(-1, $settings['g']))) { return true; }
}

/**
 * Retrieves the list of recent reports/forms to use on PhreeForm home page
 * @param integer $limit - [default 25] limits the number of results
 * @return array = list of most recent $limit reports/forms
 */
function getRecent($limit=25)
{
    $values= [];
    $cnt   = 0;
    $result= dbGetMulti(BIZUNO_DB_PREFIX."phreeform", "mime_type IN ('rpt','frm')", "create_date DESC");
    foreach ($result as $report) {
        if (phreeformSecurity($report['security'], true)) {
            $values[] = $report;
            $cnt++;
        }
        if ($cnt >= $limit) { break; }
    }
    return $values;
}

/**
 * Retrieves reports/forms that have the current user allowed access, less All option
 * @param integer $limit - [default 25] limits the number of results returned
 * @return array - list of reports that calls out the users security user, and group
 */
function getMine($limit=25)
{
    $values= [];
    $cnt   = 0;
    $result= dbGetMulti(BIZUNO_DB_PREFIX."phreeform", "mime_type IN ('rpt','frm')", "title");
    foreach ($result as $report) {
        if (phreeformSecurity($report['security'], false)) {
            $values[] = $report;
            $cnt++;
        }
        if ($cnt >= $limit) { break; }
    }
    return $values;
}

/**
 * Reads the default reports from a folder and builds an HTML DOM select element
 * @param integer $id - database record id
 * @param string $path - path where the reports/forms are stored
 * @param string $lang - [default en_US] language to search as default
 * @return string - HTML containing the list of default reports from the installation folder
 */
function ReadDefReports($id, $path='', $lang="en_US")
{
    if (!$path) { $path = BIZUNO_LIB."locale/en_US/reports"; }
    // build the report titles
    $titles = [];
    foreach (getModuleCache('phreeform', 'rptGroups') as $value) { $titles[$value['id']] = $value['text']; }
    foreach (getModuleCache('phreeform', 'frmGroups') as $value) { $titles[$value['id']] = $value['text']; }
    $ReportList = [];
    $files = @scandir($path);
    foreach ($files as $DefRpt) {
        $pinfo  = pathinfo("$path/$DefRpt");
        $strXML = file_get_contents("$path/$DefRpt");
        $report = parseXMLstring($strXML);
        if (!is_object($report)) { continue; }
        if (!empty($report->PhreeformReport)) { $report = $report->PhreeformReport; } // old way, remove container tag
        $ReportList[$report->groupname][] = [
            'title'      => $report->title,
            'description'=> $report->description,
            'path'       => $pinfo['basename']];
    }
    if (sizeof($ReportList) == 0) { return lang('msg_no_documents'); }

    $output   = '<select id="'.$id.'" size="15">';
    ksort($ReportList);
    foreach ($ReportList as $GrpName => $members) {
        $output .= '<optgroup label="'.$titles[$GrpName].'">';
        foreach ($members as $Temp) {
            $output .= '<option value="'.$Temp['path'].'">'.htmlspecialchars($Temp['title'].' - '.$Temp['description']).'</option>';
        }
        $output .= '</optgroup>';
    }
    return $output.'</select>';
}

/**
 * Takes an XML input string and parses into an object
 * @param string $xmlReport - raw xml report to parse to an object
 * @return object - parser report
 */
function phreeFormXML2Obj($xmlReport)
{
//    msgDebug("\nstarted with = ".print_r($xmlReport, true));
    // fix reports coming out of db
    // deprecate this line after WordPress Update from initial release
    if (strpos($xmlReport, '<?xml') !== 0 || strpos($xmlReport, '<PhreeformReport>') === false) { $xmlReport = '<PhreeformReport>'.$xmlReport.'</PhreeformReport>'; }
    $report = parseXMLstring($xmlReport);
//    msgDebug("\nparsed report = ".print_r($report, true));
    if (isset($report->PhreeformReport) && is_object($report->PhreeformReport)) { $report= $report->PhreeformReport; } // remove container tag, if present
    if (isset($report->tables)    && is_object($report->tables))    { $report->tables    = [$report->tables]; }
    if (isset($report->fieldlist) && is_object($report->fieldlist)) { $report->fieldlist = [$report->fieldlist]; }
    if (isset($report->grouplist) && is_object($report->grouplist)) { $report->grouplist = [$report->grouplist]; } // if only one entry, make it an array of length one
    if (isset($report->sortlist)  && is_object($report->sortlist))  { $report->sortlist  = [$report->sortlist]; }
    if (isset($report->filterlist)&& is_object($report->filterlist)){ $report->filterlist= [$report->filterlist]; }
    if (isset($report->fieldlist)) { foreach ($report->fieldlist as $key => $field) {
        if (isset($field->settings->boxfield) && is_object($field->settings->boxfield)) {
            $report->fieldlist[$key]->settings->boxfield = [$report->fieldlist[$key]->settings->boxfield];
        }
    } }
//    msgDebug("\nAfter cleaning parsed report = ".print_r($report, true));
    return $report;
}

/**
 * Imports a report into the phreeform table
 * @param string $RptName - new title of the imported report
 * @param string $RptFileName - filename of the imported report, if specified by the user
 * @param string $import_path - where to find the report in the file system
 * @param boolean $verbose - [default true] whether to display a message if there is an error
 * @param boolean $replace - [default false] whether to replace a report if the name is the same
 * @return array - record ID and report title
 */
function phreeformImport($RptName='', $RptFileName='', $import_path='', $verbose=true, $replace=false)
{
    $rID = 0;
    $imp = new \bizuno\io();
    if ($RptFileName <> '') { // then a locally stored report was chosen
        if (strtolower(substr($RptFileName, -4)) <> '.xml') { return; }
        $path = $import_path . $RptFileName;
    } else if ($imp->validateUpload('fileUpload', 'xml', 'xml', false)) {
        $path = $_FILES['fileUpload']['tmp_name'];
    } else {
        if ($verbose) { msgAdd(lang('err_phreeform_import')); }
        return;
    }
    if (!$contents = file_get_contents($path))  { return; }
    if (!$report = phreeFormXML2Obj($contents)) { return; }
    if ($RptName <> '') { $report->title = $RptName; } // replace the title if provided
    // error check
    $rID = dbGetValue(BIZUNO_DB_PREFIX."phreeform", "id", "title='$report->title' AND mime_type IN ('rpt','frm')");
    if ($rID && !$replace) { // the report name already exists
        if ($verbose) { msgAdd(sprintf(lang('err_phreeform_title_dup'), $report->title)); }
        return;
    }
    if ($report->reporttype=='rpt') { // clean up phreebooks report format to new format
        $gID = explode(":", $report->groupname);
        $report->groupname = $gID[0].":rpt";
    }
    $parent = dbGetValue(BIZUNO_DB_PREFIX."phreeform", "id", "group_id='$report->groupname' and mime_type='dir'");
    if (!$parent) {
        $report->groupname = $report->reporttype=='rpt' ? 'misc:rpt': 'misc:misc';
        $parent = dbGetValue(BIZUNO_DB_PREFIX."phreeform", "id", "group_id='$report->groupname' and mime_type='dir'");
    }
    $sql_array = [
        'parent_id'  => $parent,
        'group_id'   => $report->groupname,
        'mime_type'  => $report->reporttype,
        'title'      => $report->title,
        'security'   => $report->security,
        'create_date'=> date('Y-m-d'),
        'last_update'=> date('Y-m-d'),
        'doc_data'   => '<PhreeformReport>'.object_to_xml($report).'</PhreeformReport>'];
    $rID = dbWrite(BIZUNO_DB_PREFIX."phreeform", $sql_array, $rID?'update':'insert', "id=$rID");
    return ['rID'=>$rID, 'title'=>$report->title];
}

/**
 * Formats the output for a serial form (receipt)
 * @param string $value - encoded input to format
 * @param integer $width - width of a line of the receipt tape
 * @param char $align - alignment of the line
 * @param string $base_string - starting string before formatting
 * @param boolean $keep_nl - [default false] set to true to retain NL characters
 * @return string - formatted string
 */
function formatReceipt($value, $width = 15, $align = 'z', $base_string = '', $keep_nl = false)
{
    $temp   = explode("\n", $value);
    $output = NULL;
    foreach ($temp as $key => $value) {
        if ($key > 0) { $output .= "\n"; } // keep the new line chars
        switch ($align) {
            case 'L':
                if (strlen($base_string)) { $output .= $value . substr($base_string, $width - strlen($value)); }
                else { $output .= str_pad($value, $width, ' ', STR_PAD_RIGHT); }
                break;
            case 'R':
                if (strlen($base_string)) { $output .= substr($base_string, 0, $width - strlen($value)) . $value; }
                else { $output .= str_pad($value, $width, ' ', STR_PAD_LEFT); }
                break;
            case 'C':
                if (strlen($base_string)) {
                    $pad = (($width - strlen($value)) / 2);
                    $output .= substr($base_string, 0, floor($pad)) . $value . substr($base_string, -ceil($pad));
                } else {
                    $num_blanks = (($width - strlen($value)) / 2) + strlen($value);
                    $value   = str_pad($value, intval($num_blanks), ' ', STR_PAD_LEFT);
                    $output .= str_pad($value, $width,              ' ', STR_PAD_RIGHT);
                }
                break;
        }
    }
    return $output;
}

/**
 * This function formats the tables to a valid sql string
 * @param object $report - working report object
 * @return modified $report
 */
function sqlTable(&$report)
{
    $sqlTable = '';
    foreach ($report->tables as $table) {
        if (isset($table->relationship)) {
            if (!isset($table->joinopt)) { $table->joinopt = ' JOIN '; }
            $sqlTable .= " $table->joinopt ".BIZUNO_DB_PREFIX."$table->tablename ON ".prefixTables($table->relationship);
        } else {
            $sqlTable .= BIZUNO_DB_PREFIX.$table->tablename;
        }
    }
    $report->sqlTable = $sqlTable;
}

/**
 * This function formats the sort fields and returns the sql string, null string if no sort values
 * @param object $report - working report object
 * @return modified $report
 */
function sqlSort(&$report)
{
    $strSort = [];
    if (isset($report->sortlist) && is_array($report->sortlist)) { foreach ($report->sortlist as $sortline) {
        if ($sortline->default == '1') { $strSort[] = prefixTables($sortline->fieldname); }
    } }
    $report->sqlSort = implode(', ', $strSort);
}

/**
 * This function formats the filter fields and returns the sql string, null string if no filter values
 * @param object $report - working report object
 * @return modified $report
 */
function sqlFilter(&$report)
{
    $strCrit = [];
    $report->sqlCritDesc = '';
    if (!isset($report->datefield)) { $report->datefield = ''; }
    if (isset($report->datedefault)) {
        msgDebug("\nWorking with datedefault = $report->datedefault");
        $dates = dbSqlDates($report->datedefault, prefixTables($report->datefield));
        if ($dates['sql']) { $strCrit[] = $dates['sql']; }
        $report->sqlCritDesc.= $dates['description'];
        $report->datedefault = substr($report->datedefault, 0, 1).":{$dates['start_date']}:{$dates['end_date']}";
    }
    $criteria = phreeformCriteria($report);
    if ($criteria['sql'])         { $strCrit[] = $criteria['sql']; }
    if ($criteria['description']) { $report->sqlCritDesc .= lang('settings').": {$criteria['description']}; "; }
    $report->sqlCrit = implode(' AND ', $strCrit);
}

/**
 * Strips the table name from the report table value, if present
 * @param string $value - full string
 * @return string - table field name without the table. prefix
 */
function stripTablename($value)
{
    return (strpos($value, '.') !== false) ? substr($value, strpos($value, '.') + 1) : $value;
}

/*
 * This function add the BIZUNO_DB_PREFIX in front of the database tables
 * @param $field -
 * @return $field - with table prefixes added
 */
function prefixTables($field)
{
    global $report;
    foreach ($report->tables as $table) { $field = str_replace($table->tablename.'.', BIZUNO_DB_PREFIX.$table->tablename.'.', $field); }
    return stripslashes($field);
}

/**
 * Tests for the existence of tables in variables (for formulas)
 * @param string $field- data to test
 * @param object $report - working report object
 * @return boolean - true if found, false otherwise
 */
function testTables($field, $report)
{
      $result = false;
      foreach ($report->tables as $table) {
        if (strpos($field, $table->tablename.'.') !== false) { $result = true; }
      }
      return $result;
}

/**
 * Builds the back part of a SQL statement
 * @global array $critChoices - working criteria choices
 * @param object $report - current report/form
 * @param boolean $xOnly
 * @return array - sql string and description string
 */
function phreeformCriteria($report, $xOnly=false)
{
      global $critChoices;
      $strCrit     = '';
      $filCrit     = '';
      $crit_prefs  = !$xOnly && isset($report->filterlist) ? $report->filterlist : [];
    if (isset($report->xfilterlist)) { $crit_prefs[]= $report->xfilterlist; }
      foreach ($crit_prefs as $settings) {
          msgDebug("\nWorking with field values = ".print_r($settings, true));
        if (empty($settings->fieldname)) { continue; } // blank row
          if (!isset($settings->default)) { // if no selection was passed, assume it's the first on the list for that selection menu
              $temp = explode(':', $critChoices[$settings->type]);
              $settings->default = sizeof($temp) > 1 ? $temp[1] : '';
          }
          $sc = '';
          $fc = '';
          switch ($settings->default) {
            case 'range':
                if (!empty($settings->min)) { // a from value entered, check
                    $sc .= prefixTables($settings->fieldname).">='$settings->min'";
                    $fc .= $settings->title." >= ".$settings->min;
                }
                if (!empty($settings->max)) { // a to value entered, check
                    if (strlen($sc)>0) { $sc .= ' AND '; $fc .= ' '.lang('and').' '; }
                    $sc .= prefixTables($settings->fieldname)."<='$settings->max'";
                    $fc .= $settings->title." <= ".$settings->max;
                }
                break;
            case 'yes':
            case 'true':
            case 'inactive':
            case 'printed':
                $sc .= prefixTables($settings->fieldname)."='1'";
                $fc .= $settings->title."=$settings->default";
                break;
            case 'no':
            case 'false':
            case 'active':
            case 'unprinted':
                $sc .= prefixTables($settings->fieldname)."='0'";
                $fc .= $settings->title."=$settings->default";
                break;
            case 'equal':
            case 'not_equal':
            case 'greater_than':
            case 'less_than':
            if ($settings->default == 'equal')        { $sign = " = "; }
            if ($settings->default == 'not_equal')    { $sign = " <> "; }
            if ($settings->default == 'greater_than') { $sign = " > "; }
            if ($settings->default == 'less_than')    { $sign = " < "; }
                if (isset($settings->min)) { // a from value entered, check
                    $q_field = testTables($settings->min, $report) ? prefixTables($settings->min) : "'".prefixTables($settings->min)."'";
                    $sc .= prefixTables($settings->fieldname).$sign.$q_field;
                    $fc .= (isset($settings->title) ? $settings->title : $settings->fieldname).$sign.$settings->min;
                }
                break;
            case 'in_list':
                if (isset($settings->min)) { // a from value entered, check
                    $csv_values = explode(',', $settings->min);
                for ($i = 0; $i < sizeof($csv_values); $i++) { $csv_values[$i] = trim($csv_values[$i]); }
                    $sc .= prefixTables($settings->fieldname)." IN ('".implode("','", $csv_values)."')";
                    $fc .= isset($settings->title) ? "$settings->title IN ($settings->min)" : '';
                }
                break;
            case 'all': // sql default anyway
            default:
          }
          if ($sc) {
            if (strlen($strCrit) > 0) {
                $strCrit .= ' AND ';
            if (isset($settings->visible)) { $filCrit .= ' '.lang('and').' '; }
            }
            $strCrit .= $sc;
          if (isset($settings->visible)) { $filCrit .= $fc; }
          }
      }
      return ['sql' => $strCrit, 'description' => $filCrit];
  }

  /**
   * Reports Only - Executes the SQL statement and puts results into encoded array to render
   * @global object $report - working report object
   * @param string $sql - Contains the full SQL statement to retrieve data from
   * @param object $report - working report object
   * @return array - results of SQL database query formatted and ready to render
   */
function BuildDataArray($sql, $report)
{
    global $report, $currencies;
//    $posted_currencies = ['currency' => getUserCache('profile', 'currency', false, 'USD'), 'currency_rate' => 1]; // use default currency
    // See if we need to group, fetch the group fieldname
    $GrpFieldName       = '';
    $GrpFieldProcessing = '';
    if (isset($report->grouplist) && is_array($report->grouplist)) { foreach ($report->grouplist as $key => $value) {
        if ($report->grouplist[$key]->default) {
            $GrpFieldName       = $value->fieldname;
            $GrpFieldProcessing = !empty($value->processing) ? $value->processing : '';
            $GrpFieldFormatting = !empty($value->formatting) ? $value->formatting : '';
        }
    } }
    // Build the sequence map of retrieved fields, order is as user wants it
    $heading = $seq = [];
    $i       = 0;
    $GrpField= '';
    foreach ($report->fieldlist as $fields) {
        if (empty($fields->visible)) { continue; }
        if ($fields->fieldname == $GrpFieldName) { $GrpField = 'c'.$i; }
        $heading[$i]= $fields->title;
        $seq[$i]    = [
            'dbField'   => $fields->fieldname,
            'break'     => isset($fields->columnbreak)? $fields->columnbreak : '1',
            'fieldname' => 'c'.$i,
            'total'     => !empty($fields->total)     ? $fields->total     : 0,
            'processing'=> !empty($fields->processing)? $fields->processing: '',
            'formatting'=> !empty($fields->formatting)? $fields->formatting: '',
            'align'     => !empty($fields->align)     ? $fields->align     : 'L',
            'grptotal'  => 0,
            'rpttotal'  => 0];
        $i++;
    }
    if (!empty($report->special_class)) {
        $fqcn = "\\bizuno\\$report->special_class";
        $sp_report = new $fqcn($report);
        return $sp_report->load_report_data($report, $seq, $sql, $GrpField); // the special report formats all of the data, we're done
    }
    if (!$stmt = dbGetResult($sql)) { return msgAdd("Problem reading from the database!"); }
    $result = $stmt->fetchAll(\PDO::FETCH_ASSOC);
    if (sizeof($result) == 0) { return msgAdd(lang('phreeform_output_none'), 'caution'); }
    msgDebug("\nreturned number of rows = ".sizeof($result));
    // Generate the output data array
    if (!isset($report->totalonly)) { $report->totalonly = '0'; }
    $currencies->isoDest = empty($report->iso) ? getUserCache('profile', 'currency', false, 'USD') : $report->iso;
    $RowCnt     = 0;
    $ColCnt     = 1;
    $GrpWorking = false;
    $OutputArray= [];
    foreach ($result as $myrow) { // Check to see if a total row needs to be displayed
        $GLOBALS['currentRow'] = $myrow; // save the current row for processing
        $report->currentValues = false; // reset the stored processing values to save sql's
        if (isset($GrpField) && $GrpField) { // we're checking for group totals, see if this group is complete
            if (($myrow[$GrpField] <> $GrpWorking) && $GrpWorking !== false) { // it's a new group so print totals
                $gTmp = viewProcess($GrpWorking, $GrpFieldProcessing);
                $OutputArray[$RowCnt][0] = 'g:'.viewFormat($gTmp, $GrpFieldFormatting);
                foreach($seq as $offset => $TotalCtl) {
                    // NOTE: Do not process here as this is just a total and the processing was used to get here, just display the total.
                    $OutputArray[$RowCnt][$offset+1] = $TotalCtl['total'] ? viewFormat($TotalCtl['grptotal'], $TotalCtl['formatting']) : ' ';
                    $seq[$offset]['grptotal'] = 0; // reset the total
                }
                $RowCnt++; // go to next row
            }
            $GrpWorking = $myrow[$GrpField]; // set to new grouping value
        }
        foreach ($seq as $key => $TableCtl) {
            $processedData = viewProcess($myrow[$TableCtl['fieldname']], $TableCtl['processing']);
            if (empty($report->totalonly)) { // insert data into output array and set to next column
                $OutputArray[$RowCnt][0] = 'd'; // let the display class know its a data element
                $OutputArray[$RowCnt][$ColCnt] = viewFormat($processedData, $TableCtl['formatting']);
            }
            $ColCnt++;
            if ($TableCtl['total']) { // add to the running total if need be
                $seq[$key]['grptotal'] += $processedData;
                $seq[$key]['rpttotal'] += $processedData;
            }
        }
        $RowCnt++;
        $ColCnt = 1;
    }
    if ($GrpWorking !== false) { // if we collected group data show the final group total
        $gTmp = viewProcess($GrpWorking, $GrpFieldProcessing);
        $OutputArray[$RowCnt][0] = 'g:'.viewFormat($gTmp, $GrpFieldFormatting);
        foreach ($seq as $TotalCtl) {
            // NOTE: Do not process here as this is just a total and the processing was used to get here, just display the total.
            $OutputArray[$RowCnt][$ColCnt] = $TotalCtl['total'] ? viewFormat($TotalCtl['grptotal'], $TotalCtl['formatting']) : ' ';
            $ColCnt++;
        }
        $RowCnt++;
        $ColCnt = 1;
    }
    // see if we have a total to send
    $ShowTotals = false;
    foreach ($seq as $TotalCtl) { if ($TotalCtl['total']=='1') { $ShowTotals = true; } }
    if ($ShowTotals) {
        $OutputArray[$RowCnt][0] = 'r:' . $report->title;
        foreach ($seq as $TotalCtl) {
            // NOTE: Do not process here as this is just a total and the processing was used to get here, just display the total.
            $OutputArray[$RowCnt][$ColCnt] = $TotalCtl['total'] ? viewFormat($TotalCtl['rpttotal'], $TotalCtl['formatting']) : ' ';
            $ColCnt++;
        }
    }
    msgDebug("\nOutput array = ".print_r($OutputArray, true));
    return $OutputArray;
}

/**
 * Replaces non-allowed characters with underscore to avoid db failures
 * @param string $string - raw data coming in
 * @return string - $string with problematic characters replaced
 */
function ReplaceNonAllowedCharacters($string)
{
    return str_replace(['"', ' ', '&', "'"], "_", $string);
}

/**
 * Creates a list of page types for a report/form, supported by TCPDF
 * @param array $lang - language translations for textual titles
 * @return array - list of page sizes
 */
function phreeformPages($lang)
{ // TBD make the key just the TCPDF supported paper sizes, pull dimensions from array
    return [
        ['id'=>'LETTER:216:279', 'text'=>$lang['paper_letter']],
        ['id'=>'LEGAL:216:357',  'text'=>$lang['paper_legal']],
        ['id'=>'A3:297:420',     'text'=>'A3'],
        ['id'=>'A4:210:297',     'text'=>'A4'],
        ['id'=>'A5:148:210',     'text'=>'A5'],
        ['id'=>'A0:841x1189',    'text'=>'A0'],
        ['id'=>'A1:594:841',     'text'=>'A1'],
        ['id'=>'A2:420:594',     'text'=>'A2'],
        ['id'=>'A6:105:148',     'text'=>'A6'],
        ['id'=>'A7:74:105',      'text'=>'A7'],
        ['id'=>'A8:52:74',       'text'=>'A8'],
        ['id'=>'A9:37:52',       'text'=>'A9'],
        ['id'=>'TABLOID:279:432','text'=>$lang['paper_tabloid']]];
}

/**
 * Generates the list of page orientations, ready for select DOM element
 * @param array $lang - Locale language file
 * @return array - ready to render
 */
function phreeformOrientation($lang)
{
    $output = [
        ['id'=>'P', 'text'=>$lang['orient_portrait']],
        ['id'=>'L', 'text'=>$lang['orient_landscape']]];
    return $output;
}

/**
 * Generates the list of TCPDF fonts available, typically from the default installation
 * @param boolean $show_default - [default true] set to false to include the null select option at the beginning
 * @return array - ready to render in select DOM element
 */
function phreeformFonts($show_default=true)
{
    $choices = glob(BIZUNO_3P_TCPDF."fonts/*.php");
    $output = $show_default ? [['id'=>'default', 'text'=>lang('default')]] : [];
    foreach ($choices as $choice) {
        $name = false;
        include($choice); // will set $name if it's a valid file
        if ($name) { $output[] = ['id'=>basename($choice, ".php"), 'text'=>$name]; }
    }
    return $output;
}

/**
 * Converts a RGB color to hexadecimal format (hash needed for easyui-color widget)
 * @param string $value - value to convert
 * @return string - converted $value
 */
function convertHex($value)
{
    if (strpos($value, '#') === 0) { return $value; } // already in hex, strip the hash
    $colors = explode(':', $value);
    $output = NULL;
    foreach ($colors as $decimal) { $output .= str_pad(dechex($decimal), 2, "0", STR_PAD_LEFT); }
    return '#'.$output;
}

/**
 * Generates the list of font sizes available to use in a report/form
 * @return array - List ready to render
 */
function phreeformSizes()
{
    return [
        ['id'=> '8', 'text'=> '8'],
        ['id'=> '9', 'text'=> '9'],
        ['id'=>'10', 'text'=>'10'],
        ['id'=>'11', 'text'=>'11'],
        ['id'=>'12', 'text'=>'12'],
        ['id'=>'14', 'text'=>'14'],
        ['id'=>'16', 'text'=>'16'],
        ['id'=>'18', 'text'=>'18'],
        ['id'=>'20', 'text'=>'20'],
        ['id'=>'24', 'text'=>'24']];
}

/**
 * Generates the list of alignments available, choices are left, center, and right
 * @return array - list ready to render
 */
function phreeformAligns()
{
    return [
        ['id'=>'L', 'text'=>lang('left')],
        ['id'=>'R', 'text'=>lang('right')],
        ['id'=>'C', 'text'=>lang('center')]];
}

/**
 * Generates the list of company data from the cache, data is sourced in Settings -> Bizuno -> General
 * @return array - list ready to render
 */
function phreeformCompany()
{
    $output = [];
    $company = array_keys(getModuleCache('bizuno', 'settings', 'company'));
    foreach ($company as $key) { $output[] = ['id'=>$key, 'text'=>pullTableLabel('address_book', $key)]; }
    return $output;
}

/**
 * Generates the list of bar code types supported by TCPDF used in the fields of a form
 * @return array - ready to render
 */
function phreeformBarCodes()
{
    return [
        ['id'=>'C39',     'text'=> 'CODE 39 - ANSI MH10.8M-1983 - USD-3 - 3 of 9.'],
        ['id'=>'C39+',    'text'=> 'CODE 39 with checksum'],
        ['id'=>'C39E',    'text'=> 'CODE 39 EXTENDED'],
        ['id'=>'C39E+',   'text'=> 'CODE 39 EXTENDED + CHECKSUM'],
        ['id'=>'C93',     'text'=> 'CODE 93 - USS-93'],
        ['id'=>'S25',     'text'=> 'Standard 2 of 5'],
        ['id'=>'S25+',    'text'=> 'Standard 2 of 5 + CHECKSUM'],
        ['id'=>'I25',     'text'=> 'Interleaved 2 of 5'],
        ['id'=>'I25+',    'text'=> 'Interleaved 2 of 5 + CHECKSUM'],
        ['id'=>'C128',    'text'=> 'CODE 128'],
        ['id'=>'C128A',   'text'=> 'CODE 128 A'],
        ['id'=>'C128B',   'text'=> 'CODE 128 B'],
        ['id'=>'C128C',   'text'=> 'CODE 128 C'],
        ['id'=>'EAN2',    'text'=> '2-Digits UPC-Based Extention'],
        ['id'=>'EAN5',    'text'=> '5-Digits UPC-Based Extention'],
        ['id'=>'EAN8',    'text'=> 'EAN 8'],
        ['id'=>'EAN13',   'text'=> 'EAN 13'],
        ['id'=>'UPCA',    'text'=> 'UPC-A'],
        ['id'=>'UPCE',    'text'=> 'UPC-E'],
        ['id'=>'MSI',     'text'=> 'MSI (Variation of Plessey code)'],
        ['id'=>'MSI+',    'text'=> 'MSI + CHECKSUM (modulo 11)'],
        ['id'=>'POSTNET', 'text'=> 'POSTNET'],
        ['id'=>'PLANET',  'text'=> 'PLANET'],
        ['id'=>'RMS4CC',  'text'=> 'RMS4CC (Royal Mail 4-state Customer Code) - CBC (Customer Bar Code)'],
        ['id'=>'KIX',     'text'=> 'KIX (Klant index - Customer index)'],
        ['id'=>'IMB',     'text'=> 'Intelligent Mail Barcode - Onecode - USPS-B-3200'],
        ['id'=>'CODABAR', 'text'=> 'CODABAR'],
        ['id'=>'CODE11',  'text'=> 'CODE 11'],
        ['id'=>'PHARMA',  'text'=> 'PHARMACODE'],
        ['id'=>'PHARMA2T','text'=> 'PHARMACODE TWO-TRACKS']];
}

/**
 * Generates a list of separators pulled from the cache, built at initialization
 * @return array - ready to render
 */
function phreeformSeparators()
{
    $output = [['id'=> '', 'text'=> lang('none')]];
    foreach (getModuleCache('phreeform', 'separators') as $key => $value) { $output[] = ['id'=> $key, 'text'=>$value['text']]; }
    return $output;
}

/**
 * Generates the list for the pull down to process report data, cache is loaded at initialization
 * @return array of choices for pull down
 */
function phreeformProcessing()
{
    $output = [['id'=> '', 'text'=> lang('none')]];
    foreach (getModuleCache('phreeform', 'processing') as $key => $value) {
        $output[] = ['id'=> $key, 'text'=>$value['text'], 'group'=>$value['group']];
    }
    return $output;
}

/**
 * Generates the list for the pull down to format report data from the cache
 * @return array of choices for pull down
 */
function phreeformFormatting()
{
    $output = [['id'=>'', 'text'=>lang('none')]];
    foreach (getModuleCache('phreeform', 'formatting') as $key => $value) {
        $output[] = ['id'=> $key, 'text'=>$value['text'], 'group'=>isset($value['group']) ? $value['group'] : ''];
    }
    return $output;
}
