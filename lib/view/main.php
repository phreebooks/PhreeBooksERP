<?php
/*
 * Main view file, has common class and support functions
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
 * @copyright  2008-2020, PhreeSoft, Inc.
 * @license    http://opensource.org/licenses/OSL-3.0  Open Software License (OSL 3.0)
 * @version    4.x Last Update: 2020-08-26
 * @filesource /view/main.php
 */

namespace bizuno;

bizAutoLoad(BIZUNO_ROOT."portal/view.php", 'portalView');

final class view extends portalView
{
    var $html = ''; // fuly formed HTML/data to send to client

    function __construct($data=[], $scope='default')
    {
        // declare global data until all modules are converted to new nested structure
        global $viewData;
        $viewData = $data;
        parent::__construct();
        $this->render($data, $scope);
    }

    /**
     * Main function to take the layout and build the HTML/AJAX response
     * @global array $msgStack - Any messages that had been set during processing
     * @param array $data - The layout to render from
     * @return string - Either HTML or JSON depending on expected response
     */
    private function render($data=[], $scope='default')
    {
        global $msgStack;
        dbWriteCache();
        $type = !empty($data['type']) ? $data['type'] : 'json';
        switch ($type) {
            case 'datagrid':
                $content = dbTableRead($data['datagrid'][$data['key']]);
                $content['message'] = $msgStack->error;
                msgDebug("\n datagrid results = ".print_r($content, true));
                echo json_encode($content);
                $msgStack->debugWrite();
                exit();
            case 'raw':
                msgDebug("\n sending type: raw and data = {$data['content']}");
                echo $data['content'];
                $msgStack->debugWrite();
                exit();
            case 'divHTML':
                $this->renderDivs($data); // may add JS, generates 'body'
                $dom = $this->html;
                $dom.= $this->renderJS($data);
                msgDebug("\n sending type: divHTML and data = $dom");
                echo $dom;
                $msgStack->debugWrite();
                break;
            case 'page':
                $dom = $this->viewDOM($data, $scope); // formats final HTML to specific host expectations
                msgDebug("\n sending type: page and data = $dom");
                echo $dom;
                $msgStack->debugWrite();
                break;
            case 'popup':
                $dom = $this->renderPopup($data); // make layout changes per device then treat like div
                msgDebug("\n sending type: popup and data = $dom");
                echo $dom;
                $msgStack->debugWrite();
                exit;
            case 'json':
            default:
                if (isset($data['action'])){ $data['content']['action']= $data['action']; }
                if (isset($data['divID'])) { $data['content']['divID'] = $data['divID']; }
                $this->renderDivs($data);
                $this->html .= $this->renderJS($data, false);
                $data['content']['html'] = empty($data['content']['html']) ? $this->html : $data['content']['html'].$this->html;
                $data['content']['message'] = $msgStack->error;
                msgDebug("\n json return (before encoding) = ".print_r($data['content'], true));
                ob_clean(); // in case there is something there, this will clear everything to prevent json errors
                echo json_encode($data['content']);
                $msgStack->debugWrite();
                exit();
        }
    }

    /**
     * Renders the HTML for the <head> tag
     * @param type $data
     */
    protected function renderHead(&$data=[])
    {
        global $html5;
        msgDebug("\nEntering renderHead");
        $html = '';
        $head = sortOrder($data['head']);
        if (!empty($head)) {
            foreach ($head as $value) {
                $html .= "\t";
                $html5->buildDiv($html, $value);
            }
        }
        if (!empty($data['jsHead'])) {
            $html .= '<script type="text/javascript">'."\n".implode("\n", $data['jsHead'])."\n</script>\n";
            $data['jsHead'] = [];
        }
        return $html;
    }

    /**
     *
     * @global \bizuno\class $html5
     * @param type $data
     * @param type $type
     * @return type
     */
    protected function renderDivs($data)
    {
        global $html5;
        if (empty($data)) { return ''; }
        $header = $footer = '';
        msgDebug("\nEntering renderDivs");
        $this->html = $html5->buildDivs($data, 'divs'); // generates $this->html body but can add headers and footers
        if (!empty($data['header'])) {
            $header .= "<header>\n";
            $html5->buildDiv($header, $data['header']);
            $header .= "</header>\n";
        }
        if (!empty($data['footer'])) {
            $footer .= "<footer>\n";
            $html5->buildDiv($footer, $data['footer']);
            $footer .= "</footer>\n";
        }
        return "$header\n$footer\n$this->html\n";
    }

