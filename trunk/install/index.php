<?php
// +-----------------------------------------------------------------+
// |                   PhreeBooks Open Source ERP                    |
// +-----------------------------------------------------------------+
// | Copyright(c) 2008-2015 PhreeSoft      (www.PhreeSoft.com)       |
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
// Path: /install/index.php
//
ob_start();
ini_set('log_errors','1'); 
ini_set('display_errors', '1');
error_reporting(E_ALL & ~E_NOTICE);
require_once('pages/main/pre_process.php');
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml"  <?php echo HTML_PARAMS; ?>>
<head>
<title><?php echo PAGE_TITLE; ?></title>
<meta http-equiv="Content-Type" content="text/html; charset=<?php echo CHARSET; ?>" />
<link rel="shortcut icon" type="image/ico" href="../favicon.ico" />
<link rel="stylesheet" type="text/css" href="../themes/default/css/start/stylesheet.css" />
<link rel="stylesheet" type="text/css" href="../themes/default/css/start/jquery_datatables.css" />
<link rel="stylesheet" type="text/css" href="../themes/default/css/start/jquery-ui.css" />

<script type="text/javascript">
	var pbBrowser       = (document.all) ? 'IE' : 'FF';
    var combo_image_on  = '';
    var combo_image_off = '';
  </script>
<script type="text/javascript" src="../includes/jquery-1.6.2.min.js"></script>
<script type="text/javascript" src="../includes/jquery-ui-1.8.16.custom.min.js"></script>
<script type="text/javascript" src="../includes/jquery.dataTables.min.js"></script>
<script type="text/javascript" src="../includes/common.js"></script>
  <?php require_once('pages/main/js_include.php'); ?>
 </head>
<body>
<?php require_once('pages/main/' . $include_template); ?>
</body>
</html>
<?php 
ob_end_flush();
session_write_close();
?>