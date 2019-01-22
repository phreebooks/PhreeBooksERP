<?php
/*
 * Functions related to logging in from portal
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
 * @version    3.x Last Update: 2018-09-27
 * @filesource lib/controller/module/bizuno/portal.php
 */

namespace bizuno;

/**
 * This is the main entry point to gain access to Bizuno but will mostly depend on the 
 * host system. The guest.php script will need to handle most of the functionality of
 * this class.
 */
bizAutoLoad(BIZUNO_ROOT."portal/guest.php", 'guest');

class bizunoPortal extends guest
{
    public $moduleID = 'bizuno';

    function __construct()
    {
        $this->lang = getLang($this->moduleID);
        parent::__construct();
    }
    
    public function login(&$layout=[])
    {
        $bID = getUserCache('profile', 'biz_id');
        if (biz_validate_user() && empty($GLOBALS['noBizunoDB'])) {
            msgLog(lang('user_login').": ".getUserCache('profile', 'email', false, 0));
            portalWrite('business', ['date_last_visit'=>date('Y-m-d h:i:s')], 'update', "id='$bID'");
            compose('bizuno', 'admin', 'loadBrowserSession', $layout); // get the browser data
            $sessionData = $layout['content'];
            unset($layout['content']);
            $action = "var sData=".json_encode($sessionData)."; sessionStorage.setItem('bizuno', JSON.stringify(sData)); window.location=bizunoHome;";
            $layout = array_replace_recursive($layout, ['content'=>['action'=>'eval','actionData'=>$action]]);
        } elseif (biz_validate_user() && !empty($GLOBALS['noBizunoDB'])) {
            $layout = array_replace_recursive($layout, ['content'=>['action'=>'eval','actionData'=>"jsonAction('bizuno/admin/installForm', $bID);"]]);
        }
    }

    /**
     * Logs a user off of Bizuno and destroys session, returns to index.php to log in
     */
    public function logout(&$layout=[]) {
        msgLog(lang('logout').": ".getUserCache('profile', 'title', false, ''));
        clearUserCache('profile', 'admin_encrypt');
        $qlinks = getUserCache('quickBar');
        $qlinks['child']['encrypt']['title'] = lang('bizuno_encrypt_enable');
        $qlinks['child']['encrypt']['icon']  = 'encrypt-off';
        setUserCache('quickBar', false, $qlinks);
        $usrEmail = getUserCache('profile', 'email', false, '');
        dbWriteCache($usrEmail); // save changes before invalidating cache
        dbWrite(BIZUNO_DB_PREFIX.'users', ['cache_date'=>''], 'update', "email='$usrEmail'");
        biz_user_logout();
        $layout = array_replace_recursive($layout, ['content'=>['action'=>'eval','actionData'=>"window.location=bizunoHome;"]]);
    }
}
