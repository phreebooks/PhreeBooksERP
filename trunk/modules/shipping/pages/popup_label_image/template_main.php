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
//  Path: /modules/shipping/pages/popup_label_image/template_main.php
//
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<title><?php echo TITLE . ' - ' . COMPANY_NAME; ?></title>
</head>
<body>
<div id="image">
<?php 
if ($image) {
	echo '<h2>' . $image . '</h2>';
} else {
	echo html_form('download', FILENAME_DEFAULT, gen_get_all_get_params(array('action')) . '&action=download') . chr(10);
	echo html_submit_field('action', SHIPPING_TEXT_DOWNLOAD, '') . chr(10);
	echo SHIPPING_THERMAL_INST;
	echo '</form>';
}
?>
</form>
</div>
</body>
</html>