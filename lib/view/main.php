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
 * @copyright  2008-2019, PhreeSoft, Inc.
 * @license    http://opensource.org/licenses/OSL-3.0  Open Software License (OSL 3.0)
 * @version    3.x Last Update: 2019-03-27
 * @filesource /view/main.php
 */

namespace bizuno;

bizAutoLoad(BIZUNO_ROOT."portal/view.php", 'portalView');

final class view extends portalView
{
    function __construct($data=[])
    {
        // declare global data until all modules are converted to new nested structure
        global $viewData;
        $viewData = $data;
        $this->output = ['head'=>[],'jsHead'=>[],'body'=>'','jsBody'=>[],'jsReady'=>[],'jsResize'=>[],'raw'=>''];
        parent::__construct();
        $this->render($data);
    }

    /**
     * Main function to take the layout and build the HTML/AJAX response
     * @global array $msgStack - Any messages that had been set during processing
     * @param array $data - The layout to render from
     * @return string - Either HTML or JSON depending on expected response
     */
    private function render($data=[])
    {
        global $msgStack;
        dbWriteCache();
        $type = !empty($data['type']) ? $data['type'] : 'json';
        switch ($type) {
            case 'datagrid':
                $content = dbTableRead($data['structure']);
                $content['message'] = $msgStack->error;
                msgDebug("\n datagrid results = ".print_r($content, true));
                echo json_encode($content);
                $msgStack->debugWrite();
                exit();
            case 'divHTML':
                $this->renderDivs($data);
                $this->renderJS($data); // add the javascript
                msgDebug("\n sending type: divHTML and data = {$this->output['body']}");
                echo $this->output['body'];
                $msgStack->debugWrite();
                exit();
            case 'page':
                $this->setEnvHTML($this->output, $data);
                $this->renderJS($data);
                $this->renderDOM($this->output);
                $msgStack->debugWrite();
                break;
            case 'raw':
                msgDebug("\n sending type: raw and data = {$data['content']}");
                echo $data['content'];
                $msgStack->debugWrite();
                exit();
            case 'xml': // Don't think this is ever used
                $xml = new SimpleXMLElement('<root/>');
                array_walk_recursive($data['content'], [$xml, 'addChild']);
                echo createXmlHeader() . $xml . createXmlFooter();
                $msgStack->debugWrite();
                exit();
            case 'popup': $this->renderPopup($data); // make layout changes per device
            case 'json':
            default:
                if (isset($data['action'])){ $data['content']['action']= $data['action']; }
                if (isset($data['divID'])) { $data['content']['divID'] = $data['divID']; }
                if (isset($data['divs']))  { $this->renderDivs($data); }
                $this->renderJS($data, false);
                $data['content']['html'] = empty($data['content']['html']) ? $this->output['body'] : $data['content']['html'].$this->output['body'];
                $data['content']['message'] = $msgStack->error;
                msgDebug("\n json return (before encoding) = ".print_r($data['content'], true));
                echo json_encode($data['content']);
                $msgStack->debugWrite();
                exit();
        }
    }

    private function renderPopup(&$data)
    {
        global $html5;
        switch($GLOBALS['myDevice']) {
            case 'mobile': // set a new panel
                // need container div with mobile panel
                $divsTemp = $data['divs'];
                $header   = ['title'=>$data['title'],'left'=>['close'],'right'=>[]];
                $data['divs'] = [
                    'header'=> ['order'=>  1,'type'=>'html','html'=>$html5->mobileMenu($header, 'header')],
                    'bodyS' => ['order'=>  2,'type'=>'html','html'=>"\n".'<div class="easyui-panel"><!-- start body -->'."\n"],
                    'body'  => ['order'=> 50,'type'=>'divs','divs'=>[$divsTemp]],
                    'bodyE' => ['order'=> 98,'type'=>'html','html'=>"\n</div><!-- end body -->\n\n"],
                    'footer'=> ['order'=> 99,'type'=>'html','html'=>$html5->mobileMenu([], 'footer')]];
                $data['divs'] = array_merge($data['divs'], $divsTemp);
                $data['jsReady'][] = "jq.mobile.go('#navPopup'); jq.parser.parse('#navPopup');"; // load the div, init easyui components
                $data['content']['action'] = 'newDiv';
                break;
            case 'tablet':
            case 'desktop': // set a javascript popup window
            default:
                $data['content']['action'] = 'window';
                $data['content']['title'] = $data['title'];
                $data['content'] = array_merge($data['content'], $data['attr']);
        }
    }

