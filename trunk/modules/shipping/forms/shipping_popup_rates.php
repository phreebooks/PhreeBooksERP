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
//  Path: /modules/shipping/forms/shipping_popup_rates.php
//

echo html_form('step2', FILENAME_DEFAULT, gen_get_all_get_params(array('action')));
echo html_hidden_field('action', '') . chr(10);
echo html_hidden_field('rowSeq', '') . chr(10);
// customize the toolbar actions
$toolbar->icon_list['cancel']['params'] = 'onclick="self.close()"';
$toolbar->icon_list['open']['show'] = false;
$toolbar->icon_list['save']['show'] = false;
$toolbar->icon_list['delete']['show'] = false;
$toolbar->icon_list['print']['show'] = false;
$toolbar->add_icon('back', 'onclick="submitToDo(\'back\')"', $order = 9);
$toolbar->add_help('09');
echo $toolbar->build_toolbar(); 
?>
<h1><?php echo SHIPPING_POPUP_WINDOW_RATE_TITLE; ?></h1>
<table>
<?php
	$temp = $rates['rates'];
	if (is_array($temp)) {
		ksort($temp);
		foreach ($temp as $key => $value) {
			// build the heading row
			echo '<tr><th colspan="6"><div align="center">';
			echo $shipping_defaults['service_levels'][$key] . '</div></th></tr>' . chr(10);
			echo '<tr>';
			echo '<th>' . SHIPPING_TEXT_CARRIER . '</th>' . chr(10);
			echo '<th>' . SHIPPING_TEXT_SERVICE . '</th>' . chr(10);
			echo '<th>' . SHIPPING_TEXT_FREIGHT_QUOTE . '</th>' . chr(10);
			echo '<th>' . SHIPPING_TEXT_BOOK_PRICE . '</th>' . chr(10);
			echo '<th>' . SHIPPING_TEXT_COST . '</th>' . chr(10);
			echo '<th>' . SHIPPING_TEXT_NOTES . '</th>' . chr(10);
			echo '</tr>';
			$odd = true;
			if (is_array($value)) foreach ($value as $carrier => $prices) {
				echo '<tr class="' . ($odd?'odd':'even') . '" style="cursor:pointer" onclick="setReturnRate(\'' . $prices['quote'] . '\', \'' . $carrier . '\', \'' . $key . '\')">' . chr(10);
				if (is_file('shipping/images/' . $carrier . '_logo.gif')) $file_name = 'shipping/images/' . $carrier . '_logo.gif';
				if (is_file('shipping/images/' . $carrier . '_logo.png')) $file_name = 'shipping/images/' . $carrier . '_logo.png';
				if (is_file('shipping/images/' . $carrier . '_logo.jpg')) $file_name = 'shipping/images/' . $carrier . '_logo.jgp';
				echo '<td align="center">' . ($no_image ? $carrier : html_image($file_name, $alt = '', '', '24')) . '</td>' . chr(10);
				echo '<td>' . constant($carrier . '_' . $key) . '</td>' . chr(10);
				echo '<td align="right">' . $currencies->format($prices['quote']) . '</td>' . chr(10);
				echo '<td align="right">' . $currencies->format($prices['book']) . '</td>' . chr(10);
				echo '<td align="right">' . $currencies->format($prices['cost']) . '</td>' . chr(10);
				echo '<td>' . $prices['note'] . '</td>' . chr(10);
				echo '</tr>';
				$odd = !$odd;
			}
		}
	}
?>
</table>