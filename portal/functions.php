<?php
/*
 * PhreeBooks 5 - Functions related to logging in from portal
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
 * @filesource /portal/functions.php
 */

namespace bizuno;

// set some application specific defines, in HTML format
define('BIZUNO_MY_FOOTER', '');

/**
 * Validates the user is logged in and returns the email address if true
 */
function biz_validate_user()
{
    $creds = clean('bizunoSession', 'json', 'cookie');
    // make sure BOTH user and business are set
    return !empty($creds[0]) && !empty($creds[1]) ? $creds : false;
}

/**
 * Verifies the username and password combination for a specified user ID from the Bizuno tables mapped to the portal
 * Uses the WordPress password verification algorithm
 * @param mixed $user - Bizuno user table, if type = id, then integer, else email
 * @param text $pass - password to verify in the portal
 * @param string $type - [default 'email'] set to 'id' for db record number
 * @return boolean
 */
function biz_validate_user_creds($user='', $pass='', $type='email')
{
    $email= $type=='id' ? dbGetValue(BIZUNO_DB_PREFIX.'users', 'email', "admin_id=$user") : $user;
    $row  = dbGetRow(BIZUNO_DB_PREFIX.'users', "email='$email'"); // make sure they have an account
    msgDebug("\nemail = $email, pass = $pass, and row = ".print_r($row, true));
    if ($row) {
        require_once(BIZUNO_ROOT.'portal/class-phpass.php');
        $wp_hasher = new \PasswordHash(8, true);
        if ($wp_hasher->CheckPassword($pass, $row['password'])) { return true; }
    }
    return msgAdd(lang('err_login_failed'));
}

function biz_hash_password($pass)
{
    require_once(BIZUNO_ROOT.'portal/class-phpass.php');
    $wp_hasher = new \PasswordHash(8, true);
    return $wp_hasher->HashPassword(trim($pass));
}

function biz_user_logout() 
{ setcookie('bizunoSession', '', time()-1, "/"); }

function viewSubMenu() { } // hook for creating menu bar within a page

function portalRead($table, $criteria='') 
{ return dbGetRow  (BIZUNO_DB_PREFIX.$table, $criteria); }

function portalMulti($table, $filter='', $order='', $field='', $limit=0) 
{ return dbGetMulti (BIZUNO_DB_PREFIX.$table, $filter,    $order,    $field,    $limit); }

function portalExecute($sql) 
{ return dbGetResult  ($sql); }

function portalWrite($table, $data=[], $action='insert', $parameters='') 
{
    if ('business'==$table) { return; }
    return dbWrite(BIZUNO_DB_PREFIX.$table, $data, $action, $parameters);
}

function portalDelete($email='') { portalExecute("DELETE FROM ".BIZUNO_DB_PREFIX."users WHERE email='$email'"); }
