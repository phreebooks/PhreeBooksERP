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
//  Path: /modules/phreemail/classes/phreemail.php
//
namespace phreemail\classes;
require_once (DIR_FS_MODULES . 'phreedom/includes/PHPMailer/class.phpmailer.php');

class phreemail extends PHPMailer{
	public $Host 		= EMAIL_SMTPAUTH_MAIL_SERVER; #pop3 server // van ouder
 	public $Port 		= "/pop3:110/notls";//EMAIL_SMTPAUTH_MAIL_SERVER_PORT; #pop3 server port // van ouder
	public $Username;	// 	= EMAIL_SMTPAUTH_MAILBOX; // van ouder
	public $Password	= EMAIL_SMTPAUTH_PASSWORD; // van ouder
	public $box			= "INBOX";
	public $imap_stream;
	public $max_headers = 10;  #How much headers you want to retrive 'max' = all headers (
	public $file_path 	= PHREEMAIL_DIR_ATTACHMENTS; #Where to write file attachments to // [full/path/to/attachment/store/(chmod777)]
	public $partsarray	= array();
	public $msgid 		= 1;
	public $num_message = 0;
	public $newid;
	public $logid;
	public $spam_folder = 3; #Folder where moving spam (ID from DB)
	public $file 		= array(); #File in multimart message
	public $EOF 		= false;

	function __construct(){
		// if (EMAIL_TRANSPORT <>'smtp' || EMAIL_TRANSPORT<>'smtpauth') die;
		$this->exceptions = true;
		validate_path($this->file_path);
	}

	/**
	 * Set the language for error messages.
	 * Returns false if it cannot load the language file.
	 * The default language is English.
	 * @param string $langcode ISO 639-1 2-character language code (e.g. French is "fr")
	 * @param string $lang_path Path to the language file directory, with trailing separator (slash)
	 * @return bool
	 * @access public
	 */
	public function setLanguage($langcode = 'en', $lang_path = 'language/')	{
		parent::setLanguage($langcode = 'en', $lang_path = 'language/');
		//Define full set of translatable strings
		$PHPMAILER_LANG = array(
				'authenticate'         => PHPMAILER_AUTH,     // 'SMTP Error: Could not authenticate.'
				'connect_host'         => PHPMAILER_CH,       // 'SMTP Error: Could not connect to SMTP host.'
				'data_not_accepted'    => PHPMAILER_DNA,      // 'SMTP Error: Data not accepted.'
				'encoding'             => PHPMAILER_ENC,      // 'Unknown encoding: '
				'empty_message'        => PHPMAILER_EMP_MSG,  // 'Message body empty'
				'execute'              => PHPMAILER_EXE,      // 'Could not execute: '
				'file_access'          => PHPMAILER_FA,       // 'Could not access file: '
				'file_open'            => PHPMAILER_FO,       // 'File Error: Could not open file: '
				'from_failed'          => PHPMAILER_FFAIL,    // 'The following From address failed: '
				'invalid_address'      => PHPMAILER_INV_ADD,  // 'Invalid address'
				'instantiate'          => PHPMAILER_INST,     // 'Could not instantiate mail function.'
				'provide_address'      => PHPMAILER_PA,       // 'You must provide at least one recipient email address.'
				'mailer_not_supported' => PHPMAILER_MNS,      // ' mailer is not supported.'
				'recipients_failed'    => PHPMAILER_RFAIL,    // 'SMTP Error: The following recipients failed: '
				'signing'              => PHPMAILER_SIGN,     // 'Signing Error: '
				'smtp_connect_failed'  => PHPMAILER_CONN_FAILT,//'SMTP connect() failed.'
				'smtp_error'           => PHPMAILER_SMTP_ERR, // 'SMTP server error: '
				'variable_set'         => PHPMAILER_VAR_SET,  // 'Cannot set or reset variable: '
		);
		$this->language = $PHPMAILER_LANG;
		return true;
	}
	/**
	 *
	 * this functions makes a connection with the imap server and sets the imap_stream
	 * @param string $Host
	 * @param string $Port
	 * @param string $Username
	 * @param string $Password
	 */
	function connect($Host, $Port, $Username, $Password){
		\core\classes\messageStack::debug_log("executing ".__METHOD__ );
		$this->error_count = 0;
		if ($Host != '') 		$this->Host 	= $Host;
  		if ($Username != '')	$this->Username = $Username;
  		if ($Port != '')		$this->Port		= $Port;
  		$this->imap_stream 	= imap_open("{". $this->Host . $this->Port."}".$this->box, $this->Username, $this->Password);
	  	if($this->imap_stream == false) throw new \core\classes\userException(imap_last_error());
  		$this->num_message = imap_num_msg($this->imap_stream);
 	}

