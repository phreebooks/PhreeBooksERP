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
//  Path: /modules/contacts/classes/admin.php
//
namespace contacts\classes;
require_once (DIR_FS_ADMIN . 'modules/contacts/config.php');
class admin extends \core\classes\admin {
	public $sort_order  = 3;
	public $id 			= 'contacts';
	public $description = MODULE_CONTACTS_DESCRIPTION;
	public $core		= true;
	public $version		= '4.0-dev';

	function __construct() {
		$this->text = sprintf(TEXT_MODULE_ARGS, TEXT_CONTACTS);
		$this->prerequisites = array( // modules required and rev level for this module to work properly
		  'phreedom'   => 3.6,
		  'phreebooks' => 3.6,
		);
		// Load configuration constants for this module, must match entries in admin tabs
	    $this->keys = array(
		  'ADDRESS_BOOK_CONTACT_REQUIRED'        => '0',
		  'ADDRESS_BOOK_ADDRESS1_REQUIRED'       => '1',
		  'ADDRESS_BOOK_ADDRESS2_REQUIRED'       => '0',
		  'ADDRESS_BOOK_CITY_TOWN_REQUIRED'      => '1',
		  'ADDRESS_BOOK_STATE_PROVINCE_REQUIRED' => '1',
		  'ADDRESS_BOOK_POSTAL_CODE_REQUIRED'    => '1',
		  'ADDRESS_BOOK_TELEPHONE1_REQUIRED'     => '0',
		  'ADDRESS_BOOK_EMAIL_REQUIRED'          => '0',
		);
		// add new directories to store images and data
		$this->dirlist = array(
		  'contacts',
		  'contacts/main',
		);
		// Load tables
		$this->tables = array(
		  TABLE_ADDRESS_BOOK => "CREATE TABLE " . TABLE_ADDRESS_BOOK . " (
			  address_id int(11) NOT NULL auto_increment,
			  ref_id int(11) NOT NULL default '0',
			  type char(2) NOT NULL default '',
			  primary_name varchar(32) NOT NULL default '',
			  contact varchar(32) NOT NULL default '',
			  address1 varchar(32) NOT NULL default '',
			  address2 varchar(32) NOT NULL default '',
			  city_town varchar(24) NOT NULL default '',
			  state_province varchar(24) NOT NULL default '',
			  postal_code varchar(10) NOT NULL default '',
			  country_code char(3) NOT NULL default '',
			  telephone1 VARCHAR(20) NULL DEFAULT '',
			  telephone2 VARCHAR(20) NULL DEFAULT '',
			  telephone3 VARCHAR(20) NULL DEFAULT '',
			  telephone4 VARCHAR(20) NULL DEFAULT '',
			  email VARCHAR(48) NULL DEFAULT '',
			  website VARCHAR(48) NULL DEFAULT '',
			  notes text,
			  PRIMARY KEY (address_id),
			  KEY customer_id (ref_id,type)
			) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci",
		  TABLE_CONTACTS => "CREATE TABLE " . TABLE_CONTACTS . " (
		  	  class VARCHAR( 255 ) NOT NULL DEFAULT '',
			  id int(11) NOT NULL auto_increment,
			  type char(1) NOT NULL default 'c',
			  short_name varchar(32) NOT NULL default '',
			  inactive enum('0','1') NOT NULL default '0',
			  contact_first varchar(32) default NULL,
			  contact_middle varchar(32) default NULL,
			  contact_last varchar(32) default NULL,
			  store_id varchar(15) NOT NULL default '',
			  gl_type_account varchar(15) NOT NULL default '',
			  gov_id_number varchar(16) NOT NULL default '',
			  dept_rep_id varchar(16) NOT NULL default '',
			  account_number varchar(16) NOT NULL default '',
			  special_terms varchar(32) NOT NULL default '0',
			  price_sheet varchar(32) default NULL,
	          tax_id INT(11) default '-1',
	          attachments text,
			  first_date date NOT NULL default '0000-00-00',
			  last_update date default NULL,
			  last_date_1 date default NULL,
			  last_date_2 date default NULL,
			  PRIMARY KEY (id),
			  KEY type (type),
			  KEY short_name (short_name)
			) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci",
		  TABLE_CONTACTS_LOG => "CREATE TABLE " . TABLE_CONTACTS_LOG . " (
			  log_id int(11) NOT NULL auto_increment,
			  contact_id int(11) NOT NULL default '0',
			  entered_by int(11) NOT NULL default '0',
			  log_date datetime NOT NULL default '0000-00-00',
			  action varchar(32) NOT NULL default '',
			  notes text,
			  PRIMARY KEY (log_id)
			) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci",
		  TABLE_DEPARTMENTS => "CREATE TABLE " . TABLE_DEPARTMENTS . " (
			  id int(11) NOT NULL auto_increment,
			  description_short varchar(30) NOT NULL default '',
			  description varchar(30) NOT NULL default '',
			  subdepartment enum('0','1') NOT NULL default '0',
			  primary_dept_id int(11) NOT NULL default '0',
			  department_type tinyint(4) NOT NULL default '0',
			  department_inactive enum('0','1') NOT NULL default '0',
			  PRIMARY KEY (id),
			  KEY type (department_type)
			) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci",
		  TABLE_DEPT_TYPES => "CREATE TABLE " . TABLE_DEPT_TYPES . " (
			  id int(11) NOT NULL auto_increment,
			  description varchar(30) NOT NULL default '',
			  PRIMARY KEY (id)
			) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci",
		  TABLE_PROJECTS_COSTS => "CREATE TABLE " . TABLE_PROJECTS_COSTS . " (
			  cost_id int(8) NOT NULL auto_increment,
			  description_short varchar(16) collate utf8_unicode_ci NOT NULL default '',
			  description_long varchar(64) collate utf8_unicode_ci NOT NULL default '',
			  cost_type varchar(3) collate utf8_unicode_ci default NULL,
			  inactive enum('0','1') collate utf8_unicode_ci NOT NULL default '0',
			  PRIMARY KEY (cost_id),
			  KEY description_short (description_short)
			) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci",
		  TABLE_PROJECTS_PHASES => "CREATE TABLE " . TABLE_PROJECTS_PHASES . " (
			  phase_id int(8) NOT NULL auto_increment,
			  description_short varchar(16) collate utf8_unicode_ci NOT NULL default '',
			  description_long varchar(64) collate utf8_unicode_ci NOT NULL default '',
			  cost_type varchar(3) collate utf8_unicode_ci default NULL,
			  cost_breakdown enum('0','1') collate utf8_unicode_ci NOT NULL default '0',
			  inactive enum('0','1') collate utf8_unicode_ci NOT NULL default '0',
			  PRIMARY KEY (phase_id),
			  KEY description_short (description_short)
			) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci",
	    );
		// Set the menus
		$this->mainmenu["customers"]->submenu ["contact"] 	= new \core\classes\menuItem (10, 	TEXT_CUSTOMERS,		'action=LoadContactMgrPage&amp;type=c&amp;list=1', SECURITY_ID_MAINTAIN_CUSTOMERS);
		$this->mainmenu["customers"]->submenu ["contact"]->submenu ["new"] 	= new \core\classes\menuItem ( 5, 	sprintf(TEXT_NEW_ARGS, TEXT_CUSTOMER),		'action=NewContact&amp;type=c');
		$this->mainmenu["customers"]->submenu ["contact"]->submenu ["mgr"] 	= new \core\classes\menuItem (10, 	sprintf(TEXT_MANAGER_ARGS, TEXT_CUSTOMER),	'action=LoadContactMgrPage&amp;type=c&amp;list=1');
		$this->mainmenu["customers"]->submenu ["crm"] 		= new \core\classes\menuItem (15, 	TEXT_CRM,		'action=LoadContactMgrPage&amp;type=i&amp;list=1');
		$this->mainmenu["vendors"]->submenu   ["contact"]	= new \core\classes\menuItem (10, 	TEXT_VENDORS,		'action=LoadContactMgrPage&amp;type=v&amp;list=1', SECURITY_ID_MAINTAIN_VENDORS);
		$this->mainmenu["vendors"]->submenu   ["contact"]->submenu ["new"]	= new \core\classes\menuItem ( 5, 	sprintf(TEXT_NEW_ARGS, TEXT_VENDOR),		'action=NewContact&amp;type=v');
		$this->mainmenu["vendors"]->submenu   ["contact"]->submenu ["mgr"]	= new \core\classes\menuItem (10, 	sprintf(TEXT_MANAGER_ARGS, TEXT_VENDOR),	'action=LoadContactMgrPage&amp;type=v&amp;list=1');
		$this->mainmenu["employees"]->submenu ["contact"] 	= new \core\classes\menuItem (10, 	TEXT_EMPLOYEES,		'action=LoadContactMgrPage&amp;type=e&amp;list=1', SECURITY_ID_MAINTAIN_EMPLOYEES);
		$this->mainmenu["employees"]->submenu ["contact"]->submenu ["new"]	= new \core\classes\menuItem ( 5, 	sprintf(TEXT_NEW_ARGS, TEXT_EMPLOYEE),		'action=NewContact&amp;type=e');
		$this->mainmenu["employees"]->submenu ["contact"]->submenu ["mgr"] 	= new \core\classes\menuItem (10, 	sprintf(TEXT_MANAGER_ARGS, TEXT_EMPLOYEE),	'action=LoadContactMgrPage&amp;type=e&amp;list=1');
		$this->mainmenu["company"]->submenu   ["branches"] 	= new \core\classes\menuItem (10, 	TEXT_BRANCHES,		'action=LoadContactMgrPage&amp;type=b&amp;list=1', SECURITY_ID_MAINTAIN_BRANCH, 'ENABLE_MULTI_BRANCH');
		$this->mainmenu["company"]->submenu   ["branches"]->submenu ["new"]	= new \core\classes\menuItem ( 5, 	sprintf(TEXT_NEW_ARGS, TEXT_BRANCH),		'action=NewContact&amp;type=b');
		$this->mainmenu["company"]->submenu   ["branches"]->submenu ["mgr"] = new \core\classes\menuItem (10, 	sprintf(TEXT_MANAGER_ARGS, TEXT_BRANCH),	'action=LoadContactMgrPage&amp;type=b&amp;list=1');	
		$this->mainmenu["customers"]->submenu ["projects"] 	= new \core\classes\menuItem (60, 	TEXT_PROJECTS,		'action=LoadContactMgrPage&amp;type=j&amp;list=1', SECURITY_ID_MAINTAIN_PROJECTS);
		$this->mainmenu["customers"]->submenu ["projects"]->submenu ["new"]	= new \core\classes\menuItem ( 5, 	sprintf(TEXT_NEW_ARGS, TEXT_PROJECT),		'action=NewContact&amp;type=j');
		$this->mainmenu["customers"]->submenu ["projects"]->submenu ["mgr"] = new \core\classes\menuItem (10, 	sprintf(TEXT_MANAGER_ARGS, TEXT_PROJECT),	'action=LoadContactMgrPage&amp;type=j&amp;list=1');
		$this->mainmenu["company"]->submenu ["configuration"]->submenu ["inventory"]  = new \core\classes\menuItem (sprintf(TEXT_MODULE_ARGS, TEXT_CONTACTS), sprintf(TEXT_MODULE_ARGS, TEXT_INVENTORY),	'module=contacts&amp;page=admin',   SECURITY_ID_CONFIGURATION);
	    parent::__construct();
	}

	function install($path_my_files, $demo = false) {
	    global $admin;
	    parent::install($path_my_files, $demo);
		if (!$admin->DataBase->field_exists(TABLE_CURRENT_STATUS, 'next_cust_id_num')) $admin->DataBase->query("ALTER TABLE " . TABLE_CURRENT_STATUS . " ADD next_cust_id_num VARCHAR( 16 ) NOT NULL DEFAULT 'C10000';");
		if (!$admin->DataBase->field_exists(TABLE_CURRENT_STATUS, 'next_vend_id_num')) $admin->DataBase->query("ALTER TABLE " . TABLE_CURRENT_STATUS . " ADD next_vend_id_num VARCHAR( 16 ) NOT NULL DEFAULT 'V10000';");
		if (!$admin->DataBase->field_exists(TABLE_CURRENT_STATUS, 'next_crm_id_num'))  $admin->DataBase->query("ALTER TABLE " . TABLE_CURRENT_STATUS . " ADD next_crm_id_num VARCHAR( 16 ) NOT NULL DEFAULT '10000';");
		require_once(DIR_FS_MODULES . 'phreedom/functions/phreedom.php');
		\core\classes\fields::sync_fields('contacts', TABLE_CONTACTS);
	}

  	function upgrade(\core\classes\basis &$basis) {
    	parent::upgrade($basis);
    	if (version_compare($this->status, '3.3', '<') ) {
	  		$basis->DataBase->query("ALTER TABLE " . TABLE_CONTACTS . " CHANGE short_name short_name VARCHAR(32) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL DEFAULT ''");
		  	if ($basis->DataBase->table_exists(DB_PREFIX . 'contacts_extra_fields')) $basis->DataBase->exec("DROP TABLE " . DB_PREFIX . "contacts_extra_fields");
		}
		if (version_compare($this->status, '3.5', '<') ) {
	  		if ( $basis->DataBase->field_exists(TABLE_CURRENT_STATUS, 'next_cust_id_desc')) $basis->DataBase->exec("ALTER TABLE " . TABLE_CURRENT_STATUS . " DROP next_cust_id_desc");
	  		if ( $basis->DataBase->field_exists(TABLE_CURRENT_STATUS, 'next_vend_id_desc')) $basis->DataBase->exec("ALTER TABLE " . TABLE_CURRENT_STATUS . " DROP next_vend_id_desc");
	  		if (!$basis->DataBase->field_exists(TABLE_CONTACTS, 'attachments')) $basis->DataBase->exec("ALTER TABLE " . TABLE_CONTACTS . " ADD attachments TEXT NOT NULL AFTER tax_id");
    	}
    	if (version_compare($this->status, '3.7', '<') ) {
      		if (!$basis->DataBase->field_exists(TABLE_CONTACTS_LOG, 'entered_by')) $basis->DataBase->exec("ALTER TABLE ".TABLE_CONTACTS_LOG." ADD entered_by INT(11) NOT NULL DEFAULT '0' AFTER contact_id");
    	}
		if (!$basis->DataBase->field_exists(TABLE_CURRENT_STATUS, 'next_crm_id_num')){
    		$result = $basis->DataBase->exec("Select MAX(short_name + 1) AS new  FROM ".TABLE_CONTACTS." WHERE TYPE = 'i'");
			$basis->DataBase->exec("ALTER TABLE ".TABLE_CURRENT_STATUS." ADD next_crm_id_num VARCHAR( 16 ) NOT NULL DEFAULT '{$result->fields['new']}';");
		}
		if (version_compare($this->status, '4.0', '<') ) {
			if (!$basis->DataBase->field_exists(TABLE_CONTACTS, 'class')) $basis->DataBase->exec("ALTER TABLE ".TABLE_CONTACTS." ADD class VARCHAR( 255 ) NOT NULL DEFAULT '' FIRST");
			$sql = $basis->DataBase->exec("UPDATE ".TABLE_CONTACTS." SET class = CONCAT('contacts\\\\classes\\\\type\\\\', type) WHERE class = '' ");
		}
		\core\classes\fields::sync_fields('contacts', TABLE_CONTACTS);
  	}

	function delete($path_my_files) {
	    global $admin;
	    parent::delete($path_my_files);
	    if ($admin->DataBase->field_exists(TABLE_CURRENT_STATUS, 'next_cust_id_num'))  $admin->DataBase->exec("ALTER TABLE " . TABLE_CURRENT_STATUS . " DROP next_cust_id_num");
		if ($admin->DataBase->field_exists(TABLE_CURRENT_STATUS, 'next_cust_id_desc')) $admin->DataBase->exec("ALTER TABLE " . TABLE_CURRENT_STATUS . " DROP next_cust_id_desc");
	    if ($admin->DataBase->field_exists(TABLE_CURRENT_STATUS, 'next_vend_id_num'))  $admin->DataBase->exec("ALTER TABLE " . TABLE_CURRENT_STATUS . " DROP next_vend_id_num");
		if ($admin->DataBase->field_exists(TABLE_CURRENT_STATUS, 'next_vend_id_desc')) $admin->DataBase->exec("ALTER TABLE " . TABLE_CURRENT_STATUS . " DROP next_vend_id_desc");
		if ($admin->DataBase->field_exists(TABLE_CURRENT_STATUS, 'next_crm_id_desc')) $admin->DataBase->exec("ALTER TABLE " . TABLE_CURRENT_STATUS . " DROP next_crm_id_desc");
		$admin->DataBase->exec("DELETE FROM " . TABLE_EXTRA_FIELDS . " WHERE module_id = 'contacts'");
		$admin->DataBase->exec("DELETE FROM " . TABLE_EXTRA_TABS   . " WHERE module_id = 'contacts'");
	}

	function load_reports() {
		$id = $this->add_report_heading(TEXT_CUSTOMERS,   'cust');
		$this->add_report_folder($id, TEXT_REPORTS,       'cust', 'fr');
		$id = $this->add_report_heading(TEXT_EMPLOYEES,   'hr');
		$this->add_report_folder($id, TEXT_REPORTS,       'hr',   'fr');
		$id = $this->add_report_heading(TEXT_VENDORS,     'vend');
		$this->add_report_folder($id, TEXT_REPORTS,       'vend', 'fr');
		parent::load_reports();
	}

	/**
	 * this function will load the contact manager page
	 */
	function LoadContactMgrPage(\core\classes\basis &$basis) {
		\core\classes\messageStack::debug_log("executing ".__METHOD__ );
		$basis->observer->send_menu($basis);
		if (! isset($basis->cInfo->type)) $basis->cInfo->type = 'c'; // default to customer
		switch ($basis->cInfo->type) {
			case 'b': $contact = TEXT_BRANCH;	break;
			case 'c': $contact = TEXT_CUSTOMER;	break;
			case 'e': $contact = TEXT_EMPLOYEE;	break;
			case 'i': $contact = TEXT_CRM;		break;
			case 'j': $contact = TEXT_PROJECT;	break;
			case 'v': $contact = TEXT_VENDOR;	break;
		}
		?>
		    <table id="dg" title="<?php echo sprintf(TEXT_MANAGER_ARGS, $contact);?>" class="easyui-datagrid" style="height:500px;padding:50px;">
        		<thead>
            		<tr>
            			<th data-options="field:'short_name',sortable:true"><?php echo sprintf(TEXT_ARGS_ID, $contact);?></th>
                		<th data-options="field:'name',sortable:true"><?php echo TEXT_NAME_OR_COMPANY?></th>
	                	<th data-options="field:'address1',sortable:true"><?php echo TEXT_ADDRESS1?></th>
    	            	<th data-options="field:'city_town',sortable:true"><?php echo TEXT_CITY_TOWN?></th>
        	        	<th data-options="field:'state_province',sortable:true"><?php echo TEXT_STATE_PROVINCE?></th>
        	        	<th data-options="field:'postal_code',sortable:true"><?php echo TEXT_POSTAL_CODE?></th>
        	        	<th data-options="field:'telephone1',sortable:true"><?php echo TEXT_TELEPHONE?></th>
            		</tr>
        		</thead>
    		</table>
    		<div id="toolbar">
		        <a href="#" class="easyui-linkbutton" iconCls="icon-add" plain="true" onclick="newUser()">New User</a>
    		    <a href="#" class="easyui-linkbutton" iconCls="icon-edit" plain="true" onclick="editUser()">Edit User</a>
        		<a href="#" class="easyui-linkbutton" iconCls="icon-remove" plain="true" onclick="destroyUser()">Remove User</a>
        		<span style="margin-left: 100px;"><?php echo  TEXT_SHOW_INACTIVE . ' :'?></span>
        		<?php echo html_checkbox_field('contact_show_inactive', '1', false,'', 'onchange="doSearch()"' );?>
        		<div style="float: right;">
        			<span><?php echo TEXT_SEARCH?> : </span>
    				<input id="search_text" style="line-height:26px;border:1px solid #ccc">
    				<?php echo html_icon('actions/system-search.png', TEXT_SEARCH, 'small', 'onclick="doSearch()"');?>
<!--     				<a href="#" class="easyui-linkbutton" onclick="doSearch()"><?php echo TEXT_SEARCH?></a> -->
    			</div>
    		</div>
		<script type="text/javascript">
	    	function doSearch(){
	        	$('#dg').datagrid('load',{
	        		search_text: $('#search_text').val(),
	        		dataType: 'json',
	                contentType: 'application/json',
	                type: '<?php echo $basis->cInfo->type;?>',
	                contact_show_inactive: $('#contact_show_inactive').is(":checked") ? 1 : 0,
	        	});
	    	}
		
			$('#dg').datagrid({
				url:		"index.php?action=GetAllContacts",
				queryParams: {
					type: '<?php echo $basis->cInfo->type;?>',
					dataType: 'json',
	                contentType: 'application/json',
				},
				onLoadSuccess:function(data){
					if(data.total == 0) $.messager.alert('<?php echo TEXT_ERROR?>',"<?php echo TEXT_NO_RESULTS_FOUND?>");
				},
				onLoadError: function(arguments){
					$.messager.alert('<?php echo TEXT_ERROR?>','Load error:'+arguments.responseText);
				},
				remoteSort:	false,
				idField:	"contactid",
				fitColumns:	true,
				singleSelect:true,
				sortName:	"short_name",
				sortOrder: 	"desc",
				loadMsg:	"<?php echo TEXT_PLEASE_WAIT?>",
				toolbar: 	"#toolbar",
				rowStyler: function(index,row){
					if (row.inactive == '1')return 'background-color:pink;';
				},
			});
		</script><?php 
		$basis->observer->send_footer($basis);
	}
	
	function GetAllContacts (\core\classes\basis &$basis) {
		\core\classes\messageStack::debug_log("executing ".__METHOD__ );
		\core\classes\messageStack::debug_log(print_r($_REQUEST,true) );
		$criteria[] = "a.type = '{$basis->cInfo->type}m'";
		if (isset($basis->cInfo->search_text) && $basis->cInfo->search_text <> '') {
			$search_fields = array('a.primary_name', 'a.contact', 'a.telephone1', 'a.telephone2', 'a.address1',
					'a.address2', 'a.city_town', 'a.postal_code', 'c.short_name');
			// hook for inserting new search fields to the query criteria.
			if (is_array($extra_search_fields)) $search_fields = array_merge($search_fields, $extra_search_fields);
			$criteria[] = '(' . implode(" like '%{$basis->cInfo->search_text}%' or ", $search_fields) . " like '%{$basis->cInfo->search_text}%')";
		}
		if (!$basis->cInfo->contact_show_inactive) $criteria[] = "(c.inactive = '0' or c.inactive = '')"; // inactive flag
		$search = (sizeof($criteria) > 0) ? (' where ' . implode(' and ', $criteria)) : '';
		$query_raw = "SELECT id as contactid, short_name, CASE c.type WHEN 'e' THEN CONCAT(contact_first , ' ',contact_last) ELSE primary_name END AS name, address1, city_town, state_province, postal_code, telephone1, inactive FROM contacts c LEFT JOIN address_book a ON c.id = a.ref_id $search ORDER BY {$basis->cInfo->sort} {$basis->cInfo->order}";
		$sql = $basis->DataBase->prepare($query_raw);
		$sql->execute();
		$results = $sql->fetchAll(\PDO::FETCH_ASSOC);
		$temp = array();
		$temp["total"] = sizeof($results);
		$temp["rows"] = $results;
		echo json_encode($temp);
	}
	
	/**
	 * will create new contact depending on type
	 * @param unknown $basis
	 */
	function NewContact (\core\classes\basis &$basis) {
		$temp = "\\contacts\\classes\\type\\{$basis->cInfo->type}";
		$contact = new $temp();
		\core\classes\user::validate_security($contact->security_level, 2);
		if (! isset($basis->cInfo->type)) $basis->cInfo->type = 'c'; // default to customer
		$sql = $basis->DataBase->prepare("INSERT INTO ".TABLE_CONTACTS." (class, type ) VALUES ('" . addcslashes(get_class($contact), '\\') . "', '{$contact->type}')");
		$sql->execute();
		$basis->cInfo->cID =  $basis->DataBase->lastInsertId('id');
		$this->LoadContactPage($basis);
	}

	/**
	 * this function will load the contact page
	 */
	function LoadContactPage(\core\classes\basis &$basis) {
		$basis->observer->send_menu($basis);
		if ( isset($basis->cInfo->rowSeq)) $basis->cInfo->cID = $basis->cInfo->rowSeq;
		if ($basis->cInfo->cID == '') throw new \core\classes\userException("cID variable isn't set can't execute method LoadContactPage ");
		$sql = $basis->DataBase->prepare("SELECT * FROM " . TABLE_CONTACTS . " WHERE id = {$basis->cInfo->cID}");
		$sql->execute();
		$basis->cInfo->contact = $sql->fetch(\PDO::FETCH_CLASS | \PDO::FETCH_CLASSTYPE);

		for ($i = 1; $i < 13; $i++) {
			$j = ($i < 10) ? '0' . $i : $i;
			$basis->cInfo->expires_month[] = array('id' => sprintf('%02d', $i), 'text' => $j . '-' . strftime('%B',mktime(0,0,0,$i,1,2000)));
		}
		$today = getdate();
		for ($i = $today['year']; $i < $today['year'] + 10; $i++) {
			$year = strftime('%Y',mktime(0,0,0,1,1,$i));
			$basis->cInfo->expires_year[] = array('id' => $year, 'text' => $year);
		}
		// load the tax rates
		$basis->cInfo->tax_rates       = ord_calculate_tax_drop_down($basis->cInfo->contact->type, true);
		$result = $basis->DataBase->prepare("SELECT id, contact_first, contact_last FROM ".TABLE_CONTACTS." WHERE type='e'");
		$sql->execute();
		$basis->cInfo->all_employees       = array();
		while ($result = $sql->fetch(\PDO::FETCH_LAZY)){
			$basis->cInfo->all_employees[$result['id']] = $result['contact_first'] . ' ' . $result['contact_last'];
		}
		$basis->module		= 'contacts';
		$basis->page		= 'main';
		$basis->template 	= 'template_detail';
		$basis->page_title  = "{$basis->cInfo->contact->page_title_edit} - ({$basis->cInfo->contact->short_name}) {$basis->cInfo->contact->address[m][0]->primary_name}";
		$basis->observer->send_footer($basis);
	}

	/**
	 * this function will call LoadContactPage but deactivate menu and footer.
	 * @param \core\classes\basis $basis
	 */
	function LoadContactsPopUp(\core\classes\basis &$basis) {
		$this->LoadContactPage($basis);
	}

	function SaveContact (\core\classes\basis &$basis) {
		if ($basis->cInfo->id == '') throw new \core\classes\userException("id variable isn't set can't execute method SaveContact ");
		if ($_POST['crm_date']) $_POST['crm_date'] = gen_db_date($_POST['crm_date']);
		if ($_POST['due_date']) $_POST['due_date'] = gen_db_date($_POST['due_date']);
		$sql = $basis->DataBase->prepare("SELECT * FROM " . TABLE_CONTACTS . " WHERE id = {$basis->cInfo->id}");
		$sql->execute();
		$basis->cInfo->contact = $sql->fetch(\PDO::FETCH_CLASS | \PDO::FETCH_CLASSTYPE);
		// error check
		$basis->cInfo->contact->data_complete();
		// start saving data
		$basis->cInfo->contact->save();
		if ($basis->cInfo->contact->type <> 'i' && ($_POST['i_short_name'] || $_POST['address']['im']['primary_name'])) { // is null
			$crmInfo = new \contacts\classes\type\i;
			$crmInfo->dept_rep_id = $basis->cInfo->contact->id;
			// error check contact
			$crmInfo->data_complete();
			$crmInfo->save();
		}
		// Check attachments
		$result = $admin->DataBase->query("SELECT attachments FROM ".TABLE_CONTACTS." WHERE id = {$basis->cInfo->contact->id}");
		$attachments = $result['attachments'] ? unserialize($result['attachments']) : array();
		for ($image_id = 0; $image_id <= sizeof($attachments); $image_id++) {
			if (isset($_POST['rm_attach_'.$image_id])) {
				@unlink("{$this->dir_attachments}contacts_{$basis->cInfo->contact->id}_{$image_id}.zip");
				unset($attachments[$image_id]);
			}
		}
		if (is_uploaded_file($_FILES['file_name']['tmp_name'])) { // find an image slot to use
			$image_id = 0;
			while (true) {
				if (!file_exists("{$this->dir_attachments}contacts_{$basis->cInfo->contact->id}_{$image_id}.zip")) break;
				$image_id++;
			}
			saveUploadZip('file_name', "{$this->dir_attachments}contacts_{$basis->cInfo->contact->id}_{$image_id}.zip");
			$attachments[$image_id] = $_FILES['file_name']['name'];
		}
		$sql = $admin->DataBase->prepare("UPDATE ".TABLE_CONTACTS." SET attachments = '".serialize($attachments). "' WHERE id = {$basis->cInfo->contact->id}");
		$sql->execute();
		// check for crm notes
		if ($_POST['crm_action'] <> '' || $_POST['crm_note'] <> '') {
			$sql_data_array = array(
					'contact_id' => $basis->cInfo->contact->id,
					'log_date'   => $_POST['crm_date'],
					'entered_by' => $_POST['crm_rep_id'],
					'action'     => $_POST['crm_action'],
					'notes'      => db_prepare_input($_POST['crm_note']),
			);
			$sql = $basis->Database->prepare("INSERT INTO " . TABLE_CONTACTS_LOG . " SET (contact_id, log_date, entered_by, action, notes) VALUES (:contact_id, :log_date, :entered_by, :action, :notes)");
			$sql->execute($sql_data_array);
		}
		$basis->addEventToStack('LoadContactMgrPage');
	}

	function DeleteContact (\core\classes\basis &$basis) {
		if ($basis->cInfo->cID == '') throw new \core\classes\userException("cID variable isn't set can't execute method DeleteContact ");
		$sql = $basis->DataBase->prepare("SELECT * FROM " . TABLE_CONTACTS . " WHERE id = {$basis->cInfo->cID}");
		$sql->execute();
		$basis->cInfo->contact = $sql->fetch(\PDO::FETCH_CLASS | \PDO::FETCH_CLASSTYPE);
		// error check
		$basis->cInfo->contact->delete();
		$basis->addEventToStack('LoadContactMgrPage');
	}

	function ContactAttachmentDownload (\core\classes\basis &$basis) {
		$cID   = db_prepare_input($_POST['id']);
		$imgID = db_prepare_input($_POST['rowSeq']);
		$filename = "contacts_{$cID}_{$imgID}.zip";
		if (file_exists($this->dir_attachments . $filename)) {
			$backup = new \phreedom\classes\backup();
			$backup->download($this->dir_attachments, $filename, true);
		}
	}

	function ContactAttachmentDownloadFirst (\core\classes\basis &$basis) {
	//	case 'dn_attach': // download from list, assume the first document only
		$cID   = db_prepare_input($_POST['rowSeq']);
		$result = $admin->DataBase->query("SELECT attachments FROM ".TABLE_CONTACTS." WHERE id = $cID");
		$attachments = unserialize($result['attachments']);
		foreach ($attachments as $key => $value) {
			$filename = "contacts_{$cID}_{$key}.zip";
			if (file_exists($this->dir_attachments . $filename)) {
				$backup = new \phreedom\classes\backup();
				$backup->download($this->dir_attachments, $filename, true);
			}
		}
	}

	function LoadContactsAccountsPopUp(\core\classes\basis &$basis) {
		history_filter('contacts_popup');
		$this->LoadContactMgrPage($basis);
		if (!isset($basis->cInfo->fill)) $basis->cInfo->fill = 'bill';
		$basis->page			= 'popup_accts';
		$basis->page_title		= TEXT_CONTACT_SEARCH;
		history_save('contacts_popup');
	}

	function LoadTermsPopUp (\core\classes\basis &$basis) {
		$temp = "\\contacts\\classes\\type\\{$basis->cInfo->type}";
		$basis->cInfo->contact = new $temp;
		$basis->cInfo->cal_terms = array(
				'name'      => 'dateReference',
				'form'      => 'popup_terms',
				'fieldname' => 'due_date',
				'imagename' => 'btn_terms',
				'default'   => '',
				'params'    => array('align' => 'left'),
		);

		$basis->page			= 'popup_terms';
		$basis->page_title 		= TEXT_PAYMENT_TERMS;
	}
	
	function ContactGetChartData (\core\classes\basis &$basis) {
		$basis->cInfo->type  = pie;
		$basis->cInfo->width  = 600;
		$basis->cInfo->height  = 400;
		switch ($basis->cInfo->fID) {
			case 'annual_sales':
				$basis->cInfo->type       = 'column';
				$basis->cInfo->title      = TEXT_MONTHLY_SALES;
				$basis->cInfo->label_text = TEXT_DATE;
				$basis->cInfo->value_text = TEXT_TOTAL;
				if (!$basis->cInfo->cID)  throw new \core\classes\userException('There is no contact id');
				$date = new \core\classes\DateTime();
				$date->modify("-1 year");
				$date->modify("first day of this month");
				$sql = $basis->DataBase->prepare("SELECT month(post_date) as month, year(post_date) as year, sum(total_amount) as total FROM ".TABLE_JOURNAL_MAIN."
						WHERE bill_acct_id = {$basis->cInfo->cID} and journal_id in (12,13) and post_date >= '".$date->format('Y-m-d')."' group by year, month LIMIT 12");
				$sql->execute();
				for ($i=0; $i<12; $i++) {
					$result = $sql->fetch(\PDO::FETCH_LAZY);
					if ($result['year'] == $date->format('Y') && $result['month'] == $date->format('m')) {
						$value = $result['total'];
					} else {
						$value = 0;
					}
					$basis->cInfo->data[] = array(
							'label' => $date->format('Y-m'),
							'value' => $value,
					);
					$date->modify("first day of next month");
				}
				break;
			default:
				throw new \core\classes\userException("Don't know report {$basis->cInfo->fID}");
		}
	}
	

	function load_demo() {
		global $admin;
		// Data for table `address_book` @todo add class to sql and keys
		$admin->DataBase->query("TRUNCATE TABLE " . TABLE_ADDRESS_BOOK);
		$admin->DataBase->query("INSERT INTO " . TABLE_ADDRESS_BOOK . " VALUES (1, 1, 'vm', 'Obscure Video', '', '1354 Triple A Ave', '', 'Chatsworth', 'CA', '93245', 'USA', '800.345.5678', '', '', '', 'obsvid@obscurevideo.com', '', '');");
		$admin->DataBase->query("INSERT INTO " . TABLE_ADDRESS_BOOK . " VALUES (2, 2, 'cm', 'CompuHouse Computer Systems', '', '8086 Intel Ave', '', 'San jose', 'CA', '94354', 'USA', '800-555-1234', '', '', '', 'sales@compuhouse.com', '', '');");
		$admin->DataBase->query("INSERT INTO " . TABLE_ADDRESS_BOOK . " VALUES (3, 3, 'vm', 'Speedy Electronics, Inc.', '', '777 Lucky Street', 'Unit #2B', 'San Jose', 'CA', '92666', 'USA', '802-555-9876', '', '', '', 'custserv@speedyelec.com', '', '');");
		$admin->DataBase->query("INSERT INTO " . TABLE_ADDRESS_BOOK . " VALUES (4, 4, 'cm', 'Computer Repair Services', '', '12932 136th Ave.', 'Suite A', 'Denver', 'CO', '80021', 'USA', '303-555-5469', '', '', '', 'servive@comprepair.net', '', '');");
		$admin->DataBase->query("INSERT INTO " . TABLE_ADDRESS_BOOK . " VALUES (5, 5, 'vm', 'LCDisplays Corp.', '', '28973 Pixel Place', '', 'Los Angeles', 'CA', '90001', 'USA', '800-555-5548', '', '', '', 'cs@lcdisplays.com', '', '');");
		$admin->DataBase->query("INSERT INTO " . TABLE_ADDRESS_BOOK . " VALUES (6, 6, 'vm', 'Big Box Corp', '', '11 Square St', '', 'Longmont', 'CO', '80501', 'USA', '303-555-9652', '', '', '', 'big.box@yahoo.com', '', '');");
		$admin->DataBase->query("INSERT INTO " . TABLE_ADDRESS_BOOK . " VALUES (7, 7, 'cm', 'John Smith Jr.', '', '13546 Euclid Ave', '', 'Ontario', 'CA', '92775', 'USA', '818-555-1000', '', '', '', 'jsmith@aol.com', '', '');");
		$admin->DataBase->query("INSERT INTO " . TABLE_ADDRESS_BOOK . " VALUES (8, 8, 'cm', 'Jim Baker', '', '995 Maple Street', 'Unit #56', 'Northglenn', 'CO', '80234', 'USA', 'unlisted', '', '', '', 'jb@hotmail.com', '', '');");
		$admin->DataBase->query("INSERT INTO " . TABLE_ADDRESS_BOOK . " VALUES (9, 9, 'cm', 'Lisa Culver', '', '1005 Gillespie Dr', '', 'Boulder', 'CO', '80303', 'USA', '303-555-6677', '', '', '', 'lisa@myveryownemailaddress.net', '', '');");
		$admin->DataBase->query("INSERT INTO " . TABLE_ADDRESS_BOOK . " VALUES (10, 10, 'cm', 'Parts Locator LLC', '', '55 Sydney Hwy', '', 'Deerfield Beach', 'FL', '33445', 'USA', '215-555-0987', '', '', '', 'parts@partslocator.com', '', '');");
		$admin->DataBase->query("INSERT INTO " . TABLE_ADDRESS_BOOK . " VALUES (11, 11, 'vm', 'Accurate Input, LLC', '', '1111 Stuck Key Ave', '', 'Burbank', 'CA', '91505', 'USA', '800-555-1267', '', '818-555-5555', '', 'sales@accurate.com', 'www.AccurateInput.com', '');");
		$admin->DataBase->query("INSERT INTO " . TABLE_ADDRESS_BOOK . " VALUES (12, 12, 'vm', 'BackMeUp Systems, Inc', '', '1324 44th Ave.', '', 'New York', 'NY', '10019', 'USA', '212-555-9854', '', '', '', 'sales@backmeup.com', '', '');");
		$admin->DataBase->query("INSERT INTO " . TABLE_ADDRESS_BOOK . " VALUES (13, 13, 'vm', 'Closed Cases', 'Fernando', '23 Frontage Rd', '', 'New York', 'NY', '10019', 'USA', '888-555-6322', '800-555-5716', '', '', 'custserv@closedcases.net', '', '');");
		$admin->DataBase->query("INSERT INTO " . TABLE_ADDRESS_BOOK . " VALUES (14, 14, 'vm', 'MegaWatts Power Supplies', '', '11 Joules St.', '', 'Denver', 'CO', '80234', 'USA', '303-222-5617', '', '', '', 'help@hotmail.com', '', '');");
		$admin->DataBase->query("INSERT INTO " . TABLE_ADDRESS_BOOK . " VALUES (15, 15, 'vm', 'Slipped Disk Corp.', 'Accts. Receivable', '1234 Main St', 'Suite #1', 'La Verne', 'CA', '91750', 'USA', '714-555-0001', '', '', '', 'sales@slippedisks.com', '', '');");
		$admin->DataBase->query("INSERT INTO " . TABLE_ADDRESS_BOOK . " VALUES (16, 16, 'em', 'John Smith', '', '123 Birch Ave', 'Apt 12', 'Anytown', 'CO', '80234', 'USA', '303-555-3451', '', '', '', 'john@mycompany.com', '', '');");
		$admin->DataBase->query("INSERT INTO " . TABLE_ADDRESS_BOOK . " VALUES (17, 17, 'em', 'Mary Johnson', '', '6541 First St', '', 'Anytown', 'CO', '80234', 'USA', '303-555-7426', '', '', '', 'nary@mycomapny.com', '', '');");
		// Data for table `contacts`
		$admin->DataBase->query("TRUNCATE TABLE " . TABLE_CONTACTS);
		$admin->DataBase->query("INSERT INTO " . TABLE_CONTACTS . " (`id`, `type`, `short_name`, `inactive`, `contact_first`, `contact_middle`, `contact_last`, `store_id`, `gl_type_account`, `gov_id_number`, `dept_rep_id`, `account_number`, `special_terms`, `price_sheet`, `tax_id`, `attachments`, `first_date`, `last_update`, `last_date_1`, `last_date_2`) VALUES (1, 'v', 'Obscure Video', '0', '', '', '', '', '2000', '', '', '', '3:1:10:30:2500.00', '', '0', '', now(), NULL, NULL, NULL);");
		$admin->DataBase->query("INSERT INTO " . TABLE_CONTACTS . " (`id`, `type`, `short_name`, `inactive`, `contact_first`, `contact_middle`, `contact_last`, `store_id`, `gl_type_account`, `gov_id_number`, `dept_rep_id`, `account_number`, `special_terms`, `price_sheet`, `tax_id`, `attachments`, `first_date`, `last_update`, `last_date_1`, `last_date_2`) VALUES (2, 'c', 'CompuHouse', '0', '', '', '', '', '4000', '', '', '', '0::::2500.00', '', '0', '', now(), NULL, NULL, NULL);");
		$admin->DataBase->query("INSERT INTO " . TABLE_CONTACTS . " (`id`, `type`, `short_name`, `inactive`, `contact_first`, `contact_middle`, `contact_last`, `store_id`, `gl_type_account`, `gov_id_number`, `dept_rep_id`, `account_number`, `special_terms`, `price_sheet`, `tax_id`, `attachments`, `first_date`, `last_update`, `last_date_1`, `last_date_2`) VALUES (3, 'v', 'Speedy Electronics', '0', '', '', '', '', '2000', '', '', '', '0::::2500.00', '', '0', '', now(), NULL, NULL, NULL);");
		$admin->DataBase->query("INSERT INTO " . TABLE_CONTACTS . " (`id`, `type`, `short_name`, `inactive`, `contact_first`, `contact_middle`, `contact_last`, `store_id`, `gl_type_account`, `gov_id_number`, `dept_rep_id`, `account_number`, `special_terms`, `price_sheet`, `tax_id`, `attachments`, `first_date`, `last_update`, `last_date_1`, `last_date_2`) VALUES (4, 'c', 'Computer Repair', '0', '', '', '', '', '4000', '', '', '', '0::::2500.00', '', '0', '', now(), NULL, NULL, NULL);");
		$admin->DataBase->query("INSERT INTO " . TABLE_CONTACTS . " (`id`, `type`, `short_name`, `inactive`, `contact_first`, `contact_middle`, `contact_last`, `store_id`, `gl_type_account`, `gov_id_number`, `dept_rep_id`, `account_number`, `special_terms`, `price_sheet`, `tax_id`, `attachments`, `first_date`, `last_update`, `last_date_1`, `last_date_2`) VALUES (5, 'v', 'LCDisplays', '0', '', '', '', '', '2000', '', '', '', '0::::2500.00', '', '0', '', now(), NULL, NULL, NULL);");
		$admin->DataBase->query("INSERT INTO " . TABLE_CONTACTS . " (`id`, `type`, `short_name`, `inactive`, `contact_first`, `contact_middle`, `contact_last`, `store_id`, `gl_type_account`, `gov_id_number`, `dept_rep_id`, `account_number`, `special_terms`, `price_sheet`, `tax_id`, `attachments`, `first_date`, `last_update`, `last_date_1`, `last_date_2`) VALUES (6, 'v', 'Big Box', '0', '', '', '', '', '2000', '', '', '', '0::::2500.00', '', '0', '', now(), NULL, NULL, NULL);");
		$admin->DataBase->query("INSERT INTO " . TABLE_CONTACTS . " (`id`, `type`, `short_name`, `inactive`, `contact_first`, `contact_middle`, `contact_last`, `store_id`, `gl_type_account`, `gov_id_number`, `dept_rep_id`, `account_number`, `special_terms`, `price_sheet`, `tax_id`, `attachments`, `first_date`, `last_update`, `last_date_1`, `last_date_2`) VALUES (7, 'c', 'Smith, John', '0', '', '', '', '', '4000', '', '', '', '0::::2500.00', '', '0', '', now(), NULL, NULL, NULL);");
		$admin->DataBase->query("INSERT INTO " . TABLE_CONTACTS . " (`id`, `type`, `short_name`, `inactive`, `contact_first`, `contact_middle`, `contact_last`, `store_id`, `gl_type_account`, `gov_id_number`, `dept_rep_id`, `account_number`, `special_terms`, `price_sheet`, `tax_id`, `attachments`, `first_date`, `last_update`, `last_date_1`, `last_date_2`) VALUES (8, 'c', 'JimBaker', '0', '', '', '', '', '4000', '', '', '', '0::::2500.00', '', '0', '', now(), NULL, NULL, NULL);");
		$admin->DataBase->query("INSERT INTO " . TABLE_CONTACTS . " (`id`, `type`, `short_name`, `inactive`, `contact_first`, `contact_middle`, `contact_last`, `store_id`, `gl_type_account`, `gov_id_number`, `dept_rep_id`, `account_number`, `special_terms`, `price_sheet`, `tax_id`, `attachments`, `first_date`, `last_update`, `last_date_1`, `last_date_2`) VALUES (9, 'c', 'Culver', '0', '', '', '', '', '4000', '', '', '', '0::::2500.00', '', '0', '', now(), NULL, NULL, NULL);");
		$admin->DataBase->query("INSERT INTO " . TABLE_CONTACTS . " (`id`, `type`, `short_name`, `inactive`, `contact_first`, `contact_middle`, `contact_last`, `store_id`, `gl_type_account`, `gov_id_number`, `dept_rep_id`, `account_number`, `special_terms`, `price_sheet`, `tax_id`, `attachments`, `first_date`, `last_update`, `last_date_1`, `last_date_2`) VALUES (10, 'c', 'PartsLocator', '0', '', '', '', '', '4000', '', '', '', '3:0:10:30:2500.00', '', '0', '', now(), NULL, NULL, NULL);");
		$admin->DataBase->query("INSERT INTO " . TABLE_CONTACTS . " (`id`, `type`, `short_name`, `inactive`, `contact_first`, `contact_middle`, `contact_last`, `store_id`, `gl_type_account`, `gov_id_number`, `dept_rep_id`, `account_number`, `special_terms`, `price_sheet`, `tax_id`, `attachments`, `first_date`, `last_update`, `last_date_1`, `last_date_2`) VALUES (11, 'v', 'Accurate Input', '0', '', '', '', '', '2000', '', '', 'SK200706', '3:0:10:30:2500.00', '', '0', '', now(), NULL, NULL, NULL);");
		$admin->DataBase->query("INSERT INTO " . TABLE_CONTACTS . " (`id`, `type`, `short_name`, `inactive`, `contact_first`, `contact_middle`, `contact_last`, `store_id`, `gl_type_account`, `gov_id_number`, `dept_rep_id`, `account_number`, `special_terms`, `price_sheet`, `tax_id`, `attachments`, `first_date`, `last_update`, `last_date_1`, `last_date_2`) VALUES (12, 'v', 'BackMeUp', '0', '', '', '', '', '2000', '', '', '', '0::::2500.00', '', '0', '', now(), NULL, NULL, NULL);");
		$admin->DataBase->query("INSERT INTO " . TABLE_CONTACTS . " (`id`, `type`, `short_name`, `inactive`, `contact_first`, `contact_middle`, `contact_last`, `store_id`, `gl_type_account`, `gov_id_number`, `dept_rep_id`, `account_number`, `special_terms`, `price_sheet`, `tax_id`, `attachments`, `first_date`, `last_update`, `last_date_1`, `last_date_2`) VALUES (13, 'v', 'Closed Cases', '0', '', '', '', '', '2000', '', '', '', '0::::2500.00', '', '0', '', now(), NULL, NULL, NULL);");
		$admin->DataBase->query("INSERT INTO " . TABLE_CONTACTS . " (`id`, `type`, `short_name`, `inactive`, `contact_first`, `contact_middle`, `contact_last`, `store_id`, `gl_type_account`, `gov_id_number`, `dept_rep_id`, `account_number`, `special_terms`, `price_sheet`, `tax_id`, `attachments`, `first_date`, `last_update`, `last_date_1`, `last_date_2`) VALUES (14, 'v', 'MegaWatts', '0', '', '', '', '', '2000', '', '', 'MW20070301', '0::::2500.00', '', '0', '', now(), NULL, NULL, NULL);");
		$admin->DataBase->query("INSERT INTO " . TABLE_CONTACTS . " (`id`, `type`, `short_name`, `inactive`, `contact_first`, `contact_middle`, `contact_last`, `store_id`, `gl_type_account`, `gov_id_number`, `dept_rep_id`, `account_number`, `special_terms`, `price_sheet`, `tax_id`, `attachments`, `first_date`, `last_update`, `last_date_1`, `last_date_2`) VALUES (15, 'v', 'Slipped Disk', '0', '', '', '', '', '2000', '', '', '', '0::::2500.00', '', '', '', now(), NULL, NULL, NULL);");
		$admin->DataBase->query("INSERT INTO " . TABLE_CONTACTS . " (`id`, `type`, `short_name`, `inactive`, `contact_first`, `contact_middle`, `contact_last`, `store_id`, `gl_type_account`, `gov_id_number`, `dept_rep_id`, `account_number`, `special_terms`, `price_sheet`, `tax_id`, `attachments`, `first_date`, `last_update`, `last_date_1`, `last_date_2`) VALUES (16, 'e', 'John', '0', 'John', '', 'Smith', '', 'b', '', 'Sales', '', '::::', '', '0', '', now(), NULL, NULL, NULL);");
		$admin->DataBase->query("INSERT INTO " . TABLE_CONTACTS . " (`id`, `type`, `short_name`, `inactive`, `contact_first`, `contact_middle`, `contact_last`, `store_id`, `gl_type_account`, `gov_id_number`, `dept_rep_id`, `account_number`, `special_terms`, `price_sheet`, `tax_id`, `attachments`, `first_date`, `last_update`, `last_date_1`, `last_date_2`) VALUES (17, 'e', 'Mary', '0', 'Mary', '', 'Johnson', '', 'e', '', 'Accounting', '', '::::', '', '0', '', now(), NULL, NULL, NULL);");
		// Data for table `departments`
		$admin->DataBase->query("TRUNCATE TABLE " . TABLE_DEPARTMENTS);
		$admin->DataBase->query("INSERT INTO " . TABLE_DEPARTMENTS . " VALUES ('1', 'Sales', 'Sales', '0', '', 2, '0');");
		$admin->DataBase->query("INSERT INTO " . TABLE_DEPARTMENTS . " VALUES ('2', 'Administration', 'Administration and Operations', '0', '', 1, '0');");
		$admin->DataBase->query("INSERT INTO " . TABLE_DEPARTMENTS . " VALUES ('3', 'Accounting', 'Accounting and Finance', '0', '', 1, '0');");
		$admin->DataBase->query("INSERT INTO " . TABLE_DEPARTMENTS . " VALUES ('4', 'Shipping', 'Shipping Operation', '0', '', 4, '0');");
		$admin->DataBase->query("INSERT INTO " . TABLE_DEPARTMENTS . " VALUES ('5', 'Warehouse', 'Materials Receiving', '0', '', 4, '0');");
		// Data for table `departments_types`
		$admin->DataBase->query("TRUNCATE TABLE " . TABLE_DEPT_TYPES);
		$admin->DataBase->query("INSERT INTO " . TABLE_DEPT_TYPES . " VALUES (1, 'Administration');");
		$admin->DataBase->query("INSERT INTO " . TABLE_DEPT_TYPES . " VALUES (2, 'Sales and Marketing');");
		$admin->DataBase->query("INSERT INTO " . TABLE_DEPT_TYPES . " VALUES (3, 'Manufacturing');");
		$admin->DataBase->query("INSERT INTO " . TABLE_DEPT_TYPES . " VALUES (4, 'Shipping & Receiving');");
		parent::load_demo();
	}
}
?>