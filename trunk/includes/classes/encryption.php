<?php
// +-----------------------------------------------------------------+
// |                   PhreeBooks Open Source ERP                    |
// +-----------------------------------------------------------------+
// | Copyright(c) 2008-2013 PhreeSoft, LLC (www.PhreeSoft.com)       |
// +-----------------------------------------------------------------+
// | This program is free software: you can redistribute it and/or   |
// | modify it under the terms of the GNU General Public License as  |
// | published by the Free Software Foundation, either version 3 of  |
// | the License, or any later version.                              |
// |                                                                 |
// | This program is distributed in the hope that it will be useful, |
// | but WITHOUT ANY WARRANTY; without even the implied warranty of  |
// | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the   |
// | GNU General Public License for more details.                    |
// +-----------------------------------------------------------------+
//  Path: /includes/classes/encryption.php
//
namespace core\classes;
class encryption {
  	private $scramble1	= '';
  	private $scramble2	= '';
  	private $adj		= 1.75;
  	private $mod		= 3;

  	final static function __construct() {
		$this->scramble1 = '! #$%&()*+,-./0123456789:;<=>?@ABCDEFGHIJKLMNOPQRSTUVWXYZ[]^_`abcdefghijklmnopqrstuvwxyz{|}~';
		$this->scramble2 = 'f^jAE]okIOzU[2&q1{3`h5w_794p@6s8?BgP>dFV=m D<TcS%Ze|r:lGK/uCy.Jx)HiQ!#$~(;Lt-R}Ma,NvW+Ynb*0X';
		if (strlen($this->scramble1) <> strlen($this->scramble2)) {
			trigger_error('** SCRAMBLE1 is not same length as SCRAMBLE2 **', E_USER_ERROR);
		}
  	}

  	final static function encrypt_cc($params) {
		if (strlen($_SESSION['admin_encrypt']) < 1) throw new \Exception(ACT_NO_KEY_EXISTS);
		if ($params['number']) {
	  		$params['number'] = preg_replace("/[^0-9]/", "", $params['number']);
	  		$hint  = substr($params['number'], 0, 4);
	  		for ($a = 0; $a < (strlen($params['number']) - 8); $a++) $hint .= '*'; 
	  		$hint .= substr($params['number'], -4);
	  		$payment = array(); // the sequence is important!
			$payment[] = $params['name'];
			$payment[] = $params['number'];
			$payment[] = $params['exp_mon'];
			$payment[] = $params['exp_year'];
			$payment[] = $params['cvv2'];
			if (isset($params['alt1'])) $payment[] = $params['alt1'];
			if (isset($params['alt2'])) $payment[] = $params['alt2'];
			$val = implode(':', $payment).':';
			$enc_value = $this->encrypt($_SESSION['admin_encrypt'], $val, 128);		
		}
		if (strlen($params['exp_year']) == 2) $params['exp_year'] = '20'.$params['exp_year'];
		$exp_date = $params['exp_year'].'-'.$params['exp_mon'].'-01';
		return array('hint' => $hint, 'encoded' => $enc_value, 'exp_date' => $exp_date);
  	}

  	final static function decrypt ($key, $source) {
		$fudgefactor = $this->_convertKey($key);
		if (empty($source)) throw new \Exception('No value has been supplied for decryption');
		$target  = null;
		$factor2 = 0;
		for ($i = 0; $i < strlen($source); $i++) {
	  		$char2 = substr($source, $i, 1);
	  		$num2 = strpos($this->scramble2, $char2);
	  		if ($num2 === false) throw new \Exception("Source string contains an invalid character ($char2)");
			$adj     = $this->_applyFudgeFactor($fudgefactor);
	  		$factor1 = $factor2 + $adj;
	  		$num1    = $num2 - round($factor1);
	  		$num1    = $this->_checkRange($num1);
	  		$factor2 = $factor1 + $num2;
	  		$char1 = substr($this->scramble1, $num1, 1);
	  		$target .= $char1;
			//echo "char1=$char1, num1=$num1, adj= $adj, factor1= $factor1, num2=$num2, char2=$char2, factor2= $factor2<br />\n";
		}
		return rtrim($target);
  	}

