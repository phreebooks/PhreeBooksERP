<?php
/*
 * Handles encryption functions for credit cards
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
 * @license    http://opensource.org/licenses/OSL-3.0  Open Software License (OSL 3.0)
 * @version    3.x Last Update: 2018-09-13
 * @filesource /lib/model/encrypter.php
 */

namespace bizuno;

final class encryption {
    var $scramble1;
    var $scramble2;
    var $adj;
    var $mod;

    /**
     * Sets some variables and the scramble sequences
     */
    function __construct() {
        $this->scramble1 = '! #$%&()*+,-./0123456789:;<=>?@ABCDEFGHIJKLMNOPQRSTUVWXYZ[]^_`abcdefghijklmnopqrstuvwxyz{|}~';
        $this->scramble2 = 'f^jAE]okIOzU[2&q1{3`h5w_794p@6s8?BgP>dFV=m D<TcS%Ze|r:lGK/uCy.Jx)HiQ!#$~(;Lt-R}Ma,NvW+Ynb*0X';
        if (strlen($this->scramble1) <> strlen($this->scramble2)) {
            trigger_error('** SCRAMBLE1 is not same length as SCRAMBLE2 **', E_USER_ERROR);
        }
        $this->adj = 1.75;
        $this->mod = 3;
    }

    /**
     * Decrypts a string using the provided key
     * @param string $key  - Key to use to decrypt the string
     * @param string $source - encrypted string
     * @return string - decrypted value
     */
    function decrypt($key, $source)
    {
        if (strlen(getUserCache('profile', 'admin_encrypt')) < 1) {
            return msgAdd(lang('err_encrypt_key_missing'));
        }
        if (!$fudgefactor = $this->_convertKey($key)) { return; }
        if (empty($source)) { return msgAdd('No value has been supplied for decryption'); }
        $target  = null;
        $factor2 = 0;
        for ($i = 0; $i < strlen($source); $i++) {
            $char2 = substr($source, $i, 1);
            $num2 = strpos($this->scramble2, $char2);
            if ($num2 === false) { return msgAdd("Source string contains an invalid character ($char2)"); }
            $adj     = $this->_applyFudgeFactor($fudgefactor);
            $factor1 = $factor2 + $adj;
            $tmp1    = $num2 - round($factor1);
            $num1    = $this->_checkRange($tmp1);
            $factor2 = $factor1 + $num2;
            $char1   = substr($this->scramble1, $num1, 1);
            $target .= $char1;
        }
        return rtrim($target);
    }

  /**
   * Encrypts the string based on the encryption key
   * @param string $key - the encryption key
   * @param string $source - The value to encrypt
   * @param integer $sourcelen - (Default: 0) Pads the string to a minimum length, a value of zero will skip padding
   * @return boolean -  encrypted value
   */
    function encrypt($key, $source, $sourcelen = 0)
    {
        if (strlen(getUserCache('profile', 'admin_encrypt')) < 1) { return msgAdd(lang('err_encrypt_key_missing')); }
        if (!$fudgefactor = $this->_convertKey($key)) { return; }
        if (empty($source)) { return msgAdd('No value has been supplied for encryption'); }
        while (strlen($source) < $sourcelen) { $source .= ' '; }
        $target = null;
        $factor2 = 0;
        for ($i = 0; $i < strlen($source); $i++) {
          $char1   = substr($source, $i, 1);
          $num1    = strpos($this->scramble1, $char1);
          if ($num1 === false) { return msgAdd("Source string contains an invalid character ($char1)"); }
          $adj     = $this->_applyFudgeFactor($fudgefactor);
          $factor1 = $factor2 + $adj;
          $tmp2    = round($factor1) + $num1;
          $num2    = $this->_checkRange($tmp2);
          $factor2 = $factor1 + $num2;
          $char2   = substr($this->scramble2, $num2, 1);
          $target .= $char2;
        }
        return $target;
    }

