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
//  Path: /modules/shipping/pages/popup_label_viewer/template_main.php
//
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<title><?php echo TITLE . ' - ' . COMPANY_NAME; ?></title>
<script type="text/javascript">
<!--
window.opener.location.reload();
// -->
</script>
</head>

<?php 
echo '<frameset rows="' . $row_string . '" cols="10%,90%">';
$idx = 0;
foreach ($content_list as $content) { 
  echo '<frame name="print_' . $idx . '" src="' . html_href_link(FILENAME_DEFAULT, 'module=shipping&amp;page=popup_label_button&amp;index=' . $idx, 'SSL') . '" />';
  echo '<frame name="content_' . $idx . '" src="' . $content . '" />';
  $idx++;
}
echo '</frameset>';
echo '<noframes>';
echo '  Your browser needs to support frames for the label print function to work.';
echo '</noframes>';
?>
</html>