  	/**
  	* Get mailbox info
  	* @todo needs to be checked
  	* the following
  	* Nmsgs - number of messages in the mailbox
 	* Recent - number of recent messages in the mailbox
  	*/
  	function mailboxmsginfo(){
  		\core\classes\messageStack::debug_log("executing ".__METHOD__ );
	    $mailbox = imap_check($this->imap_stream);
  		if($mailbox == false) throw new \core\classes\userException(imap_last_error());
		\core\classes\messageStack::debug_log("\n mailboxmsginfo ".print_r($mailbox, true));
       	return $mailbox;
  	}

	/**
	 *
	 * Enter description here ...
	 * @param unknown_type $p
	 * @param unknown_type $msgid
	 * @param unknown_type $section
	 */
  	function parsepart($p, $msgid, $section){
  		\core\classes\messageStack::debug_log("executing ".__METHOD__ );
   		$part = imap_fetchbody($this->imap_stream, $msgid, $section);
   		//Multipart
   		if ($p->type != 0){
       		//if base64
       		if ($p->encoding == 3) $part = base64_decode($part);
       		//if quoted printable
       		if ($p->encoding == 4) $part = quoted_printable_decode($part);
       		//If binary or 8bit -we no need to decode

			//body type (to do)
       		if($p->type == 5) { // image
          			$this->partsarray[$section]['image'] = array('filename' => imag1, 'string' => $part, 'part_no' => $section);
       		}
       		//Get attachment
       		$filename = '';
       		if (count($p->dparameters)>0){
           		foreach ($p->dparameters as $param){
               		if ((strtoupper($param->attribute) == 'NAME') ||(strtoupper($param->attribute) == 'FILENAME')) $filename = $param->value;
               	}
           	}
       		//If no filename
       		if ($filename == ''){
           		if (count($p->parameters)>0){
               		foreach ($p->parameters as $param){
                   		if ((strtoupper($param->attribute) == 'NAME') ||(strtoupper($param->attribute) == 'FILENAME')) $filename = $param->value;
                   	}
               	}
           	}
       		if ($filename != ''){
           		$this->partsarray[$section]['attachment'] = array(
           			'filename' 	=> $filename,
           			'string' 	=> $part,
           			'encoding' 	=> $p->encoding,
           			'part_no' 	=> $section,
           			'type' 		=> $p->type,
           			'subtype' 	=> $p->subtype);

           	}
   		}else if( $p->type == 0){//Text email
       		//decode text
       		//if base_64
       		if ($p->encoding == 3) $part = base64_decode($part);
       		//if QUOTED-PRINTABLE
       		if ($p->encoding == 4) $part = quoted_printable_decode($part);

			//if plain text
       		if (strtoupper($p->subtype) == 'PLAIN') 1;
       		//if HTML
       		else if (strtoupper($p->subtype) == 'HTML') 1;
       		$this->partsarray[$section]['text'] = array('type' => $p->subtype, 'string' => $part);
   		}

   		#if subparts
   		if (count($p->parts) > 0){
       		foreach ($p->parts as $partnumber => $partvariables){
           		$this->parsepart($partvariables, $msgid, ($section.'.'.($partnumber + 1)));
           	}
       	}
   		return;
  	}

 
   	/**
   	* Get email
   	*/
  	function email_get($msgid){
  		\core\classes\messageStack::debug_log("executing ".__METHOD__ );
    	$email 	= array();
    	$header = imap_headerinfo($this->imap_stream, $msgid);
    	foreach($header as $key => $value) $email[strtolower_utf8($key)] = db_prepare_input($value);
    	\core\classes\messageStack::debug_log("\n email header ".print_r($email, true));
    	/*Recent - R if recent and seen, N if recent and not seen, ' ' if not recent.
 		* Unseen - U if not seen AND not recent, ' ' if seen OR not seen and recent
 		*/
    	if ($header->Unseen == "U" || $header->Recent == "N") {
    		//Check is it multipart messsage
    		$s = imap_fetchstructure($this->imap_stream, $msgid);
    		if (count($s->parts)>0){
	    		foreach ($s->parts as $partno=>$partarr){
		    		//parse parts of email
		    		$this->parsepart($partarr, $msgid, $partno + 1);
	    		}
	    	} else { //for not multipart messages
		    	//get body of message
		    	$email['message'] = imap_body($this->imap_stream, $msgid);
		    	//decode if quoted-printable
		    	if ($s->encoding == 4) $email['message'] = quoted_printable_decode($text);
		    	$this->partsarray['not multipart']['text'] = array('type' => $s->subtype, 'string' => $email['message']);
	    	}
	    	if(is_array($header->from)){
	     		foreach ($header->from as $id => $object) {
	       			$fromname = $object->personal;
	       			$fromaddress = $object->mailbox . "@" . $object->host;
	     		}
	    	}
	    	if(is_array($header->to)){
	     		foreach ($header->to as $id => $object) {
	       			$toaddress = $object->mailbox . "@" . $object->host;
	     		}
	    	}
	    	$email['x_subject']    	= $this->mimie_text_decode($header->Subject);
	    	$email['x_from_name']  	= $this->mimie_text_decode($fromname);
	    	$email['x_from_email'] 	= $fromaddress;
	    	$email['x_to_email']   	= $toaddress;
	    	$email['x_date']       	= date("Y-m-d H:i:s",strtotime($header->date));
    	}
	    unset($email['to']);
	    unset($email['from']);
	    unset($email['reply_to']);
	    unset($email['sender']);
	    unset($email['msgno']);
   		return $email;

	}

