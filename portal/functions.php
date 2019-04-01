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
 * @copyright  2008-2019, PhreeSoft, Inc.
 * @license    http://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 * @version    3.x Last Update: 2018-09-21
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
function biz_validate_user_creds($user='', $pass='', $type='email', $verbose=true)
{
    $email= $type=='id' ? dbGetValue(BIZUNO_DB_PREFIX.'users', 'email', "admin_id=$user") : $user;
    $row  = dbGetRow(BIZUNO_DB_PREFIX.'users', "email='$email'"); // make sure they have an account
    msgDebug("\nemail = $email, pass = $pass, and row = ".print_r($row, true));
    if ($row) {
        require_once(BIZUNO_ROOT.'portal/class-phpass.php');
        $wp_hasher = new \PasswordHash(8, true);
        if ($wp_hasher->CheckPassword($pass, $row['password'])) { return true; }
    }
    return $verbose ? msgAdd(lang('err_login_failed')) : false;
}

function biz_hash_password($pass)
{
    require_once(BIZUNO_ROOT.'portal/class-phpass.php');
    $wp_hasher = new \PasswordHash(8, true);
    return $wp_hasher->HashPassword(trim($pass));
}

function biz_user_logout()
{
    $email = getUserCache('profile', 'email');
    portalWrite('users', ['cache_date'=>''], 'update', "biz_user='$email'");
    setcookie('bizunoSession', '', time()-1, "/");
}

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
    $params = str_replace('biz_user', 'email', $parameters);
    if (!empty($data['biz_pass'])) { $data['password'] = $data['biz_pass']; unset($data['biz_pass']); }
    return dbWrite(BIZUNO_DB_PREFIX.$table, $data, $action, $params);
}

function portalDelete($email='') { portalExecute("DELETE FROM ".BIZUNO_DB_PREFIX."users WHERE email='$email'"); }

function portalUpdateBizID() { }

/**
 * This method retrieves data from a remote server using cURL
 * @param string $url - url to request data
 * @param string $data - data string, will be attached for get and through setopt as post or an array
 * @param string $type - [default 'get'] Choices are 'get' or 'post'
 * @return result if successful, false (plus messageStack error) if fails
 */
function portalCurl($url, $data='', $type='get', $opts=[]) {
    $size = is_array($data) ? 'array('.sizeof($data).')' : strlen($data);
    msgDebug("\nAt class io, sending request of length $size to url: $url via $type with opts = ".print_r($opts, true));
    if ($type == 'get') { $url = $url.'?'.$data; }
    $ch = curl_init();
    if (isset($opts) && sizeof($opts)>0 ) { foreach ($opts as $opt => $value) {
        switch ($opt) {
            case 'useragent': curl_setopt($ch, CURLOPT_USERAGENT, $this->useragent); break;
            default:          curl_setopt($ch, constant($opt), $value); break;
        }
    } }
    curl_setopt($ch, CURLOPT_URL,           $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER,true);
    curl_setopt($ch, CURLOPT_TIMEOUT,       30); // in seconds
    curl_setopt($ch, CURLOPT_HEADER,        false);
    curl_setopt($ch, CURLOPT_VERBOSE,       false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER,false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST,false);
    if (strtolower($type) == 'post') {
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, is_array($data) ? http_build_query($data) : $data);
    }
    // for debugging cURL issues, uncomment below
//      $fp = fopen($this->myFolder."cURL_trace.txt", 'w');
//      curl_setopt($ch, CURLOPT_VERBOSE, true);
//      curl_setopt($ch, CURLOPT_STDERR, $fp);
    $response = curl_exec($ch);
    if (curl_errno($ch)) {
        msgDebug('cURL Error # '.curl_errno($ch).'. '.curl_error($ch));
        msgAdd('cURL Error # '.curl_errno($ch).'. '.curl_error($ch));
        curl_close ($ch);
        return;
    } elseif (empty($response)) { // had an issue connecting with TLSv1.2, returned no error but no response (ALPN, server did not agree to a protocol)
        msgAdd("Oops! I Received an empty response back from the cURL request. There was most likely a problem with the connection that was not reported.", 'caution');
    }
    curl_close ($ch);
    return $response;
}
