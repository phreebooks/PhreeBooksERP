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
//  Path: /modules/payment/pages/admin/template_tab_methods.php
//

?>
<div id="tab_methods">
  <fieldset>
	<table class="ui-widget" style="border-collapse:collapse;width:100%;">
	 <thead class="ui-widget-header">
	  <tr>
	    <th colspan="2"><?php echo TEXT_PAYMENT_MODULES_AVAILABLE; ?></th>
	    <th><?php echo TEXT_SORT_ORDER; ?></th>
	    <th><?php echo TEXT_ACTION; ?></th>
	  </tr>
	 </thead>
	 <tbody class="ui-widget-content">
	  <?php 
  if (sizeof($methods) > 0) foreach ($methods as $method) {
  	// load the method properties
	require_once($method_dir . $method . '/' . $method . '.php');
	$properties = new $method;
	
    $installed = defined('MODULE_PAYMENT_' . strtoupper($properties->code) . '_STATUS');
	$bkgnd = $installed ? ' class="ui-state-active"' : '';
	if (file_exists(DIR_WS_MODULES . 'payment/methods/' . $properties->code . '/images/logo.png')) {
	  $logo = DIR_WS_MODULES . 'payment/methods/' . $properties->code . '/images/logo.png';
	} elseif (file_exists(DIR_WS_MODULES . 'payment/methods/' . $properties->code . '/images/logo.jpg')) {
	  $logo = DIR_WS_MODULES . 'payment/methods/' . $properties->code . '/images/logo.jpg';
	} elseif (file_exists(DIR_WS_MODULES . 'payment/methods/' . $properties->code . '/images/logo.gif')) {
	  $logo = DIR_WS_MODULES . 'payment/methods/' . $properties->code . '/images/logo.gif';
	} else {
	  $logo = DIR_WS_MODULES . 'payment/images/no_logo.png';
	}
	echo '      <tr>' . chr(10);
	echo '        <td>' . html_image($logo, $properties->title, $width = '', $height = '32', $params = '') . '</td>' . chr(10);
	echo '        <td' . $bkgnd . '>' . $properties->title . ' - ' . $properties->description . '</td>' . chr(10);
	if (!$installed) {
      echo '        <td align="center">&nbsp;</td>' . chr(10);
	  if ($security_level > 1) echo '        <td align="center">' . html_button_field('btn_' . $properties->code, TEXT_INSTALL, 'onclick="submitToDo(\'install_' . $properties->code . '\')"') . '</td>' . chr(10);
	  echo '      </tr>' . chr(10);
	} else {
	  echo '        <td align="center">' . $properties->getsortorder() . '</td>' . chr(10);//constant('MODULE_PAYMENT_' . strtoupper($method) . '_SORT_ORDER') . '</td>' . chr(10);
	  echo '        <td align="center" nowrap="nowrap">' . chr(10);
	  if ($security_level > 3) echo html_button_field('btn_' . $properties->code, TEXT_REMOVE, 'onclick="if (confirm(\'' . TEXT_REMOVE_MESSAGE . '\')) submitToDo(\'remove_' . $properties->code . '\')"') . chr(10);
	  echo html_icon('categories/preferences-system.png', TEXT_PROPERTIES, 'medium', 'onclick="toggleProperties(\'prop_' . $properties->code . '\')"') . chr(10);
	  echo '</td>' . chr(10);
	  echo '      </tr>' . chr(10);
	  // load the method properties
	  echo '      <tr id="prop_' . $properties->code . '" style="display:none"><td colspan="3">';
	  echo '<table width="100%" cellspacing="0" cellpadding="1">' . chr(10);
	  if (defined('MODULE_PAYMENT_' . strtoupper($properties->code) . '_TEXT_INTRODUCTION')) {
	    echo '<tr><td colspan="2">' . constant('MODULE_PAYMENT_' . strtoupper($properties->code) . '_TEXT_INTRODUCTION') . '</td></tr>';
	  }
	  foreach ($properties->keys() as $value) {
	    echo '<tr><td colspan="2">' . $value['text'] . '</td><td>'; 
		echo $properties->configure($value['key']); 
		echo '</td></tr>';
	  }
	  echo '      </table></td></tr>' . chr(10);
	}
  }
?>
	 </tbody>
	</table>
  </fieldset>
</div>
