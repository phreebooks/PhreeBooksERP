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
//  Path: /modules/contacts/classes/contacts.php
//
namespace contacts\classes;
class contacts {
	public  $security_token		= "";
	public  $terms_type         = 'AP';
	public  $title;
	public  $page_title_new;
	public  $page_title_edit;
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
    public  $dir_attachments;
    public  $security_level		= 0;

    public function __construct(){
    	if ($this->security_token != '') $this->security_level = \core\classes\user::validate($this->security_token); // in this case it must be done after the class is defined for
    	$this->page_title_new	= sprintf(TEXT_NEW_ARGS, $this->title);
    	$this->page_title_edit	= sprintf(TEXT_EDIT_ARGS, $this->title);
    	$this->dir_attachments  = DIR_FS_MY_FILES . "{$_SESSION['user']->company}/contacts/main/";
    	//set defaults
        $this->crm_date			= date('Y-m-d');
        $this->crm_rep_id		= $_SESSION['user']->account_id <> 0 ? $_SESSION['user']->account_id : $_SESSION['user']->admin_id;
        $this->fields 			= new \contacts\classes\fields(false, $this->type);
        foreach ($_POST as $key => $value) $this->$key = db_prepare_input($value);
        $this->special_terms  =  db_prepare_input($_POST['terms']); // TBD will fix when popup terms is redesigned
        if ($this->id  == '') $this->id  = db_prepare_input($_POST['rowSeq'], true) ? db_prepare_input($_POST['rowSeq']) : db_prepare_input($_GET['cID']);
        if ($this->aid == '') $this->aid = db_prepare_input($_GET['aID'],     true) ? db_prepare_input($_GET['aID'])     : db_prepare_input($_POST['aID']);
        $this->crm_actions = array(
        	'0' 		=> array('id' => '0',			'text'  => TEXT_NONE),
        	'new' 		=> array('id' => 'new', 		'text'  => sprintf(TEXT_NEW_ARGS, TEXT_CALL)),
        	'ret'  		=> array('id' => 'ret', 		'text'  => TEXT_RETURNED_CALL),
        	'flw'  		=> array('id' => 'flw', 		'text'  => TEXT_FOLLOW_UP_CALL),
        	'inac'  	=> array('id' => 'inac', 		'text'  => TEXT_INACTIVE),
        	'lead'  	=> array('id' => 'lead', 		'text'  => sprintf(TEXT_NEW_ARGS, TEXT_LEAD)),
        	'mail_in'  	=> array('id' => 'mail_in', 	'text'  => TEXT_EMAIL_RECEIVED),
        	'mail_out'  => array('id' => 'mail_out', 	'text'  => TEXT_EMAIL_SEND),
        );
        if ($this->id  != '') $this->getContact();
    }

