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