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
//  Path: /modules/contacts/classes/type/j.php
//  jobs/projects
namespace contacts\classes\type;
class j extends \contacts\classes\contacts{
	public $security_token	= SECURITY_ID_MAINTAIN_PROJECTS;
	public $address_types	= array('jm', 'js', 'jb', 'im');
	public $type            = 'j';
	public $title			= TEXT_PROJECT;
	public $dept_rep_id 	= AR_DEF_GL_SALES_ACCT; //will overwrite if exists in database.

	
	public function __construct(){
		$this->project_cost_types = array(
			 'LBR' => TEXT_LABOR,
			 'MAT' => TEXT_MATERIALS,
			 'CNT' => TEXT_CONTRACTORS,
			 'EQT' => TEXT_EQUIPMENT,
			 'OTH' => TEXT_OTHER,
		);
		parent::__construct();
	}
	/**
	 * editContacts page main tab
	 */
	function PageMainTabGeneral(){
		?>	<table>
	      		<tr> 
	      			<td><?php echo \core\classes\htmlElement::textbox("short_name",	sprintf(TEXT_ARGS_ID, TEXT_PROJECT), 'size="21" maxlength="20"', $this->short_name, $this->auto_type == false);?></td>
	      			<td><?php echo \core\classes\htmlElement::checkbox('inactive', TEXT_INACTIVE, '1', $this->inactive );?></td>
			        <td><?php 
			            echo TEXT_BREAK_INTO_PHASES . ': ';
			            echo html_radio_field('account_number', 1, ($this->account_number == '1' ? true : false)) . TEXT_YES . chr(10);
			            echo html_radio_field('account_number', 2, (($this->account_number == '' || $this->account_number == '2') ? true : false)) . TEXT_NO  . chr(10);
			          ?>
				   </td>
				</tr>
				<tr>
					<td><?php echo \core\classes\htmlElement::combobox("dept_rep_id", 	ACT_J_REP_ID, gen_get_rep_ids('c'), $this->dept_rep_id ); ?></td>
        			<td><?php echo \core\classes\htmlElement::date('contact_first', TEXT_START_DATE); ?></td>
      			</tr>
      			<tr>
      				<td><?php echo \core\classes\htmlElement::textbox("gov_id_number", ACT_J_ID_NUMBER, 'size="17" maxlength="16"', $this->gov_id_number);?></td>
        			<td><?php echo \core\classes\htmlElement::date('contact_last', TEXT_END_DATE); ?></td>
      			</tr>
      		</table>
	<?php 
	}
}
?>