	public function getContact() {
	  	global $admin;
	  	if ($this->id == '' && !$this->aid == ''){
	  		$sql = $admin->DataBase->prepare("SELECT * FROM ".TABLE_ADDRESS_BOOK." WHERE address_id = {$this->aid}");
	  		$sql->execute();
	  		$result = $sql->fetch(\PDO::FETCH_LAZY);
	  		// Load contact info, including custom fields
	  		$sql = $admin->DataBase->prepare("SELECT * FROM ".TABLE_CONTACTS." WHERE id = {$result['ref_id']}");
	  		$sql->execute();
	  		$this[] = $sql->fetch(\PDO::FETCH_LAZY);
	  	}
		// expand attachments
		$this->attachments = $result['attachments'] ? unserialize($result['attachments']) : array();
		// Load the address book
		$sql = $admin->DataBase->query("SELECT * FROM ".TABLE_ADDRESS_BOOK." WHERE ref_id = {$this->id} ORDER BY primary_name");
		$sql->execute();
		$this->address = array();
		while ($result = $sql->fetch(\PDO::FETCH_LAZY)) {
			$i = sizeof($this->address[$result['type']]);
		  	$this->address[$result['type']][$i] = get_object_vars ($result);
		}
		// load payment info
		if ($_SESSION['admin_encrypt'] && ENABLE_ENCRYPTION) {
		  	$sql = $admin->DataBase->prepare("SELECT id, hint, enc_value FROM ".TABLE_DATA_SECURITY." WHERE module='contacts' and ref_1={$this->id}");
		  	$sql->execute();
		  	while ($result = $sql->fetch(\PDO::FETCH_LAZY)) {
		    	$val = explode(':', \core\classes\encryption::decrypt($_SESSION['admin_encrypt'], $result['enc_value']));
		    	$this->payment_data[] = array(
			  	  'id'   => $result['id'],
			  	  'name' => $val[0],
			  	  'hint' => $result['hint'],
			  	  'exp'  => $val[2] . '/' . $val[3],
		    	);
		  	}
		}
		$sql = $admin->DataBase->prepare("SELECT * FROM ".TABLE_CONTACTS_LOG." WHERE contact_id = {$this->id} ORDER BY log_date DESC");
		$sql->execute();
		while ($result = $sql->fetch(\PDO::FETCH_LAZY)) {
			$i = sizeof($this->crm_log);
			foreach ($result as $key => $value) $this->crm_log[$i] = get_object_vars ($result);
			if ( $this->contact_first != '' || $this->contact_last != '') {
				$this->crm_log[$i]['with'] = $this->contact_first . ' ' . $this->contact_last;
			} else {
				$this->crm_log[$i]['with'] = $this->short_name . ' ' . $this->address["{$this->type}m"][0]['primary_name'];
			}
		}
		// load contacts info
		$sql = $admin->DataBase->prepare("SELECT * FROM ".TABLE_CONTACTS." WHERE dept_rep_id={$this->id}");
		$sql->execute();
		$this->contacts = $sql->fetchAll(\PDO::FETCH_CLASS | \PDO::FETCH_CLASSTYPE) ;
		foreach($this->contacts as $contact){
			$this->crm_log = array_merge($this->crm_log, $contact->crm_log);
		}
		// load sales reps
		$this->sales_rep_array = gen_get_rep_ids($basis->cInfo->contact->type);
  	}

	/**
	 * this function deletes a contact if it is save
	 * @return boolean
	 */
  	public function delete(){
		global $admin;
		\core\classes\user::validate_security($this->security_level, 4);
		if ( $this->id == '' ) throw new \core\classes\userException("the id field isn't set");	// error check, no delete if a journal entry exists
		$result = $admin->DataBase->query("SELECT id FROM ".TABLE_JOURNAL_MAIN." WHERE bill_acct_id={$this->id} OR ship_acct_id={$this->id} OR store_id={$this->id} LIMIT 1");
		if ($result->fetch(\PDO::FETCH_NUM) != 0) throw new \core\classes\userException(ACT_ERROR_CANNOT_DELETE);
	  	$admin->DataBase->exec("DELETE FROM ".TABLE_ADDRESS_BOOK ." WHERE ref_id={$this->id}");
	  	$admin->DataBase->exec("DELETE FROM ".TABLE_DATA_SECURITY." WHERE ref_1={$this->id}");
	  	$admin->DataBase->exec("DELETE FROM ".TABLE_CONTACTS     ." WHERE id={$this->id}");
	  	$admin->DataBase->exec("DELETE FROM ".TABLE_CONTACTS_LOG ." WHERE contact_id={$this->id}");
	  	foreach (glob("{$this->dir_attachments}contacts_{$this->id}_*.zip") as $filename) unlink($filename); // remove attachments
  	}