	function mimie_text_decode($string){
		\core\classes\messageStack::debug_log("executing ".__METHOD__ );
    	$string = htmlspecialchars(chop($string));

    	$elements = imap_mime_header_decode($string);
    	if(is_array($elements)){
     		for ($i=0; $i<count($elements); $i++) {
      			$charset = $elements[$i]->charset;
      			$txt .= $elements[$i]->text;
     		}
    	} else {
      		$txt = $string;
    	}
    	return $txt;
	}

	/**
   	* Save messages on local disc
   	*/
  	function save_files($filename, $part){
  		\core\classes\messageStack::debug_log("executing ".__METHOD__ );
	    if (!$handle = @fopen($this->file_path . $filename, "w+")) 	throw new \core\classes\userException(sprintf(ERROR_ACCESSING_FILE, $filename));
	    if (!@fwrite($handle,$part)) 								throw new \core\classes\userException(sprintf(ERROR_WRITE_FILE, $filename));
    	if (!@fclose($handle)) 										throw new \core\classes\userException(sprintf(ERROR_CLOSING_FILE, $filename));
	}

   	/**
   	* Set flags
   	* @zou misschien flags moeten ontvangen.
   	*/
  	function email_setflag(){
  		\core\classes\messageStack::debug_log("executing ".__METHOD__ );
    	imap_setflag_full($this->imap_stream, "2,5","\\Seen \\Flagged");
  	}

   	/**
   	* Mark a current message for deletion
   	*/
  	function email_delete($msgid){
  		\core\classes\messageStack::debug_log("executing ".__METHOD__ );
    	if (DEBUG == false) imap_delete($this->imap_stream, $msgid);
  	}

  	/**
   	* Delete marked messages
   	*/
  	function email_expunge(){
  		\core\classes\messageStack::debug_log("executing ".__METHOD__ );
    	if (DEBUG == false) imap_expunge($this->imap_stream);
	}

	/**
   	* Close IMAP connection
   	*/
  	function close(){
  		\core\classes\messageStack::debug_log("executing ".__METHOD__ );
    	imap_close($this->imap_stream);
  	}

