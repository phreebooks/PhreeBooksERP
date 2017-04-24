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
//  Path: /modules/contacts/classes/type/c.php
//  customers
namespace contacts\classes\type;
class c extends \contacts\classes\contacts{
	public $terms_type		= 'AR';
	public $credit_limit     = AR_CREDIT_LIMIT_AMOUNT;
	public $discount_percent = AR_PREPAYMENT_DISCOUNT_PERCENT;
	public $discount_days    = AR_PREPAYMENT_DISCOUNT_DAYS;
	public $num_days_due     = AR_NUM_DAYS_DUE;
	public $security_token	= SECURITY_ID_MAINTAIN_CUSTOMERS;
	public $auto_type		= AUTO_INC_CUST_ID;
	public $auto_field		= 'next_cust_id_num';
	public $order_jid		= '10';
	public $journals		= '12,13,19';
	public $help			= '07.03.02.02';
	public $help_terms		= '07.03.02.04';
	public $address_types	= array('cm', 'cs', 'cb');
	public $type			= 'c';
	public $title			= TEXT_CUSTOMER;
	public $contact_level	= 'r';

	public function __construct(){
		$this->tab_list[] = array('file'=>'template_payment',	'tag'=>'payment',  'order'=>30);
		$this->tab_list[] = array('file'=>'template_addbook',	'tag'=>'addbook',  'order'=>20);
		$this->tab_list[] = array('file'=>'template_contacts',	'tag'=>'contacts', 'order'=> 5);
		$this->tab_list[] = array('file'=>'template_history',	'tag'=>'history',  'order'=>10);
		$this->tab_list[] = array('file'=>'template_general',	'tag'=>'general',  'order'=> 1);
		parent::__construct();
		$this->contacts_levels = array(
			'r' => array('id' => 'r', 'text'=> TEXT_RETAIL),
			'd' => array('id' => 'd', 'text'=> TEXT_DEALER),
			'w' => array('id' => 'w', 'text'=> TEXT_WHOLESALE),
		);
	}
}
?>