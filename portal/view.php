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
 * @copyright  2008-2019, PhreeSoft, Inc.
 * @license    http://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 * @version    3.x Last Update: 2018-12-17
 * @filesource portal/view.php
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
        $theme = getUserCache('profile', 'colors');
        if (!empty($theme)) { // OLD WAY - DEPRECATED SETTING CAN BE DELETED AFTER 30 DAYS FROM 9/15/2018
            $icons = getUserCache('profile', 'theme', false, 'default');
            setUserCache('profile', 'icons', $icons);
            setUserCache('profile', 'theme', $theme);
            clearUserCache('profile', 'colors');
        } else {
            $icons = getUserCache('profile', 'icons', false, 'default');
            $theme = getUserCache('profile', 'theme', false, 'default');
        }
        $pathTheme= $theme=='default' ? BIZUNO_URL.'view/easyUI/jquery-easyui/themes/' : BIZUNO_THEMES;
        $myBiz   = getUserCache('profile', 'biz_id',false, 0);
        $lang    = substr(getUserCache('profile', 'language', false, 'en_US'), 0, 2);
        $logoPath= getModuleCache('bizuno', 'settings', 'company', 'logo');
        $myDevice= !empty($GLOBALS['myDevice']) ? $GLOBALS['myDevice'] : 'desktop';
        $favicon = $logoPath ? BIZUNO_URL_FS."&src=" . getUserCache('profile', 'biz_id')."/images/$logoPath" : BIZUNO_LOGO;
        // Create page Head HTML
        $output['head']['meta'][]= ['order'=>10,'html'=>'<title>'.(!empty($data['title']) ? $data['title'] : getModuleCache('bizuno', 'properties', 'title')).'</title>'];
        $output['head']['meta'][]= ['order'=>20,'html'=>'<!-- p:'.clean('p',['format'=>'path_rel','default'=>''],'get').' -->'];
        $output['head']['meta'][]= ['order'=>30,'html'=>'<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />'];
        $output['head']['meta'][]= ['order'=>40,'html'=>'<meta name="viewport" content="width=device-width, initial-scale=1, minimum-scale=1.0, maximum-scale=1.0, minimal-ui" />'];
        $output['head']['meta'][]= ['order'=>90,'html'=>'<link rel="icon" type="image/png" href="'.$favicon.'" />'];
        $output['head']['css'][] = ['order'=>10,'html'=>'<link type="text/css" rel="stylesheet" href="'.$pathTheme .$theme.'/easyui.css" />'];
        $output['head']['css'][] = ['order'=>20,'html'=>'<link type="text/css" rel="stylesheet" href="'.BIZUNO_URL .'view/easyUI/jquery-easyui/themes/icon.css">'];
        $output['head']['css'][] = ['order'=>30,'html'=>'<link type="text/css" rel="stylesheet" href="'.BIZUNO_URL .'view/easyUI/stylesheet.css" />'];
        $output['head']['css'][] = ['order'=>40,'html'=>'<link type="text/css" rel="stylesheet" href="'.BIZUNO_SRVR.'bizunoCSS.php?icons='.$icons.'&code='.$myBiz.'" />'];
        $output['head']['js'][]  = ['order'=>10,'html'=>'<script type="text/javascript" src="'.BIZUNO_SRVR.'apps/jquery-3.2.1.js"></script>'];
        $output['head']['js'][]  = ['order'=>15,'html'=>'<script type="text/javascript" src="'.BIZUNO_URL .'view/easyUI/jquery-easyui/jquery.easyui.min.js?ver='.MODULE_BIZUNO_VERSION.'"></script>'];
        $output['head']['js'][]  = ['order'=>25,'html'=>'<script type="text/javascript" src="'.BIZUNO_URL .'view/easyUI/jquery-easyui/easyui-extensions.js?ver='.MODULE_BIZUNO_VERSION.'"></script>'];
        $output['head']['js'][]  = ['order'=>30,'html'=>'<script type="text/javascript" src="'.BIZUNO_SRVR.'apps/jquery-i18n/src/jquery.i18n.js?ver='.MODULE_BIZUNO_VERSION.'"></script>'];
        $output['head']['js'][]  = ['order'=>35,'html'=>'<script type="text/javascript" src="'.BIZUNO_SRVR.'apps/jquery-i18n/src/jquery.i18n.messagestore.js?ver='.MODULE_BIZUNO_VERSION.'"></script>'];
        $output['head']['js'][]  = ['order'=>40,'html'=>'<script type="text/javascript" src="'.BIZUNO_URL .'view/easyUI/jquery-easyui/locale/easyui-lang-'.$lang.'.js?ver='.MODULE_BIZUNO_VERSION.'"></script>'];
        $output['head']['js'][]  = ['order'=>45,'html'=>'<script type="text/javascript">var myDevice='."'$myDevice';var bizID=1;</script>"];
        $output['head']['js'][]  = ['order'=>50,'html'=>'<script type="text/javascript" src="'.BIZUNO_SRVR.'portal/view.js?ver='.MODULE_BIZUNO_VERSION.'"></script>'];
        $output['head']['js'][]  = ['order'=>55,'html'=>'<script type="text/javascript" src="'.BIZUNO_URL .'view/easyUI/common.js?ver='.MODULE_BIZUNO_VERSION.'"></script>'];
        if (getModuleCache('bizuno', 'settings', 'general', 'session_max') === 0) { $output['jsReady']['sessionClk'] = "refreshSessionClock();"; }
        switch ($myDevice) {
            case 'mobile': $html5->layoutMobile ($output, $data); break;
            case 'tablet': $html5->layoutTablet ($output, $data); break;
            case 'desktop':$html5->layoutDesktop($output, $data); break;
        }
    }
    
    public function viewMain()
    {
        $region   = getUserCache('profile', 'menu', false, 'left');
        $logoPath = getModuleCache('bizuno', 'settings', 'company', 'logo');
        $src      = $logoPath ? BIZUNO_URL_FS."&src=".getUserCache('profile', 'biz_id')."/images/$logoPath" : BIZUNO_LOGO;
        $htmlLogo = ['label'=>getModuleCache('bizuno', 'properties', 'title'),'events'=>['onClick'=>"hrefClick('');"],'attr'=>['type'=>'img','src'=>$src,'height'=>'48']];
        $version  = PHREEBOOKS_VERSION." (Bizuno Library ".MODULE_BIZUNO_VERSION.") ".getUserCache('profile', 'language')."-".getUserCache('profile', 'currency', false, 'No ISO');
        $company  = getModuleCache('bizuno', 'settings', 'company', 'primary_name').' - '.lang('period').': '.getModuleCache('phreebooks', 'fy', 'period').' | '.$version;
        $company .= ' | '.lang('copyright').' &copy;'.date('Y').' <a href="http://www.PhreeSoft.com" target="_blank">PhreeSoft&trade;</a>';
        if ($GLOBALS['bizunoModule'] <> 'bizuno') { $company .= '-'.$GLOBALS['bizunoModule'].' '.getModuleCache($GLOBALS['bizunoModule'], 'properties', 'status'); }
        $bizSearch= '<div style="text-align:center;padding-top:5px"><input class="easyui-searchbox" data-options="prompt:\''.lang('search').'\'" style="width:300px;text-align:center"></div>';
        if ($region == 'left') { $bizSearch= str_replace('padding-top:5px', 'padding-top:15px', $bizSearch); }
        if (!biz_validate_user()) { $bizSearch = ''; }
        $divHome   = '<div style="float:left;cursor:pointer">'.html5('imgFooter', $htmlLogo).'</div>';
        $footer    = '<div style="font-size:9px;float:right">'.$company."</div>\n";
        $data = ['type'=>'page', 'divs'=>[
            'qlinks' => ['order'=> 1,'type'=>'menu','region'=>'top','size'=>'large','classes'=>['menuHide'],'styles'=>['float'=>'right','display'=>'none'],'data'=>getUserCache('quickBar')],
            'imgHome'=> ['order'=> 2,'type'=>'html','region'=>'top','html'=>$divHome],
//          'bizsrch'=> ['order'=>50,'type'=>'html','region'=>'top','html'=>$bizSearch],
            'menu'   => ['order'=> 5,'type'=>'menu','region'=>$region,'classes'=>['menuHide'],'styles'=>['display'=>'none'],'data'=>getUserCache('menuBar')],
            'footer' => ['order'=>99,'type'=>'divs','region'=>'bottom','id'=>'bizFooter','divs'=>[
                'sTag' => ['order'=> 1,'type'=>'html','html'=>"<!-- Footer -->\n<footer>"],
                'stats'=> ['order'=>50,'type'=>'html','html'=>$footer],
                'eTag' => ['order'=>99,'type'=>'html','html'=>"</footer>"]]]],
            'jsReady'=> ['initPage'=>"jq('.menuHide').css('display', 'inline-block');"]];
        if (defined('BIZUNO_MY_FOOTER')) {
            $data['divs']['footer']['divs']['myFooter'] = ['order'=>10, 'type'=>'html', 'html'=>BIZUNO_MY_FOOTER];
        }
        if (!getUserCache('profile', 'biz_id')) { unset($data['divs']['menu']); }
        return $data;
    }

    public function renderDOM($output) {
        echo "<!DOCTYPE HTML>\n<html>";
        $this->renderHead($output['head']);
        switch ($GLOBALS['myDevice']) {
            case 'mobile': $this->renderMobile ($output['body']); break;
            case 'tablet': $this->renderTablet ($output['body']); break;
            default:
            case 'desktop':$this->renderDesktop($output['body']); break;
        }
        echo $output['raw'];
        echo "</html>";
    }
    
    private function renderHead($head)
    {
        echo "<head>\n";
        $meta = sortOrder($head['meta']);
        foreach ($meta as $value) { echo "\t{$value['html']}\n"; }
        $css = sortOrder($head['css']);
        foreach ($css as $value) { echo "\t{$value['html']}\n"; }
        $js = sortOrder($head['js']);
        foreach ($js as $value) { echo "\t{$value['html']}\n"; }
        echo "</head>\n";
    }
    
    private function renderMobile($body)
    {
        echo "<body>\n$body</div>\n".'<div id="navPopup" class="easyui-navpanel">'."</div>\n</body>\n"; // close the mobile navpanel div here, add popup panel
    }

    private function renderTablet($body)
    {
        return $this->renderMobile($body);
    }

    private function renderDesktop($body)
    {
        echo '<body class="easyui-layout">'."\n$body</body>\n";
    }
}