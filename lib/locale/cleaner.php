<?php
/*
 * Functions and methods that are locale related, i.e. currency, numbers, character sets, etc.
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
 * @version    3.x Last Update: 2019-04-10
 * @filesource /locale/cleaner.php
 */

namespace bizuno;

class cleaner
{

    /**
     * This is designed to clean any data input, generally used for _GET and _POST variables but can be used for any data types
     * @param mixed $value this excepts any data and applies filters and tests
     * @param array $processing - Filter/test to validate the data, including type
     * @return mixed - Validated data after processing has been applied
     */
    function clean($idx, $processing='', $src='')
    {
        switch ($src) {
            case 'cookie': $value = isset($_COOKIE[$idx]) ? $_COOKIE[$idx] : ''; break;
            case 'get':    $value = isset($_GET[$idx])    ? $_GET[$idx]    : ''; break;
            case 'post':   $value = isset($_POST[$idx])   ? $_POST[$idx]   : ''; break;
            case 'request':$value = isset($_REQUEST[$idx])? $_REQUEST[$idx]: ''; break; // $_POST overrides $_GET and $_COOKIE ???
            default:       $value = $idx; // it's just a value
        }
        if (!is_array($processing)) { $processing = ['format'=>$processing]; }
        $default = isset($processing['default'])? $processing['default']: null; // changed from null to '' [empty string], currency suffix became the text 'null' and broke order forms
        $option  = isset($processing['option']) ? $processing['option'] : null;
        if (!isset($processing['format'])) { $processing['format'] = $processing['attr']['type']; } // use the field type if no processing specified
        // Some applications add slashes to all unput variables, strip them here for most formats
        if (defined('BIZUNO_STRIP_SLASHES') && BIZUNO_STRIP_SLASHES) {
            if (!in_array($processing['format'], ['array','implode'])) { $value = stripslashes($value); }
        }
        switch ($processing['format']) {
            case 'alpha_num':return preg_replace("/[^a-zA-Z0-9 ]/", "", $value);
            case 'array':    return is_array($value) ? $value : [];
            case 'bool':     return substr(trim($value), 0 , 1) == '1' ? true : false;
            case 'char':     return strlen(trim($value))==0 ? $default : substr(trim($value), 0 , 1); // tbd what about length? char(3), etc
            case 'cmd':      return strlen(trim($value)) ? preg_replace("/[^a-zA-Z0-9\_\-\:]/", '', $value) : $default;
            case 'command':  return $this->cleanCommand($value, $default);
            case 'country':  return $this->cleanCountry($value, $option);
            case 'currency': return $this->cleanCurrency($value);
            case 'date':     return $this->localeDateToDb($value, $separator = '/', $default);
            case 'datetime': return $this->localeDateTimeToDb($value, '/', ':');
            case 'db_string':return addslashes($value);
            case 'decimal':
            case 'double':
            case 'float':    return empty($value) ? (!empty($default) ? $default : 0) : floatval($value);
            case 'email':    return !empty($value) ? preg_replace("/[^a-zA-Z0-9\-\_\.\,\@]/", '', $value) : $default;
            case 'filename': return preg_replace("/[^a-zA-Z0-9\-\_\.\/]/", '', $value);
            case 'implode':  return is_array($value) ? implode(';', $value) : $value;
            case 'integer':  return empty($value) ? (!empty($default) ? $default : 0) : intval($value);
            case 'json':     return json_decode($value, true);
            case 'jsonObj':  return json_decode($value); // return object format
            case 'numeric':  return preg_replace("/[^0-9 ]/", "", $value);
            case 'path_rel': // relative path with file, removes leading and trailing slashes and double slashes
                if (substr($value, 0, 1)== '/') { $value = substr($value, 1); }
                if (substr($value, 0, 2)== './'){ $value = substr($value, 2); }
                if (substr($value, -1)  == '/') { $value = substr($value, 0, strlen($value)-1); }
                return str_replace('//', '/', $value);
            case 'stringify': return trim($value); // has been converted from json to string, DO NOT REMOVE SLASHES
            case 'select':
            case 'text':
            case 'textarea': return $this->cleanTextarea($value, $default);
            case 'time':     return $this->localeTimeToDb($value, $separator = ':');
            case 'url':      return urlencode(str_replace([' ','/','\\'], '-', $value));
            default:
//                msgDebug("\nCleaning with format = {$processing['format']} the value: ".print_r($value, true));
                return trim(stripslashes($value));
        }
    }

