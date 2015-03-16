<?php
// +-----------------------------------------------------------------+
// |                   PhreeBooks Open Source ERP                    |
// +-----------------------------------------------------------------+
// | Copyright(c) 2008-2015 PhreeSoft      (www.PhreeSoft.com)       |
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
//  Path: /modules/payment/classes/admin.php
//
namespace payment\classes;
require_once ('/config.php');
class admin extends \core\classes\admin {
	public $id 			= 'payment';
	public $text		= TEXT_PAYMENT_MODULE;
	public $description = MODULE_PAYMENT_DESCRIPTION;
	public $core		= true;
	public $sort_order  = 7;
	public $version		= '3.6';

  	function __construct() {
		$this->prerequisites = array( // modules required and rev level for this module to work properly
	  	  'contacts'   => 3.71,
	  	  'phreedom'   => 3.6,
	  	  'phreebooks' => 3.6,
		);
		parent::__construct();
  	}

  	function after_SaveContact (\core\classes\basis &$basis) {
  		// payment fields
		if (ENABLE_ENCRYPTION && $_POST['payment_cc_name'] && $_POST['payment_cc_number']) { // save payment info
			$cc_info = array(
					'name'    => db_prepare_input($_POST['payment_cc_name']),
					'number'  => db_prepare_input($_POST['payment_cc_number']),
					'exp_mon' => db_prepare_input($_POST['payment_exp_month']),
					'exp_year'=> db_prepare_input($_POST['payment_exp_year']),
					'cvv2'    => db_prepare_input($_POST['payment_cc_cvv2']),
			);
			$enc_value = \core\classes\encryption::encrypt_cc($cc_info);
			$payment_array = array(
					'hint'      => $enc_value['hint'],
					'module'    => 'contacts',
					'enc_value' => $enc_value['encoded'],
					'ref_1'     => $basis->cInfo->contact->id,
					'ref_2'     => $basis->cInfo->contact->address[$type.'m']['address_id'],
					'exp_date'  => $enc_value['exp_date'],
			);
			if($_POST['payment_id']) {
				$sql = $basis->Database->prepare("INSERT INTO " . TABLE_DATA_SECURITY . " SET (hint, module, enc_value, ref_1, ref_2, exp_date) VALUES (:hint, :module, :enc_value, :ref_1, :ref_2, :exp_date)");
			}else{
				$sql = $basis->Database->prepare("UPDATE " . TABLE_DATA_SECURITY . " SET hint = :hint, module = :module, enc_value = :enc_value, ref_1 = :ref_1, ref_2 = :ref_1, exp_date = :exp_date");
			}
			$sql->execute($payment_array);
		}
	}
}
?>