 	/**
 	 * (non-PHPdoc)
 	 * @return array of mailboxes
 	 */
	function listmailbox(){
		\core\classes\messageStack::debug_log("executing ".__METHOD__ );
		$list = imap_list($this->imap_stream, "{$this->Host}", "*");
	  	if (is_array($list) == false) throw new \core\classes\userException(imap_last_error());
	   	return $list;
	}

/*******************************************************************************
 *                                 DB FUNCTIONS
 ******************************************************************************/

/**
 *
 * this function stores the email in the database and creates a crm entry
 * @todo flags updaten als de email in de tabel staat.
 * @param array $email
 */
  	function db_add_message(array $email){
  		\core\classes\messageStack::debug_log("executing ".__METHOD__ );
		global $admin;
		unset($email['x_subject']);
		unset($email['x_from_name']);
	    unset($email['x_from_email']);
	    unset($email['x_to_email']);
	    unset($email['x_date']);
	    $remove_non_flags = array(`email_id`,`message_id`,`toaddress_id`,`fromaddress_id`,`toaddress`,`fromaddress`,`reply_toaddress`,`senderaddress`,`account`,`date`,`maildate`,`udate`,`database_date`,`subject`,`message`,`message_html`,`size`);
		$temp = $admin->DataBase->query("select * from ".TABLE_PHREEMAIL." where message_id ='{$email['message_id']}'");
		if($temp->fetch(\PDO::FETCH_NUM)> 0 ){
			foreach ($remove_non_flags as $key) unset($email[$key]);
			db_perform(TABLE_PHREEMAIL, $email, 'update'," message_id='{$email['message_id']}'");
			\core\classes\messageStack::debug_log("\n email {$email['message_id']} is already in database");
			return false;// returning false because no futher action is needed
		}
		\core\classes\messageStack::debug_log("\n email {$email['message_id']} is not in database");
		//TABLE_ADDRESS_BOOK
		$temp = $admin->DataBase->query("select address_id, ref_id from " . TABLE_ADDRESS_BOOK . " where email ='{$email['fromaddress']}' and ref_id <> 0");
		if($temp->fetch(\PDO::FETCH_NUM) == 0) $email['spam'] = 'maybe'; // mark that it could be spam if contact is is unknowm
		$email['fromaddress_id'] 	= $temp->fields['address_id'];
		$ref_id 					= $temp->fields['ref_id'];
		$temp = $admin->DataBase->query("select address_id, ref_id from " . TABLE_ADDRESS_BOOK . " where email ='".$email['toaddress']."'");
		$email['toaddress_id'] 		= $temp->fields['address_id'];
		$email['account']			= $this->Username;
		$email['database_date'] 	= date("Y-m-d H:i:s");
  		db_perform(TABLE_PHREEMAIL, $email, 'insert');
  		$this->newid = \core\classes\PDO::lastInsertId('id');

  		// save in crm_notes
		$temp = $admin->DataBase->query("select account_id from " . TABLE_USERS . " where admin_email = '" . $this->Username . "'");
		$sql_array['contact_id'] 	= $ref_id;
		$sql_array['log_date']   	= $email['date'];
		$sql_array['entered_by'] 	= $temp->fields['account_id'];
		$sql_array['action']     	= 'mail_in';
		$sql_array['notes']      	= $email['subject'];
		db_perform(TABLE_CONTACTS_LOG, $sql_array, 'insert');
		return true;
	}
 /**
 * Add attachments to DB
 * @TODO should maybe be in main table like other moduels
 **/

	function db_add_attach($file_orig, $filename){
		\core\classes\messageStack::debug_log("executing ".__METHOD__ );
 		$sql_data_array['IDEmail'] 		= $this->newid;
 		$sql_data_array['FileNameOrg'] 	= addslashes($file_orig);
 		$sql_data_array['Filename'] 	= addslashes($filename);
  		db_perform(TABLE_PHREEMAIL_ATTACH, $sql_data_array, 'insert');
	}

/**
* Add email to DB
* Rewritten for phreedom
*/
  	function db_update_message($msg, $type= 'PLAIN'){
  		\core\classes\messageStack::debug_log("executing ".__METHOD__ );
    	switch ($type){
			case 'HTML':
				$sql_data_array['Message_html'] = addslashes($msg);
				break;
			default:
  				$sql_data_array['Message'] 	= addslashes($msg);
  		}
  		db_perform(TABLE_PHREEMAIL, $sql_data_array, 'update', "email_id = '$this->newid'");
	}

