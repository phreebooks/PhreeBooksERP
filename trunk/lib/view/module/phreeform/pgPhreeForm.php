<?php
/*
 * Main view template for Phreeform
 *
 * NOTICE OF LICENSE
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.TXT.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/OSL-3.0
 *
 * DISCLAIMER
 * Do not edit or add to this file if you wish to upgrade Bizuno to newer
 * versions in the future. If you wish to customize Bizuno for your
 * needs please refer to http://www.phreesoft.com for more information.
 *
 * @name       Bizuno ERP
 * @author     Dave Premo, PhreeSoft <support@phreesoft.com>
 * @copyright  2008-2018, PhreeSoft
 * @license    http://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 * @version    2.x Last Update: 2017-05-14

 * @filesource /lib/view/module/phreeform/pgPhreeForm.php
 */

namespace bizuno;

$output['body'] .= '
<table style="border-style:none;width:100%">'."
 <tr>".'
  <td width="30%" valign="top">
    <a href="#" class="easyui-linkbutton" onClick="jq(\'#treePhreeform\').tree(\'expandAll\');">'  .lang('expand_all')  .'</a>
    <a href="#" class="easyui-linkbutton" onClick="jq(\'#treePhreeform\').tree(\'collapseAll\');">'.lang('collapse_all')."</a><br />";
    htmlTree($output, $data, 'treePhreeform');
$output['body'] .= '  </td><td width="70%" valign="top"><fieldset><legend>'.lang('details').'</legend><div id="rightColumn">'."\n";
require (BIZUNO_LIB."view/module/phreeform/divHomeDetail.php");
$output['body'] .= "</div></fieldset></td></tr></table>\n";
