<?php
/*
 * PhreeBooks 5 - Guest methods and log in verification
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
 * @version    3.x Last Update: 2019-01-21
 * @filesource /portal/guest.php
 */

namespace bizuno;

/**
 * Handles entry settings, configuration and environmental activities for PhreeSoft hosted users
 * This class varies depending on framework.
 */
class guest
{
    function __construct() {
        $this->lang = getLang('bizuno');
    }

    public function bizunoNewUser(&$layout=[])
    {
        $email  = clean('email',  'email','post');
        $pass   = clean('pass',   'text', 'post');
        $newPW  = clean('NewPW',  'text', 'post');
        $newPWRP= clean('NewPWRP','text', 'post');
        if (!$email || !$pass || !$newPW || !$newPWRP ) { return msgAdd($this->lang['plz_fill']); }
        //check e-mail
        if (!$user = dbGetRow(BIZUNO_DB_PREFIX.'users', "email='$email'")) { return msgAdd($this->lang['wrong_email']); }
        $passInfo = explode(":", $user['pw_reset'], 2);
        $passInfo[0] += 60*60*48; // 2 days
        if ($passInfo[0] > time() && $passInfo[1] == $pass) {
            $password = $this->passwordReset($newPW, $newPWRP);
            if ($password) {
                dbWrite(BIZUNO_DB_PREFIX.'users', ['password'=>$password, 'pw_reset'=>''], 'update', "email='$email'");
                $layout = array_replace_recursive($layout, ['content'=>['action'=>'eval', 'actionData'=>"window.location='index.php';"]]);
            }
        } else { msgAdd($this->lang['wrong_code_time']); }
    }

    /**
     * Makes a request for a new password.
     * This send a e-mail and send a reset code to confirm a password change.
     * @return boolean
     */
    public function bizunoLostPW()
    {
        $email = clean('email', 'email', 'post');
        if (!$name = dbGetRow(BIZUNO_DB_PREFIX.'users', "email='$email'")) { return msgAdd($this->lang['wrong_email']); }
        $password = randomValue(); //reset password
        dbWrite(BIZUNO_DB_PREFIX.'users', ['pw_reset'=>time().":".$password], 'update', "email='$email'");
        $link = BIZUNO_SRVR.'index.php?lost=true';
        $fromEmail= defined('BIZUNO_SUPPORT_EMAIL')? constant('BIZUNO_SUPPORT_EMAIL'): $this->guessAdmin();
        $fromName = defined('BIZUNO_SUPPORT_NAME') ? constant('BIZUNO_SUPPORT_NAME') : 'Site Admin';
        $subject  = $this->lang['email_sub_request'];
        $body     = sprintf($this->lang['email_request_pass'], $name['email'], $link, $link, $password);
        require_once(BIZUNO_LIB."model/mail.php");
        $mail     = new bizunoMailer($email, $name['email'], $subject, $body, $fromEmail, $fromName);
        if ($mail->sendMail()) { msgAdd($this->lang['request_pass'], 'success'); }
        msgLog("Sending password reset request to E-mail: " . $email);
    }

    /**
     * Resets the password for the user, requires a generated password and a time limit of two days.
     * @param string $email
     * @param string $userPW
     * @return boolean
     */
    public function bizunoResetPW(&$layout)
    {
        $email  = clean('email',   'email','post');
        $pass   = clean('pass',    'text', 'post');
        $newPW  = clean('NewPW',   'text', 'post');
        $newPWRP= clean('NewPWRP', 'text', 'post');
        if (!$email || !$pass || !$newPW || !$newPWRP ) { return msgAdd($this->lang['plz_fill']); }
        //check e-mail
        if (!$user = dbGetRow(BIZUNO_DB_PREFIX.'users', "email='$email'")) { return msgAdd($this->lang['wrong_email']); }
        $passInfo = explode(":", $user['pw_reset'], 2);
        $passInfo[0] += 60*60*48; // 2 days
        if (isset($passInfo[0]) && $passInfo[0] > time() && isset($passInfo[1]) && $passInfo[1] == $pass) {
            $password = $this->passwordReset($newPW, $newPWRP);
            if ($password) {
                dbWrite(BIZUNO_DB_PREFIX.'users', ['password'=>$password, 'pw_reset'=>''], 'update', "email='$email'");
                $layout = array_replace_recursive($layout, ['content'=>['action'=>'eval', 'actionData'=>"window.location='".BIZUNO_HOME."';"]]);
            }
        } else { return msgAdd($this->lang['wrong_code_time']); }
    }

