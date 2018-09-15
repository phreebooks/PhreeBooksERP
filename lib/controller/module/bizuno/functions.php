<?php
/*
 * Functions to support Bizuno module
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
 * @copyright  2008-2018, PhreeSoft, Inc.
 * @license    http://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 * @version    3.x Last Update: 2018-02-14
 * @filesource lib/controller/module/bizuno/functions.php
 */

namespace bizuno;

/**
 * Loads the list of available icon sets
 * @return array - list of icon sets 
 */
function getIcons()
{
	$icons = [];
	$choices= scandir(BIZUNO_LIB."view/icons/");
	foreach ($choices as $choice) {
		if (!in_array($choice, ['.','..']) && is_dir(BIZUNO_LIB."view/icons/$choice")) {
			$icons[] = ['id'=>$choice, 'text'=>ucwords(str_replace('-', ' ', $choice))];
		}
	}
    // look for extension themes
    if (!defined('BIZUNO_ICONS') || !is_dir(BIZUNO_ICONS)) { return $icons; }
    $extIcons = scandir(BIZUNO_ICONS);
    foreach ($extIcons as $choice) {
        if (!in_array($choice, ['.','..']) && is_dir(BIZUNO_ICONS.$choice)) {
            $icons[] = ['id'=>$choice, 'text'=>ucwords(str_replace('-', ' ', $choice))];
        }
    }
	return $icons;
}

/**
 * generates a keyed array of color choices from the DEFAULT theme folder
 * @return array - keyed array needs to be converted before rendering drop-dowm menu
 */
function getThemes()
{
	$themes = [];
	$choices = scandir(BIZUNO_LIB."view/easyUI/jquery-easyui/themes/");
	foreach ($choices as $choice) {
        if (!in_array($choice, ['.','..','icons']) && is_dir(BIZUNO_LIB."view/easyUI/jquery-easyui/themes/$choice")) { 
            $themes[] = ['id'=>$choice, 'text'=>ucwords(str_replace('-', ' ', $choice))];
        }
	}
    // look for extension themes
    if (!defined('BIZUNO_THEMES') || !is_dir(BIZUNO_THEMES)) { return $themes; }
    $extThemes = scandir(BIZUNO_THEMES);
    foreach ($extThemes as $choice) {
        if (!in_array($choice, ['.','..']) && is_dir(BIZUNO_THEMES.$choice)) {
            $themes[] = ['id'=>$choice, 'text'=>ucwords(str_replace('-', ' ', $choice))];
        }
    }
	return $themes;
}

/**
 * Loads additional tabs to the roles edit page for modules other than Bizuno
 * @param integer $security - security setting to control output displayed
 * @return string - HTML view
 */
function roleTabs($security=[])
{
    global $bizunoMod;
    $output= [];
	$order = 5;
	foreach ($bizunoMod as $mID => $props) { 
        if (!getModuleCache($mID, 'properties', 'status')) { continue; }
        if (!isset($props['properties']['path']) || !file_exists("{$props['properties']['path']}/admin.php")) { continue; }
        require_once("{$props['properties']['path']}/admin.php");
        $fqcn = "\\bizuno\\{$mID}Admin";
        $tmp = new $fqcn();
        if (!empty($tmp->structure['menuBar']['child']) || !empty($tmp->structure['quickBar']['child'])) {
            $tab = "<div>\n<fieldset><legend>".lang('security')."</legend>\n";
            if (!empty($tmp->structure['menuBar']['child'])) {
                $tab .= roleTabsChildren($tmp->structure['menuBar']['child'], $props['properties']['title'], $security);
            }
            if (!empty($tmp->structure['quickBar']['child'])) {
                $tab .= roleTabsChildren($tmp->structure['quickBar']['child'], $props['properties']['title'], $security);
            }
            $tab .= "</fieldset>\n</div>\n";
            $output[$mID] = ['type'=>'html', 'order'=>$order, 'label'=>$props['properties']['title'], 'html'=>$tab];
        }
        $order = $order + 5;
    }
	return $output;
}

/**
 * Sets the possible role security levels for menu children 
 * @param array $children - list of menu children
 * @param type $security - Security setting of parent
 * @return string - HTML view
 */
function roleTabsChildren($children=[], $title='', $security=0)
{
	$tab = '';
	$securityChoices = [
        ['id'=>'0', 'text'=>lang('none')],
        ['id'=>'1', 'text'=>lang('readonly')],
        ['id'=>'2', 'text'=>lang('add')],
        ['id'=>'3', 'text'=>lang('edit')],
        ['id'=>'4', 'text'=>lang('full')]];
	foreach ($children as $id => $props) {
        if (isset($props['child'])) { $tab .= roleTabsChildren($props['child'], $title, $security); }
		elseif (empty($props['required'])) {
			$value = array_key_exists($id, $security) ? $security[$id] : 0;
            if (!isset($props['label'])) { msgAdd("label not set: ".print_r($props, true)); }
            $label = $props['label'] == 'reports' ? lang($title).' - '.lang($props['label']) : lang($props['label']);
			$tab  .= html5("sID:$id", ['label'=>$label,'position'=>'after','values'=>$securityChoices,'attr'=>['type'=>'select','value'=>$value]])."<br />\n";
		}
	}
	return $tab;
}