    /**
     * This method validates credit card numbers
     * @param string $ccNumber - credit card number
     * @return true on success, false on error with message
     */
    public function validate($ccNumber)
    {
        $cardNumber = strrev($ccNumber);
        $numSum = 0;
        for ($i = 0; $i < strlen($cardNumber); $i++) {
            $currentNum = substr($cardNumber, $i, 1);
            if ($i % 2 == 1) { $currentNum *= 2; } // Double every second digit
            if ($currentNum > 9) { // Add digits of 2-digit numbers together
                $firstNum = $currentNum % 10;
                $secondNum = ($currentNum - $firstNum) / 10;
                $currentNum = $firstNum + $secondNum;
            }
            $numSum += $currentNum;
        }
        if ($numSum % 10 <> 0) { return msgAdd("Credit card failed validation!", 'caution'); }
        return true;
    }

    /**
     * Special encryption case to be used for compressed credit card information
     * @param array $fields
     * @return string - Credit card hint if successful, false on error
     */
    public function encryptCC($fields)
    {
        if (!isset($fields['number']) || !$fields['number'] || !$this->validate($fields['number'])) { return msgAdd(lang('err_cc_num_invalid')); }
        if (!isset($fields['ref_1'])  || !$fields['ref_1']) { return msgAdd(lang('err_cc_num_no_contact_id')); }
        if (!getUserCache('profile', 'admin_encrypt')) { return msgAdd(lang('err_encrypt_key_missing')); }
        $exp_date = $fields['year'].'-'.$fields['month'].'-01';
        $today    = date('Y-m-d');
        if ($today > $exp_date) { return msgAdd(lang('err_cc_expired')); }
        $fields['number'] = preg_replace("/[^0-9]/", "", $fields['number']);
        $hint  = substr($fields['number'], 0, 4);
        for ($a = 0; $a < (strlen($fields['number']) - 8); $a++) { $hint .= '*'; }
        $hint .= substr($fields['number'], -4);
        $val = implode(':', [$fields['name'], $fields['number'], $fields['month'], $fields['year'], $fields['cvv']]);
        if (!$encoded = $this->encrypt(getUserCache('profile', 'admin_encrypt'), $val, 128)) { return msgAdd('Encryption error - '.implode('. ', $this->errors)); }
        if (strlen($fields['year']) == 2) { $fields['year'] = '20'.$fields['year']; }
        if (isset($fields['module']) && $fields['module']) {
            $sqlData = [
                'module'   => $fields['module'],
                'ref_1'    => isset($fields['ref_1']) ? $fields['ref_1'] : '',
                'hint'     => $hint,
                'enc_value'=> $encoded,
                'exp_date' => $exp_date,
                ];
            $id = isset($fields['id']) ? $fields['id'] : 0;
            dbWrite(BIZUNO_DB_PREFIX.'data_security', $sqlData, $id?'update':'insert', "id=$id");
        }
        return $hint;
    }

    /** 
     * Special function to decrypt credit card information
     * @param type $id
     * @param type $fields
     * @return boolean
     */
    public function decryptCC($id=0, &$fields=[])
    {
        if (!$id || !getUserCache('profile', 'admin_encrypt')) { return msgAdd(lang('err_encrypt_key_missing')); }
        $cc_value = dbGetValue(BIZUNO_DB_PREFIX.'data_security', ['enc_value', 'hint'], "id=$id");
        if (!$cc_value) { return; }
        $enc_value= $this->decrypt(getUserCache('profile', 'admin_encrypt'), $cc_value['enc_value']);
        $values   = explode(':', $enc_value);
        $fields['name']  = isset($values[0]) ? $values[0] : '';
        $fields['number']= isset($values[1]) ? $values[1] : '';
        $fields['month'] = isset($values[2]) ? substr('0'.$values[2], -2) : '';
        $fields['year']  = isset($values[3]) ? $values[3] : '';
        $fields['cvv']   = isset($values[4]) ? substr("0000".$values[4], -4)  : '';
        $fields['hint']  = $cc_value['hint'];
        return true;
    }

