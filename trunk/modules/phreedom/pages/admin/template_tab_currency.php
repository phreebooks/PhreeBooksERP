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
//  Path: /modules/phreedom/pages/admin/template_currency_currency.php
//
$currency_toolbar = new \core\classes\toolbar('currency');
$currency_toolbar->icon_list['cancel']['show'] = false;
$currency_toolbar->icon_list['open']['show']   = false;
$currency_toolbar->icon_list['save']['show']   = false;
$currency_toolbar->icon_list['delete']['show'] = false;
$currency_toolbar->icon_list['print']['show']  = false;
if ($security_level > 1) $currency_toolbar->add_icon('new', 'onclick="loadPopUp(\'currency_new\', \'\')"', $order = 10);
if (ENABLE_MULTI_CURRENCY) {
  $currency_toolbar->add_icon('update', 'onclick="tabPage(\'currency\', \'update\')"', $order = 10);
  $currency_toolbar->icon_list['update']['text'] = SETUP_UPDATE_EXC_RATE;
}

?>
<div title="<?php echo SETUP_TITLE_CURRENCIES;?>" id="tab_currency">
    <?php echo $currency_toolbar->build_toolbar(); ?>
	<h1><?php echo $currencies->title; ?></h1>
    <div id="currency_content"><?php echo $currencies->build_main_html(); ?></div>
</div>
