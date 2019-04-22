<?php
/*
 * Common functions
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
 * @name       Bizuno
 * @author     Dave Premo, PhreeSoft <support@phreesoft.com>
 * @copyright  2008-2019, PhreeSoft, Inc.
 * @license    http://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 * @version    3.x Last Update: 2019-03-19
 * @filesource lib/controller/functions.php
 */

namespace bizuno;

/**
 * Should eventually replace all calls to msgDebugWrite but need way to set file name first.
 */
//function bizShutdown() { msgTrap(); msgDebugWrite(); }
//register_shutdown_function("\\bizuno\\bizShutdown");

/**
 * Autoloads files, if it's already loaded, returns true. if not, tests for files existence before requiring else dies.
 * @param string $path - Path to load file
 * @param string $method - [Default: false] A class or function within the file to test for the loaded presence
 * @param string $type - [Default: class] Whether 'class' or 'function' are being tested
 * @return boolean - true if already loaded, script dies with notice if the file is not there
 */
function bizAutoLoad($path, $method='', $type='class') {
    if     (!empty($method)) { $method = __NAMESPACE__.'\\'. str_replace(__NAMESPACE__, '', $method); } // check for just one namespace
    if     ($type=='class'    && !empty($method) && class_exists   ($method)) { return true; }
    elseif ($type=='function' && !empty($method) && function_exists($method)) { return true; }
    if     (file_exists($path)) { require_once($path); }
    else   { return false; } // msgAdd("Fatal Error - File: $path does not exist!", 'trap'); } Causes issues with failed loads in PhreeForm
    return true;
}

/**
 * Composer gathers the module and mods, sorts them and executes in sequence.
 * @param string $module - Module ID
 * @param string $page - Page (filename) where the method is requested
 * @param string $method - Method on the given page to execute
 * @param array $layout - Current working layout, typically enters with empty array
 * @return boolean false, message Stack will have results as well as layout array
 */
function compose($module, $page, $method, &$layout=[])
{
    $processes = mergeHooks($module, $page, $method);
    foreach ($processes as $modID => $modProps) {
//      msgAdd("modID = $modID AND modProps = ".print_r($modProps, true));
        $fqdn = isset($modProps['class']) ? "\\bizuno\\".$modProps['class'] : "\\bizuno\\".$modID.ucfirst($modProps['page']);
        $controller = "{$modProps['path']}{$modProps['page']}.php";
        if (!file_exists($controller)) {
            msgDebug("\nCache hooks for module: $module contains: ".print_r(getModuleCache($module, 'hooks'), true));
            msgAdd("Path = $controller - Expecting method: {$modProps['method']} in module $modID and page {$modProps['page']} with controller: $fqdn but could not find the method. Ignoring!", 'caution');
            continue;
        }
        msgDebug("\nWorking with controller: $controller");
        bizAutoLoad($controller, $fqdn);
        $process = new $fqdn();
        if (!isset($modProps['method'])) { $modProps['method'] = $method; }
        if (method_exists($process, $modProps['method'])) {
            $process->{$modProps['method']}($layout);
        } else {
            msgAdd("Path = $controller - Method: {$modProps['method']} NOT FOUND! Module $modID and page {$modProps['page']} with controller: $fqdn but could not find the method. Ignoring!", 'caution');
        }
    }
    // cURL action moved outside of loop as mods may need to augment layout before calling cURL, causes dups if inside loop with mods, before and after mod, see PrestaShop.
    if (isset($layout['curlAction'])) {
        $layout['cURLresp'] = doCurlAction($layout['curlAction']);
        if (isset($layout['curlResponse'])) {
            $fqdn = "\\bizuno\\".$layout['curlResponse']['module'].ucfirst($layout['curlResponse']['page']);
            $process = new $fqdn();
            $process->{$layout['curlResponse']['method']}($layout);
        }
    }
    if (isset($layout['dbAction'])) { dbAction($layout); }  // act on the db, if needed
}

/**
 * This function merges the primary method (at position 0) with any hooks, hooks with a negative order will preceed the primary method, positive order will follow
 * @param type $module - Module ID
 * @param type $page - Page ID, is also the filename where to find the method
 * @param type $method - method ID within the page
 * @return string $hooks - Sorted list of processes to execute
 */