    /**
     *
     * @global \bizuno\class $html5
     * @param type $data
     * @param type $addMsg
     * @return string
     */
    protected function renderJS($data, $addMsg=true)
    {
        global $html5;
        $dom = '';
        msgDebug("\nEntering renderJS");
        if (!isset($data['jsHead']))   { $data['jsHead']  = []; }
        if (!isset($data['jsBody']))   { $data['jsBody']  = []; }
        if (!isset($data['jsReady']))  { $data['jsReady'] = []; }
        if (!isset($data['jsResize'])) { $data['jsResize']= []; }
        // gather everything together
        $jsHead  = array_merge($data['jsHead'],  $html5->jsHead);
        $jsBody  = array_merge($data['jsBody'],  $html5->jsBody);
        $jsReady = array_merge($data['jsReady'], $html5->jsReady);
        $jsResize= array_merge($data['jsResize'],$html5->jsResize);
        msgDebug("\n jsHead = ".print_r($jsHead, true));
        msgDebug("\n jsBody = ".print_r($jsBody, true));
        msgDebug("\n jsReady = ".print_r($jsReady, true));
        msgDebug("\n jsResize = ".print_r($jsResize, true));
        if (sizeof($jsResize)) { $jsHead['reSize'] = "var windowWidth = jq(window).width();
function resizeEverything() { ".implode(" ", $jsResize)." }
jq(window).resize(function() { if (jq(window).width() != windowWidth) { windowWidth = jq(window).width(); resizeEverything(); } });"; }
        if ($addMsg) { $jsReady['msgStack'] = $html5->addMsgStack(); }
        // Render the output
        if (sizeof($jsHead)) { // first
            $dom .= '<script type="text/javascript">'."\n".implode("\n", $jsHead)."\n</script>\n";
        }
        if (sizeof($jsBody)) { // second
            $dom .= '<script type="text/javascript">'."\n".implode("\n", $jsBody)."\n</script>\n";
        }
        if (sizeof($jsReady)) { // doc ready, last
            $dom .= '<script type="text/javascript">'."jq(document).ready(function() {\n".implode("\n", $jsReady)."\n});\n</script>\n";
        }
        return $dom;
    }

    /**
     * Renders popups which vary based on the type of device
     * @global array $msgStack
     * @param type $data
     * @return type
     */
    public function renderPopup($data)
    {
        global $msgStack, $html5;
        switch($GLOBALS['myDevice']) {
            case 'mobile': // set a new panel
                if (biz_validate_user()) {
                    $data['header'] = ['classes'=>['m-toolbar'],'type'=>'divs','divs'=>[
//                      'left'  => ['order'=>10,'type'=>'menu','size'=>'small','hideLabels'=>true,'classes'=>['m-left'], 'options'=>['plain'=>'true'],
//                          'data'=>$html5->layoutMenuLeft('back')],
                        'center'=> ['order'=>20,'type'=>'html','classes'=>['m-title'],'html'=>!empty($data['title']) ? $data['title'] : ''],
                        'right' => ['order'=>30,'type'=>'menu','size'=>'small','hideLabels'=>true,'classes'=>['m-right'],'options'=>['plain'=>'true'],
                            'data'=>$html5->layoutMenuLeft('back')]]];
                } else {
                    $data['header'] = ['classes'=>['m-toolbar'],'type'=>'divs','divs'=>[
                        'center'=> ['order'=>20,'type'=>'html','classes'=>['m-title'],'html'=>!empty($data['title']) ? $data['title'] : '']]];
                }
                $data['jsReady'][] = "jq.mobile.go('#navPopup'); jq.parser.parse('#navPopup');"; // load the div, init easyui components
                $dom  = $this->renderDivs($data);
                $dom .= $this->renderJS($data);
                $data['content']['action']= 'newDiv';
                $data['content']['html']  = $dom;
                break;
            case 'tablet':
            case 'desktop': // set a javascript popup window
            default:
                $data['content']['action']= 'window';
                $data['content']['title'] = $data['title'];
                $data['content'] = array_merge($data['content'], $data['attr']);
                $this->renderDivs($data);
                $this->html .= $this->renderJS($data, false);
                $data['content']['html']  = empty($data['content']['html']) ? $this->html : $data['content']['html'].$this->html;
                break;
        }
        $data['content']['message'] = $msgStack->error;
        return json_encode($data['content']);
    }
}

/**
 * Formats a system value to the locale view format
 * @global array $currencies
 * @param mixed $value - value to be formatted
 * @param string $format - Specifies the formatting to apply
 * @return string
 */
function viewFormat($value, $format = '')
{
//  msgDebug("\nIn viewFormat value = $value and format = $format");
    switch ($format) {
        case 'blank':      return '';
        case 'blankNull':  return $value ? $value : '';
        case 'contactID':  return ($result = dbGetValue(BIZUNO_DB_PREFIX."contacts", 'short_name',   "id='$value'")) ? $result : getModuleCache('bizuno', 'settings', 'company', 'id');
        case 'contactGID': return ($result = dbGetValue(BIZUNO_DB_PREFIX."contacts", 'gov_id_number',"id='$value'")) ? $result : '';
        case 'contactName':if (!$value) { return ''; }
            return ($result = dbGetValue(BIZUNO_DB_PREFIX."address_book", 'primary_name', "ref_id='$value' AND type='m'")) ? $result : '';
        case 'contactType':return pullTableLabel(BIZUNO_DB_PREFIX."contacts", 'type', $value);
        case 'curNull0':
        case 'currency':
        case 'curLong':
        case 'curExc':     return viewCurrency($value, $format);
        case 'date':
        case 'dateNoY':
        case 'datetime':   return viewDate($value, false, $format);
        case 'dateLong':   return viewDate($value).' '.substr($value, strpos($value, ' '));
        case 'encryptName':if (empty(getUserCache('profile', 'admin_encrypt'))) { return ''; }
            bizAutoLoad(BIZUNO_LIB."model/encrypter.php", 'encryption');
            $enc = new encryption();
            $result = $enc->decrypt(getUserCache('profile', 'admin_encrypt'), $value);
            msgDebug("\nDecrypted: ".print_r($result, true));
            $values = explode(':', $result);
            return is_array($values) ? $values[0] : '';
        case 'glActive':  return !empty(getModuleCache('phreebooks', 'chart', 'accounts', $value, '')['inactive']) ? lang('yes') : '';
        case 'glType':    return lang('gl_acct_type_'.$value);
        case 'glTitle':   return getModuleCache('phreebooks', 'chart', 'accounts', $value, $value)['title'];
        case 'lc':        return mb_strtolower($value);
        case 'j_desc':    return lang("journal_main_journal_id_$value");
        case 'json':      return json_decode($value, true);
        case 'neg':       return -$value;
        case 'n2wrd':     return viewNumToWords($value);
        case 'null0':     return (round((float)$value, 4) == 0) ? '' : $value;
        case 'number':    return number_format((float)$value, getModuleCache('bizuno', 'settings', 'locale', 'number_precision'), getModuleCache('bizuno', 'settings', 'locale', 'number_decimal'), getModuleCache('bizuno', 'settings', 'locale', 'number_thousand'));
        case 'percent':   return floatval($value) ? number_format($value * 100, 1)." %" : '';
        case 'printed':   return $value ? '' : lang('duplicate');
        case 'precise':   $output = number_format((float)$value, getModuleCache('bizuno', 'settings', 'locale', 'number_precision'));
            $zero = number_format(0, getModuleCache('bizuno', 'settings', 'locale', 'number_precision')); // to handle -0.00
            return ($output == '-'.$zero) ? $zero : $output;
        case 'rep_id':      $result = dbGetValue(BIZUNO_DB_PREFIX."users", 'title', "admin_id='$value'");
            return $result ? $result : $value;
        case 'rnd0d':     return !is_numeric($value) ? $value : number_format(round($value, 0), 0, '.', '');
        case 'rnd2d':     return !is_numeric($value) ? $value : number_format(round($value, 2), 2, '.', '');
        case 'taxTitle':  return viewTaxTitle($value);
        case 'cTerms':    return viewTerms($value, true, 'id'); // must be passed contact id, default terms will use customers default
        case 'terms':     return viewTerms($value); // must be passed encoded terms, default terms will use customers default
        case 'terms_v':   return viewTerms($value, true, 'v'); // must be passed encoded terms, default terms will use vendors default
        case 'today':     return date('Y-m-d');
        case 'uc':        return mb_strtoupper($value);
        case 'yesBno':    return !empty($value) ? lang('yes') : '';
    }
    if (getModuleCache('phreeform', 'formatting', $format, 'function')) {
        $func = getModuleCache('phreeform', 'formatting')[$format]['function'];
        $fqfn = __NAMESPACE__."\\$func";
        if (!function_exists($fqfn)) {
            $module = getModuleCache('phreeform', 'formatting')[$format]['module'];
            $path = getModuleCache($module, 'properties', 'path');
            if (!bizAutoLoad("{$path}functions.php", $fqfn, 'function')) {
                msgDebug("\nFATAL ERROR looking for file {$path}functions.php and function $func and format $format, but did not find", 'trap');
                return $value;
            }
        }
        return $fqfn($value, $format);
    }
    if (substr($format, 0, 5) == 'dbVal') { // retrieve a specific db field value from the reference $value field
        if (!$value) { return ''; }
        $tmp = explode(';', $format); // $format = dbVal;table;field;ref or dbVal;table;field:index;ref
        if (sizeof($tmp) <> 4) { return $value; } // wrong element count, return $value
        $fld = explode(':', $tmp[2]);
        $result = dbGetValue(BIZUNO_DB_PREFIX.$tmp[1], $fld[0], $tmp[3]."='$value'", false);
        if (isset($fld[1])) {
            $settings = json_decode($result, true);
            return isset($settings[$fld[1]]) ? $settings[$fld[1]] : 'set';
        } else { return $result ? $result : '-'; }
    } elseif (substr($format, 0, 5) == 'attch') { // see if the record has any attachments
        if (!$value) { return '0'; }
        $tmp = explode(':', $format); // $format = attch:path (including prefix)
        if (sizeof($tmp) <> 2) { return '0'; } // wrong element count, return 0
        $path = str_replace('idTBD', $value, $tmp[1]).'*';
        $result = glob(BIZUNO_DATA.$path);
        if ($result===false) { return '0'; }
        return sizeof($result) > 0 ? '1' : '0';
    } elseif (substr($format, 0, 5) == 'cache') {
        $tmp = explode(':', $format); // $format = cache:module:index
        if (sizeof($tmp) <> 3 || empty($value)) { return ''; } // wrong element count, return empty string
        return getModuleCache($tmp[1], $tmp[2], $value, false, $value);
    }
    return $value;
}

/**
 *
 * @param type $content
 * @param type $action
 */
function viewDashLink($left='', $right='', $action='')
{
    return '<div class="dashHover"><span style="width:100%;float:left;height:20px;"><span style="float:left" class="menuHide dashAction">'.$action.'</span>'.$left.'<span style="float:right">'.$right.'</span></span></div>';
}

/**
 *
 * @param type $content
 * @param type $action
 */
function viewDashList($content='', $action='')
{
    return '<div class="dashHover"><span style="width:100%;float:left">'.$content.'<span style="float:right" class="menuHide dashAction">'.$action.'</span></span></div>';
}

/**
 * This function takes the db formatted date and converts it into a locale specific format as defined in the settings
 * @param date $raw_date - raw date in db format
 * @param bool $long - [default: false] Long format
 * @param sring $action - [default: date] The desired output format
 * @return string - Formatted date for rendering
 */
function viewDate($raw_date = '', $long=false, $action='date')
{
    // from db to locale display format
    if (empty($raw_date) || $raw_date=='0000-00-00' || $raw_date=='0000-00-00 00:00:00') { return ''; }
    $error  = false;
    $year   = substr($raw_date,  0, 4);
    $month  = substr($raw_date,  5, 2);
    $day    = substr($raw_date,  8, 2);
    $hour   = $long ? substr($raw_date, 11, 2) : 0;
    $minute = $long ? substr($raw_date, 14, 2) : 0;
    $second = $long ? substr($raw_date, 17, 2) : 0;
    if ($month<    1 || $month>   12) { $error = true; }
    if ($day  <    1 || $day  >   31) { $error = true; }
    if ($year < 1900 || $year > 2099) { $error = true; }
    if ($error) {
        $date_time = time();
    } else {
        $date_time = mktime($hour, $minute, $second, $month, $day, $year);
    }
    $format = getModuleCache('bizuno', 'settings', 'locale', 'date_short').($long ? ' h:i:s a' : '');
    if ($action=='dateNoY') { $format = trim(str_replace('Y', '', $format), "-./"); }// no year
    return date($format, $date_time);
}

function viewDiv(&$output, $prop)
{
    global $html5;
    $html5->buildDiv($output, $prop);
}

/**
 * This function generates the format for a drop down based on an array
 * @param array $source - source data, typically pulled directly from the db
 * @param string $idField (default `id`) - specifies the associative key to use for the id field
 * @param string $textField (default `text`) - specifies the associative key to use for the description field
 * @param string $addNull (default false) - set to true to include 'None' at beginning of select list
 * @return array - data values ready to be rendered by function html5 for select element
 */
 function viewDropdown($source, $idField='id', $textField='text', $addNull=false)
{
    $output = $addNull ? [['id'=>'0', 'text'=>lang('none')]] : [];
    if (is_array($source)) { foreach ($source as $row) { $output[] = ['id'=>$row[$idField],'text'=>$row[$textField]]; } }
    return $output;
}

/**
 * Pulls the average sales over the past 12 months of the specified SKU, with cache for multiple hits
 * @param type integer - number of sales, zero if not found or none
 */
function viewInvSales($sku='',$range='m12')
{
    if (empty($GLOBALS['invSkuSales'])) {
        $dates  = localeGetDates();
        $month0 = $dates['ThisYear'].'-'.substr('0'.$dates['ThisMonth'], -2).'-01';
        $monthE = localeCalculateDate($month0, 0,  1,  0);
        $month1 = localeCalculateDate($month0, 0, -1,  0);
        $month3 = localeCalculateDate($month0, 0, -3,  0);
        $month6 = localeCalculateDate($month0, 0, -6,  0);
        $month12= localeCalculateDate($month0, 0,  0, -1);
        $sql    = "SELECT m.post_date, m.journal_id, i.sku, i.qty FROM ".BIZUNO_DB_PREFIX."journal_main m JOIN ".BIZUNO_DB_PREFIX."journal_item i ON m.id=i.ref_id
            WHERE m.post_date >= '$month12' AND m.post_date < '$monthE' AND m.journal_id IN (12,13,14,16) AND i.sku<>'' ORDER BY i.sku";
        $stmt   = dbGetResult($sql);
        $result = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        msgDebug("\nReturned annual sales by SKU rows = ".sizeof($result));
        foreach ($result as $row) {
            if (empty($GLOBALS['invSkuSales'][$row['sku']])) { $GLOBALS['invSkuSales'][$row['sku']] = ['m0'=>0,'m1'=>0,'m3'=>0,'m6'=>0,'m12'=>0]; }
            if (in_array($row['journal_id'], [13,14])) { $row['qty'] = -$row['qty']; }
            if ($row['post_date'] >= $month0) { $GLOBALS['invSkuSales'][$row['sku']]['m0'] += $row['qty']; }
            else { // prior month(s)
                if ($row['post_date'] >= $month1) { $GLOBALS['invSkuSales'][$row['sku']]['m1'] += $row['qty'];    }
                if ($row['post_date'] >= $month3) { $GLOBALS['invSkuSales'][$row['sku']]['m3'] += $row['qty']/3;  }
                if ($row['post_date'] >= $month6) { $GLOBALS['invSkuSales'][$row['sku']]['m6'] += $row['qty']/6;  }
                $GLOBALS['invSkuSales'][$row['sku']]['m12']+= $row['qty']/12;
            }
        }
    }
    return !empty($GLOBALS['invSkuSales'][$sku][$range]) ? number_format($GLOBALS['invSkuSales'][$sku][$range], 2, '.', '') : 0;
}

/**
 * Calculates the min stock level and compares to current level, returns new min stock if in band else null
 * @param string $sku - db sku field
 */
function viewInvMinStk($sku)
{
    $tolerance= 0.10; // 10% tolerance band
    $yrSales  = viewInvSales($sku);
    $curMinStk= dbGetValue(BIZUNO_DB_PREFIX."inventory", ['qty_min','lead_time'], "sku='$sku'");
    $newMinStk= ($yrSales/12) * (($curMinStk['lead_time']/30) + 1); // 30 days of stock
    return abs($newMinStk - $curMinStk['qty_min']) > abs($curMinStk['qty_min'] * $tolerance) ? number_format($newMinStk,0) : '';
}

/**
 * This function takes a keyed array and converts it into a format needed to render a HTML drop down
 * @param array $source
 * @param boolean $addNull - inserts at the beginning a choice to not select a value and return a value of 0 if selected
 * @return array $output - contains array compatible with function HTML5 to render a drop down input element
 */
function viewKeyDropdown($source, $addNull=false)
{
    $output = $addNull ? [['id'=>'', 'text'=>lang('none')]] : [];
    if (is_array($source)) { foreach ($source as $key => $value) { $output[] = ['id'=>$key, 'text'=>$value]; } }
    return $output;
}

function viewNumToWords($value=0)
{
    $lang = getUserCache('profile', 'language', false, 'en_US');
    if ($lang <> 'en_US') {
        if (file_exists(BIZUNO_ROOT."locale/".getUserCache('profile', 'language')."/functions.php")) { // PhreeBooks 5
            bizAutoLoad(BIZUNO_ROOT."locale/".getUserCache('profile', 'language')."/functions.php", 'viewCurrencyToWords', 'function');
        } elseif (file_exists(BIZUNO_DATA."locale/".getUserCache('profile', 'language')."/functions.php")) { // WordPress
            bizAutoLoad(BIZUNO_DATA."locale/".getUserCache('profile', 'language')."/functions.php", 'viewCurrencyToWords', 'function');
        }
    }
    bizAutoLoad(BIZUNO_LIB."locale/en_US/functions.php", 'viewCurrencyToWords', 'function');
    return viewCurrencyToWords($value);
}

/**
 * Processes a string of data with a user specified process, returns unprocessed if function not found
 * @param mixed $strData - data to process
 * @param string $Process - process to apply to the data
 * @return mixed - processed string if process found, original string if not
 */
function viewProcess($strData, $Process=false)
{
//    msgDebug("\nEntering viewProcess with strData = $strData and process = $Process");
    if ($Process && getModuleCache('phreeform', 'processing', $Process, 'function')) {
        $func = getModuleCache('phreeform', 'processing')[$Process]['function'];
        $fqfn = "\\bizuno\\$func";
        $mID  = getModuleCache('phreeform', 'processing')[$Process]['module'];
        if (bizAutoLoad(getModuleCache($mID, 'properties', 'path')."functions.php", $fqfn, 'function')) {
            return $fqfn($strData, $Process);
        }
    }
    return $strData;
}

/**
 * Determines the users screen size
 * small - mobile device, phone, restrict to one column
 * medium - tablet, ipad, restrict to two columns
 * large - laptop, desktop, unlimited columns
 */
function viewScreenSize()
{
    $size = 'large';
    return $size;
}

/**
 * Generates a value if at a sub menu dashboard page.
 * @param string $menuID - Derives from menuID get variable
 * @return array structure of menu
 */
function viewSubMenu($menuID=false) {
    if (!$menuID) { $menuID = clean('menuID', 'cmd', 'get'); }
    if (empty($menuID) || $menuID=='home') { return; } // only show submenu when viewing a dashboard
    if ($menuID == 'settings') { // special case for settings in the quickbar
        $menu = getUserCache('quickBar');
        $prop = $menu['child']['home'];
    } else {
        $menu = getUserCache('menuBar');
        if (!isset($menu['child'][$menuID])) { return ''; }
        $prop = $menu['child'][$menuID];
    }
    return $prop;
}

/**
 * Takes a text string and truncates it to a given length, if the string is longer will append ... to the truncated string
 * @param string $text - Text to test/truncate
 * @param type $length - (Default: 25) Maximum length of string
 * @return string - truncated string (with ...) of over length $length
 */
function viewText($text, $length=25)
{
    return strlen($text)>$length ? substr($text, 0, $length).'...' : $text;
}

/**
 * This function pulls the available languages from the Locale folder and prepares for drop down menu
 */
function viewLanguages($skipDefault=false)
{
    $output = [];
    if (!$skipDefault) { $output[] = ['id'=>'','text'=>lang('default')]; }
    $output[]= ['id'=>'en_US','text'=>'English (U.S.) [en_US]']; // put English first
    $langCore= [];
    if (!defined('BIZUNO_LOCALE') || !file_exists(BIZUNO_LOCALE)) { return $output; }
    $langs   = scandir(BIZUNO_LOCALE);
    foreach ($langs as $lang) {
        if (!in_array($lang, ['.', '..', 'en_US']) && is_dir(BIZUNO_LOCALE."$lang")) {
            require(BIZUNO_LOCALE."$lang/language.php");
            $output[] = ['id'=>$lang, 'text'=>isset($langCore['language_title']) ? $langCore['language_title']." [$lang]" : $lang];
        }
    }
    return $output;
}

/**
 * Generates a list of available methods to render a pull down menu
 * @param string $module - Lists the module to pull methods from
 * @param string $type - Lists the grouping (default = 'methods')
 * @return array $output - active payment modules list ready for pull down display
 */
function viewMethods($module, $type='methods')
{
    $output = [];
    $methods = sortOrder(getModuleCache($module, $type));
    foreach ($methods as $mID => $value) {
        if (isset($value['status']) && $value['status']) {
            $output[] = ['id'=>$mID, 'text'=>$value['title'], 'order'=>$value['settings']['order']];
        }
    }
    return $output; // should be sorted during registry build
}

/**
 * This recursive function formats the structure needed by jquery easyUI to populate a tree remotely (by ajax call)
 * @param array $data - contains the tree structure information
 * @param integer $parent - database record id of the parent of a given element (used for recursion)
 * @return array $output - structured array ready to be sent back to browser (after json encoding)
 */
function viewTree($data, $parent=0, $sort=true)
{
    global $bizunoLang;
    $output  = [];
    $parents = [];
    foreach ($data as $idx => $row) {
        $parents[$row['parent_id']] = $row['parent_id'];
        if (!empty($bizunoLang[$row['title']])) { $data[$idx]['title'] = $bizunoLang[$row['title']]; }
    }
    if ($sort) { $data = sortOrder($data, 'title'); }
    foreach ($data as $row) {
        if ($row['parent_id'] != $parent) { continue; }
        $temp = ['id'=> $row['id'],'text'=>$row['title']];
        $attr = [];
        if (isset($row['url']))       { $attr['url']       = $row['url']; }
        if (isset($row['mime_type'])) { $attr['mime_type'] = $row['mime_type']; }
        if (sizeof($attr) > 0) { $temp['attributes'] = json_encode($attr); }
        if (in_array($row['id'], $parents)) { // folder with contents
            $temp['state']    = 'closed';
            $temp['children'] = viewTree($data, $row['id'], $sort);
        } elseif (isset($row['mime_type']) && $row['mime_type']=='dir') { // empty folder, force to be folder
            $temp['state']    = 'closed';
            $temp['children'] = [['text'=>lang('msg_no_documents')]];
        }
        $output[] = $temp;
    }
    return $output;
}

function trimTree(&$data)
{
    if (!isset($data['children'])) { return; } // leaf
    $allEmpty = true;
    foreach ($data['children'] as $idx => $child) {
        $childEmpty = true;
        $attr = !empty($data['children'][$idx]['attributes']) ? json_decode($data['children'][$idx]['attributes'], true) : [];
        if (isset($attr['mime_type']) && $attr['mime_type']=='dir') {
            msgDebug("\nTrimming branch {$child['text']}");
            trimTree($data['children'][$idx]);
        }
        if (!empty($data['children'][$idx]['id'])) { $childEmpty = $allEmpty = false; }
        if ($childEmpty) { unset($data['children'][$idx]); }
    }
    if ($allEmpty) {
        msgDebug("\nBranch {$data['text']} is empty unsetting id.");
        $data = ['id'=>false, 'children'=>[]];
    }
    $data['children'] = array_values($data['children']);
}

function viewTaxTitle($value)
{
    if (empty($GLOBALS['taxDB'])) {
        $tax_rates= dbGetMulti(BIZUNO_DB_PREFIX."tax_rates");
        foreach ($tax_rates as $row) { $GLOBALS['taxDB'][$row['id']] = $row; }
    }
    return !empty($GLOBALS['taxDB'][$value]['title']) ? $GLOBALS['taxDB'][$value]['title'] : $value;
}

/**
 * Generates the textual display of payment terms from the encoded value
 * @param string $terms_encoded - Encoded terms to use as source data
 * @param boolean $short - (Default: true) if true, generates terms in short form, otherwise long form
 * @param type $type - (Default: c) Contact type, c - Customers, v - Vendors
 * @param type $inc_limit - (Default: false) Include the Credit Limit in the text as well
 * @return string
 */
function viewTerms($terms_encoded='', $short=true, $type='c', $inc_limit=false)
{
    if ($type=='id') { // type == id for cID passed
        $cID = intval($terms_encoded);
        $result = dbGetValue(BIZUNO_DB_PREFIX.'contacts', ['terms','type'], "id=$cID");
        if (empty($result)) { return 'N/A'; }
        $type = $result['type'];
        $terms_encoded = $result['terms'];
    }
    $idx = $type=='v' ? 'vendors' : 'customers';
    $terms_def = explode(':', getModuleCache('phreebooks', 'settings', $idx, 'terms'));
    if (!$terms_encoded) { $terms = $terms_def; }
    else                 { $terms = explode(':', $terms_encoded); }
    $credit_limit = isset($terms[4]) ? $terms[4] : (isset($terms_def[4]) ? $terms_def[4] : 1000);
    if ($terms[0]==0) { $terms = $terms_def; }
    $output = '';
    switch ($terms[0]) {
        default:
        case '0': // Default terms
        case '3': // Special terms
            if ((isset($terms[1]) || isset($terms[2])) && $terms[1]) { $output = sprintf($short ? lang('contacts_terms_discount_short') : lang('contacts_terms_discount'), $terms[1], $terms[2]).' '; }
            if (!isset($terms[3])) { $terms[3] = 30; }
            $output .=  sprintf($short ? lang('contacts_terms_net_short') : lang('contacts_terms_net'), $terms[3]);
            break;
        case '1': $output = lang('contacts_terms_cod');     break; // Cash on Delivery (COD)
        case '2': $output = lang('contacts_terms_prepaid'); break; // Prepaid
        case '4': $output = sprintf(lang('contacts_terms_date'), viewFormat($terms[3], 'date')); break; // Due on date
        case '5': $output = lang('contacts_terms_eom');     break; // Due at end of month
        case '6': $output = lang('contacts_terms_now');     break; // Due upon receipt
    }
    if ($inc_limit) { $output .= ' '.lang('contacts_terms_credit_limit').' '.viewFormat($credit_limit, 'currency'); }
    return $output;
}

/**
 * ISO source is always the default currency as all values are stored that way. Setting isoDest forces the default to be converted to that ISO
 * @global object $currencies - details of the ISO currency ->iso and ->rate
 * @param float $value
 * @param string $format - How to format the data
 * @return string - Formatted data to $currencies->iso if specified, else default currency
 */
function viewCurrency($value, $format='currency')
{
    global $currencies;
    if ($format=='curNull0' && (float)$value == 0) { return ''; }
    if (!is_numeric($value)) { return $value; }
    $isoDef = getDefaultCurrency();
    $iso    = !empty($currencies->iso)  ? $currencies->iso  : $isoDef;
    $isoVals= getModuleCache('phreebooks', 'currency', 'iso', $iso);
    if (empty($isoVals)) { $isoVals = ['dec_pt'=>'.', 'dec_len'=>2, 'sep'=>',', 'prefix'=>'$', 'suffix'=>'']; } // when not logged in default to USD
    $rate   = !empty($currencies->rate) ? $currencies->rate : ($iso==$isoDef ? 1 : $isoVals['value']);
    $newNum = number_format($value * $rate, $isoVals['dec_len'], $isoVals['dec_pt'], $isoVals['sep']);
    $zero   = number_format(0, $isoVals['dec_len']); // to handle -0.00
    if ($newNum == '-'.$zero)       { $newNum  = $zero; }
    if (!empty($isoVals['prefix'])) { $newNum  = $isoVals['prefix'].' '.$newNum; }
    if (!empty($isoVals['suffix'])) { $newNum .= ' '.$isoVals['suffix']; }
    msgDebug("\nviewCurrency default: $isoDef, used: $iso, rate: $rate, starting value = $value, ending value $newNum");
    return $newNum;
}

/**
 * This function builds the currency drop down based on the locale XML file.
 * @return multitype:multitype:NULL
 */
function viewCurrencySel($curData=[])
{
    $output = [];
    if (empty($curData)) { $curData= localeLoadDB(); }
    foreach ($curData->Locale as $value) {
        if (isset($value->Currency->ISO)) {
            $output[$value->Currency->ISO] = ['id'=>$value->Currency->ISO, 'text'=>$value->Currency->Title];
        }
    }
    return sortOrder($output, 'text');
}

function viewTimeZoneSel($locale=[])
{
    $zones = [];
    if (empty($locale)) { $locale= localeLoadDB(); }
    foreach ($locale->Timezone as $value) {
        $zones[] = ['id' => $value->Code, 'text'=> $value->Description];
    }
    return $zones;
}

function viewRoles($none=true, $inactive=false)
{
    $result = dbGetMulti(BIZUNO_DB_PREFIX.'roles', $inactive ? '' : "inactive='0'", 'title', ['id','title']);
    foreach ($result as $row) { $output[] = ['id'=>$row['id'], 'text'=>$row['title']]; }
    if ($none) { array_unshift($output, ['id'=>'0', 'text'=>lang('none')]); }
    return $output;
}

/**
 * This function build a drop down array of users based on their assigned role
 * @param string $type (Default -> sales) - The role type to build list from, set to all for all users
 * @param boolean $inactive (Default - false) - Whether or not to include inactive users
 * @param string $source - where to pull the id from, [default] contacts (contacts table record id), users (users table admin_id)
 * @return array $output - formatted result ready for drop down field values
 */
function viewRoleDropdown($type='sales', $inactive=false, $source='contacts')
{
    $result = dbGetMulti(BIZUNO_DB_PREFIX.'roles', $inactive ? '' : "inactive='0'");
    $roleIDs= [];
    foreach ($result as $row) {
        $settings = json_decode($row['settings'], true);
        if ($type=='all' || !empty($settings['bizuno']['roles'][$type])) { $roleIDs[] = $row['id']; }
    }
    $output = [];
    if (sizeof($roleIDs) > 0) {
        $result = dbGetMulti(BIZUNO_DB_PREFIX."users", "role_id IN (".implode(',', $roleIDs).")".($inactive ? '' : " AND inactive='0'"));
        foreach ($result as $row) {
            $rID = $source=='users' ? $row['admin_id'] : $row['contact_id'];
            if ($rID) { $output[] = ['id'=>$rID, 'text'=>$row['title']]; } // skip if no id
        }
    }
    $ordered = sortOrder($output, 'text');
    array_unshift($ordered, ['id'=>'0', 'text'=>lang('none')]);
    return $ordered;
}

/**
 * This function builds a drop down for sales tax selection drop down menus
 * @param string $type - Choices are [default] 'c' for customers or 'v' for vendors
 * @param string $opts - Choices are NULL, 'contacts' for Per Contact option or 'inventory' for Per Inventory item option
 * @return array - result ready for render
 */
function viewSalesTaxDropdown($type='c', $opts='')
{
    $output = [];
    if ($opts=='contacts')  { $output[] = ['id'=>'-1', 'text'=>lang('per_contact'),  'status'=>0, 'tax_rate'=>'-']; }
    if ($opts=='inventory') { $output[] = ['id'=>'-1', 'text'=>lang('per_inventory'),'status'=>0, 'tax_rate'=>'-']; }
    $output[] = ['id'=>'0', 'text'=>lang('none'), 'status'=>0, 'tax_rate'=>0];
    foreach (getModuleCache('phreebooks', 'sales_tax', $type, false, []) as $row) {
        if ($row['status'] == 0) { $output[] = ['id'=>$row['id'], 'text'=>$row['title'], 'status'=>$row['status'], 'tax_rate'=>$row['rate']]; }
    }
    return $output;
}

/**
 * Takes a number in full integer style and converts to short hand format MB, GB, etc.
 * @param string $path - Full path to the file including the users root folder (since the path is not part of the returned value)
 * @return string - Textual string in block size format
 */
function viewFilesize($path)
{
    $bytes = sprintf('%u', filesize($path));
    if ($bytes > 0) {
        $unit = intval(log($bytes, 1024));
        $units = ['B', 'KB', 'MB', 'GB'];
        if (array_key_exists($unit, $units) === true) { return sprintf('%d %s', $bytes / pow(1024, $unit), $units[$unit]); }
    }
    return $bytes;
}

/**
 * Takes a file extension and tries to determine the MIME type to assign to it.
 * @param string $type - extension of the file
 * @return string - MIME type code
 */
function viewMimeIcon($type)
{
    $icon = strtoupper($type);
    switch ($icon) {
        case 'DRW':
        case 'JPG':
        case 'JPEG':
        case 'GIF':
        case 'PNG': return 'mimeImg';
        case 'DIR': return 'mimeDir';
        case 'DOC':
        case 'FRM': return 'mimeDoc';
        case 'DRW': return 'mimeDrw';
        case 'PDF': return 'mimePdf';
        case 'PPT': return 'mimePpt';
        case 'ODS':
        case 'XLS': return 'mimeXls';
        case 'ZIP': return 'mimeZip';
        case 'HTM':
        case 'HTML':return 'mimeHtml';
        case 'PHP':
        case 'RPT':
        case 'TXT':
        default:    return 'mimeTxt';
    }
}

/**
 * This function builds the core structure for rendering HTML pages. It will include the menu and footer
 * @return array $data - structure for rendering HTML pages with header and footer
 */
function viewMain()
{
    global $html5;
    $menuID = clean('menuID', ['format'=>'cmd', 'default'=>'home'], 'get');
    switch ($GLOBALS['myDevice']) {
        case 'mobile':  $data = $html5->layoutMobile($menuID);  break;
        case 'tablet': // use desktop layout as the screen is big enough
        default:
        case 'desktop': $data = $html5->layoutDesktop($menuID); break;
    }
    if (!getUserCache('profile', 'biz_id')) { unset($data['header'], $data['footer']); }
    return $data;
}

/**
 * Generates the main view for modules settings and properties. If the module has settings, the structure will be generated here as well
 * @param string $module - Module ID
 * @param array $structure - Current working structure, Typically will be empty array
 * @param string $lang -
 * @return array - Newly formed layout
 */
function adminStructure($module, $structure=[], $lang=[])
{
    $props= getModuleCache($module, 'properties');
    msgDebug("\nmodule $module properties = ".print_r($props, true));
    $title= $props['title'].' - '.lang('settings');
    $data = ['title'=>$title, 'statsModule'=>$module, 'security'=>getUserCache('security', 'admin', false, 0),
        'divs'    => [
            'heading'=> ['order'=>30,'type'=>'html','html'=>html5('',['icon'=>'back','events'=>['onClick'=>"hrefClick('bizuno/settings/manager');"]])."<h1>$title</h1>"],
            'main'   => ['order'=>50,'type'=>'tabs','key'=>'tabAdmin']],
        'toolbars'=> ['tbAdmin' =>['icons'=>['save'=>['order'=>20,'events'=>['onClick'=>"jq('#frmAdmin').submit();"]]]]],
        'forms'   => ['frmAdmin'=>['attr'=>['type'=>'form', 'action'=>BIZUNO_AJAX."&p=$module/admin/adminSave"]]],
        'tabs'    => ['tabAdmin'=>['divs'=>['settings'=>['order'=>10,'label'=>lang('settings'),'type'=>'divs','divs'=>[
            'toolbar'=> ['order'=>10,'type'=>'toolbar',  'key' =>'tbAdmin'],
            'formBOF'=> ['order'=>15,'type'=>'form',     'key' =>'frmAdmin'],
            'body'   => ['order'=>50,'type'=>'accordion','key' =>'accSettings'],
            'formEOF'=> ['order'=>85,'type'=>'html',     'html'=>"</form>"]]]]]],
        'jsReady'=>['init'=>"ajaxForm('frmAdmin');"]];
    if (!empty($structure)) { adminSettings($data, $structure, $lang); }
    else                    { unset($data['tabs']['tabAdmin']['divs']['settings'], $data['jsReady']['init']); }
    $methDirs = ['dashboards'];
    if     (isset($props['dirMethods']) && is_array($props['dirMethods'])) { $methDirs = array_merge($methDirs, $props['dirMethods']); }
    elseif (!empty($props['dirMethods']))  { $methDirs[] = $props['dirMethods']; }
    $order = 70;
    foreach ($methDirs as $folder) { // keys = 'dashboards','totals','carriers', etc.
        $methProps = getModuleCache($module, $folder);
        if (empty($methProps)) { continue; }
        msgDebug("\nReady to process folder $folder and props = ".print_r($methProps, true));
        $html = adminMethods($module, $methProps, $folder);
        $data['tabs']['tabAdmin']['divs']['tab'.$folder] = ['order'=>$order,'label'=>lang($folder),'type'=>'html','html'=>$html];
        $order++;
    }
    return array_replace_recursive(viewMain(), $data);


}

function adminSettings(&$data, $structure, $lang)
{
    $order = 50;
    foreach ($structure as $category => $entry) {
        $data['accordion']['accSettings']['divs'][$category] = ['order'=>$order,'ui'=>'none','label'=>$entry['label'],'type'=>'list','key'=>$category];
        if (empty($entry['fields'])) { continue; }
        foreach ($entry['fields'] as $key => $props) {
            $props['attr']['id'] = $category."_".$key;
            if ( empty($props['attr']['type'])){ $props['attr']['type']= 'text'; }
            if ( empty($props['langKey']))     { $props['langKey']     = $key; }
            if ($props['attr']['type']=='password') { $props['attr']['value']= ''; }
            $label = isset($props['label'])? $props['label']: lang($key);
            $tip   = isset($props['tip'])  ? $props['tip']  : (isset($lang['set_'.$key]) ? $lang['set_'.$key] : '');
            $props['label']= !empty($lang[$props['langKey']."_lbl"]) ? $lang[$props['langKey']."_lbl"] : $label;
            $props['tip']  = !empty($lang[$props['langKey']."_tip"]) ? $lang[$props['langKey']."_tip"] : $tip;
            $props['desc'] = !empty($lang[$props['langKey']."_desc"])? $lang[$props['langKey']."_desc"]: '';
            $data['lists'][$category][$key] = $props;
        }
        $order++;
    }
}

/**
 * Generates the view for modules methods including any dashboards
 * @param string $module - module or extension id
 * @param array $props - module properties from cache
 * @param string $key - id of the folder to build view
 * @return array - HTML code for the structure
 */
function adminMethods($module, $props, $key)
{
    $security = validateSecurity('bizuno', 'admin', 1);
    $fields = [
        'btnMethodAdd' => ['attr'=>['type'=>'button','value'=>lang('install')],'hidden'=>$security>1?false:true],
        'btnMethodDel' => ['attr'=>['type'=>'button','value'=>lang('remove')], 'hidden'=>$security>4?false:true],
        'btnMethodProp'=> ['icon'=>'settings'],
        'settingSave'  => ['icon'=>'save']];
    $html  = '<table style="border-collapse:collapse;width:100%">'."\n".' <thead class="panel-header">'."\n";
    $title = $key == 'dashboards' ? lang('dashboard') : lang('method');
    $html .= "  <tr><th>&nbsp;</th><th>$title</th><th>".lang('description')."</th><th>".lang('action')."</th></tr>\n </thead>\n <tbody>\n";
    foreach ($props as $method => $settings) {
        $fqcn = "\\bizuno\\$method";
        bizAutoLoad("{$settings['path']}$method.php", $fqcn);
        if (empty($settings['settings'])) { $settings['settings'] = []; }
        $clsMeth = new $fqcn($settings['settings']);
        if (isset($clsMeth->hidden) && $clsMeth->hidden) { continue; }
        $html .= "  <tr>\n";
        $html .= '    <td valign="top">'.htmlFindImage($settings, "32")."</td>\n";
        $html .= '    <td valign="top" '.($settings['status'] ? ' style="background-color:lightgreen"' : '').">".$settings['title'].'</td>';
        $html .= "    <td><div>".$settings['description'];
        if ($key <> 'dashboards' && !$settings['status'] && $security > 1) {
            $html .= "</div></td>\n";
            $fields['btnMethodAdd']['events']['onClick'] = "jsonAction('bizuno/settings/methodInstall&module=$module&path=$key&method=$method');";
            $html .= '    <td valign="top" style="text-align:right;">'.html5('install_'.$method, $fields['btnMethodAdd'])."</td>\n";
        } else {
            $html .= "</div>";
            $html .= '<div id="divMethod_'.$method.'" style="display:none;" class="layout-expand-over">';
            $html .= html5("frmMethod_$method", ['attr'=>['type'=>'form','action'=>BIZUNO_AJAX."&p=bizuno/settings/methodSettingsSave&module=$module&type=$key&method=$method"]]);
            $structure = method_exists($clsMeth, 'settingsStructure') ? $clsMeth->settingsStructure() : [];
            foreach ($structure as $setting => $values) {
                $mult = isset($values['attr']['multiple']) ? '[]' : '';
                if (isset($values['attr']['multiple'])) { $values['attr']['value'] = explode(':', $values['attr']['value']); }
                $html .= html5($method.'_'.$setting.$mult, $values)."<br />\n";
            }
            $fields['settingSave']['events']['onClick'] = "jq('#frmMethod_".$method."').submit();";
            $html  .= '<div style="text-align:right">'.html5('imgMethod_'.$method, $fields['settingSave']).'</div>';
            $html  .= "</form></div>";
            htmlQueue("ajaxForm('frmMethod_$method');", 'jsReady');
            $html  .= "</td>\n";
            $html  .= '<td valign="top" nowrap="nowrap" style="text-align:right;">' . "\n";
            $fields['btnMethodDel']['events']['onClick'] = "if (confirm('".lang('msg_method_delete_confirm')."')) jsonAction('bizuno/settings/methodRemove&module=$module&type=$key&method=$method');";
            if ($security>4 && $key<>'dashboards' && empty($clsMeth->required)) {
                $html .= html5('remove_'.$method, $fields['btnMethodDel']) . "\n";
            }
            $fields['btnMethodProp']['events']['onClick'] = "jq('#divMethod_{$method}').toggle('slow');";
            $html .= html5('prop_'.$method, $fields['btnMethodProp'])."\n";
            $html .= "</td>\n";
        }
        $html .= "  </tr>\n";
        $html .= '<tr><td colspan="5"><hr /></td></tr>'."\n";
    }
    $html .= " </tbody>\n</table>\n";
    return $html;
}

/**
 * Builds the HTML for custom tabs, sorts, generates structure
 * @param array $data - Current working layout to modify
 * @param array $structure - Current structure to process data
 * @param string $module - Module ID
 * @param string $tabID - id of the tab container to insert tabs
 * @return string - Updated $data with custom tabs HTML added
 */
function customTabs(&$data, $module, $tabID)
{
    $tabs = getModuleCache($module, 'tabs');
    if (empty($tabs)) { return; }
    $data['fields'] = sortOrder($data['fields']);
    foreach ($data['fields'] as $key => $field) { // gather by groups
        if (isset($field['tab']) && $field['tab'] > 0) { $tabs[$field['tab']]['groups'][$field['group']]['fields'][$key] = $field; }
    }
    foreach ($tabs as $tID => $tab) {
        if (!isset($tab['groups'])) { continue; }
        if (!isset($tab['title'])) { $tab['title'] = 'Untitled'; }
        if (!isset($tab['group'])) { $tab['group'] = $tab['title']; }
        $groups = sortOrder($tab['groups']);
        $data['tabs'][$tabID]['divs']["tab_$tID"] = ['order'=>isset($tab['sort_order']) ? $tab['sort_order'] : 50,'label'=>$tab['title'],'type'=>'divs','classes'=>['areaView']];
        foreach ($groups as $gID =>$group) {
            if (empty($group['fields'])) { continue; }
            $keys = [];
            $title = isset($group['title']) ? $group['title'] : $gID;
            foreach ($group['fields'] as $fID => $field) {
                $keys[] = $fID;
                switch($field['attr']['type']) {
                    case 'radio':
                        $cur = isset($data['fields'][$fID]['attr']['value']) ? $data['fields'][$fID]['attr']['value'] : '';
                        foreach ($field['opts'] as $elem) {
                            $data['fields'][$fID]['attr']['value'] = $elem['id'];
                            $data['fields'][$fID]['attr']['checked'] = $cur == $elem['id'] ? true : false;
                            $data['fields'][$fID]['label'] = $elem['text'];
                        }
                        break;
                    case 'select': $data['fields'][$fID]['values'] = $field['opts']; // set the choices and render
                    default:
                }
            }
            $data['tabs'][$tabID]['divs']["tab_$tID"]['divs']["{$tID}_{$gID}"] = ['order'=>10,'type'=>'panel','classes'=>['block33'],'key'=>"tab_{$tID}_{$gID}"];
            $data['panels']["tab_{$tID}_{$gID}"] = ['label'=>$title,'type'=>'fields','keys'=>$keys];
        }
    }
    msgDebug("\nstructure = ".print_r($data, true));
}

/**
 * This function builds an HTML element based on the properties passed, element of type INPUT is the default if not specified
 * @param string $id - becomes the DOM id and name of the element
 * @param array $prop - structure of the HTML element
 * @return string - HTML5 compatible element
 */
function html5($id='', $prop=[])
{
    global $html5;
    return $html5->render($id, $prop);
}

/**
 * Adds HTML content to the specified queue in preparation to render.
 * @global class $html5 - UI HTML render class
 * @param string $html - Content to add to queue
 * @param type $type - [default: body] Which queue to use, choices are: jsHead, jsBody, jsReady, jsResize, and body
 */
function htmlQueue($html, $type='body')
{
    global $html5;
    switch ($type) {
        case 'jsHead':   $html5->jsHead[]  = $html; break;
        case 'jsBody':   $html5->jsBody[]  = $html; break;
        case 'jsReady':  $html5->jsReady[] = $html; break;
        case 'jsResize': $html5->jsResize[]= $html; break;
        default:
        case 'body':     $this->html .= $html; break;
    }
}

/**
 * Searches a given directory for a filename match and generates HTML if found
 * @param string $path - path from the users root to search
 * @param string $filename - File name to search for
 * @param integer $height - Height of the image, width is auto-sized by the browser
 * @return string - HTML of image
 */
function htmlFindImage($settings, $height=32)
{
    if (!isset($settings['path']) || !is_dir($settings['path'])) { return ''; }
    $files = scandir($settings['path']);
    if (!$files) { return ''; }
    foreach ($files as $file) {
        $ext = substr($file, strrpos($file, '.')+1);
        if (in_array(strtolower($ext), ['gif', 'jpg', 'jpeg', 'png']) && $file == "{$settings['id']}.$ext") {
            return html5('', ['attr'=>['type'=>'img','src'=>"{$settings['url']}$file", 'height'=>$height]]);
        }
    }
    return '';
}

/**
* This function builds the combo box editor HTML for the country list
 * @return string set the editor structure
 */
function htmlComboContact($id, $props=[])
{
    $defaults = ['type'=>'c','store'=>false,'callback'=>'contactsDetail','opt1'=>'b','opt2'=>'']; // opt1=>suffux, opt2=>fill
    $attr = array_replace($defaults, $props);
    return html5($id, ['label'=>lang('search'),'classes'=>['easyui-combogrid'],'attr'=>['data-options'=>"
        width:130, panelWidth:750, delay:900, idField:'id', textField:'primary_name', mode: 'remote',
        url:'".BIZUNO_AJAX."&p=contacts/main/managerRows&clr=1&type={$attr['type']}&store=".($attr['store']?'1':'0')."',
        onBeforeLoad:function (param) { var newValue=jq('#$id').combogrid('getValue'); if (newValue.length < 3) { return false; } },
        selectOnNavigation:false,
        onClickRow:  function (idx, row){ {$attr['callback']}(row, '{$attr['opt1']}', '{$attr['opt2']}'); },
        columns: [[{field:'id', hidden:true},{field:'email', hidden:true},
            {field:'short_name',  title:'".jsLang('contacts_short_name')."', width:100},
            {field:'type',        hidden:".(strlen($attr['type'])>1?'false':'true').",title:'".jsLang('contacts_type')."', width:100},
            {field:'primary_name',title:'".jsLang('address_book_primary_name')."', width:200},
            {field:'address1',    title:'".jsLang('address_book_address1')."', width:100},
            {field:'city',        title:'".jsLang('address_book_city')."', width:100},
            {field:'state',       title:'".jsLang('address_book_state')."', width: 50},
            {field:'postal_code', title:'".jsLang('address_book_postal_code')."', width:100},
            {field:'telephone1',  title:'".jsLang('address_book_telephone1')."', width:100}]]"]]);
}

/**
 * This function builds the combo box editor HTML for a datagrid to view GL Accounts
 * @return string set the editor structure
 */
function dgHtmlGLAcctData()
{
    return "{type:'combogrid',options:{ data:pbChart, mode:'local', width:300, panelWidth:450, idField:'id', textField:'title',
inputEvents:jq.extend({},jq.fn.combogrid.defaults.inputEvents,{ keyup:function(e){ glComboSearch(jq(this).val()); } }),
rowStyler:  function(index,row){ if (row.inactive=='1') { return { class:'row-inactive' }; } },
columns:    [[{field:'id',title:'".jsLang('gl_account')."',width:80},{field:'title',title:'".jsLang('title')."',width:200},{field:'type',title:'".jsLang('type')."',width:160}]]}}";
}

/**
 *
 * @param type $id
 * @param type $field
 * @param type $type
 * @param type $xClicks
 * @return type
 */
function dgHtmlTaxData($id, $field, $type='c', $xClicks='')
{
    return "{type:'combogrid',options:{data: bizDefaults.taxRates.$type.rows,width:120,panelWidth:210,idField:'id',textField:'text',
        onClickRow:function (idx, data) { jq('#$id').edatagrid('getRows')[curIndex]['$field'] = data.id; $xClicks },
        rowStyler:function(idx, row) { if (row.status==1) { return {class:'journal-waiting'}; } else if (row.status==2) { return {class:'row-inactive'}; }  },
        columns: [[{field:'id',hidden:true},{field:'text',width:120,title:'".jsLang('journal_main_tax_rate_id')."'},{field:'tax_rate',width:70,title:'".jsLang('amount')."',align:'center'}]]
    }}";
}

