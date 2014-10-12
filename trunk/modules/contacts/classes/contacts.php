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
//  Path: /modules/contacts/classes/contacts.php
//

require_once(DIR_FS_MODULES . 'contacts/classes/contact_fields.php');

class contacts {
	public  $terms_type         = 'AP'; 
	public  $page_title_new     = '';
	public  $auto_type          = false;
	public  $inc_auto_id 		= false;
	public  $auto_field         = '';
	public  $help		        = '';
	public  $tab_list           = array(); 
	public  $address_types      = array();
	public  $type               = '';
	public  $crm_log			= array();
	public  $crm_date           = '';
	public  $crm_rep_id         = '';
    public  $crm_action         = '';
    public  $crm_note           = '';
    public  $payment_cc_name    = '';
    public  $payment_cc_number  = '';
    public  $payment_exp_month  = '';
    public  $payment_exp_year   = '';
    public  $payment_cc_cvv2    = '';
    public  $special_terms      = '0'; 
    private $duplicate_id_error = ACT_ERROR_DUPLICATE_ACCOUNT;
    private $sql_data_array     = array();
	
    public function __construct(){
    	global $db;
    	//set defaults
        $this->crm_date        = date('Y-m-d');
        $this->crm_rep_id      = $_SESSION['account_id'] <> 0 ? $_SESSION['account_id'] : $_SESSION['admin_id'];
        foreach ($_POST as $key => $value) $this->$key = db_prepare_input($value);
        $this->special_terms  =  db_prepare_input($_POST['terms']); // TBD will fix when popup terms is redesigned
        if ($this->id  == '') $this->id  = db_prepare_input($_POST['rowSeq'], true) ? db_prepare_input($_POST['rowSeq']) : db_prepare_input($_GET['cID']);
        if ($this->aid == '') $this->aid = db_prepare_input($_GET['aID'],     true) ? db_prepare_input($_GET['aID'])     : db_prepare_input($_POST['aID']); 
    }
		
	public function getContact() {
	  	global $db, $messageStack;
	  	if ($this->id == '' && !$this->aid == ''){
	  		$result = $db->Execute("select * from ".TABLE_ADDRESS_BOOK." where address_id = $this->aid ");
	  		$this->id = $result->fields['ref_id'];
	  	}
		// Load contact info, including custom fields
		$result = $db->Execute("select * from ".TABLE_CONTACTS." where id = $this->id");
		foreach ($result->fields as $key => $value) $this->$key = $value;
		// expand attachments
		$this->attachments = $result->fields['attachments'] ? unserialize($result->fields['attachments']) : array();
		// Load the address book
		$result = $db->Execute("select * from ".TABLE_ADDRESS_BOOK." where ref_id = $this->id order by primary_name");
		$this->address = array();
		while (!$result->EOF) {
		  $type = substr($result->fields['type'], 1);
		  $this->address_book[$type][] = new objectInfo($result->fields);
		  if ($type == 'm') { // prefill main address
		  	foreach ($result->fields as $key => $value) $this->address[$result->fields['type']][$key] = $value;
		  }
		  $result->MoveNext();
		}
		// load payment info
		if ($_SESSION['admin_encrypt'] && ENABLE_ENCRYPTION) {
		  $result = $db->Execute("select id, hint, enc_value from ".TABLE_DATA_SECURITY." where module='contacts' and ref_1=$this->id");
		  $encrypt = new encryption();
		  while (!$result->EOF) {
		    if (!$values = $encrypt->decrypt($_SESSION['admin_encrypt'], $result->fields['enc_value'])) {
			  $error = $messageStack->add('Encryption error - ' . implode('. ', $encrypt->errors), 'error');
		    }
		    $val = explode(':', $values);
		    $this->payment_data[] = array(
			  'id'   => $result->fields['id'],
			  'name' => $val[0],
			  'hint' => $result->fields['hint'],
			  'exp'  => $val[2] . '/' . $val[3],
		    );
		    $result->MoveNext();
		  }
		}
		// load contacts info
		$result = $db->Execute("select * from ".TABLE_CONTACTS." where dept_rep_id=$this->id");
		$this->contacts = array();
		while (!$result->EOF) {
		  $cObj = new objectInfo();
		  foreach ($result->fields as $key => $value) $cObj->$key = $value;
		  $addRec = $db->Execute("select * from ".TABLE_ADDRESS_BOOK." where type='im' and ref_id=".$result->fields['id']);
		  $cObj->address['m'][] = new objectInfo($addRec->fields);
		  $this->contacts[] = $cObj; //unserialize(serialize($cObj));
    	  // load crm notes
		  $logs = $db->Execute("select * from ".TABLE_CONTACTS_LOG." where contact_id = ". $result->fields['id']. " order by log_date desc");
		  while (!$logs->EOF) {
		    $this->crm_log[] = new objectInfo($logs->fields);
		    $logs->MoveNext();
		  }
		  $result->MoveNext();
		}
		// load crm notes
		$result = $db->Execute("select * from ".TABLE_CONTACTS_LOG." where contact_id = $this->id order by log_date desc");
		while (!$result->EOF) {
		  $this->crm_log[] = new objectInfo($result->fields);
		  $result->MoveNext();
		}
  }

