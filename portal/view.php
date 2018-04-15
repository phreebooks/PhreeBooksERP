<?php
/*
 * PhreeBooks 5 - View methods tailored for this host
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
 * @version    2.x Last Update: 2018-03-28
 * @filesource /portal/view.php
 */

namespace bizuno;

/**
 * Handles page rendering specific to the distribution
 * This class varies depending on framework.
 */
class portalView
{
    function __construct() { }
    
    public function setEnvHTML(&$output, $data)
    {
        global $html5;
        $theme   = getUserCache('profile', 'theme', false, 'default');
        $color   = getUserCache('profile', 'colors',false, 'default');
        $myBiz   = getUserCache('profile', 'biz_id',false, 0);
        $lang    = substr(getUserCache('profile', 'language', false, 'en_US'), 0, 2);
        $logoPath= getModuleCache('bizuno', 'settings', 'company', 'logo');
        $favicon = $logoPath ? BIZUNO_URL_FS."&src=" . getUserCache('profile', 'biz_id')."/images/$logoPath" : BIZUNO_LOGO;
        // Create page Head HTML
        $output['head'] .= '
    <title>' . (isset($data['pageTitle']) ? $data['pageTitle'] : getModuleCache('bizuno', 'properties', 'title')) . '</title>
    <!-- p: ' . clean('p', ['format' => 'path_rel', 'default' => ''], 'get') . ' -->
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <link rel="icon" type="image/png" href="'.$favicon.'" />
    <link type="text/css" rel="stylesheet" href="'.BIZUNO_URL .'view/easyUI/jquery-easyui/themes/'.$color.'/easyui.css" />
    <link type="text/css" rel="stylesheet" href="'.BIZUNO_URL .'view/easyUI/jquery-easyui/themes/icon.css">
    <link type="text/css" rel="stylesheet" href="'.BIZUNO_URL .'view/easyUI/stylesheet.css" />
    <link type="text/css" rel="stylesheet" href="'.BIZUNO_SRVR.'bizunoCSS.php?style='.$theme.'&code='.$myBiz.'" />
    <script type="text/javascript" src="'.BIZUNO_SRVR.'apps/jquery-3.2.1.js"></script>
    <script type="text/javascript" src="'.BIZUNO_URL .'view/easyUI/jquery-easyui/jquery.easyui.min.js?ver='.MODULE_BIZUNO_VERSION.'"></script>
    <script type="text/javascript" src="'.BIZUNO_URL .'view/easyUI/jquery-easyui/easyui-extensions.js?ver='.MODULE_BIZUNO_VERSION.'"></script>
    <script type="text/javascript" src="'.BIZUNO_SRVR.'apps/jquery-i18n/src/jquery.i18n.js?ver='.MODULE_BIZUNO_VERSION.'"></script>
    <script type="text/javascript" src="'.BIZUNO_SRVR.'apps/jquery-i18n/src/jquery.i18n.messagestore.js?ver='.MODULE_BIZUNO_VERSION.'"></script>
    <script type="text/javascript" src="'.BIZUNO_URL .'view/easyUI/jquery-easyui/locale/easyui-lang-'.$lang.'.js?ver='.MODULE_BIZUNO_VERSION.'"></script>
    <script type="text/javascript">var bizID='.getUserCache('profile', 'biz_id',false, 0).';</script>
    <script type="text/javascript" src="'.BIZUNO_SRVR.'portal/view.js?ver='       .MODULE_BIZUNO_VERSION.'"></script>
    <script type="text/javascript" src="'.BIZUNO_URL .'view/easyUI/common.js?ver='.MODULE_BIZUNO_VERSION.'"></script>'."\n";
        // for standalone javascript that needs to be in the head
        if (getModuleCache('bizuno', 'settings', 'general', 'session_max') === 0) { $output['jsBody'][] = "refreshSessionClock();"; }
        switch (viewScreenSize()) {
            case 'small':  $html5->layoutMobile( $output, $data); break;
            case 'medium': $html5->layoutTablet( $output, $data); break;
            case 'large':  $html5->layoutDesktop($output, $data); break;
        }
    }
    