function mergeHooks($module, $page, $method)
{
    $thisHooks = getModuleCache($module, 'hooks', $page, $method, []);
//  msgDebug("\nthisHooks for module: $module contains: ".print_r($thisHooks, true));
    // add in the primary method
    $thisHooks[$module] = ['order'=>0,'path'=>getModuleCache($module, 'properties', 'path'),'page'=>$page,'class'=>$module.ucfirst($page),'method'=>$method]; // put primary method at 0
    $output = sortOrder($thisHooks); // sort them all up
    msgDebug("\nTotal methods to process with hooks = ".print_r($output, true));
    return $output;
}

/**
 * Error handler function to aid in debugging
 * @param type $errno
 * @param type $errstr
 * @param type $errfile
 * @param type $errline
 * @return boolean true
 */
function myErrorHandler($errno, $errstr, $errfile, $errline)
{
    if (!(error_reporting() & $errno)) { return; } // This error code is not included in error_reporting
    $debug = defined('BIZUNO_DEBUG') && constant('BIZUNO_DEBUG')===true ? true : false;
     switch ($errno) {
        case E_USER_ERROR:
            msgAdd("<b>ERROR</b> [$errno] $errstr<br />\n  Fatal error on line $errline in file $errfile, PHP " . PHP_VERSION . " (" . PHP_OS . ")<br />\nAborting...<br />\n");
            msgDebugWrite();
            exit(1);
        case E_USER_WARNING:
            if ($debug){ msgAdd("<b>WARNING</b> [$errno] $errstr<br />\n", 'caution'); }
            else       { error_log("<b>WARNING</b> [$errno] $errstr<br />\n"); }
            break;
        default:
        case E_USER_NOTICE:
            if ($debug){ msgAdd("<b>NOTICE</b> [$errno] $errstr - Line $errline in file $errfile", 'caution'); }
            else       { error_log("<b>NOTICE</b> [$errno] $errstr - Line $errline in file $errfile"); }
            break;
    }
    return true; /* Don't execute PHP internal error handler */
}

function myExceptionHandler($e)
{
    global $msgStack;
    msgTrap();
    msgDebug("Fatal error on line ".$e->getLine()." in file ".$e->getFile().". Description: ".$e->getCode()." - ".$e->getMessage());
    msgAdd("Fatal error on line ".$e->getLine()." in file ".$e->getFile().". Description: ".$e->getCode()." - ".$e->getMessage());
    msgDebugWrite();
    if (BIZUNO_HOST!='phreesoft' || (defined('BIZUNO_DEBUG') && constant('BIZUNO_DEBUG')===true)) {
        exit(json_encode(['message' => $msgStack->error]));
    } else {
        exit("Program Exception! Please fill out a support ticket with the details that got you here.");
    }
}

/**
 * Detects device to set screen size and menu structure
 * @return string - device type, [mobile, tablet, desktop]
 */