    private function renderDivs($data)
    {
        global $html5;
        if (!isset($data['divs'])) { return; }
        msgDebug("\nEntering renderDivs");
        $html5->buildDivs($this->output, $data);
    }

    private function renderJS($data, $addMsg=true)
    {
        global $html5;
        msgDebug("\nEntering renderJS");
        if (!isset($data['jsHead']))   { $data['jsHead']  = []; }
        if (!isset($data['jsBody']))   { $data['jsBody']  = []; }
        if (!isset($data['jsReady']))  { $data['jsReady'] = []; }
        if (!isset($data['jsResize'])) { $data['jsResize']= []; }
        // gather everything together
        $jsHead  = array_merge($this->output['jsHead'],  $data['jsHead'],  $html5->jsHead);
        $jsBody  = array_merge($this->output['jsBody'],  $data['jsBody'],  $html5->jsBody);
        $jsReady = array_merge($this->output['jsReady'], $data['jsReady'], $html5->jsReady);
        $jsResize= array_merge($this->output['jsResize'],$data['jsResize'],$html5->jsResize);
        if (sizeof($jsResize)) { $jsReady['reSize'] = "var windowWidth = jq(window).width();\njq(window).resize(function() { if (jq(window).width() != windowWidth) { windowWidth = jq(window).width(); ".implode(" ", $jsResize)." } });"; }
        if ($addMsg) { $jsReady['msgStack'] = $html5->addMsgStack(); }
        // Render the output
        if (sizeof($jsHead)) { // first
            $this->output['body'] .= '<script type="text/javascript">'."\n".implode("\n", $jsHead)."\n</script>\n";
        }
        if (sizeof($jsBody)) { // second
            $this->output['body'] .= '<script type="text/javascript">'."\n".implode("\n", $jsBody)."\n</script>\n";
        }
        if (sizeof($jsReady)) { // doc ready, last
            $this->output['body'] .= '<script type="text/javascript">'."jq(document).ready(function() {\n".implode("\n", $jsReady)."\n});\n</script>\n";
        }
    }
}

/**
 * Formats a system value to the locale view format
 * @global array $currencies
 * @param mixed $value - value to be formatted
 * @param string $format - Specifies the formatting ot apply
 * @return string
 */