   	/**
   	* this function returns alle order
   	*/
  	function load_orders($journal_id, $only_open = true, $limit = 0) {
  		global $admin;
  		$raw_sql  = "SELECT id, journal_id, closed, closed_date, post_date, total_amount, purchase_invoice_id, purch_order_id FROM ".TABLE_JOURNAL_MAIN." WHERE";
  		$raw_sql .= ($only_open) ? " closed = '0' and " : "";
  		$raw_sql .= " journal_id in ({$journal_id}) and bill_acct_id = {$this->id} ORDER BY post_date DESC";
  		$raw_sql .= ($limit) ? " LIMIT {$limit}" : "";
  		$sql = $admin->DataBase->prepare($raw_sql);
  		$sql->execute();
  		if ($sql->fetch(\PDO::FETCH_NUM) == 0) return array();	// no open orders
  		$output = array();
  		$i = 1;
  		$output[0] = array('id' => '', 'text' => TEXT_NEW);
  		while ($result = $sql->fetch(\PDO::FETCH_LAZY)) {
  	    	$output[$i] = $result;
  	    	$output[$i]['text'] = $result['purchase_invoice_id'];
  	    	$output[$i]['total_amount'] = in_array($result['journal_id'], array(7,13)) ? -$result['total_amount'] : $result['total_amount'];
  			$i++;
  		}
  		return $output;
  	}

  	public function data_complete(){
  		global $admin, $messageStack;
  		if ($this->auto_type && $this->short_name == '') {
    		$result = $admin->DataBase->query("SELECT {$this->auto_field} as next_id FROM ".TABLE_CURRENT_STATUS);
        	$this->short_name  = $result['next_id'];
        	$this->inc_auto_id = true;
    	}
  		foreach ($this->address_types as $value) {
      		if (($value <> 'im' && substr($value, 1, 1) == 'm') || // all main addresses except contacts which is optional
        	  ($this->address[$value]['primary_name'] <> '')) { // optional billing, shipping, and contact
          		$msg_add_type = TEXT_A_REQUIRED_FIELD_HAS_BEEN_LEFT_BLANK_FIELD . ': ' . constant('ACT_CATEGORY_' . strtoupper(substr($value, 1, 1)) . '_ADDRESS');
	      		if (false === db_prepare_input($this->address[$value]['primary_name'],   $required = true))                     throw new \core\classes\userException($msg_add_type.' - '.TEXT_NAME_OR_COMPANY);
	      		if (false === db_prepare_input($this->address[$value]['contact'],        ADDRESS_BOOK_CONTACT_REQUIRED))        throw new \core\classes\userException($msg_add_type.' - '.TEXT_ATTENTION);
	      		if (false === db_prepare_input($this->address[$value]['address1'],       ADDRESS_BOOK_ADDRESS1_REQUIRED))       throw new \core\classes\userException($msg_add_type.' - '.TEXT_ADDRESS1);
	      		if (false === db_prepare_input($this->address[$value]['address2'],       ADDRESS_BOOK_ADDRESS2_REQUIRED))       throw new \core\classes\userException($msg_add_type.' - '.TEXT_ADDRESS2);
	      		if (false === db_prepare_input($this->address[$value]['city_town'],      ADDRESS_BOOK_CITY_TOWN_REQUIRED))      throw new \core\classes\userException($msg_add_type.' - '.TEXT_CITY_TOWN);
	      		if (false === db_prepare_input($this->address[$value]['state_province'], ADDRESS_BOOK_STATE_PROVINCE_REQUIRED)) throw new \core\classes\userException($msg_add_type.' - '.TEXT_STATE_PROVINCE);
	      		if (false === db_prepare_input($this->address[$value]['postal_code'],    ADDRESS_BOOK_POSTAL_CODE_REQUIRED))    throw new \core\classes\userException($msg_add_type.' - '.TEXT_POSTAL_CODE);
	      		if (false === db_prepare_input($this->address[$value]['telephone1'],     ADDRESS_BOOK_TELEPHONE1_REQUIRED))     throw new \core\classes\userException($msg_add_type.' - '.TEXT_TELEPHONE);
	      		if (false === db_prepare_input($this->address[$value]['email'],          ADDRESS_BOOK_EMAIL_REQUIRED))          throw new \core\classes\userException($msg_add_type.' - '.TEXT_EMAIL);
      		}
    	}
    	$this->duplicate_id();
    	return true;
  	}

