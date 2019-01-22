<?php
/*
 * Bizuno dashboard - Install
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
 * @version    3.x Last Update: 2019-01-10
 * @filesource /lib/controller/module/bizuno/dashboards/install/install.php
 */

namespace bizuno;

define('DASHBOARD_INSTALL_VERSION','3.1');

class install
{
    public $moduleID = 'bizuno';
    public $methodDir= 'dashboards';
    public $code     = 'install';
    public $noSettings= true;
    public $noCollapse= true;
    public $noClose   = true;

    function __construct()
    {
        $this->security= empty($GLOBALS['bizuno_install_biz_id']) && getUserCache('profile', 'biz_id', false, 0) ? 0 : 1; // only for the portal to log in
        $this->hidden  = true;
        $this->lang    = getMethLang($this->moduleID, $this->methodDir, $this->code);
    }

    public function render($settings=[])
    {
        $bID = !empty($GLOBALS['bizuno_install_biz_id']) ? $GLOBALS['bizuno_install_biz_id'] : 0;
        if (!$bID) { return 'Biz_id cannot be zero!'; }
        $data = [
            'btnInstall' => ['attr'=>['type'=>'button','value'=>lang('install')],'styles'=>['cursor'=>'pointer'],
                'events' => ['onClick'=> "jq('#frmInstall').submit();"]]];
        $html  = '<div><p>'.$this->lang['instructions'].'</p>';
        $html .= '<form id="frmInstall" method="post" action="'.BIZUNO_AJAX.'&p=bizuno/admin/installPreFlight&bID='.$bID.'">';
        if (!getUserCache('profile', 'email', false, '')) { // collect username and database info as not logged in
            $lang['userDesc']= "Please set a username (email only) and password to set as your administrator of your business.";
            $html .= "<fieldset><legend>User Settings</legend>".$lang['userDesc']."<br />";
            $html .= html5('UserEmail', ['label'=>'User Email','attr'=>['size'=>40]])."<br />";
            $html .= html5('UserPass', ['label'=>'Password', 'attr'=>['type'=>'password']])."</fieldset>";
        }
        if (!function_exists('curl_init')) { msgAdd('Bizunio needs cURL to run properly. Please install/enable cURL PHP extension before performing any Input/Output operations.'); }
        if (!dbTableExists(BIZUNO_DB_PREFIX.'users') && !in_array(BIZUNO_HOST,['phreesoft','wordpress'])) { // collect username and database info as db has not been initialized
            $lang['dbDesc']  = "Since your db tables have not been set, we'll need your database credentials to make sure we can connect to your db.";
            $html .= "<fieldset><legend>Database Settings</legend>".$lang['dbDesc']."<br />";
            $html .= html5('dbHost', ['label'=>'Database Host',     'attr'=>['value'=>$GLOBALS['dbPortal']['host']]])  ."<br />";
            $html .= html5('dbName', ['label'=>'Database Name',     'attr'=>['value'=>$GLOBALS['dbPortal']['name']]])  ."<br />";
            $html .= html5('dbUser', ['label'=>'Database User name','attr'=>['value'=>$GLOBALS['dbPortal']['user']]])  ."<br />";
            $html .= html5('dbPass', ['label'=>'Database Password', 'attr'=>['value'=>$GLOBALS['dbPortal']['pass']]])  ."<br />";
            $html .= html5('dbPrfx', ['label'=>'Database Prefix',   'attr'=>['value'=>$GLOBALS['dbPortal']['prefix']]])."</fieldset>";
        }
        $html .= '<div style="text-align:center">'.html5('btnInstall', $data['btnInstall']).'</div></form></div>';
        $html .= htmlJS("ajaxForm('frmInstall');");
        return $html;
    }
}
