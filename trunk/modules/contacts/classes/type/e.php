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
//  Path: /modules/contacts/classes/type/e.php
//  employees
namespace contacts\classes\type;
class e extends \contacts\classes\contacts{
	public $security_token	= SECURITY_ID_MAINTAIN_EMPLOYEES;
	public $help			= '07.07.01.02';
	public $address_types	= array('em', 'es', 'eb', 'im');
    public $type			= 'e';
    public $title			= TEXT_EMPLOYEE;
    public $dept_rep_id 	= EMP_DEFAULT_DEPARTMENT; //will overwrite if exists in database.

	public function __construct(){
		global $admin;
		$this->tab_list[] = array('file'=>'template_history',	'tag'=>'history',  'order'=>10, 'text'=>TEXT_HISTORY);
		$this->employee_types = array(
			'e' => TEXT_EMPLOYEE,
			's' => TEXT_SALES_REP,
			'b' => TEXT_BUYER,
		);
		// load contacts info
		$sql = $admin->DataBase->prepare("SELECT * FROM ".TABLE_DEPARTMENTS." WHERE id={$this->id}");
		$sql->execute();
		$this->department = $sql->fetchAll() ;
		parent::__construct();
	}

  	function delete() {
	  	global $admin;
	  	if ( $this->id == '' ) $this->id = $id;
  		$result = $admin->DataBase->query("SELECT admin_id FROM ".TABLE_USERS." WHERE account_id =". $this->id);
		if ($result->fetch(\PDO::FETCH_NUM) != 0) throw new \core\classes\userException(ACT_ERROR_CANNOT_DELETE_EMPLOYEE);
	  	parent::delete();
  	}

  	function list_row ($js_function = "submitSeq") {
  		\core\classes\messageStack::debug_log("executing ".__METHOD__ ." of class ". get_class($admin_class));
  		$security_level = \core\classes\user::validate($this->security_token); // in this case it must be done after the class is defined for
  		$bkgnd          = ($this->inactive) ? ' style="background-color:pink"' : '';
  		$attach_exists  = $this->attachments ? true : false;
  		echo "<td $bkgnd onclick='$js_function( $this->id, \"editContact\")'>". htmlspecialchars($this->short_name) ."</td>";
  		echo "<td $bkgnd onclick='$js_function( $this->id, \"editContact\")'>". htmlspecialchars($this->contact_first . ' ' . $this->contact_last). "</td>";
  		echo "<td        onclick='$js_function( $this->id, \"editContact\")'>". htmlspecialchars($this->address1) ."</td>";
  		echo "<td        onclick='$js_function( $this->id, \"editContact\")'>". htmlspecialchars($this->city_town)."</td>";
  		echo "<td        onclick='$js_function( $this->id, \"editContact\")'>". htmlspecialchars($this->state_province)."</td>";
  		echo "<td        onclick='$js_function( $this->id, \"editContact\")'>". htmlspecialchars($this->postal_code)."</td>";
  		echo "<td 	     onclick='$js_function( $this->id, \"editContact\")'>". htmlspecialchars($this->telephone1)."</td>";
  		echo "<td align='right'>";
  		if ($js_function == "submitSeq") {
	  		// build the action toolbar
  			if ($security_level > 1) echo html_icon('mimetypes/x-office-presentation.png', TEXT_SALES, 'small', 	"onclick='contactChart(\"annual_sales\", $this->id)'") . chr(10);
  			if ($security_level > 1) echo html_icon('actions/edit-find-replace.png', TEXT_EDIT, 'small', 			"onclick='submitSeq($this->id, \"editContact\")'") . chr(10);
	  		if ($attach_exists) 	 echo html_icon('status/mail-attachment.png', TEXT_DOWNLOAD_ATTACHMENT,'small', "onclick='submitSeq($this->id, \"ContactAttachmentDownloadFirst\", true)'") . chr(10);
  			if ($security_level > 3) echo html_icon('emblems/emblem-unreadable.png', TEXT_DELETE, 'small', 			"onclick='if (confirm(\"" . ACT_WARN_DELETE_ACCOUNT . "\")) submitSeq($this->id, \"DeleteContact\")'") . chr(10);
  		}else if ($js_function == "setReturnAccount"){
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
  	 
  	 /**
  	  * editContacts page main tab
  	  */
  	 function PageMainTabGeneral(){
  	 	?>	<table>
	      		<tr> 
	      			<td><?php echo \core\classes\htmlElement::textbox("short_name",	sprintf(TEXT_ARGS_ID, TEXT_EMPLOYEE), 'size="21" maxlength="20"', $this->short_name, $this->auto_type == false);?></td>
	      			<td><?php echo \core\classes\htmlElement::textbox("gov_id_number",	ACT_E_ID_NUMBER, 'size="17" maxlength="16"', $this->gov_id_number);?></td>
					<td><?php echo \core\classes\htmlElement::checkbox('inactive', TEXT_INACTIVE, '1', $this->inactive );?></td>
	      		</tr>
      			<tr>
			    	<td><?php echo \core\classes\htmlElement::textbox("contact_first",	TEXT_FIRST_NAME,  	'size="33" maxlength="32"', $this->contact_first);?></td>
			        <td><?php echo \core\classes\htmlElement::textbox("contact_middle",	TEXT_MIDDLE_NAME,	'size="33" maxlength="32"', $this->contact_middle);?></td>
			        <td><?php echo \core\classes\htmlElement::textbox("contact_last",	TEXT_LAST_NAME, 	'size="33" maxlength="32"', $this->contact_last);?></td>
			    </tr>
			    <tr>
					<td><?php echo \core\classes\htmlElement::combobox("dept_rep_id", 	ACT_E_REP_ID, gen_get_pull_down(TABLE_DEPARTMENTS, true, 1), $this->dept_rep_id ); ?></td>
	      		</tr>
	      		<tr>
		    		<td align="right"><?php echo TEXT_EMPLOYEE_ROLES; ?></td>
				      <?php
				        $col_count = 1;
					    foreach ($this->employee_types as $key => $value) {
					      $preset = (strpos($this->gl_type_account, $key) !== false) ? '1' : '0';
					      echo '<td>' . \core\classes\htmlElement::checkbox("gl_type_account[{$key}]", $value, '1', $preset)."</td>";
					      $col_count++;
					      if ($col_count == 6) {
					        echo '</tr><tr>' . chr(10);
						    echo '<td>&nbsp;</td>';
					        $col_count = 1;
					      }
					    }
				      ?>
	      		</tr>
	    	</table>

  	 	  		<?php 
  	 	  	}
}
?>