    /**
     * Method to clean a command string received through the GET variable
     * @param string $value - Command string as received from GET variable
     * @param string $default - Default value to use if $value is empty
     */
    private function cleanCommand($value, $default='bizuno/main/bizunoHome')
    {
        if (substr_count($value, '/') != 2) { // check for valid structure, else home or default
            $value = getUserCache('profile', 'biz_id') ? 'bizuno/main/bizunoHome' : $default;
        }
        $temp = explode('/', $value);
        $GLOBALS['bizunoModule'] = $temp[0];
        $GLOBALS['bizunoPage']   = $temp[1];
        $GLOBALS['bizunoMethod'] = $this->clean($temp[2], 'cmd'); // remove illegal characters, fix for WordPress as it was adding ? at the end of the $_GET string
    }

    /**
     *
     * @param string $value - starting value
     * @param string $option - [Default ISO3] format to return, choices are ISO2 and ISO3
     * @return string - requested ISO format in upper case
     */
    private function cleanCountry($value, $option='ISO3')
    {
        $source = strtoupper($value);
        $lenRtn = $option=='ISO2' ? 2 : 3;
        if (strlen($source) == $lenRtn) { return $source; } // already there
        $match = $option=='ISO2' ? 'ISO3' : 'ISO2';
        $return= $option=='ISO2' ? 'ISO2' : 'ISO3';
        msgDebug("\ncleaning country with value = $value and match = $match and return = $return");
        $country_object = localeLoadDB();
        foreach ($country_object->Locale as $country_info) {
            $target = strtoupper($country_info->Country->$match);
            if ($target == $source) {
                msgDebug("\nReturning with country = ".$country_info->Country->$return);
                return $country_info->Country->$return; }
        }
        return $source ? $source : 'USA';
    }

    /**
     * Method to clean currency values to database format
     * @param string $value - Locale currency to clean to float format
     * @return float - Cleaned value consisting of just the number
     */
    private function cleanCurrency($value)
    {
        $iso   = !empty($GLOBALS['bizunoCurrency']) ? $GLOBALS['bizunoCurrency'] : getUserCache('profile', 'currency', false, 'USD');
        $values= getModuleCache('phreebooks', 'currency', 'iso', $iso);
        $temp0 = str_replace([$values['prefix'], $values['suffix'], $values['sep']], '', trim($value));
        $temp1 = str_replace($values['dec_pt'], '.', $temp0);
        $output= preg_replace("/[^-0-9.]+/", "", $temp1);
        msgDebug("\nEntering cleanCurrency with iso = $iso and value = $value, returning ".floatval($output));
        return floatval($output);
    }

    /**
     * Method to clean text area from submitted forms.
     * @param mixed $value - typically the string from a text area field
     * @param mixed $default
     * @return mixed - returns default if text area is empty
     */
    private function cleanTextarea($value, $default='')
    {
        if (!is_string($value)) { return print_r($value, true); } // an array was submitted
        return strlen(trim($value))==0 ? $default : trim($value);
    }

    /**
     * DEPRECATED AS VALUES ARE IN US NUMERIC FORMAT
     * Converts a provided number to system format (no thousands character, dot for decimal, dash fo negative)
     * @param string $value
     * @param string $type - [default: integer] Choices are integer or float
     * @return integer/float Result after conversion
     */
/*   private function localeNumber($value=0, $type='integer', $default=0)
   {
       if (!strlen($value)) { return $default===null ? 0 : $default; }
       $vals = getModuleCache('bizuno', 'settings', 'locale');
       $dec  = isset($vals['number_decimal']) ? $vals['number_decimal'] : '.';
       $sep  = isset($vals['number_thousand'])? $vals['number_thousand'] : ',';
       $pfx  = isset($vals['number_prefix'])  ? $vals['number_prefix'] : '';
       $sfx  = isset($vals['number_suffix'])  ? $vals['number_suffix'] : '';
       $pfxN = isset($vals['number_neg_pfx']) ? $vals['number_neg_pfx'] : '-';
       $sfxN = isset($vals['number_neg_sfx']) ? $vals['number_neg_sfx'] : '';
       if ($pfx)  { $value = str_replace($pfx, '', $value); }
       if ($sfx)  { $value = str_replace($sfx, '', $value); }
       if ($sfxN) { $value = str_replace($sfxN, '', $value); }
       if ($pfxN) { $value = str_replace($pfxN, '-', $value); }
       if ($sep)  { $value = str_replace($sep, '', $value); }
       if ($dec)  { $value = str_replace($dec, '.', $value); }
       return $type=='integer' ? intval($value) : floatval($value);
    } */