 /**
 * Set folder
 * Rewritten for phreedom
 */
  	function update_folder($id, $folder){
  		\core\classes\messageStack::debug_log("executing ".__METHOD__ );
  		$sql_data_array['Type'] 	= addslashes($folder);
  		db_perform(TABLE_PHREEMAIL, $sql_data_array, 'update', "ID = '$id'");
  	}

   	function MoveNext(){
   		\core\classes\messageStack::debug_log("executing ".__METHOD__ );
  		if ($this->msgid == $this->num_message) $this->EOF = true;
  		$this->msgid++;
  	}

  	function getEmailFromDb($id){
  		\core\classes\messageStack::debug_log("executing ".__METHOD__  ." id = $id");
  		global $admin;
		$temp = $admin->DataBase->query("SELECT * FROM " . TABLE_PHREEMAIL . " WHERE id = '{$id}'");
		foreach ($temp as $key => $value) $this->$key = $value;
	}

/*
 * @TODO don't know if this is used
 */
  	function dir_name() {
  		\core\classes\messageStack::debug_log("executing ".__METHOD__ );
    	$year  = date('Y');
  		$month = date('m');
  		$dir_n = $year . "_" . $month;

  		validate_path($this->file_path . $dir_n);
    	return $dir_n . '/';
 	}


	function do_action(){
		\core\classes\messageStack::debug_log("executing ".__METHOD__ );
	    for ($i = 1; $i <= $this->num_message; ++$i) {
	    	$this->partsarray = array();
	   		$email 	= null;
	    	$email 	= $this->email_get($i); //Get first message
	    	$dir 	= $this->dir_name(); //Get store dir
	    	if(!$this->db_add_message($email)) break;//Insert message to db, if false is returned exit function.
	    	\core\classes\messageStack::debug_log("$email, Copy e-mail - start" );
	    	foreach($this->partsarray as $part){
	     		if($part[text]['type'] == 'HTML'){
	       			#$message_HTML = $part[text][string];
	       			$this->db_update_message($part[text][string], $type= 'HTML');
	     		}elseif($part[text]['type'] == 'PLAIN'){
	       			#$message_PLAIN = $part[text][string];
	       			$this->db_update_message($part[text][string], $type= 'PLAIN');
	     		}elseif($part[attachment]){
	        		#Save files(attachments) on local disc
			       // $message_ATTACH[] = $part[attachment];
	        		foreach(array($part[attachment]) as $attach){
	            		$attach[filename] = $this->mimie_text_decode($attach[filename]);
	            		$attach[filename] = preg_replace('/[^a-z0-9_\-\.]/i', '_', $attach[filename]);
	            		\core\classes\messageStack::debug_log("$email, Start coping file:".strip_tags($attach[filename]));
			            $this->save_files($this->newid.$attach[filename], $attach[string]);
			            $filename =  $dir.$this->newid.$attach[filename];
	        		    $this->db_add_attach($attach[filename], $filename);
	            		\core\classes\messageStack::debug_log("{$email}, {$filename} Finish coping: ".strip_tags($attach[filename]));
	        		}
	     		}elseif($part[image]){ //Save files(attachments) on local disc
	        		$message_IMAGE[] = $part[image];
	        		foreach($message_IMAGE as $image){
	            		$image[filename] = $this->mimie_text_decode($image[filename]);
	            		$image[filename] = preg_replace('/[^a-z0-9_\-\.]/i', '_', $image[filename]);
	            		\core\classes\messageStack::debug_log("$email, Start coping file: ".strip_tags($image[filename]));
						$this->save_files($this->newid.$image[filename], $image[string]);
	            		$filename =  $dir.$this->newid.$image[filename];
	            		$this->db_add_attach($image[filename], $filename);
	            		\core\classes\messageStack::debug_log("{$filename} Finish coping:".strip_tags($image[filename]));
	        		}
	     		}
	    	}
	    	\core\classes\messageStack::debug_log("$email ,Finish coping");
	        if(!empty($email)){
	    		//unset($this->partsarray);
	    		\core\classes\messageStack::add(PHREEMAIL_NEW_MAIL,'caution');
	    	}
	   	}
	}

  	function __destruct(){
  		if ($this->Mailer == 'smtp') { //close any open SMTP connection nicely
  			$this->smtpClose();
  		}
  		if($this->imap_stream) $this->close();
  	}

}

?>
