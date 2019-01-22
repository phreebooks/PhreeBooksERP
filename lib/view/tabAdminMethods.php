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
 * @copyright  2008-2019, PhreeSoft, Inc.
 * @license    http://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 * @version    3.x Last Update: 2019-01-05
 * @filesource /lib/view/tabAdminMethods.php
 */

namespace bizuno;

$module = isset($prop['settings']['module'])? $prop['settings']['module']: false;
$dirMeth= isset($prop['settings']['path'])  ? $prop['settings']['path']  : false;
// set the buttons
$viewData['btnMethodAdd']  = ['attr'=>['type'=>'button','value'=>lang('install')],'hidden'=>$viewData['security']> 1?false:true];
$viewData['btnMethodDel']  = ['attr'=>['type'=>'button','value'=>lang('remove')], 'hidden'=>$viewData['security']==4?false:true];
$viewData['btnMethodProp'] = ['icon'=>'settings','size'=>'large'];
$viewData['settingSave']   = ['icon'=>'save',    'size'=>'large'];

$output['body'] .= '<table style="border-collapse:collapse;width:100%">'."\n";
$output['body'] .= ' <thead class="panel-header">'."\n";
if ($dirMeth == 'dashboards') { // special case for dashboards
    $output['body'] .= "  <tr><th>&nbsp;</th><th>".lang('dashboard')."</th><th>".lang('description')."</th><th>".lang('action')."</th></tr>\n";
} else {
    $output['body'] .= "  <tr><th>&nbsp;</th><th>".lang('method')."</th><th>".lang('description')."</th><th>".lang('action')."</th></tr>\n";
}
$output['body'] .= " </thead>\n";
$output['body'] .= " <tbody>\n";
$methods = $module ? getModuleCache($module, $dirMeth) : [];
msgDebug("\nprop = ".print_r($prop, true));
foreach ($methods as $method => $settings) {
    $fqcn = "\\bizuno\\$method";
    bizAutoLoad("{$settings['path']}$method.php", $fqcn);
    if (empty($settings['settings'])) { $settings['settings'] = []; }
    $clsMeth = new $fqcn($settings['settings']);
    if (isset($clsMeth->hidden) && $clsMeth->hidden) { continue; }
    $output['body'] .= "  <tr>\n";
    $output['body'] .= '    <td valign="top">'.htmlFindImage($settings, "32")."</td>\n";
    $output['body'] .= '    <td valign="top" '.($settings['status'] ? ' style="background-color:lightgreen"' : '').">".$settings['title'].'</td>';
    $output['body'] .= "    <td><div>".$settings['description'];
    if ($dirMeth <> 'dashboards' && !$settings['status'] && $viewData['security'] > 1) {
        $output['body'] .= "</div></td>\n";
        $viewData['btnMethodAdd']['events']['onClick'] = "jsonAction('bizuno/settings/methodInstall&module=$module&path=$dirMeth&method=$method');";
        $output['body'] .= '    <td valign="top" style="text-align:right;">'.html5('install_'.$method, $viewData['btnMethodAdd'])."</td>\n";
    } else {
        $output['body'] .= "</div>";
        $output['body'] .= '<div id="divMethod_'.$method.'" style="display:none;" class="layout-expand-over">';
        $output['body'] .= html5("frmMethod_$method", ['attr'=>['type'=>'form','action'=>BIZUNO_AJAX."&p=bizuno/settings/methodSettingsSave&module=$module&type=$dirMeth&method=$method"]]);
        $structure = method_exists($clsMeth, 'settingsStructure') ? $clsMeth->settingsStructure() : [];
        foreach ($structure as $setting => $values) {
            $mult = isset($values['attr']['multiple']) ? '[]' : '';
            if (isset($values['attr']['multiple'])) { $values['attr']['value'] = explode(':', $values['attr']['value']); }
            $output['body'] .= html5($method.'_'.$setting.$mult, $values)."<br />\n";
        }
        $viewData['settingSave']['events']['onClick'] = "jq('#frmMethod_".$method."').submit();";
        $output['body'] .= '<div style="text-align:right">'.html5('imgMethod_'.$method, $viewData['settingSave']).'</div>';
        $output['body'] .= "</form></div>";
        $output['jsBody'][]  = "ajaxForm('frmMethod_$method');";
        $output['body'] .= "</td>\n";
        $output['body'] .= '<td valign="top" nowrap="nowrap" style="text-align:right;">' . "\n";
        $viewData['btnMethodDel']['events']['onClick'] = "if (confirm('".lang('msg_method_delete_confirm')."')) jsonAction('bizuno/settings/methodRemove&module=$module&type=$dirMeth&method=$method');";
        if ($viewData['security'] == 5 && $dirMeth <> 'dashboards' && (!isset($clsMeth->required) || !$clsMeth->required)) { 
            $output['body'] .= html5('remove_'.$method, $viewData['btnMethodDel']) . "\n";
        }
        $viewData['btnMethodProp']['events']['onClick'] = "jq('#divMethod_".$method."').toggle('slow');";
        $output['body'] .= html5('prop_'.$method, $viewData['btnMethodProp'])."\n";
        $output['body'] .= "</td>\n";
    }
    $output['body'] .= "  </tr>\n";
    $output['body'] .= '<tr><td colspan="5"><hr /></td></tr>'."\n";
}
$output['body'] .= " </tbody>\n";
$output['body'] .= "</table>\n";