    /**
     * Converts locale date formats to db format YYY-MM-DD
     * Handles periods (.), dashes (-), and slashes (/) as date separators
     * @param type $raw_date
     * @param type $separator
     * @return string
     */
    private function localeDateToDb($raw_date='', $separator='/', $default='')
    {
       if (!$raw_date) { return $default; }
       $error = false;
       $date_format = getModuleCache('bizuno', 'settings', 'locale', 'date_short');
       $second_separator = $separator;
       if (strpos($date_format, '.') !== false) { $separator = '.'; }
       if (strpos($date_format, '-') !== false) { $separator = '-'; }
       $date_vals = explode($separator, $date_format);
       if (strpos($raw_date, '.') !== false) { $second_separator = '.'; }
       if (strpos($raw_date, '-') !== false) { $second_separator = '-'; }
       $parts     = explode($second_separator, $raw_date);
       if (sizeof($parts) < 3) {
           return msgAdd("localeDateToDb - Bad date received to convert: $raw_date");
       }
       foreach ($date_vals as $key => $position) {
           switch ($position) {
               case 'Y': $year  = substr('20'.$parts[$key], -4, 4); break;
               case 'm': $month = substr('0' .$parts[$key], -2, 2); break;
               case 'd': $day   = substr('0' .$parts[$key], -2, 2); break;
           }
       }
       if ($month < 1    || $month > 12)   { $error = true; }
       if ($day   < 1    || $day   > 31)   { $error = true; }
       if ($year  < 1900 || $year  > 2099) { $error = true; }
       if ($error) {
           msgDebug("\n error in localeDateToDb, trying to calculate date with input value = $raw_date");
           msgAdd(sprintf(lang('err_calendar_format'), $raw_date, getModuleCache('bizuno', 'settings', 'locale', 'date_short')), 'caution');
           return date('Y-m-d');
       }
       return $year.'-'.$month.'-'.$day;
    }

    /**
     * Converts locale date/time strings to db format (assume time is already in db format [00:00:00]
     * @param string $raw - date as received from input
     * @param char $separator - [default: /] set the character to look for
     * @return string - $date $time after conversion from DB
     */
    private function localeDateTimeToDb($raw, $separator='/')
    {
       $parts = explode(' ', $raw);
       if (sizeof($parts) > 0) { $date = $this->localeDateToDb($parts[0], $separator); }
       $time = isset($parts[1]) ? $parts[1] : '00:00:00';
       return "$date $time";
    }

    /**
     * Replaces the separator, time formats tend to be universal
     * @param string $raw - unformatted time
     * @return string - formatted time (hh:ii:ss)
     */
    private function localeTimeToDb($raw, $separator=':')
    {
       return str_replace($separator, ':', $raw);
    }
}

/**
 * Wrapper to cleaner to validate input variables
 * @param mixed $idx - New way: index of source array This accepts any data and removes any bad data.
 * @param array $processing - What to do to the source data, works with attr from table structure arrays, common usage: ['format'=>processing,'default'=>value]
 * @param string $src - [default: get] Source variable, valid values are get, post or blank for old way
 * @param mixed $default - [default: false] Default value to use if result is empty, null, or false
 * @return cleaned value according to the specified processing and source
 */
function clean($idx, $processing='text', $src='')
{
    global $cleaner;
    return $cleaner->clean($idx, $processing, $src);
}

/**
 * Translates into the locale language from the registry
 * @param string $idx - The index of the $lang array to pull the language translation from
 * @param string $suffix - suffix to check for variants of the index value
 * @return string - translation if found, original $idx if not
 */
function lang($idx, $suffix='')
{
    global $bizunoLang;
    if (!is_null($suffix)) {
        if (isset($bizunoLang[$idx.'_'.$suffix])) { return $bizunoLang[$idx.'_'.$suffix]; }
    }
    return isset($bizunoLang[$idx]) ? $bizunoLang[$idx] : $idx;
}