function viewFormat($value, $format = '')
{
    global $currencies, $bizunoLang;
//  msgDebug("\nIn viewFormat value = $value and format = $format");
    switch ($format) {
        case 'blank':      return '';
        case 'blankNull':  return $value ? $value : '';
        case 'contactID':
            return ($result = dbGetValue(BIZUNO_DB_PREFIX."contacts", 'short_name', "id='$value'")) ? $result : getModuleCache('bizuno', 'settings', 'company', 'id');
        case 'contactName':if (!$value) { return ''; }
            $result = dbGetValue(BIZUNO_DB_PREFIX."address_book", 'primary_name', "ref_id='$value' AND type='m'");
            return $result ? $result : '';
        case 'contactType':return pullTableLabel(BIZUNO_DB_PREFIX."contacts", 'type', $value);
        case 'curNull0':
        case 'currency':
        case 'curLong':
        case 'curExc':     return viewCurrency($value, $format);
        case 'date':
        case 'datetime':   return viewDate($value);
        case 'encryptName':if (!getUserCache('profile', 'admin_encrypt')) { return ''; }
            bizAutoLoad(BIZUNO_LIB."model/encrypter.php", 'encryption');
            $enc = new encryption();
            $result = $enc->decrypt(getUserCache('profile', 'admin_encrypt'), $value);
            msgDebug("\nDecrypted: ".print_r($result, true));
            $values = explode(':', $result);
            return is_array($values) ? $values[0] : '';
        case 'glActive':  return !empty(getModuleCache('phreebooks', 'chart', 'accounts', $value, '')['inactive']) ? lang('yes') : '';
        case 'glType':    return lang('gl_acct_type_'.$value);
        case 'glTitle':   return getModuleCache('phreebooks', 'chart', 'accounts', $value, $value)['title'];
        case 'inv_sku':      $result = dbGetValue(BIZUNO_DB_PREFIX."inventory", 'sku', "id='$value'");
            return $result ? $result : $value;
        case 'inv_image': $result = dbGetValue(BIZUNO_DB_PREFIX."inventory", 'image_with_path', "id='$value'"); // when user is logged in, internal access only
            return $result ? BIZUNO_DATA."images/$result" : '';
        case 'inv_mv0':   $range = 'm0';
        case 'inv_mv1':   if (empty($range)) { $range = 'm1'; }
        case 'inv_mv3':   if (empty($range)) { $range = 'm3'; }
        case 'inv_mv6':   if (empty($range)) { $range = 'm6'; }
        case 'inv_mv12':  if (empty($range)) { $range = 'm12';}
        case 'inv_mvmnt': if (empty($range)) { $range = 'm12';} // @todo REMOVED AFTER 3/31/2019, deprecated case, replaced with inv_mv12
                          return viewInvSales($value, $range); // value passed should be the SKU
        case 'inv_stk':   return viewInvMinStk($value); // value passed should be the SKU
        case 'lc':        return mb_strtolower($value);
        case 'j_desc':    return isset($bizunoLang["journal_main_journal_id_$value"]) ? $bizunoLang["journal_main_journal_id_$value"] : $value;
        case 'json':      return json_decode($value, true);
        case 'neg':       return -$value;
        case 'n2wrd':     return bizAutoLoad(BIZUNO_LIB."locale/".getUserCache('profile', 'language', false, 'en_US')."/functions.php") ? viewCurrencyToWords($value) : $value;
        case 'null0':     return (round((real)$value, 4) == 0) ? '' : $value;
        case 'number':    return number_format((float)$value, getModuleCache('bizuno', 'settings', 'locale', 'number_precision'), getModuleCache('bizuno', 'settings', 'locale', 'number_decimal'), getModuleCache('bizuno', 'settings', 'locale', 'number_thousand'));
        case 'printed':   return $value ? '' : lang('duplicate');
        case 'precise':   $output = number_format((real)$value, getModuleCache('bizuno', 'settings', 'locale', 'number_precision'));
            $zero = number_format(0, getModuleCache('bizuno', 'settings', 'locale', 'number_precision')); // to handle -0.00
            return ($output == '-'.$zero) ? $zero : $output;
        case 'rep_id':      $result = dbGetValue(BIZUNO_DB_PREFIX."users", 'title', "admin_id='$value'");
            return $result ? $result : $value;
        case 'rnd2d':     return !is_numeric($value) ? $value : number_format(round($value, 2), 2, '.', '');
        case 'taxTitle':  return viewTaxTitle($value);
        case 'terms':     return viewTerms($value); // must be passed encoded terms, default terms will use customers default
        case 'terms_v':   return viewTerms($value, true, 'v'); // must be passed encoded terms, default terms will use customers default
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
 * This function takes the db formatted date and converts it into a locale specific format as defined in the settings
 * @param date $raw_date
 * @param bool $long
 * @return string - Formatted date for rendering
 */
function viewDate($raw_date = '', $long = false)
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
    if ($month < 1   || $month > 12)  { $error = true; }
    if ($day   < 1   || $day > 31)    { $error = true; }
    if ($year < 1900 || $year > 2099) { $error = true; }
    if ($error) {
        $date_time = time();
    } else {
        $date_time = mktime($hour, $minute, $second, $month, $day, $year);
    }
    $format = getModuleCache('bizuno', 'settings', 'locale', 'date_short').($long ? ' h:i:s a' : '');
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
    return !empty($GLOBALS['invSkuSales'][$sku][$range]) ? number_format($GLOBALS['invSkuSales'][$sku][$range], 1) : 0;
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

/**
 * Processes a string of data with a user specified process, returns unprocessed if function not found
 * @param mixed $strData - data to process
 * @param string $Process - process to apply to the data
 * @return mixed - processed string if process found, original string if not
 */
function viewProcess($strData, $Process=false)
{
    msgDebug("\nEntering viewProcess with $strData = $strData and process = $Process");
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
    $langs   = scandir(BIZUNO_ROOT."locale/");
    foreach ($langs as $lang) {
        if (!in_array($lang, ['.', '..', 'en_US']) && is_dir(BIZUNO_ROOT."locale/$lang")) {
            require(BIZUNO_ROOT."locale/$lang/language.php");
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
        $data = ['id'=>false];
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
        case '1': $output = lang('contacts_terms_cod');                     break; // Cash on Delivery (COD)
        case '2': $output = lang('contacts_terms_prepaid');                 break; // Prepaid
        case '4': $output = sprintf(lang('contacts_terms_date'), $terms[3]);break; // Due on date
        case '5': $output = lang('contacts_terms_eom');                     break; // Due at end of month
        case '6': $output = lang('contacts_terms_now');                     break; // Due upon receipt
    }
    if ($inc_limit) { $output .= ' '.lang('contacts_terms_credit_limit').' '.viewFormat($credit_limit, 'currency'); }
    return $output;
}

/**
 * ISO source is always the default currency as all values are stored that way. Setting isoDest forces the default to be converted to that ISO
 * @global array $currencies - details of the ISO currency ->iso and ->rate OR ->isoDest ISO currency override
 * @param float $value
 * @param string $format - How to format the data
 * @return string - Formatted data to $currencies->iso or $currencies->isoDest
 */
function viewCurrency($value, $format='currency')
{
    global $currencies;
    if ($format=='curNull0' && (real)$value == 0) { return ''; }
    $defISO = getUserCache('profile', 'currency', false, 'USD');
    if (!is_numeric($value))         { return $value; }
    if ( empty($currencies->iso))    { $currencies->iso = $defISO; }
    if (!empty($currencies->isoDest)){ $currencies->iso = $currencies->isoDest; unset($currencies->rate); } // force current rate
    $isoVals= getModuleCache('phreebooks', 'currency', 'iso', $currencies->iso);
    if ( empty($currencies->rate))   { $currencies->rate= !empty($isoVals['value']) ? $isoVals['value'] : 1; }
    $newNum = number_format($value * $currencies->rate, $isoVals['dec_len'], $isoVals['dec_pt'], $isoVals['sep']);
    $zero   = number_format(0, $isoVals['dec_len']); // to handle -0.00
    if ($newNum == '-'.$zero) { $newNum = $zero; }
    if (!empty($isoVals['prefix'])) { $newNum  = $isoVals['prefix'].' '.$newNum; }
    if (!empty($isoVals['suffix'])) { $newNum .= ' '.$isoVals['suffix']; }
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

/**
 * This function build a drop down array based on the search criteria to list roles for phreebooks screens
 * @param string $type (Default -> sales) - The role type to build list from, set to all for all users
 * @param boolean $inactive (Default - false) - Whether or not to include inactive users
 * @param string $source - where to pull the id from, [default] contacts (contacts table record id), users (users table admin_id)
 * @return array $output - formatted result ready for drop down field values
 */
function viewRoleDropdown($type='sales', $inactive=false, $source='contacts')
{
    $result = dbGetMulti(BIZUNO_DB_PREFIX."roles", $inactive ? '' : "inactive='0'");
    $roleIDs= [];
    foreach ($result as $row) {
        $settings = json_decode($row['settings'], true);
        if (isset($settings['bizuno']['roles'][$type]) && $settings['bizuno']['roles'][$type]) { $roleIDs[] = $row['id']; }
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
    if ($opts=='contacts')  { $output[] = ['id'=>'-1', 'text'=>lang('per_contact')]; }
    if ($opts=='inventory') { $output[] = ['id'=>'-1', 'text'=>lang('per_inventory')]; }
    $output[] = ['id'=>'0', 'text'=>lang('none')];
    foreach (getModuleCache('phreebooks', 'sales_tax', $type, false, []) as $row) { $output[] = ['id'=>$row['id'], 'text'=>$row['title']]; }
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
    $view = new portalView();
    return $view->viewMain();
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
    $title= getModuleCache($module, 'properties', 'title').' - '.lang('settings');
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
 * Builds the HTML for custom tabs, sorts, generates fieldset and input HTML
 * @param array $data - Current working layout to modify
 * @param array $structure - Current structure to process data
 * @param string $module - Module ID
 * @param string $tabID - id of the tab container to insert tabs
 * @return string - Updated $data with custom tabs HTML added
 */
function customTabs(&$data, $module, $tabID)
{
    $structure = $data['fields'];
    // @todo Use sortOrder function
    $temp = []; // sort the fields
    foreach ($structure as $key => $value) { $temp[$key] = isset($value['order']) ? $value['order'] : 50; }
    array_multisort($temp, SORT_ASC, $structure);
    $tabs = getModuleCache($module, 'tabs');
    if (empty($tabs)) { return; }
    foreach ($structure as $key => $field) { // pull out the groups
        if (isset($field['tab']) && $field['tab'] > 0) { $tabs[$field['tab']]['groups'][$field['group']]['fields'][$key] = $field; }
    }
    foreach ($tabs as $tID => $tab) {
        if (!isset($tab['groups'])) { continue; }
        if (!isset($tab['title'])) { $tab['title'] = 'Untitled'; }
        if (!isset($tab['group'])) { $tab['group'] = $tab['title']; }
        $temp = [];
        foreach ($tab['groups'] as $key => $value) { $temp[$key] = isset($value['order']) ? $value['order'] : 50; }
        array_multisort($temp, SORT_ASC, $tab['groups']);
        $html = '';
        foreach ($tab['groups'] as $gID =>$group) {
            if (isset($group['fields']) && sizeof($group['fields']) > 0) {
                $html .= "  <fieldset>";
                $title = isset($group['title']) ? $group['title'] : $gID;
                if ($title) { $html .= "<legend>$title</legend>\n"; }
                foreach ($group['fields'] as $fID => $field) {
                    $structure[$fID]['position'] = 'after'; // put the labels after
                    switch($field['attr']['type']) {
                        case 'radio':
                            $cur = isset($structure[$fID]['attr']['value']) ? $structure[$fID]['attr']['value'] : '';
                            foreach ($field['opts'] as $elem) {
                                $structure[$fID]['attr']['value'] = $elem['id'];
                                $structure[$fID]['attr']['checked'] = $cur == $elem['id'] ? true : false;
                                $structure[$fID]['label'] = $elem['text'];
                                $html .= "    ".html5($fID, $structure[$fID])."<br />\n";
                            }
                            break;
                        case 'select': $structure[$fID]['values'] = $field['opts']; // set the choices and render
                        default:       $html .= "    ".html5($fID, $structure[$fID])."<br />\n";
                    }
                }
                $html .= "  </fieldset>\n";
            }
        }
        $data['tabs'][$tabID]['divs']["tab_".$tID] = ['type'=>'html', 'order'=>isset($tab['sort_order']) ? $tab['sort_order'] : 50, 'label'=>$tab['title'], 'html'=>$html];
    }
    $data['fields'] = $structure;
}

/**
 * This function builds an html element based on the properties passed, element of type INPUT is the default if not specified
 * @param string $id - becomes the DOM id and name of the element
 * @param array $prop - structure of the HTML element
 * @return string $output - HTML5 compatible element
 */
function html5($id='', $prop=[])
{
    global $html5;
    return $html5->render($id, $prop);
}

function htmlJS($js='')
{
    if (!$js) { return ''; }
    return '<script type="text/javascript">'."\n$js\n</script>\n";
}

/**
 * Searches a given directory for a filename match and generates html if found
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
 * This function builds the HTML output render a jQuery easyUI accordion feature
 * @param string $output - running string of them HTML output to be add to
 * @param array $prop - complete data array containing structure of entire page, only JavaScript part is used to force load from JavaScript data
 * @param string $id - accordion DOM ID
 * @param array $settings - the structure of the accordions (i.e. div structure for each accordion)
 */
function htmlAccordion(&$output, $prop, $idx=false)
{
    global $html5;
    if ($idx) {  // legacy to old style
        $prop = array_merge($prop['accordion'][$idx], ['id'=>$idx]);
    }
    $html5->layoutAccordion($output, $prop);
}

function htmlAddress(&$output, $prop)
{
    global $html5;
    $html5->layoutAddress($output, $prop);
}

/**
 * This function builds the html (and javascript) content to render a jquery easyUI datagrid
 * @param array $output - running HTML string to render the page
 * @param string $prop - The structure source data to pull from
 * @param array $idx - The index in $data to grab the structure to build
 * @return string - HTML formatted EasyUI datagrid appended to $output
 */
function htmlDatagrid(&$output, $prop, $idx=false)
{
    global $html5;
    $html5->layoutDatagrid($output, $prop, $idx);
}

/**
 * This function takes the menu structure and builds the easyUI HTML markup
 * @param string $output - The running html output
 * @param array $data - The current working structure to render
 * @return string - HTML formatted EasyUI menu appended to $output
 */
function htmlMenu(&$output)
{
    global $html5;
    $html5->menu($output);
}

/**
 * This function generates the tables pulled from the current structure, position: $data['tables'][$idx]
 * @param array $output - running HTML string to render the page
 * @param string $prop - The structure source data to pull from
 * @param array $idx - The index in $data to grab the structure to build
 * @return string - HTML formatted EasyUI tables appended to $output
 */
function htmlTables(&$output, $prop, $idx=false)
{
    global $html5;
    if ($idx) {  // legacy to old style
        $prop = $prop['tables'][$idx];
        $prop['attr']['id'] = $idx;
    }
    $html5->layoutTable($output, $prop);
}

/**
 * This function generates the tabs pulled from the current structure, position: $data['tabs'][$idx]
 * @param array $output - running HTML string to render the page
 * @param string $prop - The structure source data to pull from
 * @param array $idx - The index in $data to grab the structure to build
 * @return string - HTML formatted EasyUI tabs appended to $output
 */
function htmlTabs(&$output, $prop, $idx=false)
{
    global $html5;
    if ($idx) {  // legacy to old style
        $prop = array_merge($prop['tabs'][$idx], ['id'=>$idx]);
    }
    $html5->layoutTab($output, $prop);
}

/**
 * This function generates a html toolbar pulled from the current structure
 * @param array $output - running HTML string to render the page
 * @param string $prop - The structure source data to pull from
 * @param array $idx - The index in $data to grab the structure to build
 * @return string - HTML formatted EasyUI toolbar appended to $output
 */
function htmlToolbar(&$output, $prop, $idx=false)
{
    global $html5;
    if ($idx) { // legacy to old style
        if (empty($prop['toolbars'][$idx])) { return; }
        $prop = array_merge($prop['toolbars'][$idx], ['id'=>$idx]);
    }
    $html5->layoutToolbar($output, $prop);
}

/**
 * This functions builds the HTML for a jQuery easyUI tree
 * @param array $output - running HTML string to render the page
 * @param string $prop - The structure source data to pull from
 * @param array $idx - The index in $data to grab the structure to build
 * @return string - HTML formatted EasyUI tree appended to $output
 */
function htmlTree(&$output, $prop, $idx=false)
{
    global $html5;
    if ($idx) {  // legacy to old style
        $prop = array_merge($prop['tree'][$idx], ['id'=>$idx]);
    }
    return $html5->layoutTree($output, $prop);
}

/**
 * This function formats database data into a JavaScript array
 * @param array $dbData - raw data from database of rows matching given criteria
 * @param string $name - JavaScript variable name linked to the datagrid to populate with data
 * @param array $structure - used for identifying the formatting of data prior to building the string
 * @param array $override - map to replace database field name to the datagrid column name
 * @return string $output - JavaScript string of data used to populate datagrids
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
//    msgDebug("\n Added datagrid data rows: ".print_r($rows, true));
    return "var $name = ".json_encode(['total'=>sizeof($rows), 'rows'=>$rows]).";\n";
}
