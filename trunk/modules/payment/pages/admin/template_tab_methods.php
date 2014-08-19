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
<div title="<?php echo TEXT_METHODS;?>" id="tab_methods">
  <fieldset>
	<table class="ui-widget" style="border-collapse:collapse;width:100%;">
	 <thead class="ui-widget-header">
	  <tr>
	    <th colspan="2"><?php echo TEXT_METHODS_AVAILABLE; ?></th>
	    <th><?php echo TEXT_SORT_ORDER; ?></th>
	    <th><?php echo TEXT_ACTION; ?></th>
	  </tr>
	 </thead>
	 <tbody class="ui-widget-content">
	  <?php 
	if (sizeof($admin_classes['payment']->methods) > 0) foreach ($admin_classes['payment']->methods as $method) {
		$bkgnd = $method->installed? ' class="ui-state-active"' : '';
		if (file_exists(DIR_WS_MODULES . 'payment/methods/' . $method->id . '/images/logo.png')) {
			$logo = DIR_WS_MODULES . 'payment/methods/' . $method->id . '/images/logo.png';
		} elseif (file_exists(DIR_WS_MODULES . 'payment/methods/' . $method->id . '/images/logo.jpg')) {
		  	$logo = DIR_WS_MODULES . 'payment/methods/' . $method->id . '/images/logo.jpg';
		} elseif (file_exists(DIR_WS_MODULES . 'payment/methods/' . $method->id . '/images/logo.gif')) {
		  	$logo = DIR_WS_MODULES . 'payment/methods/' . $method->id . '/images/logo.gif';
		} else {
		  	$logo = DIR_WS_MODULES . 'payment/images/no_logo.png';
		}
		echo '      <tr>' . chr(10);
		echo '        <td>' . html_image($logo, $method->text, $width = '', $height = '32', $params = '') . '</td>' . chr(10);
		echo '        <td' . $bkgnd . '>' . $method->title . ' - ' . $method->description . '</td>' . chr(10);
		if (!$method->installed) {
	      	echo '        <td align="center">&nbsp;</td>' . chr(10);
		  	if ($security_level > 1) echo '        <td align="center">' . html_button_field('btn_' . $method->id, TEXT_INSTALL, 'onclick="submitToDo(\'install_' . $method->id . '\')"') . '</td>' . chr(10);
		  	echo '      </tr>' . chr(10);
		} else {
		  	echo "        <td align='center'>{$method->sort_order}</td>" . chr(10);
		  	echo '        <td align="center" nowrap="nowrap">' . chr(10);
		  	if ($security_level > 3) echo html_button_field('btn_' . $method->id, TEXT_REMOVE, 'onclick="if (confirm(\'' . TEXT_REMOVE_MESSAGE . '\')) submitToDo(\'remove_' . $method->id . '\')"') . chr(10);
		  	echo html_icon('categories/preferences-system.png', TEXT_PROPERTIES, 'medium', 'onclick="toggleProperties(\'prop_' . $method->id . '\')"') . chr(10);
		  	echo '</td>' . chr(10);
		  	echo '      </tr>' . chr(10);
		  	echo '      <tr id="prop_' . $method->id . '" style="display:none"><td colspan="3">';
		  	echo '<table width="100%" cellspacing="0" cellpadding="1">' . chr(10);
		  	if (defined('MODULE_PAYMENT_' . strtoupper($method->id) . '_TEXT_INTRODUCTION')) {
		    	echo '<tr><td colspan="2">' . constant('MODULE_PAYMENT_' . strtoupper($method->id) . '_TEXT_INTRODUCTION') . '</td></tr>';
		  	}
			foreach ($method->keys as $key) {
		    	echo '<tr><td colspan="2">' . $key['text'] . '</td><td>'; 
				echo $method->configure($key['key']); 
				echo '</td></tr>';
		  	}
		  	echo '</table></td></tr>' . chr(10);
		}
	}
?>
	 </tbody>
	</table>
  </fieldset>
</div>
