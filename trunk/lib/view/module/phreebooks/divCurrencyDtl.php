<?php
/*
 * 2008-2018 PhreeSoft
 *
 * 
 *
 * PHP version 5

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
 * @version    2.x Last Update: 2017-06-01

 * @filesource /lib/view/module/phreebooks/divCurrencyDtl.php
 */

namespace bizuno;

htmlToolbar($output, $data, 'tbCurrency');
$output['body'] .= html5('frmCurrency', $data['form']['frmCurrency'])."
 <fieldset><legend>".lang('settings')."</legend>
  <table>
   <tbody>
    <tr>
     <td>".html5('title',  $data['fields']['title'])  .html5('is_def', $data['fields']['is_def'])  ."</td>
     <td>".html5('xrate',  $data['fields']['xrate']) ."</td>
     <td>".html5('code',   $data['fields']['code'])   ."</td>
    </tr>
    <tr>
     <td>".html5('dec_len',$data['fields']['dec_len'])."</td>
     <td>".html5('dec_pt', $data['fields']['dec_pt']) ."</td>
     <td>".html5('sep',    $data['fields']['sep'])    ."</td>
    </tr>
    <tr>
     <td>".html5('prefix', $data['fields']['prefix']) ."</td>
     <td>".html5('suffix', $data['fields']['suffix']) ."</td>
     <td>&nbsp</td>
     </tr>
    <tr>
     <td>".html5('pfxneg', $data['fields']['pfxneg']) ."</td>
     <td>".html5('sfxneg', $data['fields']['sfxneg']) ."</td>
     <td>&nbsp</td>
     </tr>
   </tbody>
  </table>
 </fieldset>\n";
/*
 <fieldset><legend>".lang('gl_defaults'.' - '.lang('customers'))."</legend>
  <table>
   <tbody>
    <tr>
     <td>".html5('gl_type_0',  $data['fields']['gl_type_0']) ."</td>
	 <td>".html5('gl_type_20', $data['fields']['gl_type_20'])."</td>
	 <td>".html5('gl_type_30', $data['fields']['gl_type_30'])."</td>
	 <td>".html5('gl_type_40', $data['fields']['gl_type_40'])."</td>
	</tr>
   </tbody>
  </table>
 </fieldset>
*/
$output['body'] .= '</form>';
$output['jsBody'][]  = "ajaxForm('frmCurrency');";