  	final static function encrypt ($key, $source, $sourcelen = 0) {
		$fudgefactor  = $this->_convertKey($key);
		if (empty($source)) throw new \Exception('No value has been supplied for encryption');
	  	while (strlen($source) < $sourcelen) $source .= ' ';
		$target = null;
		$factor2 = 0;
		for ($i = 0; $i < strlen($source); $i++) {
	  		$char1 = substr($source, $i, 1);
	  		$num1 = strpos($this->scramble1, $char1);
	  		if ($num1 === false) throw new \Exception("Source string contains an invalid character ($char1)");
			$adj     = $this->_applyFudgeFactor($fudgefactor);
	  		$factor1 = $factor2 + $adj;
	  		$num2    = round($factor1) + $num1;
	  		$num2    = $this->_checkRange($num2);
	  		$factor2 = $factor1 + $num2;
	  		$char2   = substr($this->scramble2, $num2, 1);
	  		$target .= $char2;
			//	echo "char1=$char1, num1=$num1, adj= $adj, factor1= $factor1, num2=$num2, char2=$char2, factor2= $factor2<br />\n";
		}
		return $target;
  	}

  	final static function getAdjustment () {
		return $this->adj;
  	}

  	final static function getModulus () {
		return $this->mod;
  	}

  	final static function setAdjustment ($adj) {
    	$this->adj = (float)$adj;
  	}

  	final static function setModulus ($mod) {
    	$this->mod = (int)abs($mod);
  	}

  	final static function _applyFudgeFactor (&$fudgefactor) {
		$fudge = array_shift($fudgefactor);
		$fudge = $fudge + $this->adj;
		$fudgefactor[] = $fudge;
		if (!empty($this->mod)) if ($fudge % $this->mod == 0) $fudge = $fudge * -1;
		return $fudge;
  	}

  	final static function _checkRange ($num) {
		$num = round($num);
		$limit = strlen($this->scramble1);
		while ($num >= $limit) $num = $num - $limit;
		while ($num < 0) $num = $num + $limit;
		return $num;
  	}

  	final static function _convertKey ($key) {
		if (empty($key)) throw new \Exception('No value has been supplied for the encryption key');
	  	$array[] = strlen($key);
		$tot = 0;
		for ($i = 0; $i < strlen($key); $i++) {
	  		$char = substr($key, $i, 1);
	  		$num = strpos($this->scramble1, $char);
	  		if ($num === false) throw new \Exception("Key contains an invalid character ($char)");
			$array[] = $num;
	  		$tot = $tot + $num;
		}
		$array[] = $tot;
		return $array;
  	}
  	
  	/**
  	 * this function will encrypt password
  	 * @param string $plain
  	 */
  	
	final static function password($plainPassword) {
		$encryptedPassword = '';
	  	for ($i=0; $i<10; $i++) {
	    	$encryptedPassword .= general_rand();
	  	}
	  	$salt = substr(md5($encryptedPassword), 0, 2);
	  	$encryptedPassword = md5($salt . $plainPassword) . ':' . $salt;
	  	return $encryptedPassword;
	}
	
	/**
	 * this function will check if passwords match
	 * @param string $plainPassword
	 * @param string $encryptedPassword
	 */
	
	final static function validate_password($plainPassword, $encryptedPassword) {
  		if (gen_not_null($plainPassword) && gen_not_null($encryptedPassword)) {
			// split apart the hash / salt
    		$stack = explode(':', $encryptedPassword);
    		if (sizeof($stack) != 2) trigger_error("varible stack han't got size 2");
    		if (md5($stack[1] . $plainPassword) == $stack[0]) return true;
  		}
  		throw new \Exception(ERROR_WRONG_LOGIN);
	}
	
	/**
	 * this function will create random password
	 * @param int $passwordLength
	 */
	
	final static function random_password($passwordLength = 12) {
  		$chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz1234567890';
  		$chars_length = (strlen($chars) - 1);
  		$string = $chars{rand(0, $chars_length)};
  		for ($i = 1; $i < $passwordLength; $i = strlen($string)) {
			$r = $chars{rand(0, $chars_length)};
			if ($r != $string{$i - 1}) $string .=  $r;
  		}
  		return $string;
	}
}