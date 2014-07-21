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
//  Path: /modules/phreedom/defaults.php
//

// set the locale 
if (defined('TIMEZONE') && TIMEZONE <> '') date_default_timezone_set(TIMEZONE);
$locale = (defined('LOCALE') && LOCALE <> '') ? LOCALE : 'en_US';
setlocale(LC_ALL, $locale.'.UTF-8');
setlocale(LC_CTYPE, 'C');

define('DEFAULT_THEME','default');
define('DEFAULT_MENU','top');
define('DEFAULT_COLORS','start');
define('MAX_CP_COLUMNS', 3); // set the maximum number of dashboard columns
define('VERSION_CHECK_URL','http://www.phreesoft.com/revisions.xml');
define('MAX_IMPORT_CSV_ITEMS',5); // for importing linked tables if possible with csv
define('DEFAULT_TEXT_LENGTH', '32');
define('DEFAULT_REAL_DISPLAY_FORMAT', '10,2');
define('DEFAULT_INPUT_FIELD_LENGTH', 120);

// used to build pull downs for filtering
$DateChoices = array(
  'a' => TEXT_ALL,
  'b' => TEXT_RANGE,
  'c' => TEXT_TODAY,
  'd' => TEXT_WEEK,
  'e' => TEXT_WTD,
  'l' => TEXT_CUR_PERIOD,
  'f' => TEXT_MONTH,
  'g' => TEXT_MTD,
  'h' => TEXT_QUARTER,
  'i' => TEXT_QTD,
  'j' => TEXT_YEAR,
  'k' => TEXT_YTD,
);

// extra tabs/fields selections
$integer_lengths = array(
  '0' => '-127 '           . TEXT_TO . ' 127',
  '1' => '-32,768 '        . TEXT_TO . ' 32,768',
  '2' => '-8,388,608 '     . TEXT_TO . ' 8,388,607',
  '3' => '-2,147,483,648 ' . TEXT_TO . ' 2,147,483,647',
  '4' => TEXT_GREATER_THAN . ' 2,147,483,648',
);

$decimal_lengths = array(
  '0' => TEXT_SGL_PREC,
  '1' => TEXT_DBL_PREC,
);

$check_box_choices = array(
  '0' => TEXT_UNCHECKED, 
  '1' => TEXT_CHECKED,
);

?>