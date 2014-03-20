<?php
// +-----------------------------------------------------------------+
// |                   PhreeBooks Open Source ERP                    |
// +-----------------------------------------------------------------+
// | Copyright(c) 2008-2013 PhreeSoft, LLC (www.PhreeSoft.com)       |

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
//  Path: /modules/shipping/pages/popup_label_image/pre_process.php
//
$security_level = \core\classes\user::validate(0, true);
/**************   page specific initialization  *************************/
require_once(DIR_FS_WORKING . 'defaults.php');
$todo   = $_REQUEST['action'];
$method = $_GET['method'];
$date   = explode('-',$_GET['date']);
$label  = $_GET['label'];
switch ($todo) {
  case 'notify':
  default:
	$image = (!$label) ? SHIPPING_TEXT_NO_LABEL : '';
	// show the form with a button to download
	break;
  case 'download':
	$file_path = SHIPPING_DEFAULT_LABEL_DIR.$method.'/'.$date[0].'/'.$date[1].'/'.$date[2].'/';
	$filename = $label . '.lpt';
	if (file_exists($file_path . $filename)) {
	  $file_size = filesize($file_path . $filename);
	  if (!$handle = @fopen($file_path . $filename, "r")) 	throw new \core\classes\userException(sprintf(ERROR_ACCESSING_FILE, $file_path . $filename));
	  if (!$image = @fread($handle, $file_size))  			throw new \core\classes\userException(sprintf(ERROR_READ_FILE,  	$file_path . $filename));
	  if (!@fclose($handle)) 								throw new \core\classes\userException(sprintf(ERROR_CLOSING_FILE, 	$file_path . $filename));
	  header('Content-type: application/octet-stream');
	  header('Content-Length: ' . $file_size);
	  header('Content-Disposition: attachment; filename=' . $filename);
	  header('Expires: 0');
	  header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
	  header('Pragma: public');
	  echo $image;
	  exit();
	} else {
	  $image = SHIPPING_TEXT_NO_LABEL;
	}
	break;
}
$custom_html      = true;
$include_header   = false;
$include_footer   = false;
$include_template = 'template_main.php';
?>