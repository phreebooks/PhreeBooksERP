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
//  Path: /modules/contacts/dashboards/customer_websites/customer_websites.php
//
// Revision history
// 2011-07-01 - Added version number for revision control
namespace contacts\dashboards\customer_websites;
class customer_websites extends \core\classes\ctl_panel {
	public $id					= 'customer_websites';
	public $description	 		= CP_CUSTOMER_WEBSITES_DESCRIPTION;
	public $security_id  		= SECURITY_ID_MAINTAIN_CUSTOMERS;
	public $text		 		= CP_CUSTOMER_WEBSITES_TITLE;
	public $version      		= '4.0';
	public $default_params 		= array();

	function output() {
		global $admin;
		if(count($this->params) != count($this->default_params)) { //upgrading
			$this->params = $this->upgrade($this->params);
		}
		$contents = '';
		$control  = '';
		$temp = "SELECT a.primary_name, a.website
		  FROM " . TABLE_CONTACTS . " c LEFT JOIN " . TABLE_ADDRESS_BOOK . " a ON c.id = a.ref_id
		  WHERE  c.type = 'c' and c.inactive = '0' and a.website !='' ORDER BY a.primary_name";
		$sql = $admin->DataBase->prepare($temp);
		$sql->execute();
		if ($sql->rowCount() < 1) {
			$contents = TEXT_NO_RESULTS_FOUND;
		} else {
			while ($result = $sql->fetch(\PDO::FETCH_LAZY)){
				$contents .= '<div style="height:16px;">';
				$contents .= "  <a href=' http://{$result['website']}' target='_blank'>{$result['primary_name']}</a>" . chr(10);
				$contents .= '</div>';
		  	}
		}
		return $this->build_div($contents, $control);
	}
}
?>