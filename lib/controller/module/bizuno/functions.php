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
 * @copyright  2008-2019, PhreeSoft, Inc.
 * @license    http://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 * @version    3.x Last Update: 2018-12-10
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
