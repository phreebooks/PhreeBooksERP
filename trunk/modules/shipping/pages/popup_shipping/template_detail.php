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
//  Path: /modules/shipping/pages/popup_shipping/template_detail.php
//
echo html_form('step2', FILENAME_DEFAULT, gen_get_all_get_params(array('action'))) . chr(10);
// include hidden fields
echo html_hidden_field('action', '')   . chr(10);
echo html_hidden_field('rowSeq', '') . chr(10);
// customize the toolbar actions
$toolbar->icon_list['cancel']['params'] = 'onclick="self.close()"';
$toolbar->icon_list['open']['show']     = false;
$toolbar->icon_list['save']['show']     = false;
$toolbar->icon_list['delete']['show']   = false;
$toolbar->icon_list['print']['show']    = false;
$toolbar->add_icon('back', 'onclick="submitToDo(\'back\')"', $order = 9);
if (count($extra_toolbar_buttons) > 0) foreach ($extra_toolbar_buttons as $key => $value) $toolbar->icon_list[$key] = $value;
$toolbar->add_help('09');
echo $toolbar->build_toolbar();
// Build the page
?>
<h1><?php echo SHIPPING_POPUP_WINDOW_RATE_TITLE; ?></h1>
<?php
  $temp = $rates['rates'];
//echo 'temp array = '; print_r($temp); echo '<br />';
  if (is_array($temp)) {
	ksort($temp);
	foreach ($temp as $carrier => $value) {
		// build the heading row
		echo '<table class="ui-widget" style="border-collapse:collapse;width:100%">';
		echo '<thead class="ui-widget-header"><tr>';
		$def_filename  = DIR_WS_MODULES . 'shipping/methods/' . $carrier . '/images/logo';
		$filename = false;
		if      (is_file($def_filename . '.gif'))  { $filename = $def_filename . '.gif'; }
		else if (is_file($def_filename . '.png'))  { $filename = $def_filename . '.png'; }
		else if (is_file($def_filename . '.jpg'))  { $filename = $def_filename . '.jpg'; }
		echo '<th width="30%" align="center">' . ($filename ? html_image($filename, $alt = $method, '', '30') : constant('MODULE_SHIPPING_'.strtoupper($carrier).'_TITLE_SHORT')) . '</th>' . chr(10);
		echo '<th width="15%">' . SHIPPING_TEXT_FREIGHT_QUOTE . '</th>' . chr(10);
		echo '<th width="15%">' . SHIPPING_TEXT_BOOK_PRICE    . '</th>' . chr(10);
		echo '<th width="15%">' . SHIPPING_TEXT_COST          . '</th>' . chr(10);
		echo '<th width="30%">' . SHIPPING_TEXT_NOTES         . '</th>' . chr(10);
		echo '</tr></thead><tbody class="ui-widget-content">' . chr(10);
		$odd = true;
		if (is_array($value)) foreach ($value as $key => $prices) {
			echo '<tr class="' . ($odd?'odd':'even') . '" style="cursor:pointer" onclick="setReturnRate(\'' . $currencies->format($prices['quote']) . '\', \'' . $carrier . '\', \'' . $key . '\')">' . chr(10);
			echo '<td>' . constant($carrier . '_' . $key) . '</td>' . chr(10);
			echo '<td align="right">' . (($prices['quote'] !== '') ? $currencies->format($prices['quote']) : '&nbsp;') . '</td>' . chr(10);
			echo '<td align="right">' . (($prices['book']  !== '') ? $currencies->format($prices['book']) : '&nbsp;') . '</td>' . chr(10);
			echo '<td align="right">' . (($prices['cost']  !== '') ? $currencies->format($prices['cost']) : '&nbsp;') . '</td>' . chr(10);
			echo '<td align="center">' . $prices['note'] . '</td>' . chr(10);
			echo '</tr>';
			$odd = !$odd;
		}
		echo '</tbody></table>';
	}
  }
?>
</form>