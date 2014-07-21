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
//  Path: /themes/default/config.php
//
$theme = array('name' => 'Cool Blue (Default)'); // theme name for display purposes
$theme_menu_options = array( // available menu location options
  'top' => TEXT_TOP,
  'left'=> TEXT_LEFT,
);
if ($include_header) { ?>
  <link rel="stylesheet" type="text/css" href="<?php echo DIR_WS_THEMES.'css/'.MY_COLORS.'/ddsmoothmenu.css'; ?>" />
  <script type="text/javascript" src="themes/default/ddsmoothmenu.js">
  /***********************************************
  * Smooth Navigational Menu- (c) Dynamic Drive DHTML code library (www.dynamicdrive.com)
  * This notice MUST stay intact for legal use
  * Visit Dynamic Drive at http://www.dynamicdrive.com/ for full source code
  ***********************************************/
  </script>
  <script type="text/javascript">
    ddsmoothmenu.init({
	  mainmenuid: "smoothmenu",
	  orientation: '<?php echo MY_MENU=='left'?'v':'h';?>',
	  classname: '<?php echo MY_MENU=='left'?'ddsmoothmenu-v':'ddsmoothmenu';?>',
	  contentsource: "markup"
    })
  </script>
<?php } // end include_header

// set up to use jquery UI calendar format
$cal_format =  preg_replace(array('/m/','/d/','/Y/'), array('mm', 'dd', 'yy'), DATE_FORMAT);
define('DATE_FORMAT_CALENDAR', $cal_format);
// wrappers to initialize jquery UI calendar, both javascript and html
if (!function_exists('js_calendar_init')) {
  function js_calendar_init($properties = NULL) {
  	$options = array();
  	if (isset($properties['params']['onchange'])) $options[] = "onSelect: function(date) { ".$properties['params']['onchange']." }";
  	$opt = sizeof($options)==0 ? '' : "{ ".implode(', ',$options)." }";
    return 'addLoadEvent(function() { $("#'.$properties['fieldname'].'").datepicker('.$opt.'); });';
  }
}
if (!function_exists('html_calendar_field')) {
  function html_calendar_field($properties = NULL) {
    return html_input_field($properties['fieldname'], $properties['default']);
  }
}
?>
<script type="text/javascript">var js_cal_date_format = '<?php echo DATE_FORMAT_CALENDAR; ?>';</script>
  