<?php
// +-----------------------------------------------------------------+
// |                   PhreeBooks Open Source ERP                    |
// +-----------------------------------------------------------------+
// | Copyright(c) 2008-2014 PhreeSoft      (www.PhreeSoft.com)       |
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
//  Path: /modules/contacts/classes/type/i.php
//  crm
require_once(DIR_FS_MODULES . 'contacts/classes/contacts.php');

class i extends contacts{	
	public  $security_token         = SECURITY_ID_PHREECRM;
	public  $page_title_new         = BOX_CONTACTS_NEW_CONTACT;
	public  $address_types          = array('im', 'is', 'ib');
	public  $type                   = 'i';
	private $duplicate_id_error     = ACT_ERROR_DUPLICATE_CONTACT;
	public 	$auto_field    			= 'next_crm_id_num';
	
  public function __construct(){
	$this->tab_list[] = array('file'=>'template_notes',		'tag'=>'notes',    'order'=>40, 'text'=>TEXT_NOTES);
	$this->tab_list[] = array('file'=>'template_i_general',	'tag'=>'general',  'order'=> 1, 'text'=>TEXT_GENERAL);
	parent::__construct();	
	if (isset($_POST['i_id'])){
		if ($_POST['i_id']) {
			$this->id             = db_prepare_input($_POST['i_id']);
		}else{
			$this->id             = '';
		}
		$this->short_name     = db_prepare_input($_POST['i_short_name']);
		$this->contact_first  = db_prepare_input($_POST['i_contact_first']);
	    $this->contact_middle = db_prepare_input($_POST['i_contact_middle']);
	    $this->contact_last   = db_prepare_input($_POST['i_contact_last']);
	    $this->gov_id_number  = db_prepare_input($_POST['i_gov_id_number']);
	    $this->account_number = db_prepare_input($_POST['i_account_number']);
	    $this->dept_rep_id    = db_prepare_input($_POST['id']); // this id is from the parent.
	}
  }
	
  function delete($id) {
  	if ( $this->id == '' ) $this->id = $id;
	return parent::do_delete();
  	
  }
  
