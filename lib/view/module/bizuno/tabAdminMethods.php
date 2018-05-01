<?php
/*
 * View for Methods for all modules
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
 * @copyright  2008-2018, PhreeSoft Inc.
 * @license    http://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 * @version    2.x Last Update: 2018-04-25
 * @filesource /lib/view/module/bizuno/tabAdminMethods.php
 */

namespace bizuno;

$module= isset($prop['settings']['module'])? $prop['settings']['module']:false;
$type  = isset($prop['settings']['type'])  ? $prop['settings']['type']  :false;
// set the buttons
$data['btnMethodAdd']  = ['attr'=>['type'=>'button','value'=>lang('install')],'hidden'=>$data['security']> 1?false:true];
$data['btnMethodDel']  = ['attr'=>['type'=>'button','value'=>lang('remove')], 'hidden'=>$data['security']==4?false:true];
$data['btnMethodProp'] = ['icon'=>'settings','size'=>'large'];
$data['settingSave']   = ['icon'=>'save',    'size'=>'large'];

$output['body'] .= '<table style="border-collapse:collapse;width:100%">'."\n";
$output['body'] .= ' <thead class="panel-header">'."\n";
if ($type == 'dashboards') { // special case for dashboards
    $output['body'] .= "  <tr><th>&nbsp;</th><th>".lang('dashboard')."</th><th>".lang('description')."</th><th>".lang('action')."</th></tr>\n";
} else {
    $output['body'] .= "  <tr><th>&nbsp;</th><th>".lang('method')."</th><th>".lang('description')."</th><th>".lang('action')."</th></tr>\n";
}
$output['body'] .= " </thead>\n";
$output['body'] .= " <tbody>\n";
$methods = $module ? getModuleCache($module, $type) : [];
foreach ($methods as $method => $settings) {
    require_once("{$settings['path']}$method.php");
    if (empty($settings['settings'])) { $settings['settings'] = []; }
    $fqcn = "\\bizuno\\$method";
    $clsMeth = new $fqcn($settings['settings']);
    if (isset($clsMeth->hidden) && $clsMeth->hidden) { continue; }
    $output['body'] .= "  <tr>\n";
    $output['body'] .= '    <td valign="top">'.htmlFindImage($settings, "32")."</td>\n";
    $output['body'] .= '    <td valign="top" '.($settings['status'] ? ' style="background-color:lightgreen"' : '').">".$settings['title'].'</td>';
    $output['body'] .= "    <td><div>".$settings['description'];
    if ($type <> 'dashboards' && !$settings['status'] && $data['security'] > 1) {
        $output['body'] .= "</div></td>\n";
        $data['btnMethodAdd']['events']['onClick'] = "jsonAction('bizuno/settings/methodInstall&module=$module&type=$type&method=$method');";
        $output['body'] .= '    <td valign="top" style="text-align:right;">'.html5('install_'.$method, $data['btnMethodAdd'])."</td>\n";
    } else {
        $output['body'] .= "</div>";
        $output['body'] .= '<div id="divMethod_'.$method.'" style="display:none;" class="layout-expand-over">';
        $output['body'] .= html5("frmMethod_$method", ['attr'=>['type'=>'form','action'=>BIZUNO_AJAX."&p=bizuno/settings/methodSettingsSave&module=$module&type=$type&method=$method"]]);
        $structure = method_exists($clsMeth, 'settingsStructure') ? $clsMeth->settingsStructure() : [];
        foreach ($structure as $setting => $values) {
            $mult = isset($values['attr']['multiple']) ? '[]' : '';
            if (isset($values['attr']['multiple'])) { $values['attr']['value'] = explode(':', $values['attr']['value']); }
            $output['body'] .= html5($method.'_'.$setting.$mult, $values)."<br />\n";
        }
        $data['settingSave']['events']['onClick'] = "jq('#frmMethod_".$method."').submit();";
        $output['body'] .= '<div style="text-align:right">'.html5('imgMethod_'.$method, $data['settingSave']).'</div>';
        $output['body'] .= "</form></div>";
        $output['jsBody'][]  = "ajaxForm('frmMethod_$method');";
        $output['body'] .= "</td>\n";
        $output['body'] .= '<td valign="top" nowrap="nowrap" style="text-align:right;">' . "\n";
        $data['btnMethodDel']['events']['onClick'] = "if (confirm('".lang('msg_method_delete_confirm')."')) jsonAction('bizuno/settings/methodRemove&module=$module&type=$type&method=$method');";
        if ($data['security'] == 4 && $type <> 'dashboards' && (!isset($clsMeth->required) || !$clsMeth->required)) { 
            $output['body'] .= html5('remove_'.$method, $data['btnMethodDel']) . "\n";
        }
        $data['btnMethodProp']['events']['onClick'] = "jq('#divMethod_".$method."').toggle('slow');";
        $output['body'] .= html5('prop_'.$method, $data['btnMethodProp'])."\n";
        $output['body'] .= "</td>\n";
    }
    $output['body'] .= "  </tr>\n";
    $output['body'] .= '<tr><td colspan="5"><hr /></td></tr>'."\n";
}
$output['body'] .= " </tbody>\n";
$output['body'] .= "</table>\n";