    /**
     * Gets the Credit Cart display information for a customer.
     * @param string $module - The module that the credit cart information you want to return. (i.e. contacts)
     * @param string $ref_1 - The ref_1 information (i.e. contact_id)
     * @return array - select view data with stored credit cards.
     */
    public function viewCC($module, $ref_1=false)
    {
        $criteria = "module='$module'";
        $secure = getUserCache('profile', 'admin_encrypt');
        if ($ref_1) { $criteria .= " AND ref_1='$ref_1'"; }
        $cc_value = dbGetMulti(BIZUNO_DB_PREFIX.'data_security', $criteria);
        msgDebug("\nPulling stored Credit Card data with criteria = $criteria resulting in ".sizeof($cc_value)." records.");
        $output = [];
        foreach ($cc_value as $row) {
            $text = '';
            if ($secure) {
                $encVal = $this->decrypt(getUserCache('profile', 'admin_encrypt'), $row['enc_value']);
                $values = explode(':', $encVal);
                $text = !empty($values[0]) ? $values[0].': ' : '';
            }
            $output[] = ['id'=>$row['id'], 'text'=>$text.$row['hint'].' - '.viewDate($row['exp_date']), 'hint'=>$row['hint']];
        }
        return $output;
    }

    private function _applyFudgeFactor(&$fudgefactor)
    {
        $fudge = array_shift($fudgefactor);
        $fudge = $fudge + $this->adj;
        $fudgefactor[] = $fudge;
        if (!empty($this->mod)) { if ($fudge % $this->mod == 0) { $fudge = $fudge * -1; } }
        return $fudge;
    }

    private function _checkRange($num)
    {
        $num = round($num);
        $limit = strlen($this->scramble1);
        while ($num >= $limit) { $num = $num - $limit; }
        while ($num < 0)       { $num = $num + $limit; }
        return $num;
    }

    private function _convertKey($key)
    {
      if (empty($key)) { return msgAdd('No value has been supplied for the encryption key'); }
      $array[] = strlen($key);
      $tot = 0;
      for ($i = 0; $i < strlen($key); $i++) {
        $char = substr($key, $i, 1);
        $num = strpos($this->scramble1, $char);
        if ($num === false) { return msgAdd("Key contains an invalid character ($char)"); }
        $array[] = $num;
        $tot = $tot + $num;
      }
      $array[] = $tot;
      return $array;
    }
}
/* END OF CLASS encryption */

/**
 * Determines if the encryption key has been set
 * @return boolean true if encryption key has been set, false otherwise
 */
function encryptEnable()
{
    return getUserCache('profile', 'admin_encrypt') ? true : false;
}

/**
 * Encrypts a value, primarily used for encrypting passwords, uses modified MD5 algorithm
 * @param srting $plain - password to be encrypted
 * @return string - encrypted value
 */
function encryptValue($plain)
{
    $password = '';
    for ($i=0; $i<10; $i++) { $password .= randomValue(); }
    $salt = substr(md5($password), 0, 2);
    $password = md5($salt . $plain) . ':' . $salt;
    return $password;
}

/**
 * 
 * @param type $request
 * @param type $refs
 * @return boolean
 */
function paymentEncrypt($request, $refs) 
{
    $cc_info = [
        'name'    => isset($request['name'])    ? $request['name']    : '',
        'number'  => isset($request['number'])  ? $request['number']  : '',
        'exp_mon' => isset($request['exp_mon']) ? $request['exp_mon'] : '',
        'exp_year'=> isset($request['exp_year'])? $request['exp_year']: '',
        'cvv2'    => isset($request['cvv2'])    ? $request['cvv2']    : '',
        'alt1'    => isset($request['alt1'])    ? $request['alt1']    : '',
        'alt2'    => isset($request['alt2'])    ? $request['alt2']    : ''];
    $encrypt = new encryption();
    if (!$enc_value = $encrypt->encrypt_cc($cc_info)) { return; }
    $sqlData = [
        'hint'     =>$enc_value['hint'],
        'module'   =>$refs['module'],
        'enc_value'=>$enc_value['encoded'],
        'ref_1'    =>$refs['ref1'],
        'exp_date' =>$enc_value['exp_date']];
    $rID = dbGetValue(BIZUNO_DB_PREFIX."data_security", 'id', "module='{$refs['module']}' AND ref_1='{$refs['ref1']}' AND exp_date='{$enc_value['exp_date']}'");
    dbWrite(BIZUNO_DB_PREFIX."data_security", $sqlData, $rID?'update':'insert', "id=$rID");
    return true;
}