  public function data_complete($error){
    global $db, $messageStack;
    if ($this->auto_field && $this->short_name == '') {
    	$result = $db->Execute("select ".$this->auto_field." from ".TABLE_CURRENT_STATUS);
    	$this->short_name  = $result->fields[$this->auto_field];
    	$this->inc_auto_id = true;
    }
    foreach ($this->address_types as $value) {
      if (($value == 'im') || // contact main address when editing the contact directly
          ($this->address[$value]['primary_name'] <> '')) { // optional billing, shipping, and contact
        $msg_add_type = GEN_ERRMSG_NO_DATA . constant('ACT_CATEGORY_' . strtoupper(substr($value, 1, 1)) . '_ADDRESS');
        if (false === db_prepare_input($this->address[$value]['primary_name'],   $required = true))                     $error = $messageStack->add(ACT_I_TYPE_NAME . ': ' . ACT_JS_SHORT_NAME,'error');
        if (false === db_prepare_input($this->address[$value]['contact'],        ADDRESS_BOOK_CONTACT_REQUIRED))        $error = $messageStack->add($msg_add_type.' - '.GEN_CONTACT,       'error');
        if (false === db_prepare_input($this->address[$value]['address1'],       ADDRESS_BOOK_ADDRESS1_REQUIRED))       $error = $messageStack->add($msg_add_type.' - '.GEN_ADDRESS1,      'error');
        if (false === db_prepare_input($this->address[$value]['address2'],       ADDRESS_BOOK_ADDRESS2_REQUIRED))       $error = $messageStack->add($msg_add_type.' - '.GEN_ADDRESS2,      'error');
        if (false === db_prepare_input($this->address[$value]['city_town'],      ADDRESS_BOOK_CITY_TOWN_REQUIRED))      $error = $messageStack->add($msg_add_type.' - '.GEN_CITY_TOWN,     'error');
        if (false === db_prepare_input($this->address[$value]['state_province'], ADDRESS_BOOK_STATE_PROVINCE_REQUIRED)) $error = $messageStack->add($msg_add_type.' - '.GEN_STATE_PROVINCE,'error');
        if (false === db_prepare_input($this->address[$value]['postal_code'],    ADDRESS_BOOK_POSTAL_CODE_REQUIRED))    $error = $messageStack->add($msg_add_type.' - '.GEN_POSTAL_CODE,   'error');
        if (false === db_prepare_input($this->address[$value]['telephone1'],     ADDRESS_BOOK_TELEPHONE1_REQUIRED))     $error = $messageStack->add($msg_add_type.' - '.GEN_TELEPHONE1,    'error');
        if (false === db_prepare_input($this->address[$value]['email'],          ADDRESS_BOOK_EMAIL_REQUIRED))          $error = $messageStack->add($msg_add_type.' - '.GEN_EMAIL,         'error');
      }
    }
    
    $error = $this->duplicate_id($error);
    return $error;
    
  }
  
  
  public function save_contact(){
    global $db;
   
    $sql_data_array['type']            = $this->type;
    $sql_data_array['short_name']      = $this->short_name;
    $sql_data_array['inactive']        = isset($this->inactive) ? '1' : '0';
    $sql_data_array['contact_first']   = $this->contact_first;
    $sql_data_array['contact_middle']  = $this->contact_middle;
    $sql_data_array['contact_last']    = $this->contact_last;
    $sql_data_array['store_id']        = $this->store_id;
    $sql_data_array['gl_type_account'] = (is_array($this->gl_type_account)) ? implode('', array_keys($this->gl_type_account)) : $this->gl_type_account;
    $sql_data_array['gov_id_number']   = $this->gov_id_number;
    $sql_data_array['dept_rep_id']     = $this->dept_rep_id;
    $sql_data_array['account_number']  = $this->account_number;
    $sql_data_array['special_terms']   = $this->special_terms;
    $sql_data_array['price_sheet']     = $this->price_sheet;
    $sql_data_array['tax_id']          = $this->tax_id;
    $sql_data_array['last_update']     = 'now()';
    
    if ($this->id == '') { //create record
        $sql_data_array['first_date'] = 'now()';
        db_perform(TABLE_CONTACTS, $sql_data_array, 'insert');
        $this->id = db_insert_id();
        //if auto-increment see if the next id is there and increment if so.
        if ($this->inc_auto_id) { // increment the ID value
            $next_id = string_increment($this->short_name);
            $db->Execute("update ".TABLE_CURRENT_STATUS." set $this->auto_field = '$next_id'");
        }
        gen_add_audit_log(TEXT_CONTACTS . '-' . TEXT_ADD . '-' . constant('ACT_' . strtoupper($this->type) . '_TYPE_NAME'), $this->short_name);
    } else { // update record
        db_perform(TABLE_CONTACTS, $sql_data_array, 'update', "id = '$this->id'");
        gen_add_audit_log(TEXT_CONTACTS . '-' . TEXT_UPDATE . '-' . constant('ACT_' . strtoupper($this->type) . '_TYPE_NAME'), $this->short_name);
    }
  }
  
  public function save_addres(){
    global $db;
    // address book fields
    foreach ($this->address_types as $value) {
      if (($value == 'im') || // contact main address when editing the contact directly
          ($this->address[$value]['primary_name'] <> '')) { // optional billing, shipping, and contact
              $sql_data_array = array(
                    'ref_id'         => $this->id,
                    'type'           => $value,
                    'primary_name'   => $this->address[$value]['primary_name'],
                    'contact'        => $this->address[$value]['contact'],
                    'address1'       => $this->address[$value]['address1'],
                    'address2'       => $this->address[$value]['address2'],
                    'city_town'      => $this->address[$value]['city_town'],
                    'state_province' => $this->address[$value]['state_province'],
                    'postal_code'    => $this->address[$value]['postal_code'],
                    'country_code'   => $this->address[$value]['country_code'],
                    'telephone1'     => $this->address[$value]['telephone1'],
                    'telephone2'     => $this->address[$value]['telephone2'],
                    'telephone3'     => $this->address[$value]['telephone3'],
                    'telephone4'     => $this->address[$value]['telephone4'],
                    'email'          => $this->address[$value]['email'],
                    'website'        => $this->address[$value]['website'],
                    'notes'          => $this->address[$value]['notes'],
                );
              if ($this->address[$value]['address_id'] == '') { // then it's a new address
                db_perform(TABLE_ADDRESS_BOOK, $sql_data_array, 'insert');
                $this->address[$value]['address_id'] = db_insert_id();
              } else { // then update address
                db_perform(TABLE_ADDRESS_BOOK, $sql_data_array, 'update', "address_id = '".$this->address[$value]['address_id']."'");
              }
      }
    }
  }
  
}
?>