function detectDevice()
{
    $tablet_browser = $mobile_browser = 0;
    $agent = !empty($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : '';
    if (preg_match('/(tablet|ipad|playbook)|(android(?!.*(mobi|opera mini)))/i', strtolower($agent))) { $tablet_browser++; }
    if (preg_match('/(up.browser|up.link|mmp|symbian|smartphone|midp|wap|phone|android|iemobile)/i', strtolower($agent))) { $mobile_browser++; }
    if ((strpos(strtolower($_SERVER['HTTP_ACCEPT']),'application/vnd.wap.xhtml+xml') > 0) or ((isset($_SERVER['HTTP_X_WAP_PROFILE']) or isset($_SERVER['HTTP_PROFILE'])))) { $mobile_browser++; }
    $mobile_ua = strtolower(substr($agent, 0, 4));
    $mobile_agents = [
        'w3c ','acs-','alav','alca','amoi','audi','avan','benq','bird','blac',
        'blaz','brew','cell','cldc','cmd-','dang','doco','eric','hipt','inno',
        'ipaq','java','jigs','kddi','keji','leno','lg-c','lg-d','lg-g','lge-',
        'maui','maxo','midp','mits','mmef','mobi','mot-','moto','mwbp','nec-',
        'newt','noki','palm','pana','pant','phil','play','port','prox',
        'qwap','sage','sams','sany','sch-','sec-','send','seri','sgh-','shar',
        'sie-','siem','smal','smar','sony','sph-','symb','t-mo','teli','tim-',
        'tosh','tsm-','upg1','upsi','vk-v','voda','wap-','wapa','wapi','wapp',
        'wapr','webc','winw','winw','xda ','xda-'];
    if (in_array($mobile_ua,$mobile_agents)) { $mobile_browser++; }
    if (strpos(strtolower($agent),'opera mini') > 0) {
        $mobile_browser++;
        //Check for tablets on opera mini alternative headers
        $stock_ua = strtolower(isset($_SERVER['HTTP_X_OPERAMINI_PHONE_UA'])?$_SERVER['HTTP_X_OPERAMINI_PHONE_UA']:(isset($_SERVER['HTTP_DEVICE_STOCK_UA'])?$_SERVER['HTTP_DEVICE_STOCK_UA']:''));
        if (preg_match('/(tablet|ipad|playbook)|(android(?!.*mobile))/i', $stock_ua)) { $tablet_browser++; }
    }
    if ($tablet_browser > 0) { // actually a tablet but we'll treat as mobile and try to set width by device
        $device = 'mobile';
        setUserCache('profile', 'cols', 2);
    } else if ($mobile_browser > 0) {
        $device = 'mobile';
        setUserCache('profile', 'cols', 1);
    } else {
       $device = 'desktop';
    }
    return $device;
}

function getColumns()
{
    switch ($GLOBALS['myDevice']) {
        case 'mobile':  return 1;
        case 'tablet':  return 2;
        default:
        case 'desktop':
    }
    return getUserCache('profile', 'cols', false, 3);
}

/**
 * Retrieves a value from the user cache
 * @global array $bizunoUser - User Cache
 * @param string $group [default => profile] - Designates the cache group to get, returns [] if group index is not set
 * @param string $lvl1 [default => false] - index of $group, if false (and $lvl2 == false), returns empty array
 * @param string $lvl2 [default => false] - index of $group, if false (and $lvl1 != false), returns $default
 * @param mixed $default [default => null] - returns this value if $lvl1 == false, $lvl2 == false OR array element is not set
 * @return mixed - result of the get, empty array or $default if not found
 */
function getUserCache($group='profile', $lvl1=false, $lvl2=false, $default=null)
{
    global $bizunoUser;
    if       (!$lvl1 && !$lvl2) { // it's a group, should always be an array
        if (is_array($group)) { return [];}
        return isset($bizunoUser[$group]) ? $bizunoUser[$group] : ($default != null ? $default : []);
    } elseif ( $lvl1 && !$lvl2) { // could be array or scalar, assume scalar for default
        return isset($bizunoUser[$group][$lvl1]) ? $bizunoUser[$group][$lvl1] : $default;
    } elseif ( $lvl1 &&  $lvl2) { // lvl1 is an array
        return isset($bizunoUser[$group][$lvl1][$lvl2]) ? $bizunoUser[$group][$lvl1][$lvl2] : $default;
    }
    return $default;
}

/**
 * Sets values in the users registry
 * @global type $bizunoUser - User Cache
 * @param type $group [default => ''] - Designates the cache group to set
 * @param type $lvl1 [default => ''] - index of $group, if empty assumes the group index to be set
 * @param type $value - data to set
 */
function setUserCache($group='', $lvl1='', $value='')
{
    global $bizunoUser;
//  msgDebug("\nSetting user group: $group with lvl1: $lvl1 and value = ".print_r($value, true));
    if     ($group && $lvl1) { $bizunoUser[$group][$lvl1]= $value; }
    elseif ($group)          { $bizunoUser[$group]       = $value; }
    $GLOBALS['updateUserCache'] = true;
}

/**
 *
 * @param type $uIDs
 * @param type $rIDs
 * @return type
 */
function setUserRole($uIDs=[], $rIDs=[]) {
    $users   = 'u:'.getUserCache('profile', 'admin_id',false, 0);
    $roles   = 'g:'.getUserCache('profile', 'role_id', false, 0);
    if     (in_array(-1, $uIDs)) { $users = 'u:-1'; }
    elseif (in_array(0,  $uIDs)) { $users = 'u:0'; }
    elseif (sizeof($uIDs))       { $users = "u:".implode(":", $uIDs); }
    if     (in_array(-1, $rIDs)) { $roles = 'g:-1'; }
    elseif (in_array(0,  $rIDs)) { $roles = 'g:0'; }
    elseif (sizeof($rIDs))       { $roles = "g:".implode(":", $rIDs);  }
    msgDebug("\nroles = $roles and users = $users");
    if ($users == 'u:0' && $roles == 'r:0') {
        msgAdd($this->lang['msg_extDocs_no_security']);
        $security = 'u:-1;g:-1';
    } else {
        $security = "$users;$roles";
    }
    return $security;
}

/**
 * Clears values in the users registry
 * @global type $bizunoUser - Global user cache array
 * @param type $group - group within users cache
 * @param type $lvl1 - first level index
 */
function clearUserCache($group='', $lvl1='')
{
    global $bizunoUser;
    if     ($group && $lvl1) {
        msgDebug("\nClearing user cache group: $group and lvl1 = $lvl1");
        unset($bizunoUser[$group][$lvl1]); }
    elseif ($group)          { unset($bizunoUser[$group]); }
    $GLOBALS['updateUserCache'] = true;
}

/**
 * Retrieves an element from the module cache
 * @global type $bizunoMod - Module Cache
 * @param type $module [required] - Module to pull data from
 * @param type $group [default => settings] - Designates the cache group to get
 * @param type $lvl1 [default => false] - index of $group, if false (and $lvl2 == false), returns empty array
 * @param type $lvl2 [default => false] - index of $group, if false (and $lvl1 != false), returns $default
 * @param type $default [default => null] - returns this value if $lvl1 == false, $lvl2 == false OR array element is not set
 * @return mixed - result of the get, empty array or $default if not found
 */
function getModuleCache($module, $group='settings', $lvl1=false, $lvl2=false, $default=null)
{
    global $bizunoMod;
    if       (!$lvl1 && !$lvl2) { // it's a group, should always be an array
        return isset($bizunoMod[$module][$group]) ? $bizunoMod[$module][$group] : ($default ? $default : []);
    } elseif ( $lvl1 && !$lvl2) { // could be array or scalar, assume scalar for default
        return isset($bizunoMod[$module][$group][$lvl1]) ? $bizunoMod[$module][$group][$lvl1] : $default;
    } elseif ( $lvl1 &&  $lvl2) { // lvl1 is an array
        return isset($bizunoMod[$module][$group][$lvl1][$lvl2]) ? $bizunoMod[$module][$group][$lvl1][$lvl2] : $default;
    }
    return $default; // bad index request
}

/**
 * Saves the settings for a given module or module group, updates the cache and sets the flag to save in db at the end of the script
 * @global type $bizunoMod - Module Cache
 * @param type $module [required] - Module to set data to
 * @param type $group [default => settings] - Designates the cache group to set
 * @param type $lvl1 [default => false] - index of $group, if false assumes the group index to be set
 * @param type $value - data to set
 */
function setModuleCache($module, $group=false, $lvl1=false, $value='')
{
    global $bizunoMod;
    if     ($group && $lvl1) { $bizunoMod[$module][$group][$lvl1] = $value; }
    elseif ($group)          { $bizunoMod[$module][$group]        = $value; }
    $GLOBALS['updateModuleCache'][$module] = true;
//    msgDebug("\nSetting module: $module and group: $group with lvl1: $lvl1 and value = ".print_r($value, true));
}

/**
 * Clears the module group or group/level 1 properties from the cache
 * @param type $module
 * @param type $group
 */
function clearModuleCache($module, $group=false, $lvl1=false)
{
    global $bizunoMod;
    if     ($group && $lvl1) { unset($bizunoMod[$group][$lvl1]); }
    elseif ($group)          { unset($bizunoMod[$group]); }
    $GLOBALS['updateModuleCache'][$module] = true;
}

/**
 * Reads the user defined settings for a given module and updates the registry
 * @param string $module - Module index name
 * @param array $structure - Structure of the settings for the given module
 * @return null - Sets module cache for with the users selections
 */
function readModuleSettings($module, $structure=[])
{
    $settings = [];
    foreach ($structure as $group => $values) {
        foreach ($values['fields'] as $setting => $props) {
            $fldVal = clean($group."_".$setting, ['format'=>isset($props['attr']['format']) ? $props['attr']['format'] : 'text'], 'post');
            if (!empty($props['attr']['type']) && $props['attr']['type']=='password' && empty($fldVal)) {
                msgDebug("\nSkipped group: $group and setting = $setting");
                $settings[$group][$setting] = !empty($props['attr']['value']) ? $props['attr']['value'] : '';
            } else {
                $settings[$group][$setting] = $fldVal;
            }
        }
    }
    msgDebug("\nSaving settings array: ".print_r($settings, true));
    setModuleCache($module, 'settings', false, $settings);
    msgAdd(lang('msg_settings_saved'), 'success');
}

/**
 * This function extracts the settings values from the view structure and puts into simple array for usage and registry storage
 * @param array structure - Bizuno settings structure to pull values from
 * @return array
 */
function getStructureValues($structure='')
{
    $output = [];
    if (empty($structure)) { return $output; }
    foreach ($structure as $group => $values) {
        foreach ($values['fields'] as $setting => $props) { $output[$group][$setting] = isset($props['attr']['value']) ? $props['attr']['value'] : ''; }
    }
    return $output;
}

/**
 * USED FOR METHODS - This function strips out the hidden settings values forcing the defaults and replaces the defaults with the user settings values, if set
 * @param array $defaults - defaults settings for the module/method, will be overridden by user settings if not hidden
 * @param array $settings - user defined settings to override
 * @param array $structure - module/method structure to act upon
 */
function settingsReplace(&$defaults, $settings=[], $structure=[]) {
    foreach ($structure as $key => $value) {
        if (empty($value['attr']['type']) || $value['attr']['type'] != 'hidden') {
            if (isset($settings[$key])) { $defaults[$key] = $settings[$key]; }
        }
    }
}

/**
 * This function populates the settings view structure with user registry values
 * Priority: table configuration, modCache[$module], default: array()
 * Moved table configuration first to load first if reloading registry after setting save, else doesn't update properly
 * @param array $structure - module structure
 * @param string $module - Module id
 */
function settingsFill(&$structure, $module='')
{
    $settings = getModuleCache($module, 'settings', false, false, []);
    if (empty($settings)) { return; }
    foreach ($settings as $group => $entries) {
        if (!is_array($entries)) { continue; } // mal-formed settings
        foreach ($entries as $key => $value) {
            if (isset($structure[$group]['fields'][$key])) { $structure[$group]['fields'][$key]['attr']['value'] = $value; }
        }
    }
}

function getTermsDate($terms_encoded='', $type='c')
{
    $idx = $type=='v' ? 'vendors' : 'customers';
    $terms_def = explode(':', getModuleCache('phreebooks', 'settings', $idx, 'terms'));
    if (!$terms_encoded){ $terms = $terms_def; }
    else                { $terms = explode(':', $terms_encoded); }
    if ($terms[0]==0)   { $terms = $terms_def; }
    switch ($terms[0]) {
        default:
        case '0': // Default terms
        case '3': // Special terms
            if (!isset($terms[3])) { $terms[3] = 30; }
            return localeCalculateDate(date('Y-m-d'), $terms[3]);
        case '1': // Cash on Delivery (COD)
        case '2': // Prepaid
        case '6': // Due upon receipt
            return date('Y-m-d');
        case '4': return $terms[3];     // Due on date
        case '5': return date('Y-m-t'); // Due at end of month
    }
}

/**
 * Returns the first hit from $_REQUEST of the array of possible indices.
 *
 * @param array $indices - [default: array('search','q')] - List of indices to comb through, q first as when instantiating the combo, q is empty but once
 * the use start typing, q has a value and should take precedence.
 * @return string - First hit
 */
function getSearch($indices=['q', 'search']) {
    if (!is_array($indices)) { $indices = [$indices]; }
    foreach ($indices as $idx) {
        if (isset($_REQUEST[$idx])) { return $_REQUEST[$idx]; }
    }
    return '';
}

/**
 * Sorts an array by specified key
 * @param type $arrToSort - Array to be sorted
 * @param type $sortKey [default: order] Specifies the key to use as the base for the sort order
 * @return array - Sorted array by key
 */
function sortOrder($arrToSort=[], $sortKey='order', $order='asc')
{
    $temp = [];
    if (!is_array($arrToSort)) { return $arrToSort; }
    foreach ($arrToSort as $key => $value) {
        $temp[$key] = isset($value[$sortKey]) ? $value[$sortKey] : 999;
    }
    $type = $order=='desc' ? SORT_DESC : SORT_ASC;
    array_multisort($temp, $type, $arrToSort);
    return $arrToSort;
}

/**
 * Sorts an array by specified key after the language translation has been applied, typically used for lists
 * @param type $arrToSort - Array to be sorted
 * @param type $sortKey [default: order] Specifies the key to use as the base for the sort order
 * @return array - Sorted array by key
 */
function sortOrderLang($arrToSort=[], $sortKey='title')
{
    $temp = [];
    if (!is_array($arrToSort)) { return $arrToSort; }
    foreach ($arrToSort as $key => $value) {
        $temp[$key] = isset($value[$sortKey]) ? lang($value[$sortKey]) : 'ZZZ';
    }
    array_multisort($temp, SORT_ASC, $arrToSort);
    return $arrToSort;
}

/**
 * Takes input global variables and updates the cache to store user selections on a given manager screen.
 * @param array $data - structure to clean and store user preferences
 * @return updated SESSION with users posted preferences
 */
function updateSelection($data)
{
    $output = [];
    foreach ($data['values'] as $settings) {
        $method = isset($settings['method']) ? $settings['method'] : 'post';
        $output[$settings['index']] = clean($settings['index'], ['format'=>$settings['clean'],'default'=>$settings['default']], $method);
    }
    setUserCache($data['path'], false, $output);
    return $output;
}

/**
 * Given a file, i.e. /css/base.js, replaces it with a string containing the file's mtime, i.e. /css/base.1221534296.js
 * @param string file - The file to be loaded.  Must be an absolute path (i.e. starting with slash).
 * @return string - Adjusted filename with date inserted into it
 */
function auto_version($file)
{
    $mtime = filemtime($file);
    return preg_replace('{\\.([^./]+)$}', ".$mtime.\$1", $file);
}

/**
 * Generates a random string of given length, characters used are A-Za-z0-9
 * @param integer $length - (Default 12) Length of string to generate
 * @return string - Random string of length $length
 */
function randomValue($length = 12)
{
    $chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz1234567890';
    $chars_length = (strlen($chars) - 1);
    $string = $chars{rand(0, $chars_length)};
    for ($i = 1; $i < $length; $i = strlen($string)) {
        $r = $chars{rand(0, $chars_length)};
        if ($r != $string{$i - 1}) { $string .=  $r; }
    }
    return $string;
}

/**
 * Round to a certain precision, includes correction for floating point issues like calculated value of 162.694999999 rounding to 162.69 instead of 162.70
 * @param float $value - Value to round
 * @param integer $precision - [default 2] precision to round to
 * @return rounded $value
 */
function roundAmount($value, $precision=2)
{
    $pass1 = round($value, $precision+4, PHP_ROUND_HALF_UP); // increased from 2 to 4 for customer tax calculation 5.62495 rounded to 5.63 vs order 5.62
    return round($pass1, $precision, PHP_ROUND_HALF_UP);
}

/**
 * Validates user security levels to access any given method.
 * @param string $module - Module to check security
 * @param string $index - Menu item to check against
 * @param integer $min_level - minimum security range 1 to 4 to set security access levels
 * @param boolean $verbose - true add error message to stack if no permission, false to suppress message
 * @return integer - Security level of user for given module/menu, false if no access is permitted
 */
function validateSecurity($module, $index, $min_level=1, $verbose=true)
{
    $access_level = getUserCache('security', $index, false, 0);
    if (!is_numeric($access_level)) { $access_level = 0; } // catches if index is null or undefined, returns array
    $approved = ($access_level >= $min_level) ? $access_level : 0;
    if (!$approved && $verbose) { msgAdd(lang('err_no_permission')." [$index]"); }
    return $approved;
}

function validateUsersRoles($security=false) {
    if (empty($security)) { return false; }
    if ($security == 'u:0;g:0') { return msgAdd('Orphaned database record. Please check your security settings!'); }
    $types = explode(';', $security);
    $users = explode(":", substr($types[0], 2)); // users first
    if (in_array(-1, $users)) { return true;  }
    if (in_array(getUserCache('profile', 'admin_id', false, 0), $users)){ return true;  }
    $roles = explode(":", substr($types[1], 2)); // roles next
    if (in_array(-1, $roles)) { return true;  }
    if (in_array(getUserCache('profile', 'role_id', false, 0), $roles)) { return true;  }
    return false;
}

/**
 * Pulls the address record from the database if $aID > 0, else returns business address information from Bizuno settings
 * @param integer $aID - record id of the address
 * @param string $suffix - suffix to append to index of returned array
 * @param boolean $ap - special case for aID=0 to pull correct settings from cache
 * @return array - keyed array of address information
 */
function addressLoad($aID=0, $suffix='', $ap=false)
{
    if (!$aID && getUserCache('profile', 'restrict_store', false, -1) > 0) {
        $result = dbGetRow(BIZUNO_DB_PREFIX.'address_book', "ref_id=".getUserCache('profile', 'restrict_store', false, -1)." AND type='m'");
    } elseif ($aID) {
        $result = dbGetRow(BIZUNO_DB_PREFIX."address_book", "address_id=$aID");
    } else { // load home address from registry
        $result = ['address_id'=>0];
        $settings = getModuleCache('bizuno', 'settings', 'company');
        foreach ($settings as $key => $value) {
            $result[$key] = $value;
            if ($ap) { $result['contact'] = getModuleCache('bizuno', 'settings', 'company', 'contact_ap'); }
        }
    }
    $output = [];
    foreach ($result as $key => $value) { $output[$key.$suffix] = $value; }
    return $output;
}

/**
 * Generates a list of expiration dates, months/years. Typically used for credit card entry forms
 * @return array - index: months, index: years ready for pull down view
 */
function pullExpDates()
{
    $output = [];
    $output['months'][]= ['id'=>0, 'text'=>lang('select')];
    $output['years'][] = ['id'=>0, 'text'=>lang('select')];
    for ($i = 1; $i < 13; $i++) {
        $j = ($i < 10) ? '0' . $i : $i;
        $output['months'][] = ['id'=>sprintf('%02d', $i), 'text'=>$j.'-'.strftime('%B',mktime(0,0,0,$i,1,2000))];
    }
    $today = getdate();
    for ($i = $today['year']; $i < $today['year'] + 10; $i++) {
        $output['years'][] = ['id'=>strftime('%Y',mktime(0,0,0,1,1,$i)), 'text'=>strftime('%Y',mktime(0,0,0,1,1,$i))];
    }
    return $output;
}

/**
 * Converts an array to an object, typically used to take db entry and make an object out of it
 * @param array $arr - Source data array
 * @return object - Converted array
 */
function array_to_object($arr=[])
{
    if (!is_array($arr)) { return $arr; }
    $output = new \stdClass();
    foreach ($arr as $key => $value) {
        $output->$key = is_array($value) ? array_to_object($value) : $output->$key = $value;
    }
    return $output;
}

/**
 * Recursively converts an object to a XML string
 * @param object/array $params - Current working object, reduces as the string is built
 * @param boolean $multiple - Indicates if the current object fragment is an array (same tag)
 * @param string $multiple_key - Key of multiple, only valid if $multiple is true
 * @param integer $level - depth level of recursion
 * @param boolean $brief - (default false) Skips generation of encapsulated ![CDATA] ]]
 * @return string - XML converted string
 */
function object_to_xml($params, $multiple=false, $multiple_key='', $level=0, $brief=false)
{
    $output = NULL;
    if (!is_array($params) && !is_object($params)) { return; }
    foreach ($params as $key => $value) {
        $xml_key = $multiple ? $multiple_key : $key;
        if       (is_array($value)) {
            $output .= object_to_xml($value, true, $key, $level, $brief);
        } elseif (is_object($value)) {
            for ($i=0; $i<$level; $i++) { $output .= "\t"; }
            $output .= "<" . $xml_key . ">\n";
            $output .= object_to_xml($value, '', '', $level+1, $brief);
            for ($i=0; $i<$level; $i++) { $output .= "\t"; }
            $output .= "</" . $xml_key . ">\n";
        } else {
            if ($value <> '') {
                for ($i=0; $i<$level-1; $i++) { $output .= "\t"; }
                $output .= xmlEntry($xml_key, $value, $brief);
            }
        }
    }
    return $output;
}

/**
 * Parses an XML string to a standard class object or array
 * @param string $strXML
 * @param boolean $type - [default false] false returns object, true returns array
 * @return parsed XML string, either object or array
 */
function parseXMLstring($strXML, $assoc=false)
{
    $result = bizuno_simpleXML($strXML);
    if ($assoc) { // associative array
        return json_decode(str_replace(':{}',':null',json_encode($result)), true);
    } else { // object
        return json_decode(str_replace(':{}',':null',json_encode($result)));
    }
}

/**
 * Wrapper for simpleXML library as some PHP installs do not include it.
 * @param string $strXML - XML string to parse
 * @return type
 */
function bizuno_simpleXML($strXML) {
    if (!function_exists('simplexml_load_string')) {
        return msgAdd('The PHP simpleXML library is missing! Bizuno requires this library to function properly.');
    }
    return simplexml_load_string(trim($strXML), 'SimpleXMLElement', LIBXML_NOCDATA);
}

/**
 * Creates an XML header used in PhreeForm reports
 * @return string
 */
function createXmlHeader()
{
    header("Content-Type: text/xml");
    if (!defined("CHARSET")) { define("CHARSET", "UTF-8"); }
    $str  = '<?xml version="1.0" encoding="'.CHARSET.'" standalone="yes" ?>'."\n";
    $str .= "<data>\n";
    return $str;
}

/**
 * Generates an XML key/value pair
 * @param string $key - XML key
 * @param string $data - XML value
 * @param boolean $ignore - (default false) if true, uses date without ![CDATA] ]] encapsulation
 * @return sring - Proper XML formatted data
 */
function xmlEntry($key, $data, $ignore = false)
{
    $str = "\t<$key>";
    if ($data != NULL) {
        if ($ignore) { $str .= $data; }
        else { $str .= "<![CDATA[" . $data . "]]>"; }
    }
    $str .= "</$key>\n";
    return $str;
}

/**
 * Creates the XML footer
 * @return string
 */
function createXmlFooter()
{
    return "</data>\n";
}

/**
 * Retrieves the default PhreeForm group id for a specific journal ID
 * @param integer $jID - Journal ID
 * @return string - PhreeForm Form Group encoded ID
 */
function getDefaultFormID($jID = 0)
{
    switch ($jID) {
        default:
        case  2: return 'gl:j2';
        case  3: return 'vend:j3';
        case  4: return 'vend:j4';
        case  6: return 'vend:j6';
        case  7: return 'vend:j7';
        case  9: return 'cust:j9';
        case 10: return 'cust:j10';
        case 12: return 'cust:j12';
        case 13: return 'cust:j13';
        case 14: return 'inv:j14';
        case 15: return 'inv:j15';
        case 16: return 'inv:j16';
        case 17:
        case 18: return 'bnk:j18';
        case 19: return 'cust:j19';
        case 20:
        case 21:
        case 22: return 'bnk:j20';
    }
}

/**
 * Returns with the image tag from a URL with a HTML in line icon base 64 encoded
 * @param string $url
 * @return string - HTML img tag for displaying an image
 */
function viewFavicon($url, $title='', $event=false)
{
    global $io;
    $target= $event ? "style=\"cursor:pointer\" onClick=\"window.open('$url', '_blank');\" " : '';
    $parts = parse_url($url);
    if (empty($parts['host'])) { return ''; }
    if (file_exists(BIZUNO_DATA."cache/icons/{$parts['host']}.fav")) { // load the icon
        $img = file_get_contents(BIZUNO_DATA."cache/icons/{$parts['host']}.fav");
    } else {
        $arr = getFavIcon($url); // try full $url
        if (empty($arr)) { $arr = getFavIcon($parts['host'], $parts['scheme']); } // if empty try domain
        if (empty($arr)) { // not found, use Google to guess
//          msgAdd("Google site: {$parts['scheme']}://{$parts['host']} with icon = null, trying Google");
            try { $result = @file_get_contents("http://www.google.com/s2/favicons?domain={$parts['host']}"); }
            catch (Exception $ex) { return msgAdd("caught Google exception => ".print_r($ex, true)); }
        } else {
            if (strpos(strtolower($arr[0]['href']), 'http') === false) { // it's relative, add url
                $host  = "{$parts['scheme']}://{$parts['host']}";
                $result= @file_get_contents($host.$arr[0]['href']);
                if (!$result && !empty($parts['path'])) { // might be in a sub-folder
                    $host .= substr($parts['path'], 0, strrpos($parts['path'], '/'));
                    $result= @file_get_contents($host.$arr[0]['href']);
                }
            } else {
                $result= @file_get_contents($arr[0]['href']);
            }
//          msgAdd("site: {$parts['scheme']}://{$parts['host']} with icon = ".htmlspecialchars($arr[0]['href']));
        }
        $img = base64_encode($result);
        if ($img) { $io->fileWrite($img, "cache/icons/{$parts['host']}.fav"); }
    }
    if (empty($img)) { $img = base64_encode(file_get_contents(BIZUNO_LIB."images/icon_16.png")); }
    return '<img src="data:image/png;base64,'.$img.'" width="32" height="32" alt="'.$title.'" '.$target.'/>';
}

function getFavIcon($host, $scheme=false)
{
    if ($scheme) { $host = "$scheme://$host"; }
    msgDebug("\nTrying url: $host");
    $site = @file_get_contents($host);
    $doc = new \DOMDocument('1.0', 'UTF-8');
    $internalErrors = libxml_use_internal_errors(true); // set error level
    $doc->strictErrorChecking = false;
    if (empty($site)) { return; }
    $doc->loadHTML($site);
    libxml_use_internal_errors($internalErrors); // Restore error level
    $xml = simplexml_import_dom($doc);
    $arr = $xml->xpath('//link[@rel="icon"]');
    if (empty($arr)) { $arr = $xml->xpath('//link[@rel="shortcut icon"]'); } // try other option
    return $arr;
}

/**
 * Encapsulates a value in quotes if a comma is present in the string
 * @param string $value - Value to be cleaned
 * @return string - Source string minus CR/LF/tab characters
 */
function csvEncapsulate($value)
{
    $cleaned = str_replace(["\r\n", "\n", "\r", "\t", "\0", "\x0B"], ' ', $value);
    return strpos($value, ',') === false ? $cleaned : '"'.$cleaned.'"';
}
