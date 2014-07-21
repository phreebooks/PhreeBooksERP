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
//  Path: /modules/phreebooks/pages/admin_tools/js_include.php
//

?>
<script type="text/javascript">
<!--
// pass any php variables generated during pre-process that are used in the javascript functions.
// Include translations here as well.
<?php 
echo js_calendar_init($cal_repost_start);
echo js_calendar_init($cal_repost_end);
?>

function init() {
}

function check_form() {
  return true;
}

// Insert other page specific functions here.
function fetchPeriod() {
  var acctPeriod = prompt('<?php echo GL_WARN_CHANGE_ACCT_PERIOD; ?>', '');
  if (acctPeriod) {
	document.getElementById('period').value = acctPeriod;
    return true;
  } else {
    return false;
  }
}

function updateEnd(index) {
  if (index != 11) {
	var tmp = document.getElementById('end_'+index).value;
	var temp = cleanDate(tmp);
	var thisDay = parseFloat(temp.substr(8,2));
	var thisMonth = parseFloat(temp.substr(5,2)) - 1;
	var thisYear = temp.substr(0,4);
	var nextDay = new Date(thisYear, thisMonth, thisDay);
	var oneDay = 1000 * 60 * 60 * 24;
	var dateInMs = nextDay.getTime();
	dateInMs += oneDay;
	nextDay.setTime(dateInMs);
	thisDay = nextDay.getDate();
	if (thisDay < 10) thisDay = '0' + thisDay;
	thisMonth = nextDay.getMonth() + 1;
	if (thisMonth < 10) thisMonth = '0' + thisMonth;
	thisYear = nextDay.getFullYear();
	temp = thisYear + '-' + thisMonth + '-' + thisDay;
	document.getElementById('start_'+(index+1)).value = formatDate(temp);
  }
}

// -->
</script>
<?php
// set up a calendar variable for each possible calendar
echo '<script type="text/javascript">' . chr(10);
$i = 0;
foreach ($fy_array as $key => $value) {
  if ($key > $max_period) { // only allow changes if nothing has bee posted above this period
	$ctl_end = 'P' . $i . 'Start';
	$cal_gen[$i] = array(
	  'name'      => $ctl_end,
	  'form'      => 'admin_tools',
	  'fieldname' => 'start_' . $i,
	  'imagename' => 'btn_date_' . $i,
	  'default'   => gen_locale_date($value['start']),
	  'params'    => array('align' => 'left', 'readonly' => 'true'),
	);
	echo js_calendar_init($cal_gen[$i]);
	$ctl_end = 'P' . $i . 'End';
	$cal_gen[$i] = array(
	  'name'      => $ctl_end,
	  'form'      => 'admin_tools',
	  'fieldname' => 'end_' . $i,
	  'imagename' => 'btn_date_' . $i,
	  'default'   => gen_locale_date($value['end']),
	  'params'    => array('align' => 'left', 'onchange' => 'updateEnd('.$i.');', 'readonly' => 'true'),
	);
	echo js_calendar_init($cal_gen[$i]);
  }
  $i++;
}
echo '</script>' . chr(10);
?>