  function delete($id) {
  	global $db; 
  	if ( $this->id == '' ) $this->id = $id;
	// error check, no delete if a journal entry exists
	$result = $db->Execute("SELECT id FROM ".TABLE_JOURNAL_MAIN." WHERE bill_acct_id=$this->id OR ship_acct_id=$this->id OR store_id=$this->id LIMIT 1");
	if ($result->RecordCount() == 0) {
	  return $this->do_delete();
	}
  	return ACT_ERROR_CANNOT_DELETE;
  }
  
  public function do_delete(){
	  global $db; // ajax nog controleren
	  $db->Execute("DELETE FROM ".TABLE_ADDRESS_BOOK ." WHERE ref_id=$this->id");
	  $db->Execute("DELETE FROM ".TABLE_DATA_SECURITY." WHERE ref_1=$this->id");
	  $db->Execute("DELETE FROM ".TABLE_CONTACTS     ." WHERE id=$this->id");
	  $db->Execute("DELETE FROM ".TABLE_CONTACTS_LOG ." WHERE contact_id=$this->id");
	  foreach (glob(CONTACTS_DIR_ATTACHMENTS.'contacts_'.$this->id.'_*.zip') as $filename) unlink($filename); // remove attachments
	  return true;
  }
   /*
   * this function loads alle open order 
   */
  
  function load_open_orders($acct_id, $journal_id, $only_open = true, $limit = 0) {
  	global $db;
  	if (!$acct_id) return array();
  	$sql  = "select id, journal_id, closed, closed_date, post_date, total_amount, purchase_invoice_id, purch_order_id from ".TABLE_JOURNAL_MAIN." where";
  	$sql .= ($only_open) ? " closed = '0' and " : "";
  	$sql .= " journal_id in (" . $journal_id . ") and bill_acct_id = " . $acct_id . ' order by post_date DESC';
  	$sql .= ($limit) ? " limit " . $limit : "";
  	$result = $db->Execute($sql);
  	if ($result->RecordCount() == 0) return array();	// no open orders
  	$output = array(array('id' => '', 'text' => TEXT_NEW));
  	while (!$result->EOF) {
  	     $output[] = array(
  	         'id'                 => $result->fields['id'],
  	         'journal_id'         => $result->fields['journal_id'],
  	         'text'               => $result->fields['purchase_invoice_id'],
	  		 'post_date'          => $result->fields['post_date'],
	  		 'closed'             => $result->fields['closed'],
	  		 'closed_date'        => $result->fields['closed_date'],
	  		 'total_amount'       => in_array($result->fields['journal_id'], array(7,13)) ? -$result->fields['total_amount'] : $result->fields['total_amount'],
	  		 'purchase_invoice_id'=> $result->fields['purchase_invoice_id'],
	  		 'purch_order_id'     => $result->fields['purch_order_id'],
  	     );
  	     $result->MoveNext();
  	}
  	return $output;
  }
  
  public function data_complete($error){
  	global $db, $messageStack;
  	if ($this->auto_type && $this->short_name == '') {
    	$result = $db->Execute("select ".$this->auto_field." from ".TABLE_CURRENT_STATUS);
        $this->short_name  = $result->fields[$this->auto_field];
        $this->inc_auto_id = true;
    }
  	foreach ($this->address_types as $value) {
      if (($value <> 'im' && substr($value, 1, 1) == 'm') || // all main addresses except contacts which is optional
          ($this->address[$value]['primary_name'] <> '')) { // optional billing, shipping, and contact
          $msg_add_type = GEN_ERRMSG_NO_DATA . constant('ACT_CATEGORY_' . strtoupper(substr($value, 1, 1)) . '_ADDRESS');
	      if (false === db_prepare_input($this->address[$value]['primary_name'],   $required = true))                     $error = $messageStack->add($msg_add_type.' - '.GEN_PRIMARY_NAME,  'error');
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
  
  /*
   * this function looks if there are duplicate id's if so it returns a error. 
   */
  
  public function duplicate_id($error){
  	global $db, $messageStack; 
  	// check for duplicate short_name IDs
    if ($this->id == '') {
      $result = $db->Execute("select id from ".TABLE_CONTACTS." where short_name = '$this->short_name' and type = '$this->type'");
    } else {
      $result = $db->Execute("select id from ".TABLE_CONTACTS." where short_name = '$this->short_name' and type = '$this->type' and id <> $this->id");
    }
    if ($result->RecordCount() > 0) $error = $messageStack->add($this->duplicate_id_error,'error');    
    return $error;
  }

  /*
   * this function saves all input in the contacts main page. 
   */
  
  public function save_contact(){
  	global $db;
  	$fields = new contact_fields(false);
  	$sql_data_array = $fields->what_to_save();
    $sql_data_array['type']            = $this->type;
    $sql_data_array['short_name']      = $this->short_name;
    $sql_data_array['inactive']        = isset($this->inactive) ? '1' : '0';
    $sql_data_array['contacts_level']  = $this->contacts_level;
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
      if (($value <> 'im' && substr($value, 1, 1) == 'm') || // all main addresses except contacts which is optional
        ($this->address[$value]['primary_name'] <> '')) { // billing, shipping, and contact if primary_name present
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
              if ($value == 'im') $sql_data_array['ref_id'] = $this->i_id; // re-point contact
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