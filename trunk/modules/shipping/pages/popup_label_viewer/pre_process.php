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
//  Path: /modules/shipping/pages/popup_label_viewer/pre_process.php
//
$security_level = validate_user(0, true);
/**************   page specific initialization  *************************/
require_once(DIR_FS_WORKING . 'defaults.php');
/**************   page specific initialization  *************************/
$method           = $_GET['method'];
$date             = $_GET['date'];
$label_list       = $_GET['labels'];
$file_path        = SHIPPING_DEFAULT_LABEL_DIR.$method.'/'.str_replace('-', '/', $date).'/';
$browser_path     = SHIPPING_DEFAULT_LABEL_WS .$method.'/'.str_replace('-', '/', $date).'/';
$labels           = explode(':',$label_list);
if (count($labels) == 0) die('No labels were passed to label_viewer!');
$content_list     = array();
foreach ($labels as $one_label) {
  $cnt = 0;
  while (true) {
    $label = $one_label . ($cnt > 0 ? '-'.$cnt : '');
    if (is_file($file_path . $label . '.pdf')) { // PDF format
	  $content_list[] = $browser_path . $label . '.pdf';
    } elseif (is_file($file_path . $label . '.lpt')) {  // Thermal label
  	  $content_list[] = html_href_link(FILENAME_DEFAULT, 'module=shipping&amp;page=popup_label_image&amp;action=notify&amp;date=' . $date . '&amp;method=' . $method . '&amp;label=' . $label, 'SSL');
    } elseif (is_file($file_path . $label . '.gif')) { // GIF image
	  $content_list[] = $browser_path . $label . '.gif';
    } else {
  	  break;
    }
	$cnt++;
  }
}
$row_size         = intval(100 / count($content_list));
$row_string       = '';
for ($i = 0; $i < count($content_list); $i++) $row_string .= $row_size . '%,';
$row_string       = substr($row_string, 0, -1);

$custom_html      = true; // need custom header to support frames
$include_header   = false;
$include_footer   = false;
$include_template = 'template_main.php';

?>