/**
 * This function is a special case of function lang that adds slashes to translations for embedding into JavaScript code
 * @param string $idx - The index of the $lang array to pull the language translation from
 * @param string $suffix - suffix to check for variants of the index value
 * @return string - translation if found with slashes added, original $idx if not (with slashes)
 */
function jsLang($idx, $suffix='')
{
    return addslashes(lang($idx, $suffix));
}

/**
 * Pulls language files from an extension, overwrites with locale, will keep English if ANY locale index is not set, helps for upgrades where language lags.
 * @return boolean false - But sets the session lang array with the admin language file
 */
function getLang($module)
{
    $myLang= getUserCache('profile', 'language', false, 'en_US');
    $lang  = [];
    if (!file_exists(BIZUNO_LIB."locale/en_US/module/$module/language.php")) { // bad access
        msgDebug("\nILLEGAL ATTEMPT TO LOAD LANGUAGE FROM A NON-MODULE: $module");
        msgAdd("ILLEGAL ATTEMPT TO LOAD LANGUAGE FROM A NON-MODULE: $module");
    } else {
        require(BIZUNO_LIB."locale/en_US/module/$module/language.php"); // populates $lang
    }
    $output= $lang;
    if ($myLang <> 'en_US') {
        if (file_exists(BIZUNO_ROOT."locale/$myLang/module/$module/language.php")) {
            require(BIZUNO_ROOT."locale/$myLang/module/$module/language.php"); // populates $lang
            $output = array_replace($output, $lang);
        }
    }
    return $output;
}

/**
 * Pulls language files from an extension, overwrites with locale, will keep English if ANY locale index is not set, helps for upgrades where language lags.
 * @return boolean false - But sets the session lang array with the admin language file
 */
function getMethLang($module, $mDir, $method)
{
    $myLang= getUserCache('profile', 'language', false, 'en_US');
    $lang  = [];
    $output= getLang($module);
    if (file_exists(BIZUNO_LIB."locale/en_US/module/$module/$mDir/$method/language.php")) {
        require(BIZUNO_LIB."locale/en_US/module/$module/$mDir/$method/language.php"); // populates $lang
        $output = array_replace($output, $lang);
    }
    if ($myLang <> 'en_US') {
        if (file_exists(BIZUNO_ROOT."locale/$myLang/module/$module/$mDir/$method/language.php")) {
            include(BIZUNO_ROOT."locale/$myLang/module/$module/$mDir/$method/language.php"); // populates $lang
            $output = array_replace($output, $lang);
        }
    }
    return $output;
}

/**
 * Pulls language files from an extension, overwrites with locale, will keep English if ANY locale index is not set, helps for upgrades where language lags.
 * @return boolean false - But sets the session lang array with the admin language file
 */
function getExtLang($path)
{
    $output = $lang = [];
    if (file_exists("{$path}/locale/en_US/language.php")) {
        require("{$path}/locale/en_US/language.php"); // populates $lang
        $output = $lang;
    }
    if (getUserCache('profile', 'language', false, 'en_US') <> 'en_US') {
        if (file_exists("{$path}/locale/".getUserCache('profile', 'language', false, 'en_US')."/language.php")) {
            require("{$path}/locale/".getUserCache('profile', 'language', false, 'en_US')."/language.php"); // populates $lang
            $output = array_replace($output, $lang);
        }
    }
    return $output;
}

/**
 * Pulls language files from an extension, overwrites with locale, will keep English if ANY locale index is not set, helps for upgrades where language lags.
 * @return boolean false - But sets the session lang array with the admin language file
 */
function getExtMethLang($module, $folder, $method)
{
    $lang = [];
    $output = getExtLang(BIZUNO_EXT."$module/");
    if (file_exists(BIZUNO_EXT."$module/locale/en_US/$folder/$method/language.php")) {
        require    (BIZUNO_EXT."$module/locale/en_US/$folder/$method/language.php"); // populates $lang
        $output = array_replace($output, $lang);
    }
    if (getUserCache('profile', 'language', false, 'en_US') <> 'en_US') {
        if (file_exists(BIZUNO_EXT."$module/locale/".getUserCache('profile', 'language', false, 'en_US')."/$folder/$method/language.php")) {
            require    (BIZUNO_EXT."$module/locale/".getUserCache('profile', 'language', false, 'en_US')."/$folder/$method/language.php"); // populates $lang
            $output = array_replace($output, $lang);
        }
    }
    return $output;
}