/**
 * This function formats database data into a JavaScript array
 * @param array $dbData - raw data from database of rows matching given criteria
 * @param string $name - JavaScript variable name linked to the grid to populate with data
 * @param array $structure - used for identifying the formatting of data prior to building the string
 * @param array $override - map to replace database field name to the grid column name
 * @return string $output - JavaScript string of data used to populate grids
 */
function formatDatagrid($dbData, $name, $structure=[], $override=[])
{
    $rows = [];
    if (is_array($dbData)) {
        foreach ($dbData as $row) {
            $temp = [];
            foreach ($row as $field => $value) {
                if (isset($override[$field])) {
                    msgDebug("\nExecuting override = {$override[$field]['type']}");
                    switch ($override[$field]['type']) {
                        case 'trash': $field = false; break;
                        case 'field': $field = $override[$field]['index']; break;
                        default:
                    }
                }
                if (is_array($value) || is_object($value))     { $value = json_encode($value); }
                if (isset($structure[$field]['attr']['type'])) {
                    if ($structure[$field]['attr']['type'] == 'currency') { $structure[$field]['attr']['type'] = 'float'; }
                    $value = viewFormat($value, $structure[$field]['attr']['type']);
                }
                if (!empty($field)) { $temp[$field] = $value; }
            }
            $rows[] = $temp;
        }
    }
    return "var $name = ".json_encode(['total'=>sizeof($rows), 'rows'=>$rows]).";\n";
}
