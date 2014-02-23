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
//  Path: /modules/payment/methods/directdebit/directdebit.php
//
// Revision history
// 2011-07-01 - Added version number for revision control
namespace payment\methods\directdebit;
class directdebit extends \payment\classes\payment {
  public $id        	= 'directdebit'; // needs to match class name
  public $text       	= MODULE_PAYMENT_DIRECTDEBIT_TEXT_TITLE;
  public $description 	= MODULE_PAYMENT_DIRECTDEBIT_TEXT_DESCRIPTION;
  public $sort_order  	= 35; 
  public $version		= '3.3';
  
  public function __construct(){
  	parent::__construct();
    $this->payment_fields = implode(':', array($this->field_0));
  }

  function selection() {
    global $order;
    return array(
	  'id'   => $this->id,
      'page' => $this->text,
	  'fields' => array(
			array(
			  'title' => MODULE_PAYMENT_DIRECTDEBIT_TEXT_REF_NUM,
			  'field' => html_input_field($this->id . '_field_0', $this->field_0, 'size="33" maxlength="32"'),
			),
		),
	);
  }
}
?>