/**
 * For methods and dashboards where the language is stored with the class. This will overwrite the default language definitions with the locale if NOT en_US
 * @param array $arrLang - original language file to be merged with locale language file
 */
function localeModuleLang(&$arrLang=[], $pathLocale=false)
{
    if (getUserCache('profile', 'language', false, 'en_US') <> 'en_US') {
        if (!$pathLocale || !file_exists($pathLocale)) { return; }
        $lang = [];
        require($pathLocale); // pulls locale overlay
        $arrLang = array_replace($arrLang, $lang);
    }
}

/**
 * Tries to pull the best label for a table field using a suffix for additional resolution.
 * @param string $table name of the table
 * @param string $field name of the column or field
 * @param string $suffix to further refine the translation, suffix is typically data from another field in the table to categorize the row
 * @return string $field the string of the label or the constant value
 */
function pullTableLabel($table, $field, $suffix='')
{
    global $bizunoLang;
    if (defined('BIZUNO_DB_PREFIX') && constant('BIZUNO_DB_PREFIX') != '') {
        $pos = strpos($table, BIZUNO_DB_PREFIX);
        if ($pos !== false) { $table = substr_replace($table, '', $pos,strlen(BIZUNO_DB_PREFIX)); }
    }
    if     (isset($bizunoLang[$table.'_'.$field.'_'.$suffix])) { return $bizunoLang[$table.'_'.$field.'_'.$suffix]; }
    elseif (isset($bizunoLang[$table.'_'.$field]))             { return $bizunoLang[$table.'_'.$field]; }
    elseif (isset($bizunoLang[$field]))                        { return $bizunoLang[$field]; }
    // If we are here, the translation could not be found, send email to devs to fix this and return $field.
//  bizAutoLoad(BIZUNO_LIB."model/mail.php", 'bizunoMailer');
//    $body = 'Missing translation from function pullTableLabel, path='.$_GET['p']." missing field=$field with suffix=$suffix";
//    $mail = new bizunoMailer('dave.premo@phreesoft.com', 'Bizuno Devs', 'Missing Translation', $body, 'kevin.premo@gmail.com', 'Bizuno App');
//    $mail->sendMail();
    return $field;
}

/**
 * Returns the parsed array from the locale file to be used with settings drop downs and defaults
 * @return object - Locale information
 */
function localeLoadDB()
{
    if (file_exists(BIZUNO_LIB."locale/".getUserCache('profile', 'language', false, 'en_US')."/locale.xml")) {
        $contents = file_get_contents(BIZUNO_LIB."locale/".getUserCache('profile', 'language', false, 'en_US')."/locale.xml");
    } else {
        $contents = file_get_contents(BIZUNO_LIB."locale/en_US/locale.xml");
    }
    return parseXMLstring($contents);
}

/**
 * Returns the parsed array from the locale file to be used with settings drop downs and defaults
 * @return array - Locale information
 */
function localeLoadCharts()
{
    $output = [];
    $lang = is_dir(BIZUNO_LIB."locale/".getUserCache('profile', 'language', false, 'en_US')."/charts") ? getUserCache('profile', 'language', false, 'en_US') : 'en_US';
    $coas = scandir(BIZUNO_LIB."locale/$lang/charts/");
    foreach ($coas as $chart) { if ($chart <> '.' && $chart <> '..') {
        $strXML   = file_get_contents(BIZUNO_LIB."locale/$lang/charts/$chart");
        $objChart = parseXMLstring($strXML);
        $output[] = ['id'=>"locale/$lang/charts/$chart", 'text'=>isset($objChart->title) ? $objChart->title : $chart];
    } }
    return $output;
}

/**
 * Pulls detailed attributes about a given date
 * @param string $this_date - (default TODAY) date to build details of off
 * @return array - details about the date requested
 */
function localeGetDates($this_date = '')
{
    // this_date format YYYY-MM-DD
    if (!$this_date) { $this_date = date('Y-m-d'); }
    $result = array();
    $result['Today']     = ($this_date) ? substr(trim($this_date), 0, 10) : date('Y-m-d');
    $result['ThisDay']   = (int)substr($result['Today'], 8, 2);
    $result['ThisMonth'] = (int)substr($result['Today'], 5, 2);
    $result['ThisYear']  = (int)substr($result['Today'], 0, 4);
    $result['TotalDays'] = date('t', mktime( 0, 0, 0, $result['ThisMonth'], $result['ThisDay'], $result['ThisYear']));
    return $result;
}