    /**
     * Generates a new password, typically used for password reset operations
     * @param string $pw_new - New password to encrypt
     * @param string $pw_confirm - Repeat of new password to validate entry
     * @return string - encrypted password
     */
    public function passwordReset($pw_new, $pw_confirm)
    {
        if (strlen($pw_new) < 8)    { return msgAdd(lang('err_password_short')); }
        if ($pw_new <> $pw_confirm) { return msgAdd(lang('err_password_mismatch')); }
        return biz_hash_password($pw_new);
    }

    /**
     * Sets the paths for the modules, core and extensions needed to build the registry
     * @param type $coreOnly
     * @return string
     */
    public function getModuleList($coreOnly=false)
    {
        // Sequence is important, do not change!
        $core = ['bizuno','phreebooks','phreeform','contacts','inventory','payment'];
        foreach ($core as $module) { $modList[$module] = BIZUNO_LIB."controller/module/$module/"; }
        if (!biz_validate_user() || $coreOnly) { return $modList; }
        $extensions = is_dir(BIZUNO_EXT) ? scandir(BIZUNO_EXT) : []; // load extensions
        msgDebug("\nRead extensions folder ".BIZUNO_EXT." with result = ".print_r($extensions, true));
        foreach ($extensions as $name) { if (file_exists(BIZUNO_EXT."$name/admin.php")) { $modList[$name] = BIZUNO_EXT."$name/"; } }
        $myModules = is_dir(BIZUNO_CUSTOM) ? scandir(BIZUNO_CUSTOM) : []; // load custom modules
        msgDebug("\nRead custom folder with result = ".print_r($myModules, true));
        foreach ($myModules as $name) { if (file_exists(BIZUNO_CUSTOM."/$name/admin.php")) { $modList[$name] = BIZUNO_CUSTOM."/$name/"; } }
        return $modList;
    }

    /**
     * Add/Update the users table in the portal, record may be local but not in portal, in this case add it.
     * If add, check for dups at the portal, if edit get portal information to revise, could also be adding a
     * new business to users list
     * @param string $email - users email address
     * @param string $title - users title, from form
     * @param boolean - specifies whether this user is new to this business
     */
    public function portalSaveUser($email, $title='New User', $newUser=false)
    {
        require_once(BIZUNO_LIB."model/mail.php");
        $pData = dbGetRow(BIZUNO_DB_PREFIX.'users', "email='$email'");
        msgDebug("\nRead portal user data = ".print_r($pData, true));
        $pID   = isset($pData['admin_id']) ? $pData['admin_id'] : 0; // portal user record ID
        $portal= ['email'=>$email];
        if (!$pID || $newUser) { // send welcome new user email
            $fromEmail= getUserCache('profile', 'email');
            $fromName = getUserCache('profile', 'title');
            $bizTitle = getModuleCache('bizuno','settings', 'company', 'primary_name');
            $link = BIZUNO_SRVR.BIZUNO_HOME;
            if (!$pData['password']) { // send new user mail with temp password
                $portal['date_created']= date('Y-m-d H:i:s');
                $password = randomValue();
                $portal['pw_reset'] = time().":".$password;
                $link.= '&newuser=true';
                $body = sprintf($this->lang['email_new_portal_body'], $fromName, $bizTitle, $link, $link, $password);
            } else { // send new user email with you already have a bizuno login message
                $body = sprintf($this->lang['email_new_user_body'], $fromName, $bizTitle, $link, $link);
            }
            $mail = new bizunoMailer($email, $title, $this->lang['email_new_user_subject'], $body, $fromEmail, $fromName);
            $mail->sendMail();
        }
        dbWrite(BIZUNO_DB_PREFIX.'users', $portal, $pID?'update':'insert', "email='$email'");
    }