    public function viewMain()
    {
        $region   = getUserCache('profile', 'menu', false, 'left');
        $logoPath = getModuleCache('bizuno', 'settings', 'company', 'logo');
        $src      = $logoPath ? BIZUNO_URL_FS."&src=".getUserCache('profile', 'biz_id')."/images/$logoPath" : BIZUNO_LOGO;
        $htmlLogo = ['label'=>getModuleCache('bizuno', 'properties', 'title'),'events'=>['onClick'=>"hrefClick('');"],'attr'=>['type'=>'img','src'=>$src,'height'=>'48']];
        $portal   = explode('.', $_SERVER['SERVER_ADDR']);
        $version  = getModuleCache('bizuno', 'properties', 'version', false, 'v?')."-{$portal[3]}-".getUserCache('profile', 'language')."-".getUserCache('profile', 'currency', false, 'No ISO');
        $company  = getModuleCache('bizuno', 'settings', 'company', 'primary_name').' - '.lang('period').': '.getModuleCache('phreebooks', 'fy', 'period').' | '.$version;
        $company .= ' - '.getModuleCache('bizuno', 'properties', 'title').' | '.lang('copyright').' &copy;'.date('Y').' <a href="http://www.PhreeSoft.com" target="_blank">PhreeSoft&trade;</a>';
        if ($GLOBALS['bizunoModule'] <> 'bizuno') { $company .= '-'.$GLOBALS['bizunoModule'].' '.getModuleCache($GLOBALS['bizunoModule'], 'properties', 'status'); }
        $bizSearch= '<div style="text-align:center;padding-top:5px"><input class="easyui-searchbox" data-options="prompt:\''.lang('search').'\'" style="width:300px;text-align:center"></div>';
        if ($region == 'left') { $bizSearch= str_replace('padding-top:5px', 'padding-top:15px', $bizSearch); }
        if (!biz_validate_user()) { $bizSearch = ''; }
        $divHome   = '<div style="float:left">'.html5('imgFooter', $htmlLogo).'</div>';
        $footer    = '<div style="font-size:9px;float:right">'.$company."</div>\n";
        $data = ['type'=>'page', 'divs'=>[
            'qlinks' => ['order'=> 1,'type'=>'menu','region'=>'top','size'=>'large','styles'=>['float'=>'right'],'data'=>getUserCache('quickBar')],
            'imgHome'=> ['order'=> 2,'type'=>'html','region'=>'top','html'=>$divHome],
//          'bizsrch'=> ['order'=>50,'type'=>'html','region'=>'top','html'=>$bizSearch],
            'menu'   => ['order'=> 5,'type'=>'menu','region'=>$region,'data'=>getUserCache('menuBar')],
            'footer' => ['order'=>99,'type'=>'divs','region'=>'bottom','id'=>'bizFooter','divs'=>[
                'sTag' => ['order'=> 1,'type'=>'html','html'=>"<!-- Footer -->\n<footer>"],
                'stats'=> ['order'=>50,'type'=>'html','html'=>$footer],
                'eTag' => ['order'=>99,'type'=>'html','html'=>"</footer>"]]]]];
        if (defined('BIZUNO_MY_FOOTER')) {
            $data['divs']['footer']['divs']['myFooter'] = ['order'=>10, 'type'=>'html', 'html'=>BIZUNO_MY_FOOTER];
        }
        if (!getUserCache('profile', 'biz_id')) { unset($data['divs']['menu']); }
        return $data;
    }

    public function renderDOM($output) {
		echo "<!DOCTYPE HTML>
<html>
<head>".$output['head']."</head>
<body class=\"easyui-layout\">".$output['body']."</body>".
    $output['raw']."
</html>";
    }
}