/**
 * This function calculates a date in db format YYY-MM-DD offset by days, months, or years.
 * @param date $start_date - in database format
 * @param integer $day_offset - Number of days to add (subtract)
 * @param integer $month_offset - Number of months to add (subtract)
 * @param integer $year_offset - Number of years to add (subtract)
 * @return date - database formatted date offset from $start_date
 */
function localeCalculateDate($start_date, $day_offset=0, $month_offset=0, $year_offset=0)
{
    $date_details= localeGetDates($start_date);
    msgDebug("\nstart date = $start_date and day offset = $day_offset and month offsest = $month_offset and year offset = $year_offset");
    if ($date_details['ThisYear'] > '1900' && $date_details['ThisYear'] < '2099') {
        // check for current day greater than the month will allow (for recurs)
        $days_in_month = date('t', mktime(0, 0, 0, $date_details['ThisMonth'] + $month_offset, 1, $date_details['ThisYear'] + $year_offset));
        $mod_this_day  = min($days_in_month, $date_details['ThisDay']);
        return date('Y-m-d', mktime(0, 0, 0, $date_details['ThisMonth'] + $month_offset, $mod_this_day + $day_offset, $date_details['ThisYear'] + $year_offset));
    } else {
        msgDebug("\n error in localeCalculateDate, trying to calculate date with input value = $start_date");
        msgAdd(sprintf(lang('err_calendar_format'), $start_date, getModuleCache('bizuno', 'settings', 'locale', 'date_short')), 'caution');
        return date('Y-m-d');
    }
}

/**
* This function takes a post date and contact terms and type, calculates the due date and discount date (terms)
* @param date $post_date
* @param encoded $terms_encoded
* @return array $output
*/
function localeDueDate($post_date, $terms_encoded=0)
{
    $terms = explode(':', $terms_encoded);
    if (empty($terms[1])) { $terms[1] = 0; }
    if (empty($terms[2])) { $terms[2] = 0; }
    if (empty($terms[3])) { $terms[3] = 30; }
    if (empty($terms[4])) { $terms[4] = 1000; }
    $date_details = localeGetDates($post_date);
    $result = array();
    msgDebug("\nin localeDueDate with post_date = $post_date and terms_encoded = $terms_encoded and terms = ".print_r($terms, true));
    switch ($terms[0]) {
        default:
        case '0': // Default terms
            $result['discount']   = 0;
            $result['net_date']   = localeCalculateDate($post_date, 30);
            $result['early_date'] = localeCalculateDate($post_date, ($result['discount'] <> 0) ? 0 : 30);
            $result['due_days']   = 30;
            break;
        case '1': // Cash on Delivery (COD)
        case '2': // Prepaid
            $result['discount']   = 0;
            $result['early_date'] = $post_date;
            $result['net_date']   = $post_date;
            $result['due_days']   = 0;
            break;
        case '3': // Special terms
            $result['discount']   = floatval($terms[1]) / 100;
            $result['early_date'] = localeCalculateDate($post_date, intval($terms[2]));
            $result['net_date']   = localeCalculateDate($post_date, intval($terms[3]));
            $result['due_days']   = intval($terms[3]);
            break;
        case '4': // Due on day of next month
            $result['discount']   = 0;
            $result['early_date'] = clean($terms[3], 'date');
            $result['net_date']   = clean($terms[3], 'date');
            $result['due_days']   = 0;
            break;
        case '5': // Due at end of month
            $result['discount']   = floatval($terms[1]) / 100;
            $result['early_date'] = localeCalculateDate($post_date, intval($terms[2]));
            $result['net_date']   = date('Y-m-d', mktime(0, 0, 0, $date_details['ThisMonth'], $date_details['TotalDays'], $date_details['ThisYear']));
            $result['due_days']   = date('d', mktime(0, 0, 0, $date_details['ThisMonth']+1, 0, $date_details['ThisYear'])) - date('d');
            break;
    }
    $result['credit_limit'] = isset($terms[4]) ? $terms[4] : 1000;
    return $result;
}

/**
 * Calculates a date in db format into the future, day, week, month, quarter or year
 * @param type $dateStart - base date in db format
 * @param type $freq - [default: m (month)] future time period, d, w, m , q, or y
 * @return type
 */
