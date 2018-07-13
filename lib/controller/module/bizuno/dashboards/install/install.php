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
 * @copyright  2008-2018, PhreeSoft
 * @license    http://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 * @version    2.x Last Update: 2018-06-19
 * @filesource /lib/controller/module/bizuno/dashboards/install/install.php
 */

namespace bizuno;

define('DASHBOARD_INSTALL_VERSION','2.0');

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
        if (empty($GLOBALS['dbPortal']['name'])) { // collect username and database info as not logged in
            $lang['dbDesc']  = "Since your db connection has not been set, we'll need your database credentials to make sure we can connect to your db.";
            $html .= "<fieldset><legend>Database Settings</legend>".$lang['dbDesc']."<br />";
            $html .= html5('dbHost', ['label'=>'Database Host','attr'=>['value'=>'localhost']])."<br />";
            $html .= html5('dbName', ['label'=>'Database Name'])."<br />";
            $html .= html5('dbUser', ['label'=>'Database User name'])."<br />";
            $html .= html5('dbPass', ['label'=>'Database Password'])."<br />";
            $html .= html5('dbPrfx', ['label'=>'Database Prefix'])."</fieldset>";
        }
		$html .= '<div style="text-align:center">'.html5('btnInstall', $data['btnInstall']).'</div></form></div>';
        $html .= htmlJS("ajaxForm('frmInstall');");
        return $html;
	}
}
