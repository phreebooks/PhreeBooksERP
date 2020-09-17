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
 * @copyright  2008-2020, PhreeSoft, Inc.
 * @license    http://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 * @version    3.x Last Update: 2020-09-16
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

    private function setEnvHTML(&$data=[])
    {
        $icons   = getUserCache('profile', 'icons', false, 'default');
        $theme   = getUserCache('profile', 'theme', false, 'bizuno');
        $pathTheme= in_array($theme, ['bizuno','default']) ? BIZUNO_URL.'view/easyUI/jquery-easyui/themes/' : BIZUNO_THEMES;
        $myBiz   = getUserCache('profile', 'biz_id',false, 0);
        $lang    = substr(getUserCache('profile', 'language', false, 'en_US'), 0, 2);
        $logoPath= getModuleCache('bizuno', 'settings', 'company', 'logo');
        $myDevice= !empty($GLOBALS['myDevice']) ? $GLOBALS['myDevice'] : 'desktop';
        $favicon = $logoPath ? BIZUNO_URL_FS."&src=".getUserCache('profile', 'biz_id')."/images/$logoPath" : BIZUNO_LOGO;
        // Create page Head HTML
        $data['head']['metaTitle']   = ['order'=>20,'type'=>'html','html'=>'<title>'.(!empty($data['title']) ? $data['title'] : getModuleCache('bizuno', 'properties', 'title')).'</title>'];
        $data['head']['metaPath']    = ['order'=>22,'type'=>'html','html'=>'<!-- route:'.clean('bizRt',['format'=>'path_rel','default'=>''],'get').' -->'];
        $data['head']['metaContent'] = ['order'=>24,'type'=>'html','html'=>'<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />'];
        $data['head']['metaViewport']= ['order'=>26,'type'=>'html','html'=>'<meta name="viewport" content="width=device-width, initial-scale=1, minimum-scale=0.9, maximum-scale=0.9" />'];
        $data['head']['metaMobile']  = ['order'=>30,'type'=>'html','html'=>'<meta name="mobile-web-app-capable" content="yes" />'];
        $data['head']['metaIcon']    = ['order'=>28,'type'=>'html','html'=>'<link rel="icon" type="image/png" href="'.$favicon.'" />'];
        $data['head']['cssTheme']    = ['order'=>40,'type'=>'html','html'=>'<link type="text/css" rel="stylesheet" href="'.$pathTheme.$theme.'/easyui.css" />'];
        $data['head']['cssIcon']     = ['order'=>42,'type'=>'html','html'=>'<link type="text/css" rel="stylesheet" href="'.BIZUNO_URL .'view/easyUI/jquery-easyui/themes/icon.css">'];
        $data['head']['cssStyle']    = ['order'=>44,'type'=>'html','html'=>'<link type="text/css" rel="stylesheet" href="'.BIZUNO_URL .'view/easyUI/stylesheet.css" />'];
        $data['head']['cssBizuno']   = ['order'=>46,'type'=>'html','html'=>'<link type="text/css" rel="stylesheet" href="'.BIZUNO_SRVR.'bizunoCSS.php?icons='.$icons.'&code='.$myBiz.'" />'];
        $data['head']['cssMobile']   = ['order'=>50,'type'=>'html','html'=>'<link type="text/css" rel="stylesheet" href="'.BIZUNO_URL .'view/easyUI/jquery-easyui/themes/mobile.css" />'];
        $data['head']['jsjQuery']    = ['order'=>60,'type'=>'html','html'=>'<script type="text/javascript" src="'.BIZUNO_SRVR.'apps/jquery-3.4.1.js"></script>'];
        $data['head']['jsEasyUI']    = ['order'=>62,'type'=>'html','html'=>'<script type="text/javascript" src="'.BIZUNO_URL .'view/easyUI/jquery-easyui/jquery.easyui.min.js?ver='.MODULE_BIZUNO_VERSION.'"></script>'];
        $data['head']['jsMobile']    = ['order'=>66,'type'=>'html','html'=>'<script type="text/javascript" src="'.BIZUNO_URL .'view/easyUI/jquery-easyui/jquery.easyui.mobile.js?ver='.MODULE_BIZUNO_VERSION.'"></script>'];
        $data['head']['jsI18N']      = ['order'=>68,'type'=>'html','html'=>'<script type="text/javascript" src="'.BIZUNO_SRVR.'apps/jquery-i18n/src/jquery.i18n.js?ver='.MODULE_BIZUNO_VERSION.'"></script>'];
        $data['head']['jsI18Nmsg']   = ['order'=>70,'type'=>'html','html'=>'<script type="text/javascript" src="'.BIZUNO_SRVR.'apps/jquery-i18n/src/jquery.i18n.messagestore.js?ver='.MODULE_BIZUNO_VERSION.'"></script>'];
        $data['head']['jsLang']      = ['order'=>72,'type'=>'html','html'=>'<script type="text/javascript" src="'.BIZUNO_URL .'view/easyUI/jquery-easyui/locale/easyui-lang-'.$lang.'.js?ver='.MODULE_BIZUNO_VERSION.'"></script>'];
        $data['head']['jsBizuno']    = ['order'=>74,'type'=>'html','html'=>'<script type="text/javascript">var myDevice='."'$myDevice';var bizID=".getUserCache('profile','biz_id',false,0).";</script>"];
        $data['head']['jsView']      = ['order'=>76,'type'=>'html','html'=>'<script type="text/javascript" src="'.BIZUNO_SRVR.'portal/view.js?ver='.MODULE_BIZUNO_VERSION.'"></script>'];
        $data['head']['jsCommon']    = ['order'=>78,'type'=>'html','html'=>'<script type="text/javascript" src="'.BIZUNO_URL .'view/easyUI/common.js?ver='.MODULE_BIZUNO_VERSION.'"></script>'];
        $data['head']['jsEasyExt']   = ['order'=>80,'type'=>'html','html'=>'<script type="text/javascript" src="'.BIZUNO_URL .'view/easyUI/jquery-easyui/easyui-extensions.js?ver='.MODULE_BIZUNO_VERSION.'"></script>'];
    }

    /**
     * Platform specific DOM, in this case is the full page
     * @param type $data
     */
    public function viewDOM($data) {
        msgDebug("\nEntering viewDOM");
        $this->setEnvHTML($data); // load the <head> HTML for pages
        $dom  = "<!DOCTYPE HTML>";
        $dom .= "<html>";
        $dom .= "<head>\n";
        $dom .= $this->renderHead($data);
        $dom .= "</head>\n";
        $dom .= "<body>\n";
        $dom .= '  <div id="bizBody" class="easyui-navpanel">'."\n";
        $dom .= $this->renderDivs($data);
        $dom .= "  </div>\n";
        $dom .= $this->renderJS($data);
        $dom .= '  <iframe id="attachIFrame" src="" style="display:none;visibility:hidden;"></iframe>'; // For file downloads
        $dom .= '  <div class="modal"></div><div id="divChart"></div><div id="navPopup" class="easyui-navpanel"></div>';
        $dom .= "\n</body>\n";
        $dom .= "</html>";
        return $dom;
    }
}
