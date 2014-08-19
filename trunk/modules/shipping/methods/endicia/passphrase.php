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
//  Path: /modules/shipping/methods/endicia/signup.php
//

gen_pull_language('contacts');
gen_pull_language('payment');
load_method_language($mod_dir.$method);
$output = array(
  'width'  => '400',
  'action' => 'changePassPhrase',
  'method' => $method,
);

$html  = '<h1>' . ENDICIA_CHANGE_PASSPHRASE . '</h1>' . "\n";
$html .= '<table class="ui-widget" style="margin-left:auto;margin-right:auto">' . "\n";
$html .= ' <thead class="ui-widget-header"><tr><th colspan="2">' . '&nbsp;' . '</th></tr></thead>' . "\n";
$html .= ' <tbody class="ui-widget-content">' . "\n";
$html .= '  <tr>' . "\n";
$html .= '   <td colspan="2">' . SHIPPING_ENDICIA_PASSPHRASE_CHANGE_DESC . '</td>' . "\n";
$html .= '  </tr>' . "\n";
$html .= '  <tr>' . "\n";
$html .= '   <td>' . TEXT_PASSPHRASE_NOW . '</td>' . "\n";
$html .= '   <td>' . html_password_field('pass_phrase_current', '', true) . '</td>' . "\n";
$html .= '  </tr>' . "\n";
$html .= '  <tr>' . "\n";
$html .= '   <td>' . TEXT_PASSPHRASE . '</td>' . "\n";
$html .= '   <td>' . html_password_field('pass_phrase_new', '', true) . '</td>' . "\n";
$html .= '  </tr>' . "\n";
$html .= '  <tr>' . "\n";
$html .= '   <td>' . TEXT_PASSPHRASE_DUP . '</td>' . "\n";
$html .= '   <td>' . html_password_field('pass_phrase_confirm', '', true) . '</td>' . "\n";
$html .= '  </tr>' . "\n";
$html .= ' </tbody>' . "\n";
$html .= '</table>' . "\n";

$output['html'] = $html;
?>