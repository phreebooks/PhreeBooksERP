<?php
/*
 * View for Tools tab in Bizuno Settings
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
 * @version    2.x Last Update: 2017-06-01
 * @filesource /lib/view/module/bizuno/admin_tools.php
 */

namespace bizuno;

$output['body'] .= "
<fieldset><legend>".$viewData['lang']['admin_status_update']."</legend>
    <p>".$viewData['lang']['desc_status_seq_change']."</p>
    ".html5('frmReference', $viewData['forms']['frmReference'])."
    <table>\n";
    foreach ($viewData['status_fields'] as $key => $settings) { if ($key != 'id') { 
        $output['body'] .= "    <tr><td>".html5($key, $settings)."</td></tr>\n";
    } }
$output['body'] .= "	<tr><td>".html5('status_btn', $viewData['status_btn'])."</td></tr>
    </table>
    </form>
</fieldset>
<fieldset><legend>".$viewData['lang']['admin_encrypt_update']."</legend>
    <p>".$viewData['lang']['desc_encrypt_config'].'</p>
    <table style="border-style:none;margin-left:auto;margin-right:auto">
        <tbody>
            <tr><td>'.html5('encrypt_key_orig',$viewData['encrypt_key_orig'])."</td></tr>
            <tr><td>".html5('encrypt_key_new', $viewData['encrypt_key_new']) ."</td></tr>
            <tr><td>".html5('encrypt_key_dup', $viewData['encrypt_key_dup']) ."</td></tr>
            <tr><td>".html5('encrypt_key_btn', $viewData['encrypt_key_btn']) ."</td></tr>
        </tbody>
    </table>
</fieldset>
<fieldset><legend>".$viewData['lang']['btn_security_clean']."</legend>
    <p>".$viewData['lang']['desc_security_clean']."</p>
    <p>".$viewData['lang']['desc_security_clean_date'].' '.html5('encrypt_clean_date', $viewData['encrypt_clean_date']).html5('encrypt_clean_btn', $viewData['encrypt_clean_btn']).'</p>
</fieldset>';
$output['jsBody'][] = "ajaxForm('frmReference');";
