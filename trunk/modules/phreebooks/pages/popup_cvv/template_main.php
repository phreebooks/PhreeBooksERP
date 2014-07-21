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
//  Path: /modules/phreebooks/pages/popup_cvv/template_main.php
//
?>
<h1><?php echo HEADING_CVV; ?></h1>
<?php echo sprintf(TEXT_CVV_HELP1, html_image(DIR_WS_IMAGES . 'cvv2visa.gif')); ?>
<?php echo sprintf(TEXT_CVV_HELP2, html_image(DIR_WS_IMAGES . 'cvv2amex.gif')); ?>
<?php echo '<center><a href="javascript:window.close()">' . TEXT_CLOSE . '</a></center>'; ?>