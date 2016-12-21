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
//  Path: /modules/contacts/classes/type/b.php
//  branches
namespace contacts\classes\type;
class b extends \contacts\classes\contacts{
	public $security_token	= SECURITY_ID_MAINTAIN_BRANCH;
	public $help			= '07.08.04';
	public $address_types	= array('bm', 'bs', 'bb', 'im');
	public $type			= 'b';
	public $title			= TEXT_BRANCH;

	/**
	 * editContacts page main tab
	 */
	function PageMainTabGeneral(){
		?>	<table>
	      		<tr> 
	      			<td><?php echo \core\classes\htmlElement::textbox("short_name",	TEXT_BRANCH_ID, 'size="21" maxlength="20"', $this->short_name, $this->auto_type == false);?></td>
	      			<td><?php echo \core\classes\htmlElement::checkbox('inactive', TEXT_INACTIVE, '1', $this->inactive );?></td>
	      		</tr>
        		<tr>
			    	<td><?php echo \core\classes\htmlElement::textbox("contact_first",	TEXT_FIRST_NAME,  	'size="33" maxlength="32"', $this->contact_first);?></td>
			        <td><?php echo \core\classes\htmlElement::textbox("contact_middle",	TEXT_MIDDLE_NAME,	'size="33" maxlength="32"', $this->contact_middle);?></td>
			        <td><?php echo \core\classes\htmlElement::textbox("contact_last",	TEXT_LAST_NAME, 	'size="33" maxlength="32"', $this->contact_last);?></td>
			    </tr>
    		</table>
	  		<?php 
	}
}
?>