    public function installPreflight(&$layout=[])
    {
        msgDebug("\nentering installPreflight");
        global $db;
        session_start(); // need a session to keep some info to new reload
        // validate user
        $email = $_SESSION['UserEmail']= clean('UserEmail', 'email', 'post');
        $pass  = $_SESSION['UserPass'] = biz_hash_password(clean('UserPass', 'text', 'post'));
        $cookie= '["'.$email.'",1,'.time().']';
        $_COOKIE['bizunoSession'] = $cookie;
        setcookie('bizunoSession', $cookie, time()+(60*60*12), "/"); // 12 hours
        $GLOBALS['dbBizuno'] = $GLOBALS['dbPortal'] = ['type'=>'mysql',
            'host'  => clean('dbHost', 'text', 'post'),
            'name'  => clean('dbName', 'text', 'post'),
            'user'  => clean('dbUser', 'text', 'post'),
            'pass'  => clean('dbPass', 'text', 'post'),
            'prefix'=> clean('dbPrfx', 'text', 'post')];
        msgDebug("\ntrying to connect to db");
        $db = new db($GLOBALS['dbBizuno']);
        msgDebug("\ninstallPreflight after db connection connected = ".($db->connected?'true':'false'));
        if (!$db->connected) { return msgAdd("I'm unable to connect to the database, please check your credentials! "); }
        // update config file
        $rows = file('bizunoCFG-dist.php');
        foreach ($rows as $idx => $row) {
            if (strpos($row, "'BIZUNO_DB_HOST'"))  { $rows[$idx] = "define('BIZUNO_DB_HOST',  '{$GLOBALS['dbBizuno']['host']}');\n"; }
            if (strpos($row, "'BIZUNO_DB_NAME'"))  { $rows[$idx] = "define('BIZUNO_DB_NAME',  '{$GLOBALS['dbBizuno']['name']}');\n"; }
            if (strpos($row, "'BIZUNO_DB_USER'"))  { $rows[$idx] = "define('BIZUNO_DB_USER',  '{$GLOBALS['dbBizuno']['user']}');\n"; }
            if (strpos($row, "'BIZUNO_DB_PASS'"))  { $rows[$idx] = "define('BIZUNO_DB_PASS',  '{$GLOBALS['dbBizuno']['pass']}');\n"; }
            if (strpos($row, "'BIZUNO_DB_PREFIX'")){ $rows[$idx] = "define('BIZUNO_DB_PREFIX','{$GLOBALS['dbBizuno']['prefix']}');\n"; }
        }
        if (file_exists('bizunoCFG.php')) { chmod('bizunoCFG.php', 0644); } // make writable if not
        file_put_contents('bizunoCFG.php', $rows);
        chmod('bizunoCFG.php', 0444); // make not writable, if possible
        return true;
    }

    public function installBizuno()
    {
        // add to users table for this portal
        dbGetResult("ALTER TABLE ".BIZUNO_DB_PREFIX."users ADD `password` VARCHAR(64) DEFAULT '' COMMENT 'tag:Password;order:15' AFTER `email`");
        dbGetResult("ALTER TABLE ".BIZUNO_DB_PREFIX."users ADD `last_login` DATETIME DEFAULT NULL COMMENT 'tag:LastLogin;order:66' AFTER `attach`");
        dbGetResult("ALTER TABLE ".BIZUNO_DB_PREFIX."users ADD `date_created` DATETIME DEFAULT NULL COMMENT 'tag:DateCreated;order:68' AFTER `last_login`");
        dbGetResult("ALTER TABLE ".BIZUNO_DB_PREFIX."users ADD `date_updated` TIMESTAMP COMMENT 'tag:DateUpdated;order:72' AFTER `cache_date`");
        dbGetResult("ALTER TABLE ".BIZUNO_DB_PREFIX."users ADD `pw_reset` VARCHAR(255) DEFAULT '' COMMENT 'tag:PwReset;order:99' AFTER `settings`");
        // set the password now that user table exists
        session_start();
        $pass = $_SESSION['UserPass'];
        $lang = getUserCache('profile', 'language');
        dbWrite(BIZUNO_DB_PREFIX.'users', ['password'=>$pass,'last_login'=>date('Y-m-d h:i:s'),'date_created'=>date('Y-m-d h:i:s')], 'update', "admin_id=1");
        clearUserCache('profile', 'password');
        session_destroy();
        return true;
    }

    private function guessAdmin()
    {
        $email = dbGetValue(BIZUNO_DB_PREFIX.'users', 'email', "password<>''"); // should be the first hit, which is the installing admin
        if (!$email) { $email = 'admin@mysite.com'; }
        return $email;
    }
}