  	/**
   	* this function looks if there are duplicate id's if so it throws a exception.
   	*/

  	public function duplicate_id(){
  		global $admin;
	  	// check for duplicate short_name IDs
    	if ($this->id == '') {
      		$result = $admin->DataBase->query("SELECT id FROM ".TABLE_CONTACTS." WHERE short_name = '$this->short_name' AND type = '$this->type'");
    	} else {
      		$result = $admin->DataBase->query("SELECT id FROM ".TABLE_CONTACTS." WHERE short_name = '$this->short_name' AND type = '$this->type' AND id <> $this->id");
    	}
    	if ($result->fetch(\PDO::FETCH_NUM) > 0) throw new \core\classes\userException($this->duplicate_id_error);
  	}

  	/**
   	* this function saves all input in the contacts main page.
   	*/

	public function save(){
  		global $admin;
  		$this->id ? \core\classes\user::validate_security($this->security_level, 3) : \core\classes\user::validate_security($this->security_level, 2);
  		$sql_data_array = $this->fields->what_to_save();
  		$sql_data_array['class']			= addcslashes(get_class($this), '\\');
    	$sql_data_array['type']            	= $this->type;
    	$sql_data_array['short_name']      	= $this->short_name;
    	$sql_data_array['inactive']        	= isset($this->inactive) ? '1' : '0';
    	$sql_data_array['contacts_level'] 	= $this->contacts_level;
    	$sql_data_array['contact_first']   	= $this->contact_first;
    	$sql_data_array['contact_middle']  	= $this->contact_middle;
    	$sql_data_array['contact_last']    	= $this->contact_last;
    	$sql_data_array['store_id']        	= $this->store_id;
    	$sql_data_array['gl_type_account'] 	= (is_array($this->gl_type_account)) ? implode('', array_keys($this->gl_type_account)) : $this->gl_type_account;
    	$sql_data_array['gov_id_number']   	= $this->gov_id_number;
    	$sql_data_array['dept_rep_id']     	= $this->dept_rep_id;
    	$sql_data_array['account_number']  	= $this->account_number;
    	$sql_data_array['special_terms']   	= $this->special_terms;
    	$sql_data_array['price_sheet']     	= $this->price_sheet;
    	$sql_data_array['tax_id']          	= $this->tax_id;
    	$sql_data_array['last_update']     	= 'now()';
    	if ($this->id == '') { //create record
    		$sql_data_array['first_date'] = 'now()';
    		$keys = array_keys($sql_data_array);
    		$fields = '`'.implode('`, `',$keys).'`';
    		$placeholder = substr(str_repeat('?,',count($keys),0,-1));
    		$sql = $admin->DataBase->prepare("INSERT INTO ".TABLE_CONTACTS." ($fields) VALUES ($placeholder)");
    		$sql->execute(get_object_vars($this));
//        	db_perform(TABLE_CONTACTS, $sql_data_array, 'insert');
        	$this->id = $basis->DataBase->lastInsertId('id');
			//	if auto-increment see if the next id is there and increment if so.
    	    if ($this->inc_auto_id) { // increment the ID value
        	    $next_id = string_increment($this->short_name);
            	$admin->DataBase->query("UPDATE ".TABLE_CURRENT_STATUS." SET {$this->auto_field} = '$next_id'");
	        }
    	    gen_add_audit_log(TEXT_CONTACTS . '-' . TEXT_ADD . '-' . $this->title, $this->short_name);
    	} else { // update record
    		$keys = array_keys($sql_data_array);
    		$fields = '`'.implode('`, `',$keys).'`';
    		$placeholder = '`:'.implode('`:, `',$keys).'`';
    		$sql = $admin->DataBase->prepare("UPDATE ".TABLE_CONTACTS." SET ($fields) VALUES ($placeholder)");
    		$sql->execute(get_object_vars($this));
        	//db_perform(TABLE_CONTACTS, $sql_data_array, 'update', "id = '$this->id'");
        	gen_add_audit_log(TEXT_CONTACTS . '-' . TEXT_UPDATE . '-' . $this->title, $this->short_name);
    	}
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
                	$this->address[$value]['address_id'] = \core\classes\PDO::lastInsertId('id');
              	} else { // then update address
                	db_perform(TABLE_ADDRESS_BOOK, $sql_data_array, 'update', "address_id = '{$this->address[$value]['address_id']}'");
              	}
      		}
    	}
  	}

  	function draw_address_fields($address_type, $reset_button = false, $hide_list = false, $short = false, $prefill_adress = true) {
//  		echo "add_type = '$address_type' entries = ".print_r($this->address[$add_type]). '<br>';
  		if (!$hide_list && sizeof($this->address[$address_type]) > 0) {
  			echo '<tr><td><table class="ui-widget" style="border-collapse:collapse;width:100%;">';
  			echo '<thead class="ui-widget-header">' . chr(10);
  			echo '<tr>' . chr(10);
  			echo '  <th>' . TEXT_NAME_OR_COMPANY .   '</th>' . chr(10);
  			echo '  <th>' . TEXT_ATTENTION .        '</th>' . chr(10);
  			echo '  <th>' . TEXT_ADDRESS1 .       '</th>' . chr(10);
  			echo '  <th>' . TEXT_CITY_TOWN .      '</th>' . chr(10);
  			echo '  <th>' . TEXT_STATE_PROVINCE . '</th>' . chr(10);
  			echo '  <th>' . TEXT_POSTAL_CODE .    '</th>' . chr(10);
  			echo '  <th>' . TEXT_COUNTRY .        '</th>' . chr(10);
  			// add some special fields
  			if (substr($address_type, 1, 1) == 'p') echo '  <th>' . TEXT_PAYMENT_REF . '</th>' . chr(10);
  			echo '  <th align="center">' . TEXT_ACTION . '</th>' . chr(10);
  			echo '</tr>' . chr(10) . chr(10);
  			echo '</thead>' . chr(10) . chr(10);
  			echo '<tbody class="ui-widget-content">' . chr(10);

  			$odd = true;
  			foreach ($this->address[$address_type] as $key => $address) {
  				if (empty($address['address_id'])) break;
  				echo "<tr id='tr_add_{$address['address_id']}' class='".($odd?'odd':'even')."' style='cursor:pointer'>";
  				echo "  <td onclick='getAddress({$address['address_id']}, '$address_type')'>{$address['primary_name']}</td>" . chr(10);
  				echo "  <td onclick='getAddress({$address['address_id']}, '$address_type')'>{$address['contact']}</td>" . chr(10);
  				echo "  <td onclick='getAddress({$address['address_id']}, '$address_type')'>{$address['address1']}</td>" . chr(10);
  				echo "  <td onclick='getAddress({$address['address_id']}, '$address_type')'>{$address['city_town']}</td>" . chr(10);
  				echo "  <td onclick='getAddress({$address['address_id']}, '$address_type')'>{$address['state_province']}</td>" . chr(10);
  				echo "  <td onclick='getAddress({$address['address_id']}, '$address_type')'>{$address['postal_code']}</td>" . chr(10);
  				echo "  <td onclick='getAddress({$address['address_id']}, '$address_type')'>{$address['country_code']}</td>" . chr(10);
  				// add special fields
  				if (substr($address_type, 1, 1) == 'p')	echo "  <td onclick='getAddress({$address['address_id']}, $address_type)'>". ($address['hint'] ? $address['hint'] : '&nbsp;') ."</td>" . chr(10);
  				echo '  <td align="center">';
  				echo html_icon('actions/edit-find-replace.png', TEXT_EDIT, 'small', "onclick='getAddress({$address['address_id']}, '$address_type')'") . chr(10);
  				echo '&nbsp;' . html_icon('emblems/emblem-unreadable.png', TEXT_DELETE, 'small', 'onclick="if (confirm(\'' . ACT_WARN_DELETE_ADDRESS . '\')) deleteAddress(' .$address['address_id'] . ');"') . chr(10);
  				echo '  </td>' . chr(10);
  				echo '</tr>' . chr(10);
  				$odd = !$odd;
  			}
  			echo '</tbody>' . chr(10) . chr(10);
  			echo '</table></td></tr>';
  		}
  		$addres = array();
		if ($prefill_adress) $addres = $this->address[$address_type][0];
  		echo '<tr><td><table class="ui-widget" style="border-collapse:collapse;width:100%;">' . chr(10);
  		if (!$short) {
  			echo '<tr>';
  			echo '  <td align="right">' . TEXT_NAME_OR_COMPANY . '</td>' . chr(10);
  			echo '  <td>' . html_input_field("address[$address_type][primary_name]", $addres['primary_name'], 'size="49" maxlength="48"', true) . '</td>' . chr(10);
  			echo '  <td align="right">' . TEXT_TELEPHONE . '</td>' . chr(10);
  			echo '  <td>' . html_input_field("address[$address_type][telephone1]", $addres['telephone1'], 'size="21" maxlength="20"', ADDRESS_BOOK_TELEPHONE1_REQUIRED) . '</td>' . chr(10);
  			echo '</tr>';
  		}
  		echo '<tr>';
  		echo '  <td align="right">' . TEXT_ATTENTION . html_hidden_field("address[$address_type][address_id]", $addres['address_id']) . '</td>' . chr(10);
  		echo '  <td>' . html_input_field("address[$address_type][contact]", $addres['contact'], 'size="33" maxlength="32"', ADDRESS_BOOK_CONTACT_REQUIRED) . '</td>' . chr(10);
  		echo '  <td align="right">' . TEXT_ALTERNATIVE_TELEPHONE_SHORT . '</td>' . chr(10);
  		echo '  <td>' . html_input_field("address[$address_type][telephone2]", $addres['telephone2'], 'size="21" maxlength="20"') . '</td>' . chr(10);
  		echo '</tr>';

  		echo '<tr>';
  		echo '  <td align="right">' . TEXT_ADDRESS1 . '</td>' . chr(10);
  		echo '  <td>' . html_input_field("address[$address_type][address1]" , $addres['address1'], 'size="33" maxlength="32"', ADDRESS_BOOK_ADDRESS1_REQUIRED) . '</td>' . chr(10);
  		echo '  <td align="right">' . TEXT_FAX . '</td>' . chr(10);
  		echo '  <td>' . html_input_field("address[$address_type][telephone3]", $addres['telephone3'], 'size="21" maxlength="20"') . '</td>' . chr(10);
  		echo '</tr>';

  		echo '<tr>';
  		echo '  <td align="right">' . TEXT_ADDRESS2 . '</td>' . chr(10);
  		echo '  <td>' . html_input_field("address[$address_type][address2]", $addres['address2'], 'size="33" maxlength="32"', ADDRESS_BOOK_ADDRESS2_REQUIRED) . '</td>' . chr(10);
  		echo '  <td align="right">' . TEXT_MOBILE_PHONE . '</td>' . chr(10);
  		echo '  <td>' . html_input_field("address[$address_type][telephone4]", $addres['telephone4'], 'size="21" maxlength="20"') . '</td>' . chr(10);
  		echo '</tr>';

  		echo '<tr>';
  		echo '  <td align="right">' . TEXT_CITY_TOWN . '</td>' . chr(10);
  		echo '  <td>' . html_input_field("address[$address_type][city_town]", $addres['city_town'], 'size="25" maxlength="24"', ADDRESS_BOOK_CITY_TOWN_REQUIRED) . '</td>' . chr(10);
  		echo '  <td align="right">' . TEXT_EMAIL . '</td>' . chr(10);
  		echo '  <td>' . html_input_field("address[$address_type][email]", $addres['email'], 'size="51" maxlength="50"') . '</td>' . chr(10);
  		echo '</tr>';

  		echo '<tr>';
  		echo '  <td align="right">' . TEXT_STATE_PROVINCE . '</td>' . chr(10);
  		echo '  <td>' . html_input_field("address[$address_type][state_province]", $addres['state_province'], 'size="25" maxlength="24"', ADDRESS_BOOK_STATE_PROVINCE_REQUIRED) . '</td>' . chr(10);
  		echo '  <td align="right">' . TEXT_WEBSITE . '</td>' . chr(10);
  		echo '  <td>' . html_input_field("address[$address_type][website]", $addres['website'], 'size="51" maxlength="50"') . '</td>' . chr(10);
  		echo '</tr>';

  		echo '<tr>';
  		echo '  <td align="right">' . TEXT_POSTAL_CODE . '</td>' . chr(10);
  		echo '  <td>' . html_input_field("address[$address_type][postal_code]", $addres['postal_code'], 'size="11" maxlength="10"', ADDRESS_BOOK_POSTAL_CODE_REQUIRED) . '</td>' . chr(10);
  		echo '  <td align="right">' . TEXT_COUNTRY . '</td>' . chr(10);
  		echo '  <td>' . html_pull_down_menu("address[$address_type][country_code]", gen_get_countries(), $addres['country_code'] ? $addres['country_code'] : COMPANY_COUNTRY) . '</td>' . chr(10);
  		echo '</tr>';

  		if (substr($address_type, 1, 1) <> 'm' || ($address_type == 'im' && substr($address_type, 0, 1) <> 'i')) {
  			echo '<tr>' . chr(10);
  			echo '  <td align="right">' . TEXT_NOTES . '</td>' . chr(10);
  			echo '  <td colspan="3">' . html_textarea_field("address[$address_type][notes]", 80, 3, $addres['notes']) . chr(10);
  			if ($reset_button) echo html_icon('actions/view-refresh.png', TEXT_RESET, 'small', "onclick='clearAddress(\"{$address_type}\")'") . chr(10);
  			echo '  </td>' . chr(10);
  			echo '</tr>' . chr(10);
  		}
  		echo '</table></td></tr>' . chr(10) . chr(10);
  	}


  	/**
  	 * this method outputs a line on the template page.
  	 */
  	function list_row ($js_function = "submitSeq") {
  		\core\classes\messageStack::debug_log("executing ".__METHOD__ ." of class ". get_class($admin_class));
  		$security_level = \core\classes\user::validate($this->security_token); // in this case it must be done after the class is defined for
  		$bkgnd          = ($this->inactive) ? ' style="background-color:pink"' : '';
  		$attach_exists  = $this->attachments ? true : false;
  		echo "<td $bkgnd onclick='$js_function( $this->id, \"editContact\")'>". htmlspecialchars($this->short_name) 	."</td>";
  		echo "<td $bkgnd onclick='$js_function( $this->id, \"editContact\")'>". htmlspecialchars($this->primary_name)	. "</td>";
  		echo "<td 		 onclick='$js_function( $this->id, \"editContact\")'></td>";
  		echo "<td    {$this->inactive}    onclick='$js_function( $this->id, \"editContact\")'>". htmlspecialchars($this->address1) 	."</td>";
  		echo "<td        onclick='$js_function( $this->id, \"editContact\")'>". htmlspecialchars($this->city_town)	."</td>";
  		echo "<td        onclick='$js_function( $this->id, \"editContact\")'>". htmlspecialchars($this->state_province)."</td>";
  		echo "<td        onclick='$js_function( $this->id, \"editContact\")'>". htmlspecialchars($this->postal_code)	."</td>";
  		echo "<td 	     onclick='$js_function( $this->id, \"editContact\")'>". htmlspecialchars($this->telephone1)	."</td>";
  		echo "<td align='right'>";
  		// build the action toolbar
  		if ($js_function == "submitSeq") {
			if ($security_level > 1) echo html_icon('mimetypes/x-office-presentation.png', TEXT_SALES, 'small', 	"onclick='contactChart(\"annual_sales\", $this->id)'") . chr(10);
	  		if ($security_level > 1) echo html_icon('actions/edit-find-replace.png', TEXT_EDIT, 'small', 			"onclick='window.open(\"" . html_href_link(FILENAME_DEFAULT, "cID={$this->id}&amp;action=editContact", 'SSL')."\",\"_blank\")'"). chr(10);
	  		if ($attach_exists) 	 echo html_icon('status/mail-attachment.png', TEXT_DOWNLOAD_ATTACHMENT,'small', "onclick='submitSeq($this->id, \"ContactAttachmentDownloadFirst\", true)'") . chr(10);
	  		if ($security_level > 3) echo html_icon('emblems/emblem-unreadable.png', TEXT_DELETE, 'small', 			"onclick='if (confirm(\"" . ACT_WARN_DELETE_ACCOUNT . "\")) submitSeq($this->id, \"DeleteContact\")'") . chr(10);
  		} else if ($js_function == "setReturnAccount"){
  			switch ($this->journal_id) {
  				case  6:
  				case  7:
  				case 12:
  				case 13:
  					switch ($this->journal_id) {
  						case  6: $search_journal = 4;  break;
  						case  7: $search_journal = 6;  break;
  						case 12: $search_journal = 10; break;
  						case 13: $search_journal = 12; break;
  					}
  					$open_order_array = $this->load_orders($search_journal);
  					if ($open_order_array) {
  						echo html_pull_down_menu('open_order_' . $this->id, $open_order_array, '', "onchange='setReturnOrder(\"{$this->id}\")'");
  					}
  			}
  		}
  		echo "</td>";
  	}

  	function print_contact_list(){
  		$security_level = \core\classes\user::validate($this->security_token); // in this case it must be done after the class is defined for
  		$bkgnd = ($this->inactive) ? 'class="ui-state-highlight"' : '';
  		echo "<td onclick='getAddress({$this->address['m'][0]->address_id}, \"im\")' $bkgnd	> {$this->contact_last}</td>";
  		echo "<td onclick='getAddress({$this->address['m'][0]->address_id}, \"im\")' $bkgnd	> {$this->contact_first}</td>";
  		echo "<td onclick='getAddress({$this->address['m'][0]->address_id}, \"im\")' 		> {$this->contact_middle}</td>";
  		echo "<td onclick='getAddress({$this->address['m'][0]->address_id}, \"im\")' 		> {$this->address['m'][0]->telephone1}</td>";
  		echo "<td onclick='getAddress({$this->address['m'][0]->address_id}, \"im\")' 		> {$this->address['m'][0]->telephone4}</td>";
  		echo "<td onclick='getAddress({$this->address['m'][0]->address_id}, \"im\")' 		> {$this->address['m'][0]->email}</td>";
  		echo "<td align='right'>";
  		// build the action toolbar
  		if ($security_level > 1) echo html_icon('actions/edit-find-replace.png', TEXT_EDIT,   'small', "onclick='getAddress({$this->address['m'][0]->address_id}, \"im\")'") . chr(10);
  		if ($security_level > 3) echo html_icon('emblems/emblem-unreadable.png', TEXT_DELETE, 'small', "onclick='if (confirm(\"". ACT_WARN_DELETE_ACCOUNT . "\")) deleteAddress({$this->address['m'][0]->address_id})'") . chr(10);
  		echo "</td>";
  	}

  //	function __destruct(){print_r($this);}
}
?>