function localePeriodicDate($dateStart, $freq='m')
{
    switch ($freq) {
        case 'd': $offD = 1; $offM = 0; $offY = 0; break;
        case 'w': $offD = 7; $offM = 0; $offY = 0; break;
        default:
        case 'm': $offD = 0; $offM = 1; $offY = 0; break;
        case 'q': $offD = 0; $offM = 3; $offY = 0; break;
        case 'y': $offD = 0; $offM = 0; $offY = 1; break;
    }
    return localeCalculateDate($dateStart, $offD, $offM, $offY);
}

/**
 * This function takes a the POST variables and cleans it to a database structure, returning cleaned values.
 * @param array $structure - Table structure
 * @param string $suffix - [default ''] Field suffix (if multiple instances are placed within a form, i.e. address_book data)
 * @param boolean $voidLabels - [default false] sets the field to null if the value equals the label (used when labels are within fields)
 * @return array $output - Cleaned form data applicable to the structure
 */
function requestData($structure, $suffix='', $voidLabels=false)
{
    $output = [];
    $request= $_POST;
    foreach ($structure as $field => $content) {
        if ($voidLabels && isset($request[$field.$suffix])) {
            if ($request[$field.$suffix] == pullTableLabel($content['table'], $field, '', $suffix)) { $request[$field.$suffix] = ''; }
        }
        if (isset($request[$field.$suffix])) {
            $output[$field] = clean($field.$suffix, $content, 'post');
        } elseif (isset($request[$content['tag'].$suffix])) {
            $output[$field] = clean($content['tag'].$suffix, $content, 'post');
        } elseif ($content['attr']['type'] == 'checkbox') {
            $output[$field] = '0';
        }
    }
    //msgDebug("\nReturning suffix = $suffix and output: ".print_r($output, true));
    return $output;
}

/**
 * This function maps the json decoded datagrid response values to an array
 * @param array $request - typically the post variables
 * @param unknown $structure - table structure
 * @param unknown $override - override map from datagrid to db table
 * @return array - cleaned array of fields mapped from form to db table
 */
function requestDataGrid($request, $structure, $override=[])
{
    $output = [];
    if (!isset($request['total'])) { // easyUI getChecked just returns the array not a total and row indexed array
        $temp   = $request;
        $request= ['total'=>sizeof($request), 'rows'=>$temp];
    }
    msgDebug("\n requestDataGrid returning row count = ".$request['total']);
    for ($i = 0; $i < $request['total']; $i++) {
        $row = $request['rows'][$i];
        $temp = [];
        foreach ($structure as $field => $content) {
            if (!isset($content['attr']['type'])) { $content['attr']['type'] = 'text'; }
            if ($content['attr']['type'] == 'currency') { $content['attr']['type'] = 'float'; } // datagrids are already in float format, try to convert again
            if (isset($override[$field])) {
                switch ($override[$field]['type']) {
                    default:
                    case 'constant': $temp[$field] = $override[$field]['value']; break;
                    case 'field':
                        if (!isset($row[$override[$field]['index']])) { $row[$override[$field]['index']] = ''; }
                        if (isset($request['rows'][$i][$override[$field]['index']])) {
                            if ($content['format'] == 'currency') { $content['format'] = 'float'; }
                            $temp[$field] = clean($request['rows'][$i][$override[$field]['index']], $content['format']);
//                          msgDebug("\nOverriding field: $field at index: ".$request['rows'][$i][$override[$field]['index']].' with filter = '.$content['format']." returning value: {$temp[$field]}");
                        } else {
                            $temp[$field] = '';
                        }
                        break;
                }
            } elseif (isset($row[$field])) {
                $temp[$field] = clean($row[$field], $content['attr']['type']);
//              msgDebug("\nCleaning field $field with index: {$row[$field]} with format {$content['attr']['type']} resulting in value: {$temp[$field]}");
            } elseif (isset($row[$content['tag']])) {
                $temp[$field] = clean($row[$content['tag']], $content['attr']['type']);
//              msgDebug("\nCleaning tag {$row[$content['tag']]} with format {$content['attr']['type']} resulting in value: {$temp[$field]}");
            }
        }
        $output[] = $temp;
    }
    msgDebug("\n requestDataGrid returning output: ".print_r($output, true));
    return $output;
}
